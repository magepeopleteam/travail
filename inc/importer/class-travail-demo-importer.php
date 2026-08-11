<?php
/**
 * One-click demo importer.
 *
 * Runs as a sequence of small, idempotent AJAX steps (rather than one
 * long request) so it never hits a PHP execution-time limit and so
 * progress can be shown to the user. Every created object's ID is
 * stored in the `travail_demo_import_map` option, so re-running the
 * importer detects existing items and skips them instead of creating
 * duplicates — safe to run more than once, and it never deletes
 * anything a site owner already has.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Travail_Demo_Importer
 */
class Travail_Demo_Importer {

	const MAP_OPTION = 'travail_demo_import_map';

	/**
	 * Boot.
	 */
	public static function init() {
		add_action( 'wp_ajax_travail_demo_import_step', array( __CLASS__, 'ajax_run_step' ) );
	}

	/**
	 * Ordered list of step slugs => human label. Exposed so the JS
	 * progress bar and the PHP dispatcher agree on the sequence.
	 *
	 * @return array<string, string>
	 */
	public static function get_steps() {
		return array(
			'pages'         => __( 'Creating pages', 'travail' ),
			'menus'         => __( 'Building menus', 'travail' ),
			'widgets'       => __( 'Setting up widgets', 'travail' ),
			'demo_content'  => __( 'Importing demo destinations, tours &amp; stories', 'travail' ),
			'theme_options' => __( 'Applying theme settings', 'travail' ),
			'homepage'      => __( 'Configuring homepage', 'travail' ),
		);
	}

	/**
	 * Standalone admin page (Travail → Demo Import).
	 */
	public static function render_page() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		?>
		<div class="wrap travail-admin-wrap">
			<div class="travail-admin-header">
				<h1><?php esc_html_e( 'Demo Import', 'travail' ); ?></h1>
				<p><?php esc_html_e( 'Import a starter homepage, pages, menus and widgets in one click.', 'travail' ); ?></p>
			</div>
			<div class="travail-wizard-panel" style="max-width:100%;">
				<?php self::render_import_widget(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * The reusable import button + progress log, used both by the
	 * standalone page above and the Setup Wizard's "Demo Import" step.
	 */
	public static function render_import_widget() {
		$map = get_option( self::MAP_OPTION, array() );
		?>
		<button type="button" class="button button-primary button-hero" id="travail-start-import">
			<?php echo empty( $map ) ? esc_html__( 'Import Demo Content', 'travail' ) : esc_html__( 'Re-run Demo Import', 'travail' ); ?>
		</button>

		<div class="travail-demo-import-progress" id="travail-import-progress" hidden>
			<progress id="travail-import-progress-bar" max="<?php echo esc_attr( count( self::get_steps() ) ); ?>" value="0" style="width:100%;height:14px;"></progress>
			<div class="travail-demo-import-log" id="travail-import-log"></div>
		</div>

		<script type="text/javascript">
		( function ( $ ) {
			var steps = <?php echo wp_json_encode( array_keys( self::get_steps() ) ); ?>;
			var labels = <?php echo wp_json_encode( array_values( self::get_steps() ) ); ?>;

			$( '#travail-start-import' ).on( 'click', function () {
				var $btn = $( this ).prop( 'disabled', true );
				var $progress = $( '#travail-import-progress' ).prop( 'hidden', false );
				var $bar = $( '#travail-import-progress-bar' );
				var $log = $( '#travail-import-log' );
				$log.empty();

				function runStep( index ) {
					if ( index >= steps.length ) {
						$log.append( '<p><strong><?php echo esc_js( __( 'Done! Your demo content is ready.', 'travail' ) ); ?></strong></p>' );
						$btn.prop( 'disabled', false ).text( '<?php echo esc_js( __( 'Re-run Demo Import', 'travail' ) ); ?>' );
						return;
					}

					$.post( ajaxurl, {
						action: 'travail_demo_import_step',
						nonce: '<?php echo esc_js( wp_create_nonce( 'travail_admin' ) ); ?>',
						step: steps[ index ],
					} ).done( function ( response ) {
						var message = ( response && response.data && response.data.message ) ? response.data.message : labels[ index ];
						var ok = response && response.success;
						$log.append( '<p>' + ( ok ? '✓ ' : '✕ ' ) + message + '</p>' );
						$bar.val( index + 1 );
						runStep( index + 1 );
					} ).fail( function () {
						$log.append( '<p>✕ <?php echo esc_js( __( 'Request failed — please try again.', 'travail' ) ); ?></p>' );
						$btn.prop( 'disabled', false );
					} );
				}

				runStep( 0 );
			} );
		} )( jQuery );
		</script>
		<?php
	}

	/**
	 * AJAX dispatcher — runs exactly one step per request.
	 */
	public static function ajax_run_step() {
		check_ajax_referer( 'travail_admin', 'nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'travail' ) ), 403 );
		}

		$step  = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
		$steps = self::get_steps();
		if ( ! isset( $steps[ $step ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown step.', 'travail' ) ), 400 );
		}

		$method = 'step_' . $step;
		if ( ! method_exists( __CLASS__, $method ) ) {
			wp_send_json_error( array( 'message' => __( 'Step not implemented.', 'travail' ) ), 500 );
		}

		try {
			$message = self::$method();
			wp_send_json_success( array( 'message' => $message ) );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Helper: get/set the persisted map of demo-created object IDs.
	 *
	 * @return array
	 */
	protected static function get_map() {
		return get_option( self::MAP_OPTION, array() );
	}

	/**
	 * @param array $map Updated map.
	 */
	protected static function save_map( $map ) {
		update_option( self::MAP_OPTION, $map );
	}

	/**
	 * Create-or-fetch a page by a stable "key" (not necessarily the
	 * slug, so relabeling later doesn't orphan the map), storing its ID
	 * so re-runs are idempotent.
	 *
	 * @param string $key      Stable identifier used in the import map.
	 * @param string $title    Page title.
	 * @param string $content  Page content (block markup or plain HTML).
	 * @param string $template Optional page template file (relative to theme root).
	 * @return int Page ID.
	 */
	protected static function create_or_get_page( $key, $title, $content = '', $template = '' ) {
		$map = self::get_map();

		if ( ! empty( $map['pages'][ $key ] ) && get_post( $map['pages'][ $key ] ) ) {
			return (int) $map['pages'][ $key ];
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'page',
			),
			true
		);

		if ( is_wp_error( $page_id ) ) {
			throw new Exception( esc_html( $page_id->get_error_message() ) );
		}

		if ( $template ) {
			update_post_meta( $page_id, '_wp_page_template', $template );
		}

		$map['pages'][ $key ] = $page_id;
		self::save_map( $map );

		return (int) $page_id;
	}

	/**
	 * Step: pages.
	 *
	 * @return string
	 */
	public static function step_pages() {
		$about_content   = "<!-- wp:paragraph -->\n<p>" . esc_html__( 'Tell your story here — who you are, why you started, and what makes your tours different. Edit this page any time from Pages → All Pages.', 'travail' ) . "</p>\n<!-- /wp:paragraph -->";
		$contact_content = "<!-- wp:paragraph -->\n<p>" . esc_html__( 'Add your contact details, a contact form (from your favorite form plugin) or an embedded map here.', 'travail' ) . "</p>\n<!-- /wp:paragraph -->";
		$faq_content     = "<!-- wp:paragraph -->\n<p>" . esc_html__( 'Answer the questions travelers ask most before booking.', 'travail' ) . "</p>\n<!-- /wp:paragraph -->";

		self::create_or_get_page( 'about', __( 'About Us', 'travail' ), $about_content );
		self::create_or_get_page( 'contact', __( 'Contact', 'travail' ), $contact_content );
		self::create_or_get_page( 'faq', __( 'FAQ', 'travail' ), $faq_content );
		self::create_or_get_page( 'blog', __( 'Blog', 'travail' ), '' );

		if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_tour_booking_manager_active() ) {
			self::create_or_get_page( 'destinations', __( 'Destinations', 'travail' ), '', 'templates/pages/page-destinations.php' );
		}

		return __( 'Pages created.', 'travail' );
	}

	/**
	 * Step: menus. Creates a Primary menu and (if content exists)
	 * assigns it, plus three small footer menus.
	 *
	 * @return string
	 */
	public static function step_menus() {
		$map          = self::get_map();
		$page_map     = isset( $map['pages'] ) ? $map['pages'] : array();
		$has_tbm      = class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_tour_booking_manager_active();
		$tour_archive = $has_tbm ? get_post_type_archive_link( 'ttbm_tour' ) : '';

		// Matches the wanderly.html reference nav exactly: Explore / Destinations /
		// Tours / Experiences / Deals / Blog — Experiences and Deals point at
		// anchors within the homepage's own sections rather than duplicate pages.
		$primary_items = array();
		$primary_items[] = array( 'title' => __( 'Explore', 'travail' ), 'url' => home_url( '/' ) );
		if ( ! empty( $page_map['destinations'] ) ) {
			$primary_items[] = array( 'title' => __( 'Destinations', 'travail' ), 'url' => get_permalink( $page_map['destinations'] ) );
		}
		if ( $tour_archive ) {
			$primary_items[] = array( 'title' => __( 'Tours', 'travail' ), 'url' => $tour_archive );
			$primary_items[] = array( 'title' => __( 'Experiences', 'travail' ), 'url' => home_url( '/#experiences' ) );
			$primary_items[] = array( 'title' => __( 'Deals', 'travail' ), 'url' => home_url( '/#deals' ) );
		}
		if ( ! empty( $page_map['blog'] ) ) {
			$primary_items[] = array( 'title' => __( 'Blog', 'travail' ), 'url' => get_permalink( $page_map['blog'] ) );
		}

		$primary_menu_id = self::create_or_get_menu( 'primary', __( 'Primary Menu', 'travail' ), $primary_items, 'primary' );

		$footer_1_items = array();
		if ( $tour_archive ) {
			$footer_1_items[] = array( 'title' => __( 'Tours', 'travail' ), 'url' => $tour_archive );
		}
		if ( ! empty( $page_map['destinations'] ) ) {
			$footer_1_items[] = array( 'title' => __( 'Destinations', 'travail' ), 'url' => get_permalink( $page_map['destinations'] ) );
		}
		if ( ! empty( $page_map['blog'] ) ) {
			$footer_1_items[] = array( 'title' => __( 'Blog', 'travail' ), 'url' => get_permalink( $page_map['blog'] ) );
		}
		self::create_or_get_menu( 'footer-1', __( 'Footer: Discover', 'travail' ), $footer_1_items, 'footer-1' );

		$footer_2_items = array();
		if ( ! empty( $page_map['about'] ) ) {
			$footer_2_items[] = array( 'title' => __( 'About', 'travail' ), 'url' => get_permalink( $page_map['about'] ) );
		}
		if ( ! empty( $page_map['contact'] ) ) {
			$footer_2_items[] = array( 'title' => __( 'Contact', 'travail' ), 'url' => get_permalink( $page_map['contact'] ) );
		}
		self::create_or_get_menu( 'footer-2', __( 'Footer: Company', 'travail' ), $footer_2_items, 'footer-2' );

		$footer_3_items = array();
		if ( ! empty( $page_map['faq'] ) ) {
			$footer_3_items[] = array( 'title' => __( 'FAQ', 'travail' ), 'url' => get_permalink( $page_map['faq'] ) );
		}
		self::create_or_get_menu( 'footer-3', __( 'Footer: Support', 'travail' ), $footer_3_items, 'footer-3' );

		return __( 'Menus created and assigned.', 'travail' );
	}

	/**
	 * Create-or-fetch a nav menu by stable key, add items, and assign it
	 * to a theme location.
	 *
	 * @param string $key      Stable identifier.
	 * @param string $name     Menu display name.
	 * @param array  $items    array of ['title' => ..., 'url' => ...].
	 * @param string $location Nav menu theme location.
	 * @return int Menu ID.
	 */
	protected static function create_or_get_menu( $key, $name, $items, $location ) {
		$map = self::get_map();

		if ( ! empty( $map['menus'][ $key ] ) && wp_get_nav_menu_object( $map['menus'][ $key ] ) ) {
			$menu_id = (int) $map['menus'][ $key ];
		} else {
			$existing = wp_get_nav_menu_object( $name );
			$menu_id  = $existing ? $existing->term_id : wp_create_nav_menu( $name );
			if ( is_wp_error( $menu_id ) ) {
				throw new Exception( esc_html( $menu_id->get_error_message() ) );
			}
			$map['menus'][ $key ] = $menu_id;
			self::save_map( $map );
		}

		// Only add items the first time (menu_item count is 0) so re-running never duplicates entries.
		$existing_items = wp_get_nav_menu_items( $menu_id );
		if ( empty( $existing_items ) ) {
			foreach ( $items as $item ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'  => $item['title'],
						'menu-item-url'    => $item['url'],
						'menu-item-status' => 'publish',
					)
				);
			}
		}

		$locations              = get_theme_mod( 'nav_menu_locations', array() );
		$locations[ $location ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );

		return $menu_id;
	}

	/**
	 * Step: widgets — populates the blog sidebar with a sensible
	 * default set (Search, Recent Posts, Categories) if it's empty.
	 *
	 * @return string
	 */
	public static function step_widgets() {
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( ! empty( $sidebars_widgets['sidebar-blog'] ) ) {
			return __( 'Blog sidebar already has widgets — left untouched.', 'travail' );
		}

		$widgets_to_add = array(
			'search'      => array(),
			'recent-posts' => array( 'title' => __( 'Recent Posts', 'travail' ) ),
			'categories'  => array( 'title' => __( 'Categories', 'travail' ) ),
		);

		$new_widget_ids = array();
		foreach ( $widgets_to_add as $id_base => $instance ) {
			$option_key                        = 'widget_' . $id_base;
			$all_instances                     = get_option( $option_key, array() );
			$next_number                       = empty( $all_instances ) ? 2 : ( max( array_diff( array_keys( $all_instances ), array( '_multiwidget' ) ) ) + 1 );
			$all_instances[ $next_number ]     = $instance;
			$all_instances['_multiwidget']     = 1;
			update_option( $option_key, $all_instances );
			$new_widget_ids[] = $id_base . '-' . $next_number;
		}

		$sidebars_widgets                = wp_get_sidebars_widgets();
		$sidebars_widgets['sidebar-blog'] = $new_widget_ids;
		wp_set_sidebars_widgets( $sidebars_widgets );

		return __( 'Blog sidebar widgets added.', 'travail' );
	}

	/**
	 * Step: demo_content — creates destination/activity taxonomy terms,
	 * five "popular experience" tours, one featured/best-seller tour,
	 * three deal tours and three blog posts, all matching the
	 * wanderly.html reference design's exact content/imagery, and points
	 * the hero/why-choose-us/newsletter theme mods at sideloaded copies
	 * of the same reference photos.
	 *
	 * Every image is sideloaded into the local media library (never
	 * hotlinked at runtime) via media_sideload_image(), and every
	 * created object's ID is cached in the import map so re-running this
	 * step is a fast no-op instead of re-downloading/re-creating anything.
	 *
	 * Tour meta uses only fields confirmed by reading the plugin's own
	 * source (ttbm_ticket_type, ttbm_travel_duration, ttbm_full_location_name,
	 * ttbm_travel_type/_start_date/_end_date, ttbm_best_seller,
	 * ttbm_tour_rating) — see inc/compatibility/tour-booking-manager.php
	 * for the equivalent read-side accessor pattern.
	 *
	 * @return string
	 */
	public static function step_demo_content() {
		if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() ) {
			return __( 'Tour Booking Manager is not active — skipped destinations/tours (blog stories were not affected).', 'travail' );
		}

		$counts = array(
			'terms'  => self::create_demo_taxonomy_terms(),
			'tours'  => self::create_demo_tours(),
			'blog'   => self::create_demo_blog_posts(),
		);

		self::apply_demo_theme_images();

		return sprintf(
			/* translators: 1: number of terms, 2: number of tours, 3: number of blog posts. */
			__( 'Imported %1$d destinations/activities, %2$d tours and %3$d travel stories.', 'travail' ),
			$counts['terms'],
			$counts['tours'],
			$counts['blog']
		);
	}

	/**
	 * Sideload an image URL into the media library, memoized in the
	 * import map so the same URL is never downloaded twice across runs.
	 *
	 * Deliberately does not use core's media_sideload_image(): that
	 * function rejects any URL without a recognizable file extension in
	 * its path (via wp_check_filetype()), and Unsplash's own image URLs
	 * — the exact URLs used by the wanderly.html reference design —
	 * carry their format in a query string (`?...&auto=format`) rather
	 * than a `.jpg` path segment, so every sideload call failed with
	 * "Invalid image URL" (caught while testing this step). Downloading
	 * via download_url() and inspecting the real Content-Type header
	 * sidesteps that entirely.
	 *
	 * @param string $url Remote image URL.
	 * @param string $alt Alt text for the created attachment.
	 * @return int Attachment ID, or 0 on failure.
	 */
	protected static function sideload_image( $url, $alt = '' ) {
		$map = self::get_map();
		$key = md5( $url );

		if ( ! empty( $map['images'][ $key ] ) && get_post( $map['images'][ $key ] ) ) {
			return (int) $map['images'][ $key ];
		}

		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$tmp_file = download_url( $url );
		if ( is_wp_error( $tmp_file ) ) {
			return 0;
		}

		$mime_type = '';
		$image_info = @getimagesize( $tmp_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors -- best-effort MIME sniff, falls back to jpg below.
		if ( is_array( $image_info ) && ! empty( $image_info['mime'] ) ) {
			$mime_type = $image_info['mime'];
		}
		$extension = 'image/png' === $mime_type ? 'png' : 'jpg';

		$file_array = array(
			'name'     => sanitize_title( $alt ? $alt : 'travail-demo-image' ) . '-' . substr( $key, 0, 8 ) . '.' . $extension,
			'tmp_name' => $tmp_file,
		);

		$attachment_id = media_handle_sideload( $file_array, 0, $alt );

		if ( is_wp_error( $attachment_id ) ) {
			wp_delete_file( $tmp_file );
			return 0;
		}

		if ( $alt ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
		}

		$map['images'][ $key ] = $attachment_id;
		self::save_map( $map );

		return (int) $attachment_id;
	}

	/**
	 * Create-or-fetch a taxonomy term, optionally setting the theme's
	 * "Card Image" (+ "Country" for locations) term meta.
	 *
	 * @param string $taxonomy    Taxonomy slug.
	 * @param string $name        Term name.
	 * @param string $image_url   Remote image URL, or '' to skip.
	 * @param string $country     Country subtitle (locations only), or ''.
	 * @return int Term ID.
	 */
	protected static function upsert_demo_term( $taxonomy, $name, $image_url = '', $country = '' ) {
		$existing = get_term_by( 'name', $name, $taxonomy );
		if ( $existing && ! is_wp_error( $existing ) ) {
			$term_id = $existing->term_id;
		} else {
			$result = wp_insert_term( $name, $taxonomy );
			if ( is_wp_error( $result ) ) {
				return 0;
			}
			// wp_insert_term() returns an array (term_id/term_taxonomy_id), not an object.
			$term_id = $result['term_id'];
		}

		if ( $image_url && ! get_term_meta( $term_id, 'travail_term_image_id', true ) ) {
			$attachment_id = self::sideload_image( $image_url, $name );
			if ( $attachment_id ) {
				update_term_meta( $term_id, 'travail_term_image_id', $attachment_id );
			}
		}

		if ( $country && ! get_term_meta( $term_id, 'travail_term_country', true ) ) {
			update_term_meta( $term_id, 'travail_term_country', $country );
		}

		return $term_id;
	}

	/**
	 * Step helper: destinations (ttbm_tour_location) + travel styles
	 * (ttbm_tour_activities), matching the reference design's grid +
	 * "Find your kind of adventure" rail.
	 *
	 * @return int Number of terms created/found.
	 */
	protected static function create_demo_taxonomy_terms() {
		$count = 0;

		$locations = array(
			array( 'Bali', 'Indonesia', 'https://images.unsplash.com/photo-1559628233-eb1b1a45564b?w=900&h=900&fit=crop&auto=format' ),
			array( 'Swiss Alps', 'Switzerland', 'https://images.unsplash.com/photo-1507039915464-9d829b6d2d78?w=900&h=900&fit=crop&auto=format' ),
			array( 'Santorini', 'Greece', 'https://images.unsplash.com/photo-1629470035936-3296c3bd8237?w=800&h=500&fit=crop&auto=format' ),
			array( 'Tokyo', 'Japan', 'https://images.unsplash.com/photo-1573455494060-c5595004fb6c?w=800&h=500&fit=crop&auto=format' ),
			array( 'Iceland', 'Iceland', 'https://images.unsplash.com/photo-1531366936337-7c912a4589a7?w=800&h=500&fit=crop&auto=format' ),
			array( 'Maldives', 'Maldives', 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=800&h=500&fit=crop&auto=format' ),
			array( 'Patagonia', 'Argentina / Chile', 'https://images.unsplash.com/photo-1490604001847-b712b0c2f967?w=800&h=500&fit=crop&auto=format' ),
		);
		foreach ( $locations as $location ) {
			if ( self::upsert_demo_term( 'ttbm_tour_location', $location[0], $location[2], $location[1] ) ) {
				++$count;
			}
		}

		$activities = array(
			array( 'Adventure', 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=200&h=160&fit=crop&auto=format' ),
			array( 'Beach', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=200&h=160&fit=crop&auto=format' ),
			array( 'Culture', 'https://images.unsplash.com/photo-1555400038-63f5ba517a47?w=200&h=160&fit=crop&auto=format' ),
			array( 'Hiking', 'https://images.unsplash.com/photo-1464852045489-bccb7d17fe39?w=200&h=160&fit=crop&auto=format' ),
			array( 'Luxury', 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=200&h=160&fit=crop&auto=format' ),
			array( 'Wildlife', 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=200&h=160&fit=crop&auto=format' ),
			array( 'Family', 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=200&h=160&fit=crop&auto=format' ),
			array( 'Wellness', 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=200&h=160&fit=crop&auto=format' ),
		);
		foreach ( $activities as $activity ) {
			if ( self::upsert_demo_term( 'ttbm_tour_activities', $activity[0], $activity[1] ) ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Build a single-ticket-type ttbm_ticket_type row.
	 *
	 * @param string $price      Regular price.
	 * @param string $sale_price Sale price, or '' for none.
	 * @return array
	 */
	protected static function demo_ticket_type( $price, $sale_price = '' ) {
		return array(
			array(
				'ticket_type_icon'        => '',
				'ticket_type_name'        => __( 'Adult', 'travail' ),
				'ticket_type_price'       => $price,
				'sale_price'              => $sale_price,
				'ticket_type_qty'         => '20',
				'ticket_type_default_qty' => '1',
				'ticket_type_resv_qty'    => '0',
				'ticket_type_qty_type'    => 'inputbox',
				'ticket_type_description' => '',
			),
		);
	}

	/**
	 * Create-or-fetch a single demo ttbm_tour, with sideloaded featured
	 * image, gallery, taxonomy terms and booking-relevant meta.
	 *
	 * @param string $key  Stable map key (not the title — titles can be edited later).
	 * @param array  $data Tour definition, see call sites for shape.
	 * @return int Tour post ID, or 0 on failure.
	 */
	protected static function upsert_demo_tour( $key, $data ) {
		$map          = self::get_map();
		$already_exists = ! empty( $map['tours'][ $key ] ) && get_post( $map['tours'][ $key ] );

		if ( $already_exists ) {
			$tour_id = (int) $map['tours'][ $key ];
		} else {
			$tour_id = wp_insert_post(
				array(
					'post_title'   => $data['title'],
					'post_content' => $data['description'],
					'post_status'  => 'publish',
					'post_type'    => 'ttbm_tour',
				),
				true
			);

			if ( is_wp_error( $tour_id ) ) {
				return 0;
			}
		}

		// Backfill the thumbnail even on an already-existing tour: an earlier
		// importer run may have created the post but failed to attach an
		// image (this happened while testing — media_sideload_image()
		// rejects Unsplash's extension-less URLs — fixed in sideload_image(),
		// but the tour post it half-created needed to still pick up the image
		// on a later run rather than being skipped forever).
		if ( ! empty( $data['image'] ) && ! has_post_thumbnail( $tour_id ) ) {
			$attachment_id = self::sideload_image( $data['image'], $data['title'] );
			if ( $attachment_id ) {
				set_post_thumbnail( $tour_id, $attachment_id );
				update_post_meta( $tour_id, 'ttbm_gallery_images', array( $attachment_id ) );
			}
		}

		if ( $already_exists ) {
			return $tour_id;
		}

		if ( ! empty( $data['location_term'] ) ) {
			$term_id = self::upsert_demo_term( 'ttbm_tour_location', $data['location_term'] );
			if ( $term_id ) {
				wp_set_post_terms( $tour_id, array( $term_id ), 'ttbm_tour_location' );
			}
		}

		if ( ! empty( $data['activity_term'] ) ) {
			$term_id = self::upsert_demo_term( 'ttbm_tour_activities', $data['activity_term'] );
			if ( $term_id ) {
				wp_set_post_terms( $tour_id, array( $term_id ), 'ttbm_tour_activities' );
			}
		}

		update_post_meta( $tour_id, 'ttbm_full_location_name', $data['full_location'] );
		update_post_meta( $tour_id, 'ttbm_ticket_type', self::demo_ticket_type( $data['price'], isset( $data['sale_price'] ) ? $data['sale_price'] : '' ) );
		update_post_meta( $tour_id, 'ttbm_travel_duration', $data['duration'] );
		update_post_meta( $tour_id, 'ttbm_travel_duration_type', $data['duration_type'] );
		if ( ! empty( $data['duration_night'] ) ) {
			update_post_meta( $tour_id, 'ttbm_travel_duration_night', $data['duration_night'] );
			update_post_meta( $tour_id, 'ttbm_display_duration_night', 'on' );
		}
		update_post_meta( $tour_id, 'ttbm_display_price_start', 'on' );
		update_post_meta( $tour_id, 'ttbm_display_duration', 'on' );
		update_post_meta( $tour_id, 'ttbm_display_location', 'on' );
		update_post_meta( $tour_id, 'ttbm_travel_max_people_allow', '20' );

		// Fixed date ~30 days out so the tour reads as "upcoming" rather than expired.
		$start_date = gmdate( 'Y-m-d', strtotime( '+30 days' ) );
		$end_date   = 'day' === $data['duration_type']
			? gmdate( 'Y-m-d', strtotime( '+' . ( 30 + max( 0, (int) $data['duration'] - 1 ) ) . ' days' ) )
			: $start_date;
		update_post_meta( $tour_id, 'ttbm_travel_type', 'fixed' );
		update_post_meta( $tour_id, 'ttbm_travel_start_date', $start_date );
		update_post_meta( $tour_id, 'ttbm_travel_end_date', $end_date );
		update_post_meta( $tour_id, 'ttbm_upcoming_date', $start_date );

		if ( ! empty( $data['rating'] ) ) {
			update_post_meta( $tour_id, 'ttbm_tour_rating', $data['rating'] );
		}

		if ( ! empty( $data['best_seller'] ) ) {
			update_post_meta( $tour_id, 'ttbm_best_seller', 'on' );
		}

		$map          = self::get_map();
		$map['tours'][ $key ] = $tour_id;
		self::save_map( $map );

		return $tour_id;
	}

	/**
	 * Step helper: the 5 "Popular experiences" + 1 featured + 3 deal
	 * tours from the reference design.
	 *
	 * @return int Number of tours created/found.
	 */
	protected static function create_demo_tours() {
		$tours = array(
			'bali-sunrise'    => array(
				'title'         => __( 'Bali Sunrise Jeep Adventure', 'travail' ),
				'description'   => __( 'Watch the sun rise over Mount Batur from a vintage 4x4 jeep, then wind through rice terraces and hot springs before breakfast.', 'travail' ),
				'image'         => 'https://images.unsplash.com/photo-1558005530-a7958896ec60?w=900&h=600&fit=crop&auto=format',
				'location_term' => 'Bali',
				'activity_term' => 'Adventure',
				'full_location' => 'Bali, Indonesia',
				'price'         => '89',
				'duration'      => '6',
				'duration_type' => 'hour',
				'rating'        => '4.9',
			),
			'swiss-hiking'    => array(
				'title'         => __( 'Swiss Alps Hiking Trail', 'travail' ),
				'description'   => __( 'A guided high-alpine hike through Grindelwald with panoramic views of the Eiger, finishing at a mountain hut for a traditional Swiss lunch.', 'travail' ),
				'image'         => 'https://images.unsplash.com/photo-1520681504224-093d46124820?w=900&h=600&fit=crop&auto=format',
				'location_term' => 'Swiss Alps',
				'activity_term' => 'Hiking',
				'full_location' => 'Grindelwald, Switzerland',
				'price'         => '149',
				'duration'      => '8',
				'duration_type' => 'hour',
				'rating'        => '4.8',
			),
			'santorini-sail'  => array(
				'title'         => __( 'Santorini Sunset Sailing', 'travail' ),
				'description'   => __( 'Sail the caldera at golden hour aboard a traditional catamaran, with a swim stop and a spread of local wine and mezze.', 'travail' ),
				'image'         => 'https://images.unsplash.com/photo-1629470035936-3296c3bd8237?w=900&h=600&fit=crop&auto=format',
				'location_term' => 'Santorini',
				'activity_term' => 'Luxury',
				'full_location' => 'Oia, Greece',
				'price'         => '119',
				'duration'      => '4',
				'duration_type' => 'hour',
				'rating'        => '5.0',
			),
			'tokyo-food-walk' => array(
				'title'         => __( 'Tokyo Food & Culture Walk', 'travail' ),
				'description'   => __( 'Wander Shibuya\'s backstreets with a local guide, sampling street food and stepping into a century-old shrine along the way.', 'travail' ),
				'image'         => 'https://images.unsplash.com/photo-1601042879364-f3947d3f9c16?w=900&h=600&fit=crop&auto=format',
				'location_term' => 'Tokyo',
				'activity_term' => 'Culture',
				'full_location' => 'Shibuya, Japan',
				'price'         => '75',
				'duration'      => '5',
				'duration_type' => 'hour',
				'rating'        => '4.9',
			),
			'iceland-lights'  => array(
				'title'         => __( 'Iceland Northern Lights', 'travail' ),
				'description'   => __( 'Chase the aurora across Iceland\'s volcanic landscape with an expert guide and hot cocoa to keep warm.', 'travail' ),
				'image'         => 'https://images.unsplash.com/photo-1531366936337-7c912a4589a7?w=900&h=600&fit=crop&auto=format',
				'location_term' => 'Iceland',
				'activity_term' => 'Adventure',
				'full_location' => 'Reykjavik, Iceland',
				'price'         => '199',
				'duration'      => '1',
				'duration_type' => 'day',
				'rating'        => '4.7',
			),
			'patagonia'       => array(
				'title'         => __( 'Discover Patagonia', 'travail' ),
				'description'   => __( "Journey through the end of the world — dramatic granite towers, vast glaciers, and pristine wilderness that will redefine what adventure means to you.", 'travail' ),
				'image'         => 'https://images.unsplash.com/photo-1490604001847-b712b0c2f967?w=1200&h=900&fit=crop&auto=format',
				'location_term' => 'Patagonia',
				'activity_term' => 'Wildlife',
				'full_location' => 'Patagonia, Argentina / Chile',
				'price'         => '1290',
				'duration'      => '8',
				'duration_type' => 'day',
				'duration_night' => '7',
				'rating'        => '4.9',
				'best_seller'   => true,
			),
			'deal-maldives'   => array(
				'title'          => __( 'Maldives All-Inclusive Escape', 'travail' ),
				'description'    => __( 'Seven nights in an overwater villa with all meals, snorkeling and sunset cruises included.', 'travail' ),
				'image'          => 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=900&h=600&fit=crop&auto=format',
				'location_term'  => 'Maldives',
				'activity_term'  => 'Beach',
				'full_location'  => 'Maldives',
				'price'          => '2890',
				'sale_price'     => '2023',
				'duration'       => '8',
				'duration_type'  => 'day',
				'duration_night' => '7',
			),
			'deal-bali'       => array(
				'title'          => __( 'Bali All-Inclusive Getaway', 'travail' ),
				'description'    => __( 'Five nights across a beach resort and a jungle villa, with daily breakfast and a private driver included.', 'travail' ),
				'image'          => 'https://images.unsplash.com/photo-1555400038-63f5ba517a47?w=900&h=600&fit=crop&auto=format',
				'location_term'  => 'Bali',
				'activity_term'  => 'Culture',
				'full_location'  => 'Bali, Indonesia',
				'price'          => '1450',
				'sale_price'     => '1160',
				'duration'       => '6',
				'duration_type'  => 'day',
				'duration_night' => '5',
			),
			'deal-iceland'    => array(
				'title'          => __( 'Iceland Ring Road Adventure', 'travail' ),
				'description'    => __( 'Six nights circling the island, with glacier hikes, hot springs and a guided aurora hunt included.', 'travail' ),
				'image'          => 'https://images.unsplash.com/photo-1516655855035-d5215bcb5604?w=900&h=600&fit=crop&auto=format',
				'location_term'  => 'Iceland',
				'activity_term'  => 'Wildlife',
				'full_location'  => 'Iceland',
				'price'          => '3200',
				'sale_price'     => '2400',
				'duration'       => '7',
				'duration_type'  => 'day',
				'duration_night' => '6',
			),
		);

		$count = 0;
		foreach ( $tours as $key => $data ) {
			if ( self::upsert_demo_tour( $key, $data ) ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Step helper: the 3 "Stories from the road" blog posts.
	 *
	 * @return int Number of posts created/found.
	 */
	protected static function create_demo_blog_posts() {
		$posts = array(
			array(
				'title'    => __( '10 unforgettable places to discover in Bali', 'travail' ),
				'content'  => __( 'From hidden waterfalls to centuries-old temples, Bali rewards travelers who venture past the beach clubs. Here are ten spots worth the detour.', 'travail' ),
				'category' => __( 'Travel Guide', 'travail' ),
				'image'    => 'https://images.unsplash.com/photo-1559628233-eb1b1a45564b?w=900&h=600&fit=crop&auto=format',
			),
			array(
				'title'    => __( "A beginner's guide to the Swiss Alps", 'travail' ),
				'content'  => __( 'Not sure where to start in the Alps? Here is everything a first-time visitor needs to know about trails, huts and getting around.', 'travail' ),
				'category' => __( 'Destination', 'travail' ),
				'image'    => 'https://images.unsplash.com/photo-1507039915464-9d829b6d2d78?w=900&h=600&fit=crop&auto=format',
			),
			array(
				'title'    => __( 'The ultimate weekend escape to Santorini', 'travail' ),
				'content'  => __( 'Two days is enough to fall in love with Santorini — here is how to make the most of a short stay on the island.', 'travail' ),
				'category' => __( 'Inspiration', 'travail' ),
				'image'    => 'https://images.unsplash.com/photo-1629470035936-3296c3bd8237?w=900&h=600&fit=crop&auto=format',
			),
		);

		$map   = self::get_map();
		$count = 0;

		foreach ( $posts as $index => $post_data ) {
			$key            = 'blog-' . $index;
			$already_exists = ! empty( $map['blog_posts'][ $key ] ) && get_post( $map['blog_posts'][ $key ] );

			if ( $already_exists ) {
				$post_id = (int) $map['blog_posts'][ $key ];
			} else {
				$post_id = wp_insert_post(
					array(
						'post_title'   => $post_data['title'],
						'post_content' => $post_data['content'],
						'post_status'  => 'publish',
						'post_type'    => 'post',
					),
					true
				);

				if ( is_wp_error( $post_id ) ) {
					continue;
				}

				// wp_insert_term() returns an array (term_id/term_taxonomy_id), not an object.
				$term    = wp_insert_term( $post_data['category'], 'category' );
				$term_id = is_wp_error( $term ) ? get_cat_ID( $post_data['category'] ) : $term['term_id'];
				if ( $term_id ) {
					wp_set_post_terms( $post_id, array( $term_id ), 'category' );
				}
			}

			// Backfill on an already-existing post too — see the matching
			// comment in upsert_demo_tour() for why this matters.
			if ( ! has_post_thumbnail( $post_id ) ) {
				$attachment_id = self::sideload_image( $post_data['image'], $post_data['title'] );
			} else {
				$attachment_id = 0;
			}
			if ( $attachment_id ) {
				set_post_thumbnail( $post_id, $attachment_id );
			}

			$map               = self::get_map();
			$map['blog_posts'][ $key ] = $post_id;
			self::save_map( $map );

			++$count;
		}

		return $count;
	}

	/**
	 * Step helper: point the hero / why-choose-us / newsletter theme
	 * mods at sideloaded copies of the reference design's own photos —
	 * only when the site owner hasn't already set a custom image.
	 */
	protected static function apply_demo_theme_images() {
		$images = array(
			'hero_image'          => 'https://images.unsplash.com/photo-1464852045489-bccb7d17fe39?w=1920&h=1080&fit=crop&auto=format',
			'why_choose_us_image' => 'https://images.unsplash.com/photo-1439853949127-fa647821eba0?w=800&h=700&fit=crop&auto=format',
			'newsletter_image'    => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920&h=800&fit=crop&auto=format',
		);

		foreach ( $images as $mod_key => $url ) {
			if ( '' !== get_theme_mod( 'travail_' . $mod_key, '' ) ) {
				continue;
			}
			$attachment_id = self::sideload_image( $url, $mod_key );
			if ( $attachment_id ) {
				$image_url = wp_get_attachment_image_url( $attachment_id, 'travail-hero' );
				if ( $image_url ) {
					set_theme_mod( 'travail_' . $mod_key, $image_url );
				}
			}
		}
	}

	/**
	 * Step: theme_options — sensible defaults for hero + footer copy,
	 * only filled in where the site owner hasn't already set a value.
	 *
	 * @return string
	 */
	public static function step_theme_options() {
		$defaults = array(
			'hero_eyebrow'   => __( 'Explore · Dream · Discover', 'travail' ),
			'hero_title'     => __( 'Discover your{break}next {emphasis}', 'travail' ),
			'hero_emphasis'  => __( 'adventure', 'travail' ),
			'hero_subtitle'  => __( 'Curated trips. Extraordinary places. Unforgettable memories.', 'travail' ),
			'hero_metric_1_value' => '12,000+',
			'hero_metric_1_label' => __( 'Happy Travelers', 'travail' ),
			'hero_metric_2_value' => '4.9/5',
			'hero_metric_2_label' => __( 'Overall Rating', 'travail' ),
			'hero_metric_3_value' => '120+',
			'hero_metric_3_label' => __( 'Countries', 'travail' ),
			'footer_description'  => __( "Curating the world's finest travel experiences. Every trip, a new story.", 'travail' ),
		);

		foreach ( $defaults as $key => $value ) {
			if ( '' === get_theme_mod( 'travail_' . $key, '' ) ) {
				set_theme_mod( 'travail_' . $key, $value );
			}
		}

		return __( 'Default theme settings applied (existing values were not overwritten).', 'travail' );
	}

	/**
	 * Step: homepage. Sends the blog to its own page and leaves the
	 * front page rendering Travail's built-in default sections (see
	 * front-page.php) — the safest choice since we don't fabricate an
	 * empty "Home" page that would otherwise look broken without
	 * Elementor content.
	 *
	 * @return string
	 */
	public static function step_homepage() {
		$map = self::get_map();
		if ( ! empty( $map['pages']['blog'] ) && 'page' !== get_option( 'show_on_front' ) ) {
			update_option( 'show_on_front', 'posts' );
			update_option( 'page_for_posts', $map['pages']['blog'] );
		}

		if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_elementor_active() ) {
			return __( 'Elementor detected — build your homepage visually with the widgets under the "Travail" category, then set it as your front page under Settings → Reading.', 'travail' );
		}

		return __( 'Homepage configured — showing the built-in Travail homepage sections. Blog posts now live on their own page.', 'travail' );
	}
}

Travail_Demo_Importer::init();
