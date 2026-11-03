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
			'plugin_samples' => __( 'Importing sample tours, hotels, guides &amp; places', 'travail' ),
			'complete_tours' => __( 'Completing tour details (itinerary, FAQ, tickets, extras &amp; reviews)', 'travail' ),
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
	 * default set (Search, Categories) if it's empty.
	 *
	 * @return string
	 */
	public static function step_widgets() {
		// Drop WordPress's default Recent Posts / Recent Comments before deciding whether the sidebar is empty.
		if ( function_exists( 'travail_remove_default_sidebar_widgets' ) ) {
			travail_remove_default_sidebar_widgets();
		}

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( ! empty( $sidebars_widgets['sidebar-blog'] ) ) {
			return __( 'Blog sidebar already has widgets — left untouched.', 'travail' );
		}

		// Recent Posts and Recent Comments are intentionally not part of the demo.
		$widgets_to_add = array(
			'search'     => array(),
			'categories' => array( 'title' => __( 'Categories', 'travail' ) ),
		);

		$new_widget_ids = array();
		foreach ( $widgets_to_add as $id_base => $instance ) {
			$option_key       = 'widget_' . $id_base;
			$all_instances    = get_option( $option_key, array() );
			$existing_numbers = array_diff( array_keys( $all_instances ), array( '_multiwidget' ) );
			// `$all_instances` can be non-empty yet contain no numeric keys at
			// all — WordPress leaves options like `widget_search` behind as
			// just `array( '_multiwidget' => 1 )` once every instance of that
			// widget type has been removed. `empty( $all_instances )` misses
			// that case, and max() on the resulting empty array is a fatal
			// ValueError on PHP 8+ (caught while triaging a "Request failed"
			// AJAX error partway through demo import), so check the diffed
			// list itself instead.
			$next_number                    = empty( $existing_numbers ) ? 2 : ( max( $existing_numbers ) + 1 );
			$all_instances[ $next_number ] = $instance;
			$all_instances['_multiwidget'] = 1;
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
			// Names must match the existing ttbm_tour_location terms exactly
			// (upsert_demo_term() looks up by name) — these two were created
			// directly on the live site, not by this importer, and had no
			// Card Image set yet (confirmed: they were falling back to the
			// theme's placeholder-tour.svg on the destination grid).
			array( 'Bandarban', 'Bangladesh', 'https://images.unsplash.com/photo-1751769539073-d675c0ac98d6?w=900&h=900&fit=crop&auto=format' ),
			array( 'Coxbazar', 'Bangladesh', 'https://images.unsplash.com/photo-1778432763684-8d14000d8e2a?w=900&h=900&fit=crop&auto=format' ),
			array( 'Bali', 'Indonesia', 'https://images.unsplash.com/photo-1559628233-eb1b1a45564b?w=900&h=900&fit=crop&auto=format' ),
			array( 'Dubai', 'UAE', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=900&h=900&fit=crop&auto=format' ),
			array( 'Paris', 'France', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=900&h=900&fit=crop&auto=format' ),
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
			// Travel styles Tour Booking Manager's own sample tours use; they carry the plugin's icon class instead of a photo.
			array( 'City Tours', '', 'mi mi-taxi-bus' ),
			array( 'Rural', '', 'mi mi-water' ),
			array( 'Snow & Ice', '', 'mi mi-snowflakes' ),
		);
		foreach ( $activities as $activity ) {
			$activity_term_id = self::upsert_demo_term( 'ttbm_tour_activities', $activity[0], $activity[1] );
			if ( $activity_term_id ) {
				++$count;
				if ( ! empty( $activity[2] ) && ! get_term_meta( $activity_term_id, 'ttbm_activities_icon', true ) ) {
					update_term_meta( $activity_term_id, 'ttbm_activities_icon', $activity[2] );
				}
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
	 * Fill Tour Booking Manager's own "city" and "country" fields from the tour's destination.
	 * The plugin's city/country filters and location labels read these meta values (what its tour editor saves),
	 * not the taxonomy, so tours imported with only a destination term showed empty location data.
	 * Only fills EMPTY values, so anything an editor typed is kept.
	 *
	 * @param int   $tour_id Tour post ID.
	 * @param array $data    Tour definition (needs `location_term`).
	 */
	protected static function set_tour_location_meta( $tour_id, $data ) {
		if ( empty( $data['location_term'] ) ) {
			return;
		}
		$term = get_term_by( 'name', $data['location_term'], 'ttbm_tour_location' );
		if ( ! $term || is_wp_error( $term ) ) {
			return;
		}
		if ( '' === (string) get_post_meta( $tour_id, 'ttbm_location_name', true ) ) {
			update_post_meta( $tour_id, 'ttbm_location_name', $term->name );
		}
		$country = (string) get_term_meta( $term->term_id, 'travail_term_country', true );
		if ( '' !== $country && '' === (string) get_post_meta( $tour_id, 'ttbm_country_name', true ) ) {
			update_post_meta( $tour_id, 'ttbm_country_name', $country );
		}
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
			self::set_tour_location_meta( $tour_id, $data );
			return $tour_id;
		}

		if ( ! empty( $data['location_term'] ) ) {
			$term_id = self::upsert_demo_term( 'ttbm_tour_location', $data['location_term'] );
			if ( $term_id ) {
				wp_set_post_terms( $tour_id, array( $term_id ), 'ttbm_tour_location' );
			}
		}
		self::set_tour_location_meta( $tour_id, $data );

		if ( ! empty( $data['activity_term'] ) ) {
			$term_id = self::upsert_demo_term( 'ttbm_tour_activities', $data['activity_term'] );
			if ( $term_id ) {
				wp_set_post_terms( $tour_id, array( $term_id ), 'ttbm_tour_activities' );
				// Tour Booking Manager's activity lists/filters read this meta (what its editor saves), not the taxonomy.
				update_post_meta( $tour_id, 'ttbm_tour_activities', array( (string) $term_id ) );
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

		if ( ! empty( $data['offdays'] ) ) {
			// Most demo tours run every day except two days a week, with no end date.
			$details = self::demo_tour_details();
			self::set_repeated_daily( $tour_id, $data['offdays'], isset( $details[ $key ]['start_time'] ) ? $details[ $key ]['start_time'] : '09:00' );
		} else {
			// Fixed date ~30 days out so the tour reads as "upcoming" rather than expired.
			$start_date = gmdate( 'Y-m-d', strtotime( '+30 days' ) );
			$end_date   = 'day' === $data['duration_type']
				? gmdate( 'Y-m-d', strtotime( '+' . ( 30 + max( 0, (int) $data['duration'] - 1 ) ) . ' days' ) )
				: $start_date;
			update_post_meta( $tour_id, 'ttbm_travel_type', 'fixed' );
			update_post_meta( $tour_id, 'ttbm_travel_start_date', $start_date );
			update_post_meta( $tour_id, 'ttbm_travel_end_date', $end_date );
			update_post_meta( $tour_id, 'ttbm_upcoming_date', $start_date );
		}

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
		$count = 0;
		foreach ( self::demo_tours() as $key => $data ) {
			if ( self::upsert_demo_tour( $key, $data ) ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * The demo tour definitions (card data). Their page content lives in demo_tour_details().
	 *
	 * @return array
	 */
	protected static function demo_tours() {
		return array(
			'bali-sunrise'    => array(
				'offdays'       => array( 'mon', 'tue' ), // Repeated daily, closed these weekdays.
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
			'dubai-safari'    => array(
				'offdays'       => array( 'fri', 'sat' ), // Repeated daily, closed these weekdays.
				'title'         => __( 'Dubai Desert Safari at Dusk', 'travail' ),
				'description'   => __( 'Dune-bash across the Arabian desert at golden hour, then unwind at a Bedouin-style camp with a barbecue dinner and live entertainment under the stars.', 'travail' ),
				'image'         => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=900&h=600&fit=crop&auto=format',
				'location_term' => 'Dubai',
				'activity_term' => 'Adventure',
				'full_location' => 'Dubai, UAE',
				'price'         => '120',
				'duration'      => '8',
				'duration_type' => 'hour',
				'rating'        => '4.8',
			),
			'paris-eiffel'    => array(
				'offdays'       => array( 'mon', 'tue' ), // Repeated daily, closed these weekdays.
				'title'         => __( 'Paris Eiffel Tower & Seine Cruise', 'travail' ),
				'description'   => __( 'Skip the line up the Eiffel Tower at golden hour, then drift past Notre-Dame and the Louvre on an evening Seine river cruise.', 'travail' ),
				'image'         => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=900&h=600&fit=crop&auto=format',
				'location_term' => 'Paris',
				'activity_term' => 'Culture',
				'full_location' => 'Paris, France',
				'price'         => '85',
				'duration'      => '4',
				'duration_type' => 'hour',
				'rating'        => '4.9',
			),
			'swiss-hiking'    => array(
				'offdays'       => array( 'wed', 'thu' ), // Repeated daily, closed these weekdays.
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
				'offdays'       => array( 'tue', 'wed' ), // Repeated daily, closed these weekdays.
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
				'offdays'       => array( 'sun', 'mon' ), // Repeated daily, closed these weekdays.
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
				'offdays'       => array( 'wed', 'thu' ), // Repeated daily, closed these weekdays.
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
				'offdays'       => array( 'sat', 'sun' ), // Repeated daily, closed these weekdays.
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
				'offdays'       => array( 'sun', 'mon' ), // Repeated daily, closed these weekdays.
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
				'offdays'       => array( 'sat', 'sun' ), // Repeated daily, closed these weekdays.
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
	}

	/**
	 * Step: give every imported tour all the sections a Tour Booking Manager tour page can show.
	 *
	 * The demo tours above only carry what the homepage cards need, so their single pages had no
	 * itinerary, FAQ, included/excluded list, extra services, second ticket type, gallery slider,
	 * reviews and so on. This step fills those in. It runs after the sample step so the sample
	 * guides exist to be assigned.
	 *
	 * It only fills values that are EMPTY, so it is safe to re-run on a site whose tours were edited:
	 * nothing a site owner typed is replaced. When a section's content is filled here, that section's
	 * display switch is turned on too (a tour saved in the editor with an empty section has it "off").
	 *
	 * @return string
	 */
	public static function step_complete_tours() {
		if ( ! post_type_exists( 'ttbm_tour' ) ) {
			return __( 'Tour Booking Manager is not active, so tour details were skipped.', 'travail' );
		}

		self::ensure_feature_terms();

		$map      = self::get_map();
		$details  = self::demo_tour_details();
		$demo_ids = array();
		foreach ( (array) ( isset( $map['tours'] ) ? $map['tours'] : array() ) as $key => $tour_id ) {
			if ( 'ttbm_tour' === get_post_type( $tour_id ) ) {
				$demo_ids[ $key ] = (int) $tour_id;
			}
		}

		$guides = get_posts(
			array(
				'post_type'      => 'ttbm_guide',
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		$tours = self::demo_tours();
		$count = 0;
		$index = 0;
		foreach ( $demo_ids as $key => $tour_id ) {
			if ( empty( $details[ $key ] ) ) {
				continue;
			}
			// The star rating the reviews should add up to (deals have none: 4.8).
			$details[ $key ]['rating']  = ! empty( $tours[ $key ]['rating'] ) ? (float) $tours[ $key ]['rating'] : 4.8;
			$details[ $key ]['offdays'] = ! empty( $tours[ $key ]['offdays'] ) ? $tours[ $key ]['offdays'] : array();
			self::complete_demo_tour( $tour_id, $key, $details[ $key ], $demo_ids, $guides, $index++ );
			++$count;
		}

		// The plugin's sample tours already carry their content; fill only the switches/fields they miss.
		foreach ( (array) ( isset( $map['samples'] ) ? $map['samples'] : array() ) as $post_id ) {
			if ( 'ttbm_tour' === get_post_type( $post_id ) ) {
				self::complete_common_tour_fields( (int) $post_id );
				++$count;
			}
		}

		return sprintf(
			/* translators: %d: number of tours */
			__( 'Completed the details of %d tours.', 'travail' ),
			$count
		);
	}

	/**
	 * Whether a meta value is missing: unset, '' or an array with nothing in it.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return bool
	 */
	protected static function meta_is_empty( $post_id, $key ) {
		$value = get_post_meta( $post_id, $key, true );
		if ( is_array( $value ) ) {
			return ! array_filter( $value );
		}
		return '' === (string) $value;
	}

	/**
	 * Set a meta value only when it is still empty.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @param mixed  $value   Value.
	 * @return bool True when the value was written.
	 */
	protected static function fill_meta( $post_id, $key, $value ) {
		if ( ! self::meta_is_empty( $post_id, $key ) ) {
			return false;
		}
		update_post_meta( $post_id, $key, $value );
		return true;
	}

	/**
	 * Fill a section's content when empty; switch the section on when its content was filled here,
	 * or when its switch has never been set.
	 *
	 * @param int    $post_id     Post ID.
	 * @param string $data_key    Meta key holding the section content.
	 * @param mixed  $value       Content.
	 * @param string $display_key Meta key of the section's on/off switch.
	 */
	protected static function fill_section( $post_id, $data_key, $value, $display_key ) {
		if ( self::fill_meta( $post_id, $data_key, $value ) ) {
			update_post_meta( $post_id, $display_key, 'on' );
			return;
		}
		self::fill_meta( $post_id, $display_key, 'on' );
	}

	/**
	 * Fields every tour page needs, sample tours included: the photo slider (the plugin treats a
	 * missing switch as OFF), language and group size.
	 *
	 * @param int $tour_id Tour post ID.
	 */
	protected static function complete_common_tour_fields( $tour_id ) {
		$gallery = array_filter( (array) get_post_meta( $tour_id, 'ttbm_gallery_images', true ) );
		if ( count( $gallery ) > 1 ) {
			self::fill_meta( $tour_id, 'ttbm_display_slider', 'on' );
		}
		self::fill_meta( $tour_id, 'ttbm_travel_language_status', 'on' );
		self::fill_meta( $tour_id, 'ttbm_travel_language', array( 'en_US' ) );
		self::fill_meta( $tour_id, 'ttbm_travel_max_people_allow', '20' );
		self::fill_meta( $tour_id, 'ttbm_display_max_people', 'on' );
	}

	/**
	 * Fill in every missing section of one demo tour.
	 *
	 * @param int    $tour_id  Tour post ID.
	 * @param string $key      Demo tour key.
	 * @param array  $d        Details from demo_tour_details().
	 * @param array  $demo_ids All demo tours, key => ID (for related tours / gallery).
	 * @param array  $guides   Guide post IDs on the site.
	 * @param int    $index    Position of this tour, used to rotate guides.
	 */
	protected static function complete_demo_tour( $tour_id, $key, $d, $demo_ids, $guides, $index ) {
		$post = get_post( $tour_id );

		// --- Gallery + slider: the tour photo, its destination and activity photos, and other demo tours at the same destination. ---
		$gallery = array_values( array_filter( array_map( 'intval', (array) get_post_meta( $tour_id, 'ttbm_gallery_images', true ) ) ) );
		if ( count( $gallery ) < 3 ) {
			$candidates = array( (int) get_post_thumbnail_id( $tour_id ) );
			$location   = get_the_terms( $tour_id, 'ttbm_tour_location' );
			$activity   = get_the_terms( $tour_id, 'ttbm_tour_activities' );
			if ( is_array( $location ) ) {
				$candidates[] = (int) get_term_meta( $location[0]->term_id, 'travail_term_image_id', true );
				foreach ( $demo_ids as $other_id ) {
					if ( $other_id !== $tour_id && has_term( $location[0]->term_id, 'ttbm_tour_location', $other_id ) ) {
						$candidates[] = (int) get_post_thumbnail_id( $other_id );
					}
				}
			}
			if ( is_array( $activity ) ) {
				$candidates[] = (int) get_term_meta( $activity[0]->term_id, 'travail_term_image_id', true );
			}
			foreach ( $candidates as $attachment_id ) {
				if ( $attachment_id && ! in_array( $attachment_id, $gallery, true ) && wp_attachment_is_image( $attachment_id ) ) {
					$gallery[] = $attachment_id;
				}
			}
			update_post_meta( $tour_id, 'ttbm_gallery_images', $gallery );
			if ( count( $gallery ) > 1 ) {
				update_post_meta( $tour_id, 'ttbm_display_slider', 'on' );
			}
		}

		// --- Overview / key facts. ---
		self::fill_meta( $tour_id, 'ttbm_short_description', wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 ) );
		self::fill_meta( $tour_id, 'ttbm_display_description', 'on' );
		self::fill_meta( $tour_id, 'ttbm_travel_min_age', $d['min_age'] );
		self::fill_meta( $tour_id, 'ttbm_display_min_age', 'on' );
		self::fill_meta( $tour_id, 'ttbm_travel_start_place', $d['start_place'] );
		self::fill_meta( $tour_id, 'ttbm_display_start_location', 'on' );
		self::fill_meta( $tour_id, 'ttbm_display_tour_type', 'on' );
		self::fill_meta( $tour_id, 'ttbm_type', 'general' );
		self::fill_meta( $tour_id, 'ttbm_travel_language_status', 'on' );
		self::fill_meta( $tour_id, 'ttbm_travel_language', $d['languages'] );
		self::fill_meta( $tour_id, 'ttbm_travel_max_people_allow', '20' );
		self::fill_meta( $tour_id, 'ttbm_display_max_people', 'on' );

		// --- Location map. ---
		self::fill_meta( $tour_id, 'ttbm_map_latitude', $d['lat'] );
		self::fill_meta( $tour_id, 'ttbm_map_longitude', $d['lon'] );
		self::fill_meta( $tour_id, 'ttbm_display_map', 'on' );

		// --- Dates: a tour with no repeated start date (imported by an older version with a fixed
		// date, or switched to "Repeated" in the editor without one, which leaves it with no bookable
		// dates) moves to repeated daily from today, never ending, with its two weekly off days. ---
		if ( ! empty( $d['offdays'] ) && self::meta_is_empty( $tour_id, 'ttbm_travel_repeated_start_date' ) ) {
			self::set_repeated_daily( $tour_id, $d['offdays'], $d['start_time'] );
		}

		// --- Times + booking window. ---
		self::fill_meta( $tour_id, 'ttbm_travel_start_time', $d['start_time'] );
		self::fill_meta( $tour_id, 'ttbm_travel_end_time', $d['end_time'] );
		if ( 'fixed' === get_post_meta( $tour_id, 'ttbm_travel_type', true ) ) {
			self::fill_meta( $tour_id, 'ttbm_travel_reg_end_date', (string) get_post_meta( $tour_id, 'ttbm_travel_start_date', true ) );
		}
		self::fill_meta( $tour_id, 'reg_end_time', '23:59' );
		self::fill_meta( $tour_id, 'ttbm_display_registration', 'on' );

		// --- Tickets: at least two types. Rows a site owner already has are kept; missing types are appended. ---
		$tickets = get_post_meta( $tour_id, 'ttbm_ticket_type', true );
		$tickets = is_array( $tickets ) ? array_values( array_filter( $tickets ) ) : array();
		if ( count( $tickets ) < 2 ) {
			$have = array();
			foreach ( $tickets as $row ) {
				$have[] = isset( $row['ticket_type_name'] ) ? strtolower( $row['ticket_type_name'] ) : '';
			}
			$adult_price = ! empty( $tickets[0]['ticket_type_price'] ) ? (float) $tickets[0]['ticket_type_price'] : (float) $d['price'];
			$adult_sale  = isset( $tickets[0]['sale_price'] ) ? (string) $tickets[0]['sale_price'] : '';
			foreach ( self::demo_ticket_rows( $adult_price, $adult_sale, ! empty( $d['senior_ticket'] ) ) as $row ) {
				if ( ! in_array( strtolower( $row['ticket_type_name'] ), $have, true ) ) {
					$tickets[] = $row;
				}
			}
			foreach ( $tickets as $i => $row ) {
				if ( empty( $row['ticket_type_icon'] ) ) {
					$tickets[ $i ]['ticket_type_icon'] = 'mi mi-employee-man-alt';
				}
			}
			update_post_meta( $tour_id, 'ttbm_ticket_type', $tickets );
		}

		// --- Extra services. ---
		$extras = array();
		foreach ( $d['extras'] as $extra ) {
			$extras[] = array(
				'service_icon'              => $extra[0],
				'service_name'              => $extra[1],
				'service_price'             => $extra[2],
				'service_sale_price'        => '',
				'service_qty'               => '100',
				'service_qty_type'          => 'inputbox',
				'extra_service_description' => $extra[3],
			);
		}
		self::fill_meta( $tour_id, 'ttbm_extra_service_data', $extras );

		// --- Included / excluded, itinerary, FAQ, why choose us. ---
		self::fill_section( $tour_id, 'ttbm_service_included_in_price', $d['included'], 'ttbm_display_include_service' );
		self::fill_section( $tour_id, 'ttbm_service_excluded_in_price', $d['excluded'], 'ttbm_display_exclude_service' );

		$itinerary = array();
		foreach ( $d['itinerary'] as $step ) {
			$itinerary[] = array(
				'ttbm_day_title'   => $step[0],
				'ttbm_day_content' => '<p>' . $step[1] . '</p>',
			);
		}
		self::fill_section( $tour_id, 'ttbm_daywise_details', $itinerary, 'ttbm_display_schedule' );

		$faq = array();
		foreach ( array_merge( $d['faq'], self::demo_shared_faq() ) as $item ) {
			$faq[] = array(
				'ttbm_faq_title'   => $item[0],
				'ttbm_faq_content' => $item[1],
			);
		}
		self::fill_section( $tour_id, 'mep_event_faq', $faq, 'ttbm_display_faq' );
		self::fill_section( $tour_id, 'ttbm_why_choose_us_texts', $d['highlights'], 'ttbm_display_why_choose_us' );

		// --- "Have a question?" box. ---
		self::fill_meta( $tour_id, 'ttbm_contact_email', get_option( 'admin_email' ) );
		self::fill_meta( $tour_id, 'ttbm_contact_phone', '+1 555 0100' );
		self::fill_meta( $tour_id, 'ttbm_contact_text', __( 'Not sure this tour is right for you? Send us a message or give us a call and our travel team will help you plan it.', 'travail' ) );
		self::fill_meta( $tour_id, 'ttbm_display_get_question', 'on' );

		// --- Activities + rating + badge switches. ---
		self::fill_meta( $tour_id, 'ttbm_display_activities', 'on' );
		self::fill_meta( $tour_id, 'ttbm_display_rating', 'on' );
		self::fill_meta( $tour_id, 'ttbm_display_duration', 'on' );
		if ( 'on' === get_post_meta( $tour_id, 'ttbm_best_seller', true ) ) {
			self::fill_meta( $tour_id, 'ttbm_top_picks_deals', array( 'popular' ) );
			self::fill_meta( $tour_id, 'ttbm_display_top_picks_deals', 'on' );
		}

		// --- Category + organizer (the booking card's "Organized by" reads the organizer term). ---
		if ( ! has_term( '', 'ttbm_tour_cat', $tour_id ) ) {
			$term_id = self::sample_upsert_term( 'ttbm_tour_cat', __( 'Fixed Tour', 'travail' ) );
			if ( $term_id ) {
				wp_set_object_terms( $tour_id, array( $term_id ), 'ttbm_tour_cat' );
			}
		}
		if ( ! has_term( '', 'ttbm_tour_org', $tour_id ) ) {
			$term_id = self::sample_upsert_term( 'ttbm_tour_org', __( 'Travail Tours', 'travail' ) );
			if ( $term_id ) {
				wp_set_object_terms( $tour_id, array( $term_id ), 'ttbm_tour_org' );
			}
		}

		// --- Tour guides: two of the sample guides, rotated so tours don't all share the same pair. ---
		if ( count( $guides ) >= 2 ) {
			$pick = array(
				(string) $guides[ $index % count( $guides ) ],
				(string) $guides[ ( $index + 1 ) % count( $guides ) ],
			);
			if ( self::fill_meta( $tour_id, 'ttbm_tour_guide', $pick ) ) {
				update_post_meta( $tour_id, 'ttbm_display_tour_guide', 'on' );
				self::fill_meta( $tour_id, 'ttbm_guide_style', 'carousel' );
				self::fill_meta( $tour_id, 'ttbm_guide_image_style', 'squire' );
				self::fill_meta( $tour_id, 'ttbm_guide_description_style', 'full' );
			}
		}

		// --- Related tours: other demo tours sharing the activity first, then the rest. ---
		$same = array();
		$rest = array();
		$activity_terms = wp_get_post_terms( $tour_id, 'ttbm_tour_activities', array( 'fields' => 'ids' ) );
		foreach ( $demo_ids as $other_key => $other_id ) {
			if ( $other_id === $tour_id ) {
				continue;
			}
			$other_terms = wp_get_post_terms( $other_id, 'ttbm_tour_activities', array( 'fields' => 'ids' ) );
			if ( is_array( $activity_terms ) && is_array( $other_terms ) && array_intersect( $activity_terms, $other_terms ) ) {
				$same[] = (string) $other_id;
			} else {
				$rest[] = (string) $other_id;
			}
		}
		$related = array_slice( array_merge( $same, $rest ), 0, 4 );
		if ( $related ) {
			self::fill_section( $tour_id, 'ttbm_related_tour', $related, 'ttbm_display_related' );
		}

		// --- Reviews behind the star rating. ---
		self::seed_demo_reviews( $tour_id, $index, $d['rating'] );
	}

	/**
	 * Make a tour run every day from today, with no end date ("never"), closed on the given weekdays.
	 * Same fields the tour editor saves for "Repeated Dates" + "Off Days".
	 *
	 * @param int    $tour_id    Tour post ID.
	 * @param array  $offdays    Weekday keys (mon, tue, wed, thu, fri, sat, sun).
	 * @param string $start_time Start time, H:i.
	 */
	protected static function set_repeated_daily( $tour_id, $offdays, $start_time ) {
		update_post_meta( $tour_id, 'ttbm_travel_type', 'repeated' );
		update_post_meta( $tour_id, 'ttbm_travel_repeated_start_date', current_time( 'Y-m-d' ) );
		update_post_meta( $tour_id, 'ttbm_travel_repeated_after', '1' );
		update_post_meta( $tour_id, 'ttbm_repeat_type', 'continue' );
		update_post_meta( $tour_id, 'ttbm_travel_repeated_end_date', '' );
		update_post_meta( $tour_id, 'ttbm_travel_repeated_start_time', $start_time );
		update_post_meta( $tour_id, 'ttbm_travel_start_time', $start_time );
		update_post_meta( $tour_id, 'ttbm_enable_off_schedule', 'yes' );
		update_post_meta( $tour_id, 'mep_ticket_offdays', array_values( $offdays ) );
		// A fixed booking deadline would close a tour that runs every day.
		delete_post_meta( $tour_id, 'ttbm_travel_reg_end_date' );

		// Refresh the "next date" the tour lists and search filter on.
		if ( class_exists( 'TTBM_Function' ) && method_exists( 'TTBM_Function', 'update_upcoming_date_month' ) ) {
			TTBM_Function::update_upcoming_date_month( $tour_id, 'yes' );
		}
	}

	/**
	 * Adult + Child (+ Senior) ticket rows for a demo tour.
	 *
	 * @param float  $adult_price Adult price.
	 * @param string $adult_sale  Adult sale price, or ''.
	 * @param bool   $senior      Add a Senior ticket.
	 * @return array
	 */
	protected static function demo_ticket_rows( $adult_price, $adult_sale, $senior ) {
		$row = function ( $name, $icon, $factor, $qty, $description ) use ( $adult_price, $adult_sale ) {
			return array(
				'ticket_type_icon'        => $icon,
				'ticket_type_name'        => $name,
				'ticket_type_price'       => (string) round( $adult_price * $factor ),
				'sale_price'              => '' !== $adult_sale ? (string) round( (float) $adult_sale * $factor ) : '',
				'ticket_type_qty'         => (string) $qty,
				'ticket_type_default_qty' => '0',
				'ticket_type_resv_qty'    => '0',
				'ticket_type_qty_type'    => 'inputbox',
				'ticket_type_description' => $description,
			);
		};
		$rows = array(
			$row( __( 'Adult', 'travail' ), 'mi mi-employee-man-alt', 1, 20, __( 'Ages 12 and over.', 'travail' ) ),
			$row( __( 'Child', 'travail' ), 'mi mi-child-head', 0.7, 10, __( 'Ages 3 to 11, with an adult.', 'travail' ) ),
		);
		if ( $senior ) {
			$rows[] = $row( __( 'Senior', 'travail' ), 'mi mi-employee-man-alt', 0.85, 10, __( 'Ages 65 and over.', 'travail' ) );
		}
		return $rows;
	}

	/**
	 * The "What's included" list only shows items that exist as feature terms, so make sure the
	 * ones the demo tours use are there (with the plugin's icons).
	 */
	protected static function ensure_feature_terms() {
		$features = array(
			'Accommodation'   => 'mi mi-bed-alt',
			'Airport Transfer' => 'mi mi-plane-alt',
			'BBQ Night'       => 'mi mi-grill',
			'Breakfast'       => 'mi mi-burger-fries',
			'Flights'         => 'mi mi-plane-alt',
			'Guide'           => 'mi mi-guide',
			'Insurance'       => 'mi mi-hand-holding-seeding',
			'Lunch'           => 'mi mi-bowl-rice',
			'Meals'           => 'mi mi-burger-glass',
			'Outing Ticket'   => 'mi mi-ticket',
			'Transport'       => 'mi mi-bus',
			'Welcome Drinks'  => 'mi mi-drink-alt',
		);
		foreach ( $features as $name => $icon ) {
			self::sample_upsert_term( 'ttbm_tour_features_list', $name, '', array( 'ttbm_feature_icon' => $icon ) );
		}
	}

	/**
	 * Five sample reviews per demo tour whose average is the tour's star rating (4.7 to 5.0).
	 * Skipped when the tour already has reviews or the review post type is not registered.
	 *
	 * @param int   $tour_id Tour post ID.
	 * @param int   $index   Tour position, used to vary reviewer names.
	 * @param float $target  Star rating the five reviews should average.
	 */
	protected static function seed_demo_reviews( $tour_id, $index, $target ) {
		if ( ! post_type_exists( 'ttbm_tour_review' ) ) {
			return;
		}
		$existing = get_posts(
			array(
				'post_type'      => 'ttbm_tour_review',
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => 1,
				'meta_key'       => 'ttbm_tour_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one-off import.
				'meta_value'     => $tour_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- one-off import.
				'no_found_rows'  => true,
			)
		);
		if ( $existing ) {
			return;
		}

		$halves = max( 0, min( 5, (int) round( ( 5 - $target ) * 10 ) ) ); // 4.9 → one 4.5-star review, 4.7 → three.

		$reviews = array(
			array( __( 'Best day of our trip', 'travail' ), __( 'Everything was well organized from pickup to drop-off, and our guide clearly loved sharing the place with us. Worth every penny.', 'travail' ) ),
			array( __( 'Would book again', 'travail' ), __( 'Small group, relaxed pace and plenty of time for photos. The guide knew the best spots and kept it fun for everyone.', 'travail' ) ),
			array( __( 'Great for families', 'travail' ), __( 'We travelled with two kids and they were looked after the whole time. Clear instructions, friendly staff and no stress.', 'travail' ) ),
			array( __( 'Exactly as described', 'travail' ), __( 'The itinerary matched what we booked, timings were spot on and the extras were good value. Easy booking too.', 'travail' ) ),
			array( __( 'A real highlight', 'travail' ), __( 'Stunning views and a guide full of stories. A couple of minutes late at the start, but that did not take anything away from it.', 'travail' ) ),
		);
		$names = array( 'Sarah M.', 'James T.', 'Priya K.', 'Daniel O.', 'Emily R.', 'Lucas F.', 'Aiko N.', 'Omar H.' );

		$sum = 0;
		foreach ( $reviews as $i => $review ) {
			$rating   = $i >= 5 - $halves ? 4.5 : 5;
			$sum     += $rating;
			$days_ago = 60 - ( $i * 12 );
			$date     = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $days_ago . ' days' ) );
			$name     = $names[ ( $index + $i ) % count( $names ) ];

			$review_id = wp_insert_post(
				array(
					'post_title'    => $review[0],
					'post_content'  => $review[1],
					'post_type'     => 'ttbm_tour_review',
					'post_status'   => 'publish',
					'post_author'   => 0,
					'post_date'     => $date,
					'post_date_gmt' => $date,
				)
			);
			if ( ! $review_id || is_wp_error( $review_id ) ) {
				continue;
			}
			update_post_meta( $review_id, 'ttbm_tour_id', $tour_id );
			update_post_meta( $review_id, 'ttbm_review_rating_meta', $rating );
			update_post_meta( $review_id, 'ttbm_tour_review_cust_name', $name );
			update_post_meta( $review_id, 'ttbm_tour_review_cust_email', strtolower( preg_replace( '/[^a-z]/i', '', $name ) ) . '@example.com' );
			update_post_meta( $review_id, 'ttbm_tour_review_cust_ID', 0 );
		}

		$average = round( $sum / count( $reviews ), 1 );
		update_post_meta( $tour_id, 'ttbm_tour_rating', $average );
		update_post_meta( $tour_id, 'ttbm_rating_average', $average );
		update_post_meta( $tour_id, 'ttbm_rating_count', count( $reviews ) );
	}

	/**
	 * FAQ entry added to every demo tour (matches the booking card's cancellation line).
	 *
	 * @return array
	 */
	protected static function demo_shared_faq() {
		return array(
			array(
				__( 'What is the cancellation policy?', 'travail' ),
				__( 'Cancel up to 24 hours before the start time for a full refund. Cancellations made later than that are not refunded.', 'travail' ),
			),
		);
	}

	/**
	 * Per-tour content for the demo tours, keyed like create_demo_tours().
	 *
	 * extras: [ icon, name, price, description ]. included/excluded: feature term names.
	 *
	 * @return array
	 */
	protected static function demo_tour_details() {
		return array(
			'bali-sunrise'    => array(
				'price'         => '89',
				'min_age'       => '8',
				'start_place'   => __( 'Ubud hotel pickup', 'travail' ),
				'languages'     => array( 'en_US', 'id_ID' ),
				'lat'           => '-8.2421',
				'lon'           => '115.3750',
				'start_time'    => '03:30',
				'end_time'      => '09:30',
				'senior_ticket' => true,
				'included'      => array( 'Transport', 'Guide', 'Breakfast', 'Welcome Drinks' ),
				'excluded'      => array( 'Lunch', 'Insurance', 'Airport Transfer' ),
				'extras'        => array(
					array( 'mi mi-camera-retro', __( 'Drone photo package', 'travail' ), '25', __( 'Aerial photos and a short video of the crater at sunrise, sent the same day.', 'travail' ) ),
					array( 'mi mi-coffee-heart', __( 'Luwak coffee tasting', 'travail' ), '12', __( 'A stop at a coffee plantation on the way back to taste Bali\'s famous coffees.', 'travail' ) ),
					array( 'mi mi-drink-alt', __( 'Hot spring entry', 'travail' ), '10', __( 'Entry to the Toya Devasya natural hot springs after the jeep ride.', 'travail' ) ),
				),
				'itinerary'     => array(
					array( __( '03:30 — Hotel pickup', 'travail' ), __( 'Your driver collects you from your Ubud hotel and drives you to the jeep base at Kintamani.', 'travail' ) ),
					array( __( '04:45 — Jeep ride up Mount Batur', 'travail' ), __( 'Climb the lava fields in a vintage 4x4 to the black-sand viewpoint above the crater lake.', 'travail' ) ),
					array( __( '05:45 — Sunrise and breakfast', 'travail' ), __( 'Watch the sun come up over Mount Agung with a hot drink and a breakfast cooked on volcanic steam.', 'travail' ) ),
					array( __( '07:30 — Rice terraces and hot springs', 'travail' ), __( 'Drive back down past rice terraces, with an optional soak in the natural hot springs.', 'travail' ) ),
					array( __( '09:30 — Drop-off', 'travail' ), __( 'Return to your hotel in Ubud in time for a late morning swim.', 'travail' ) ),
				),
				'faq'           => array(
					array( __( 'How early is the pickup?', 'travail' ), __( 'Around 03:30 from Ubud. Hotels further away are collected earlier; we confirm the exact time the day before.', 'travail' ) ),
					array( __( 'Is the jeep ride bumpy?', 'travail' ), __( 'Yes, parts of the lava track are rough. It is not recommended for travellers who are pregnant or have back problems.', 'travail' ) ),
					array( __( 'What should I wear?', 'travail' ), __( 'Warm layers. It can be around 10°C at the top before sunrise, even when the coast is hot.', 'travail' ) ),
				),
				'highlights'    => array(
					__( 'Sunrise over Mount Agung from the Batur lava fields', 'travail' ),
					__( 'Breakfast cooked on volcanic steam', 'travail' ),
					__( 'Small groups of up to six per jeep', 'travail' ),
				),
			),
			'dubai-safari'    => array(
				'price'         => '120',
				'min_age'       => '3',
				'start_place'   => __( 'Dubai hotel pickup', 'travail' ),
				'languages'     => array( 'en_US', 'ar' ),
				'lat'           => '24.9857',
				'lon'           => '55.6911',
				'start_time'    => '15:00',
				'end_time'      => '23:00',
				'senior_ticket' => true,
				'included'      => array( 'Transport', 'Guide', 'BBQ Night', 'Welcome Drinks' ),
				'excluded'      => array( 'Insurance', 'Airport Transfer', 'Accommodation' ),
				'extras'        => array(
					array( 'mi mi-bus', __( 'Quad bike (30 min)', 'travail' ), '45', __( 'Ride your own quad bike across the dunes with an instructor.', 'travail' ) ),
					array( 'mi mi-camera-retro', __( 'Falcon photo session', 'travail' ), '15', __( 'Hold a trained falcon and take home the photos.', 'travail' ) ),
					array( 'mi mi-ticket', __( 'VIP front-row seating', 'travail' ), '20', __( 'Reserved front-row majlis seats for the evening show.', 'travail' ) ),
				),
				'itinerary'     => array(
					array( __( '15:00 — Pickup by 4x4', 'travail' ), __( 'A Land Cruiser collects you from your Dubai hotel and heads to the Lahbab red dunes.', 'travail' ) ),
					array( __( '16:30 — Dune bashing', 'travail' ), __( 'Thirty minutes of dune driving with an expert desert driver, then a stop for sandboarding.', 'travail' ) ),
					array( __( '18:00 — Sunset camel ride', 'travail' ), __( 'A short camel ride and photo stop as the sun sets over the desert.', 'travail' ) ),
					array( __( '19:00 — Camp dinner and shows', 'travail' ), __( 'Barbecue buffet at a Bedouin-style camp with tanoura and fire shows.', 'travail' ) ),
					array( __( '23:00 — Return to hotel', 'travail' ), __( 'Drive back to the city and drop-off at your hotel.', 'travail' ) ),
				),
				'faq'           => array(
					array( __( 'Is dune bashing safe for children?', 'travail' ), __( 'Children aged 3 and over can join with a child seat. If you prefer, ask the driver for a gentler ride.', 'travail' ) ),
					array( __( 'Are vegetarian meals available?', 'travail' ), __( 'Yes, the buffet always has vegetarian and vegan dishes. Tell us about other diets when you book.', 'travail' ) ),
					array( __( 'Is alcohol served at the camp?', 'travail' ), __( 'Soft drinks, water, tea and coffee are included. Alcohol is available to buy at the camp bar.', 'travail' ) ),
				),
				'highlights'    => array(
					__( 'Dune bashing on the Lahbab red dunes', 'travail' ),
					__( 'Barbecue dinner under the stars', 'travail' ),
					__( 'Hotel pickup and drop-off included', 'travail' ),
				),
			),
			'paris-eiffel'    => array(
				'price'         => '85',
				'min_age'       => '0',
				'start_place'   => __( 'Eiffel Tower, Champ de Mars', 'travail' ),
				'languages'     => array( 'en_US', 'fr_FR' ),
				'lat'           => '48.8584',
				'lon'           => '2.2945',
				'start_time'    => '17:00',
				'end_time'      => '21:00',
				'senior_ticket' => true,
				'included'      => array( 'Guide', 'Outing Ticket', 'Welcome Drinks' ),
				'excluded'      => array( 'Transport', 'Meals', 'Insurance' ),
				'extras'        => array(
					array( 'mi mi-ticket', __( 'Summit access', 'travail' ), '30', __( 'Go up to the very top of the tower, 276 m above the city.', 'travail' ) ),
					array( 'mi mi-drink-alt', __( 'Champagne on the cruise', 'travail' ), '18', __( 'A glass of champagne served on board the Seine cruise.', 'travail' ) ),
					array( 'mi mi-camera-retro', __( 'Photo package', 'travail' ), '20', __( 'Professional photos of your group at the Trocadéro viewpoint.', 'travail' ) ),
				),
				'itinerary'     => array(
					array( __( '17:00 — Meet at the Eiffel Tower', 'travail' ), __( 'Meet your guide at the south pillar and skip the ticket line.', 'travail' ) ),
					array( __( '17:15 — Second floor at golden hour', 'travail' ), __( 'Ride the lift to the second floor and take in the view over the Seine and Montmartre.', 'travail' ) ),
					array( __( '18:45 — Walk to the river', 'travail' ), __( 'A short walk down to the Port de la Bourdonnais pier.', 'travail' ) ),
					array( __( '19:30 — Seine river cruise', 'travail' ), __( 'One-hour cruise past the Louvre, Notre-Dame and the Musée d\'Orsay as the lights come on.', 'travail' ) ),
					array( __( '21:00 — Tour ends', 'travail' ), __( 'Finish at the pier, right next to the sparkling tower.', 'travail' ) ),
				),
				'faq'           => array(
					array( __( 'Do we skip the line?', 'travail' ), __( 'Yes, your guide has timed entry tickets, so you skip the ticket queue. Security checks still apply to everyone.', 'travail' ) ),
					array( __( 'Is the tour wheelchair accessible?', 'travail' ), __( 'The tower lifts and the cruise boat are accessible. Let us know when booking so we can plan the route.', 'travail' ) ),
					array( __( 'Can I bring a stroller?', 'travail' ), __( 'Folding strollers are fine. Large strollers may need to be left at the tower\'s stroller park.', 'travail' ) ),
				),
				'highlights'    => array(
					__( 'Skip-the-line Eiffel Tower entry', 'travail' ),
					__( 'Seine cruise as the city lights up', 'travail' ),
					__( 'Live guide in English and French', 'travail' ),
				),
			),
			'swiss-hiking'    => array(
				'price'         => '149',
				'min_age'       => '10',
				'start_place'   => __( 'Grindelwald station', 'travail' ),
				'languages'     => array( 'en_US', 'de_DE' ),
				'lat'           => '46.6242',
				'lon'           => '8.0414',
				'start_time'    => '08:00',
				'end_time'      => '16:00',
				'senior_ticket' => true,
				'included'      => array( 'Guide', 'Lunch', 'Outing Ticket', 'Insurance' ),
				'excluded'      => array( 'Accommodation', 'Airport Transfer', 'Breakfast' ),
				'extras'        => array(
					array( 'mi mi-guide', __( 'Trekking pole rental', 'travail' ), '8', __( 'A pair of adjustable trekking poles for the day.', 'travail' ) ),
					array( 'mi mi-ticket', __( 'First Cliff Walk', 'travail' ), '15', __( 'Add the cliff walk and its viewing platform at the top station.', 'travail' ) ),
					array( 'mi mi-bus', __( 'Interlaken transfer', 'travail' ), '20', __( 'Return train tickets between Interlaken and Grindelwald.', 'travail' ) ),
				),
				'itinerary'     => array(
					array( __( '08:00 — Gondola to First', 'travail' ), __( 'Meet at Grindelwald station and ride the gondola up to First at 2,168 m.', 'travail' ) ),
					array( __( '09:00 — Hike to Bachalpsee', 'travail' ), __( 'An easy climb to the Bachalpsee lake, with the Eiger, Mönch and Jungfrau reflected in it.', 'travail' ) ),
					array( __( '12:00 — Lunch at a mountain hut', 'travail' ), __( 'Swiss rösti and local cheese at a family-run hut.', 'travail' ) ),
					array( __( '13:30 — Descent via Bort', 'travail' ), __( 'Walk down through alpine meadows to Bort, then take the gondola back to the village.', 'travail' ) ),
					array( __( '16:00 — Back in Grindelwald', 'travail' ), __( 'The tour ends in the village centre.', 'travail' ) ),
				),
				'faq'           => array(
					array( __( 'How hard is the hike?', 'travail' ), __( 'Moderate: about 12 km and 500 m of climbing on good paths. You should be comfortable walking for 5 hours.', 'travail' ) ),
					array( __( 'What happens if the weather is bad?', 'travail' ), __( 'If the guide decides the route is unsafe, we move you to another date or give you a full refund.', 'travail' ) ),
					array( __( 'What should I bring?', 'travail' ), __( 'Hiking boots, a rain jacket, sun cream, a hat and at least one litre of water.', 'travail' ) ),
				),
				'highlights'    => array(
					__( 'Views of the Eiger, Mönch and Jungfrau', 'travail' ),
					__( 'Certified mountain guide', 'travail' ),
					__( 'Traditional lunch at a mountain hut', 'travail' ),
				),
			),
			'santorini-sail'  => array(
				'price'         => '119',
				'min_age'       => '0',
				'start_place'   => __( 'Ammoudi Bay, Oia', 'travail' ),
				'languages'     => array( 'en_US', 'el' ),
				'lat'           => '36.4618',
				'lon'           => '25.3753',
				'start_time'    => '15:30',
				'end_time'      => '20:30',
				'senior_ticket' => false,
				'included'      => array( 'Meals', 'Welcome Drinks', 'Guide', 'Transport' ),
				'excluded'      => array( 'Accommodation', 'Airport Transfer', 'Insurance' ),
				'extras'        => array(
					array( 'mi mi-drink-alt', __( 'Premium wine pairing', 'travail' ), '25', __( 'Three Santorini wines chosen to go with the mezze.', 'travail' ) ),
					array( 'mi mi-camera-retro', __( 'Sunset photo shoot', 'travail' ), '35', __( 'A photographer on board for 30 minutes of sunset photos.', 'travail' ) ),
				),
				'itinerary'     => array(
					array( __( '15:30 — Board at Ammoudi Bay', 'travail' ), __( 'Board the catamaran below Oia and meet the crew.', 'travail' ) ),
					array( __( '16:30 — Red Beach and swim stop', 'travail' ), __( 'Sail along the caldera to the Red Beach for a swim and snorkel.', 'travail' ) ),
					array( __( '17:30 — Hot springs', 'travail' ), __( 'Swim in the warm volcanic springs near Nea Kameni.', 'travail' ) ),
					array( __( '18:30 — Dinner on board', 'travail' ), __( 'A freshly cooked Greek dinner with local wine.', 'travail' ) ),
					array( __( '20:00 — Sunset at Oia', 'travail' ), __( 'Watch the sun go down from the water before sailing back to the harbour.', 'travail' ) ),
				),
				'faq'           => array(
					array( __( 'Is there a toilet on board?', 'travail' ), __( 'Yes, every catamaran has a toilet and a shower for after your swim.', 'travail' ) ),
					array( __( 'Is hotel transfer included?', 'travail' ), __( 'Yes, a shared minibus collects you from hotels anywhere on the island.', 'travail' ) ),
					array( __( 'What if the sea is rough?', 'travail' ), __( 'If the port authority cancels sailings, you can choose another date or a full refund.', 'travail' ) ),
				),
				'highlights'    => array(
					__( 'Sunset from the water at Oia', 'travail' ),
					__( 'Swim stops at the Red Beach and hot springs', 'travail' ),
					__( 'Dinner and local wine on board', 'travail' ),
				),
			),
			'tokyo-food-walk' => array(
				'price'         => '75',
				'min_age'       => '6',
				'start_place'   => __( 'Hachiko statue, Shibuya', 'travail' ),
				'languages'     => array( 'en_US', 'ja' ),
				'lat'           => '35.6595',
				'lon'           => '139.7005',
				'start_time'    => '17:30',
				'end_time'      => '22:30',
				'senior_ticket' => true,
				'included'      => array( 'Guide', 'Meals', 'Welcome Drinks' ),
				'excluded'      => array( 'Transport', 'Accommodation', 'Insurance' ),
				'extras'        => array(
					array( 'mi mi-drink-alt', __( 'Sake tasting flight', 'travail' ), '18', __( 'Taste three regional sakes with a sommelier.', 'travail' ) ),
					array( 'mi mi-ticket', __( 'Shibuya Sky ticket', 'travail' ), '20', __( 'Entry to the Shibuya Sky rooftop after the walk.', 'travail' ) ),
					array( 'mi mi-camera-retro', __( 'Photo package', 'travail' ), '15', __( 'Photos of your evening, sent the next day.', 'travail' ) ),
				),
				'itinerary'     => array(
					array( __( '17:30 — Meet at Hachiko', 'travail' ), __( 'Meet your guide at the Hachiko statue and cross the famous Shibuya scramble.', 'travail' ) ),
					array( __( '18:00 — Nonbei Yokocho', 'travail' ), __( 'Yakitori and a drink in "Drunkard\'s Alley", a lane of tiny bars.', 'travail' ) ),
					array( __( '19:15 — Konno Hachimangu shrine', 'travail' ), __( 'Visit a quiet shrine that has stood here for more than 900 years.', 'travail' ) ),
					array( __( '20:00 — Ramen and gyoza', 'travail' ), __( 'A local ramen counter, then gyoza at a family-run spot.', 'travail' ) ),
					array( __( '21:30 — Dessert', 'travail' ), __( 'Finish with taiyaki or matcha sweets before the tour ends near the station.', 'travail' ) ),
				),
				'faq'           => array(
					array( __( 'How much food is included?', 'travail' ), __( 'Six tastings, which add up to a full dinner. Come hungry.', 'travail' ) ),
					array( __( 'Can you cater for allergies?', 'travail' ), __( 'Vegetarian and most allergies can be catered for if you tell us when you book. Gluten-free is harder in Japan.', 'travail' ) ),
					array( __( 'How far do we walk?', 'travail' ), __( 'About 3 km at an easy pace, with breaks at every stop.', 'travail' ) ),
				),
				'highlights'    => array(
					__( 'Six tastings in Shibuya\'s backstreets', 'travail' ),
					__( 'A 900-year-old shrine away from the crowds', 'travail' ),
					__( 'Local guide who grew up in Tokyo', 'travail' ),
				),
			),
			'iceland-lights'  => array(
				'price'         => '199',
				'min_age'       => '6',
				'start_place'   => __( 'Reykjavik bus stop pickup', 'travail' ),
				'languages'     => array( 'en_US', 'is_IS' ),
				'lat'           => '64.1466',
				'lon'           => '-21.9426',
				'start_time'    => '19:00',
				'end_time'      => '01:00',
				'senior_ticket' => true,
				'included'      => array( 'Transport', 'Guide', 'Welcome Drinks' ),
				'excluded'      => array( 'Meals', 'Accommodation', 'Insurance' ),
				'extras'        => array(
					array( 'mi mi-camera-retro', __( 'Aurora photos', 'travail' ), '30', __( 'Your guide photographs you under the lights with a professional camera.', 'travail' ) ),
					array( 'mi mi-guide', __( 'Thermal suit rental', 'travail' ), '15', __( 'A warm insulated overall to wear over your clothes.', 'travail' ) ),
					array( 'mi mi-burger-glass', __( 'Lamb soup dinner', 'travail' ), '20', __( 'Traditional Icelandic lamb soup at a countryside stop.', 'travail' ) ),
				),
				'itinerary'     => array(
					array( __( '19:00 — Pickup in Reykjavik', 'travail' ), __( 'Your guide picks you up at your nearest bus stop.', 'travail' ) ),
					array( __( '20:00 — Drive away from city lights', 'travail' ), __( 'Head out to the dark countryside, choosing the spot with the clearest skies.', 'travail' ) ),
					array( __( '21:00 — Aurora hunt', 'travail' ), __( 'Wait for the northern lights with hot cocoa and tips for photographing them.', 'travail' ) ),
					array( __( '01:00 — Return to Reykjavik', 'travail' ), __( 'Drive back to the city and drop-off at your bus stop.', 'travail' ) ),
				),
				'faq'           => array(
					array( __( 'What if we don\'t see the northern lights?', 'travail' ), __( 'If the aurora doesn\'t show, you can join another night for free.', 'travail' ) ),
					array( __( 'When is the best season?', 'travail' ), __( 'From September to April, when the nights are long and dark.', 'travail' ) ),
					array( __( 'How cold does it get?', 'travail' ), __( 'It can drop to -10°C at night. Wear warm layers, a hat, gloves and boots.', 'travail' ) ),
				),
				'highlights'    => array(
					__( 'Free second night if the aurora doesn\'t appear', 'travail' ),
					__( 'Guide who reads the forecast to find clear skies', 'travail' ),
					__( 'Hot cocoa to keep warm', 'travail' ),
				),
			),
			'patagonia'       => array(
				'price'         => '1290',
				'min_age'       => '12',
				'start_place'   => __( 'El Calafate airport', 'travail' ),
				'languages'     => array( 'en_US', 'es_ES' ),
				'lat'           => '-49.3315',
				'lon'           => '-72.8863',
				'start_time'    => '10:00',
				'end_time'      => '12:00',
				'senior_ticket' => false,
				'included'      => array( 'Accommodation', 'Breakfast', 'Guide', 'Transport', 'Airport Transfer' ),
				'excluded'      => array( 'Flights', 'Insurance', 'Lunch' ),
				'extras'        => array(
					array( 'mi mi-guide', __( 'Glacier ice trek', 'travail' ), '180', __( 'Walk on the Perito Moreno glacier with crampons and a glacier guide.', 'travail' ) ),
					array( 'mi mi-bed-alt', __( 'Private room upgrade', 'travail' ), '320', __( 'A private room in every hotel instead of a shared twin.', 'travail' ) ),
					array( 'mi mi-drink-alt', __( 'Malbec tasting dinner', 'travail' ), '60', __( 'An Argentine steak dinner with three Malbec wines.', 'travail' ) ),
				),
				'itinerary'     => array(
					array( __( 'Day 1: Arrive in El Calafate', 'travail' ), __( 'Airport pickup and welcome briefing at your lakeside hotel.', 'travail' ) ),
					array( __( 'Day 2: Perito Moreno glacier', 'travail' ), __( 'Walk the balconies facing the glacier and watch ice crash into the lake.', 'travail' ) ),
					array( __( 'Day 3: Drive to El Chaltén', 'travail' ), __( 'Cross the steppe to the trekking capital of Argentina, stopping for photos of Mount Fitz Roy.', 'travail' ) ),
					array( __( 'Day 4: Laguna de los Tres', 'travail' ), __( 'A full-day hike to the lake at the foot of Fitz Roy.', 'travail' ) ),
					array( __( 'Day 5: Laguna Torre', 'travail' ), __( 'A gentler hike to the glacier lake below Cerro Torre.', 'travail' ) ),
					array( __( 'Day 6: Cross into Chile', 'travail' ), __( 'Travel to Puerto Natales, gateway to Torres del Paine.', 'travail' ) ),
					array( __( 'Day 7: Torres del Paine', 'travail' ), __( 'Hike to the base of the granite towers.', 'travail' ) ),
					array( __( 'Day 8: Departure', 'travail' ), __( 'Transfer to Puerto Natales airport for your flight home.', 'travail' ) ),
				),
				'faq'           => array(
					array( __( 'How fit do I need to be?', 'travail' ), __( 'You should be able to hike 6 to 8 hours a day on mountain trails. Each hike has a shorter option.', 'travail' ) ),
					array( __( 'Are flights included?', 'travail' ), __( 'International flights are not included. All transfers inside Patagonia are.', 'travail' ) ),
					array( __( 'Where do we stay?', 'travail' ), __( 'Comfortable 3-star hotels and lodges, in twin rooms. A private room is available as an extra.', 'travail' ) ),
				),
				'highlights'    => array(
					__( 'Perito Moreno, Fitz Roy and Torres del Paine in one trip', 'travail' ),
					__( 'Small group of up to 12 travellers', 'travail' ),
					__( 'Every transfer and hotel arranged for you', 'travail' ),
				),
			),
			'deal-maldives'   => array(
				'price'         => '2890',
				'min_age'       => '0',
				'start_place'   => __( 'Velana International Airport, Malé', 'travail' ),
				'languages'     => array( 'en_US' ),
				'lat'           => '4.1755',
				'lon'           => '73.5093',
				'start_time'    => '14:00',
				'end_time'      => '12:00',
				'senior_ticket' => false,
				'included'      => array( 'Accommodation', 'Meals', 'Airport Transfer', 'Welcome Drinks' ),
				'excluded'      => array( 'Flights', 'Insurance', 'Guide' ),
				'extras'        => array(
					array( 'mi mi-guide', __( 'Scuba dive', 'travail' ), '120', __( 'A guided reef dive with all equipment. Beginners welcome.', 'travail' ) ),
					array( 'mi mi-drink-alt', __( 'Sandbank dinner', 'travail' ), '250', __( 'A private dinner for two on a sandbank at sunset.', 'travail' ) ),
					array( 'mi mi-plane-alt', __( 'Seaplane transfer upgrade', 'travail' ), '400', __( 'Arrive by seaplane instead of speedboat.', 'travail' ) ),
				),
				'itinerary'     => array(
					array( __( 'Day 1: Arrival', 'travail' ), __( 'Speedboat from Malé to the resort, welcome drink and check in to your overwater villa.', 'travail' ) ),
					array( __( 'Days 2–3: Reef and lagoon', 'travail' ), __( 'Guided snorkelling on the house reef and time to relax in the lagoon.', 'travail' ) ),
					array( __( 'Day 4: Island hopping', 'travail' ), __( 'Visit a local island and a sandbank by boat.', 'travail' ) ),
					array( __( 'Days 5–6: Sunset cruise', 'travail' ), __( 'Free days at the resort, with a sunset dolphin cruise.', 'travail' ) ),
					array( __( 'Day 7: Spa day', 'travail' ), __( 'A spa treatment and a final dinner on the beach.', 'travail' ) ),
					array( __( 'Day 8: Departure', 'travail' ), __( 'Check out and speedboat back to Malé airport.', 'travail' ) ),
				),
				'faq'           => array(
					array( __( 'What does all-inclusive cover?', 'travail' ), __( 'Breakfast, lunch, dinner, snacks, soft drinks and house wine. Premium drinks cost extra.', 'travail' ) ),
					array( __( 'Is the villa over the water?', 'travail' ), __( 'Yes, every booking is in an overwater villa with steps down to the lagoon.', 'travail' ) ),
					array( __( 'Do I need a visa?', 'travail' ), __( 'Most nationalities get a free 30-day visa when they arrive. Check the rules for your passport before you travel.', 'travail' ) ),
				),
				'highlights'    => array(
					__( 'Seven nights in an overwater villa', 'travail' ),
					__( 'All meals and drinks included', 'travail' ),
					__( 'Snorkelling and a sunset cruise included', 'travail' ),
				),
			),
			'deal-bali'       => array(
				'price'         => '1450',
				'min_age'       => '0',
				'start_place'   => __( 'Ngurah Rai Airport, Denpasar', 'travail' ),
				'languages'     => array( 'en_US', 'id_ID' ),
				'lat'           => '-8.5069',
				'lon'           => '115.2625',
				'start_time'    => '14:00',
				'end_time'      => '12:00',
				'senior_ticket' => false,
				'included'      => array( 'Accommodation', 'Breakfast', 'Transport', 'Airport Transfer' ),
				'excluded'      => array( 'Flights', 'Lunch', 'Insurance' ),
				'extras'        => array(
					array( 'mi mi-guide', __( 'Balinese cooking class', 'travail' ), '45', __( 'Market visit and a half-day cooking class in Ubud.', 'travail' ) ),
					array( 'mi mi-hand-holding-seeding', __( 'Spa package', 'travail' ), '80', __( 'A two-hour Balinese massage and flower bath.', 'travail' ) ),
					array( 'mi mi-ticket', __( 'Nusa Penida day trip', 'travail' ), '95', __( 'Fast boat to Nusa Penida with Kelingking Beach and snorkelling.', 'travail' ) ),
				),
				'itinerary'     => array(
					array( __( 'Day 1: Arrive in Seminyak', 'travail' ), __( 'Airport pickup and check in to your beach resort.', 'travail' ) ),
					array( __( 'Day 2: Beach day', 'travail' ), __( 'Free day on the beach, ending with sunset at Tanah Lot temple.', 'travail' ) ),
					array( __( 'Day 3: Move to Ubud', 'travail' ), __( 'Your driver takes you to a jungle villa, via the Tegallalang rice terraces.', 'travail' ) ),
					array( __( 'Day 4: Temples and waterfalls', 'travail' ), __( 'Tirta Empul water temple and Tegenungan waterfall with your private driver.', 'travail' ) ),
					array( __( 'Day 5: Free day in Ubud', 'travail' ), __( 'Explore the Monkey Forest, the market and the cafés.', 'travail' ) ),
					array( __( 'Day 6: Departure', 'travail' ), __( 'Transfer to the airport for your flight home.', 'travail' ) ),
				),
				'faq'           => array(
					array( __( 'Is the driver available every day?', 'travail' ), __( 'Your private driver is available for up to 8 hours a day on days 3 and 4, and for every transfer.', 'travail' ) ),
					array( __( 'Can I change the hotels?', 'travail' ), __( 'Yes, ask when you book and we will quote for the hotels you prefer.', 'travail' ) ),
					array( __( 'Is it suitable for families?', 'travail' ), __( 'Yes, both hotels have family rooms and pools. Children travel at the child price.', 'travail' ) ),
				),
				'highlights'    => array(
					__( 'Beach resort and jungle villa in one trip', 'travail' ),
					__( 'Private driver for sightseeing days', 'travail' ),
					__( 'Daily breakfast included', 'travail' ),
				),
			),
			'deal-iceland'    => array(
				'price'         => '3200',
				'min_age'       => '8',
				'start_place'   => __( 'Keflavík International Airport', 'travail' ),
				'languages'     => array( 'en_US', 'is_IS' ),
				'lat'           => '64.1466',
				'lon'           => '-21.9426',
				'start_time'    => '10:00',
				'end_time'      => '16:00',
				'senior_ticket' => false,
				'included'      => array( 'Accommodation', 'Breakfast', 'Guide', 'Transport', 'Airport Transfer' ),
				'excluded'      => array( 'Flights', 'Insurance', 'Meals' ),
				'extras'        => array(
					array( 'mi mi-ticket', __( 'Blue Lagoon entry', 'travail' ), '90', __( 'Comfort entry to the Blue Lagoon with towel and a drink.', 'travail' ) ),
					array( 'mi mi-guide', __( 'Ice cave tour', 'travail' ), '150', __( 'Explore a natural blue ice cave under Vatnajökull (winter only).', 'travail' ) ),
					array( 'mi mi-bus', __( 'Whale watching', 'travail' ), '85', __( 'A three-hour whale watching trip from Húsavík.', 'travail' ) ),
				),
				'itinerary'     => array(
					array( __( 'Day 1: Reykjavik', 'travail' ), __( 'Airport pickup, check in and an evening walk around the old harbour.', 'travail' ) ),
					array( __( 'Day 2: Golden Circle', 'travail' ), __( 'Þingvellir, the Geysir hot springs and Gullfoss waterfall.', 'travail' ) ),
					array( __( 'Day 3: South coast', 'travail' ), __( 'Seljalandsfoss and Skógafoss waterfalls and the black sands of Reynisfjara.', 'travail' ) ),
					array( __( 'Day 4: Glacier lagoon', 'travail' ), __( 'A glacier hike, then icebergs at Jökulsárlón and Diamond Beach.', 'travail' ) ),
					array( __( 'Day 5: East fjords', 'travail' ), __( 'Drive the winding fjords to Egilsstaðir.', 'travail' ) ),
					array( __( 'Day 6: Lake Mývatn', 'travail' ), __( 'Volcanic craters, Dettifoss and the Mývatn nature baths.', 'travail' ) ),
					array( __( 'Day 7: Return to Reykjavik', 'travail' ), __( 'Drive back west via Akureyri and transfer to the airport.', 'travail' ) ),
				),
				'faq'           => array(
					array( __( 'Is the Ring Road open in winter?', 'travail' ), __( 'Yes, but weather can close roads. Your guide may change the order of the days to keep you safe.', 'travail' ) ),
					array( __( 'Do we see the northern lights?', 'travail' ), __( 'From September to April there is a guided aurora hunt on clear nights. Sightings are never guaranteed.', 'travail' ) ),
					array( __( 'What kind of hotels are used?', 'travail' ), __( 'Comfortable country hotels and guesthouses with private bathrooms.', 'travail' ) ),
				),
				'highlights'    => array(
					__( 'The whole Ring Road in seven days', 'travail' ),
					__( 'Glacier hike and hot springs included', 'travail' ),
					__( 'Expert local guide and driver', 'travail' ),
				),
			),
		);
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

			}

			// Backfill the category on every run too, not just at creation —
			// caught a run where the initial wp_set_post_terms() call below
			// didn't stick (post was left on the default "Uncategorized"
			// term WordPress auto-assigns on insert) and, since this only
			// lived inside the create-only branch, no later run ever
			// corrected it. Same rationale as the thumbnail backfill just
			// below: never leave a demo post silently wrong forever just
			// because it already exists.
			$travail_current_cats = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'names' ) );
			if ( is_wp_error( $travail_current_cats ) || array( 'Uncategorized' ) === $travail_current_cats || empty( $travail_current_cats ) ) {
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
			'hero_image'                    => 'https://images.unsplash.com/photo-1464852045489-bccb7d17fe39?w=1920&h=1080&fit=crop&auto=format',
			'why_choose_us_image'            => 'https://images.unsplash.com/photo-1439853949127-fa647821eba0?w=800&h=700&fit=crop&auto=format',
			'newsletter_image'               => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920&h=800&fit=crop&auto=format',
			// Travello homepage (see Customizer → Homepage) — same photos
			// travello.html itself uses for these spots.
			'travello_hero_image'            => 'https://images.unsplash.com/photo-1477346611705-65d1883cee1e?w=1920&h=1080&fit=crop&auto=format',
			'travello_why_us_image'          => 'https://images.unsplash.com/photo-1618479357575-1cbeaf396a93?w=800&h=700&fit=crop&auto=format',
			'travello_newsletter_image'      => 'https://images.unsplash.com/photo-1770838916964-0ae934bf7632?w=1920&h=800&fit=crop&auto=format',
			'travello_service_tours_image'   => 'https://images.unsplash.com/photo-1559628233-eb1b1a45564b?w=600&h=500&fit=crop&auto=format',
			'travello_service_hotels_image'  => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=600&h=500&fit=crop&auto=format',
			'travello_service_transport_image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=600&h=500&fit=crop&auto=format',
			'travello_service_activities_image' => 'https://images.unsplash.com/photo-1618479357575-1cbeaf396a93?w=600&h=500&fit=crop&auto=format',
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
	 * Step: homepage. Sends the blog to its own page, then — when
	 * Elementor is active — builds two fully Elementor-editable homepage
	 * pages (one per built-in design, via Travail_Elementor_Page_Builder)
	 * and activates one of them as the front page, unless a site owner
	 * has already picked one from Travail → Homepages.
	 *
	 * Without Elementor active, page creation is skipped entirely — an
	 * `_elementor_data` payload with no Elementor plugin to render it
	 * would just be a blank page, which is worse than the existing
	 * hook-based homepage front-page.php already falls back to.
	 *
	 * @return string
	 */
	public static function step_homepage() {
		$map = self::get_map();

		// Blog posts get their own page whenever one was created — this is
		// independent of which homepage design is active, since
		// show_on_front='page' supports a dedicated "Posts page" at the
		// same time as a static front page. (Previously this block also
		// forced show_on_front to 'posts' when nothing else had claimed
		// 'page' yet — but that fought the homepage-activation logic
		// below rather than deferring to it, so it's gone.)
		if ( ! empty( $map['pages']['blog'] ) ) {
			update_option( 'page_for_posts', $map['pages']['blog'] );
		}

		if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_elementor_active() ) {
			return __( 'Elementor is not active — install and activate it, then re-run Demo Import to generate two ready-made, fully editable homepage pages.', 'travail' );
		}

		if ( ! class_exists( 'Travail_Elementor_Page_Builder' ) ) {
			return __( 'Homepage builder unavailable — skipped.', 'travail' );
		}

		$context = array(
			'destinations_url'    => ! empty( $map['pages']['destinations'] ) ? get_permalink( $map['pages']['destinations'] ) : '',
			'tours_url'           => ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_tour_booking_manager_active() ) ? get_post_type_archive_link( 'ttbm_tour' ) : '',
			'hero_image'          => get_theme_mod( 'travail_hero_image', '' ),
			'newsletter_image'    => get_theme_mod( 'travail_newsletter_image', '' ),
			'travello_hero_image' => get_theme_mod( 'travail_travello_hero_image', '' ),
		);

		$page_ids = array();
		foreach ( Travail_Elementor_Page_Builder::get_designs() as $design_key => $design ) {
			$elements               = Travail_Elementor_Page_Builder::build_elements( $design_key, $context );
			$page_ids[ $design_key ] = self::create_or_get_elementor_page( $design_key, $design['title'], $elements );
		}

		// Re-affirm whichever homepage is already active every single run
		// — not just the first time — and enforce it onto Settings →
		// Reading regardless of what that screen currently says. This is
		// what keeps the two screens from drifting apart: confirmed, a
		// stray manual change on Settings → Reading (e.g. to "Your latest
		// posts"), or a stale ID left over from an earlier import run
		// whose page had since been trashed/recreated, could otherwise
		// leave the front page silently showing the old hook-based
		// homepage while both admin screens still claimed a design was
		// active. First run ever (nothing chosen yet) defaults to Travello.
		$active_id = self::resolve_active_homepage_id( $page_ids );
		if ( $active_id ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $active_id );
			update_option( 'travail_active_homepage_id', $active_id );
		}

		return __( 'Two Elementor-ready homepage pages are set up — pick and edit them any time under Travail → Homepages.', 'travail' );
	}

	/**
	 * Resolve which page ID should be the active homepage: whatever is
	 * already recorded (re-affirmed, not just trusted blindly — see the
	 * publish-status checks below), falling back to a page manually
	 * assigned via Settings → Reading, falling back to Travello as the
	 * default for a brand-new activation.
	 *
	 * Both existing-choice checks require the referenced page to still
	 * actually exist and be published — an option holding a stale ID
	 * (its page trashed/deleted, or recreated with a new ID by a later
	 * import run) must never be trusted as-is.
	 *
	 * @param array $page_ids This run's design key => page ID map, see step_homepage().
	 * @return int Page ID, or 0 if nothing resolved (shouldn't happen once $page_ids is non-empty).
	 */
	protected static function resolve_active_homepage_id( $page_ids ) {
		$chosen_id = (int) get_option( 'travail_active_homepage_id' );
		if ( $chosen_id && 'publish' === get_post_status( $chosen_id ) ) {
			return $chosen_id;
		}

		if ( 'page' === get_option( 'show_on_front' ) ) {
			$front_page_id = (int) get_option( 'page_on_front' );
			if ( $front_page_id && 'publish' === get_post_status( $front_page_id ) && get_post_meta( $front_page_id, '_travail_homepage_design', true ) ) {
				return $front_page_id;
			}
		}

		return ! empty( $page_ids['travello'] ) ? (int) $page_ids['travello'] : 0;
	}

	/**
	 * Create-or-fetch one of the generated homepage pages.
	 *
	 * `_elementor_data` is (re)written whenever it's safe to do so: on
	 * first creation, or when the page's current Elementor content still
	 * exactly matches what this builder wrote last time (tracked via the
	 * `_travail_homepage_builder_hash` meta) — meaning nobody has edited
	 * it in Elementor since. That lets a theme update's layout fixes
	 * reach an already-imported site on the next Demo Import run, while
	 * still never clobbering a site owner's own edits. A page with
	 * Elementor content but no recorded hash (created before this
	 * tracking existed) is treated as safe to refresh once, after which
	 * it's protected the same as any other.
	 *
	 * @param string $key      Stable design key ('travello' or 'default' — see
	 *                         Travail_Elementor_Page_Builder::get_designs()). Also
	 *                         stored as the page's `_travail_homepage_design` post
	 *                         meta flag so travail_is_travello_home() recognizes it
	 *                         even if the import map option is ever lost.
	 * @param string $title    Page title.
	 * @param array  $elements Elementor element tree from Travail_Elementor_Page_Builder.
	 * @return int Page ID.
	 */
	protected static function create_or_get_elementor_page( $key, $title, $elements ) {
		$page_id = self::create_or_get_page( 'homepage-' . $key, $title );

		// Elementor's own "Full Width" page template — keeps the theme's
		// header/footer but skips page.php entirely, so none of its
		// wrapping <div class="travail-container"> or page-title header
		// (both meant for ordinary pages) end up squeezing/labeling an
		// Elementor-built homepage when it's viewed on its own permalink
		// rather than as the front page (front-page.php has no such
		// wrapper and was never affected by this). 'elementor_header_footer'
		// is Elementor's own stable, documented template slug — confirmed
		// against \Elementor\Modules\PageTemplates\Module::TEMPLATE_HEADER_FOOTER
		// in this install rather than assumed. Backfilled unconditionally,
		// like the design-flag meta below, so a page created before this
		// fix existed is corrected on the next run.
		update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );

		$new_json     = wp_json_encode( $elements );
		$new_hash     = md5( $new_json );
		$current_data = get_post_meta( $page_id, '_elementor_data', true );
		$last_hash    = get_post_meta( $page_id, '_travail_homepage_builder_hash', true );
		$safe_to_write = ( '' === $current_data ) || ! $last_hash || $last_hash === md5( $current_data );

		if ( $safe_to_write ) {
			update_post_meta( $page_id, '_elementor_data', wp_slash( $new_json ) );
			update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
			update_post_meta( $page_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.18.0' );
			update_post_meta( $page_id, '_travail_homepage_builder_hash', $new_hash );
		}

		// Backfilled unconditionally (like the thumbnail/category backfills
		// elsewhere in this file) so the flag is self-healing even if it
		// was ever cleared without the Elementor data itself changing.
		update_post_meta( $page_id, '_travail_homepage_design', $key );

		$map                       = self::get_map();
		$map['homepages'][ $key ]  = $page_id;
		self::save_map( $map );

		return $page_id;
	}

	/**
	 * Step: plugin_samples — the Tour Booking Manager sample content this site ships with: 5 tours, 3 hotels,
	 * 5 tour guides and 20 places (with their organizers, categories, activities, FAQs, itineraries, extra
	 * services, tickets and photos). The data lives in demo-import/plugin-samples.json and the photos in
	 * demo-import/images/, so nothing is downloaded.
	 *
	 * Safe to re-run: every object is tracked in the import map, and a tour/hotel/guide/place whose title already
	 * exists on the site (for example from the plugin's own sample data) is reused instead of duplicated.
	 *
	 * @return string
	 */
	public static function step_plugin_samples() {
		if ( ! post_type_exists( 'ttbm_tour' ) ) {
			return __( 'Tour Booking Manager is not active, so the sample tours and hotels were skipped.', 'travail' );
		}

		$file = TRAVAIL_DIR . '/demo-import/plugin-samples.json';
		if ( ! file_exists( $file ) ) {
			return __( 'No sample data file found — skipped.', 'travail' );
		}
		$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
		if ( ! is_array( $data ) ) {
			return __( 'The sample data file could not be read — skipped.', 'travail' );
		}

		// Date meta in the file is relative to the day it was exported; move it forward so tours stay upcoming.
		$offset_days = 0;
		if ( ! empty( $data['exported_on'] ) ) {
			$offset_days = max( 0, (int) floor( ( strtotime( current_time( 'Y-m-d' ) ) - strtotime( $data['exported_on'] ) ) / DAY_IN_SECONDS ) );
		}

		// Terms first, so posts can be attached to them by name.
		foreach ( (array) $data['terms'] as $term ) {
			self::sample_upsert_term( $term['taxonomy'], $term['name'], '', isset( $term['meta'] ) ? (array) $term['meta'] : array() );
		}

		$ids   = array();
		$count = array( 'guides' => 0, 'places' => 0, 'hotels' => 0, 'tours' => 0 );

		foreach ( (array) $data['guides'] as $row ) {
			$ids[ $row['key'] ] = self::sample_upsert_post( 'ttbm_guide', $row );
			++$count['guides'];
		}
		foreach ( (array) $data['places'] as $row ) {
			$ids[ $row['key'] ] = self::sample_upsert_post( 'ttbm_places', $row );
			++$count['places'];
		}

		$hotel_seen = array();
		foreach ( (array) $data['hotels'] as $row ) {
			$hotel_seen[ $row['title'] ] = isset( $hotel_seen[ $row['title'] ] ) ? $hotel_seen[ $row['title'] ] + 1 : 1;
			$hotel_id = self::sample_upsert_post( 'ttbm_hotel', $row, $hotel_seen[ $row['title'] ] );
			if ( $hotel_id ) {
				$ids[ $row['key'] ] = $hotel_id;
				++$count['hotels'];
				// A hotel that was already on the site keeps everything it has.
				if ( 'created' !== get_post_meta( $hotel_id, '_travail_sample_state', true ) ) {
					continue;
				}
				delete_post_meta( $hotel_id, '_travail_sample_state' );
				self::sample_apply_meta( $hotel_id, $row, $offset_days );
				if ( ! empty( $row['gallery'] ) ) {
					$gallery = array();
					foreach ( $row['gallery'] as $image ) {
						$att = self::sideload_local_image( $image );
						if ( $att ) {
							$gallery[] = $att;
						}
					}
					if ( $gallery ) {
						update_post_meta( $hotel_id, 'ttbm_gallery_images_hotel', $gallery );
					}
				}
				foreach ( (array) ( isset( $row['term_selection'] ) ? $row['term_selection'] : array() ) as $meta_key => $selection ) {
					$term_ids = array();
					foreach ( (array) $selection['names'] as $name ) {
						$term_id = self::sample_upsert_term( $selection['taxonomy'], $name );
						if ( $term_id ) {
							$term_ids[] = $term_id;
						}
					}
					if ( $term_ids ) {
						update_post_meta( $hotel_id, $meta_key, $term_ids );
					}
				}
			}
		}

		foreach ( (array) $data['tours'] as $row ) {
			$tour_id = self::sample_upsert_post( 'ttbm_tour', $row );
			if ( ! $tour_id ) {
				continue;
			}
			$ids[ $row['key'] ] = $tour_id;
			++$count['tours'];
		}

		// Second pass for tours: everything that points at another object by id.
		foreach ( (array) $data['tours'] as $row ) {
			if ( empty( $ids[ $row['key'] ] ) ) {
				continue;
			}
			$tour_id = $ids[ $row['key'] ];
			if ( 'created' !== get_post_meta( $tour_id, '_travail_sample_state', true ) ) {
				continue; // An existing tour keeps whatever it already has.
			}
			self::sample_apply_meta( $tour_id, $row, $offset_days );

			foreach ( (array) $row['terms'] as $taxonomy => $terms ) {
				$term_ids = array();
				foreach ( (array) $terms as $term ) {
					$term_id = self::sample_upsert_term( $taxonomy, $term['name'], isset( $term['slug'] ) ? $term['slug'] : '' );
					if ( $term_id ) {
						$term_ids[] = $term_id;
					}
				}
				if ( $term_ids && taxonomy_exists( $taxonomy ) ) {
					wp_set_object_terms( $tour_id, $term_ids, $taxonomy );
				}
			}

			$activity_ids = array();
			foreach ( (array) $row['activities'] as $name ) {
				$term_id = self::sample_upsert_term( 'ttbm_tour_activities', $name );
				if ( $term_id ) {
					$activity_ids[] = (string) $term_id;
				}
			}
			update_post_meta( $tour_id, 'ttbm_tour_activities', $activity_ids );

			$guides = array();
			foreach ( (array) $row['guides'] as $key ) {
				if ( ! empty( $ids[ $key ] ) ) {
					$guides[] = (string) $ids[ $key ];
				}
			}
			update_post_meta( $tour_id, 'ttbm_tour_guide', $guides );

			$places = array();
			foreach ( (array) $row['places'] as $place ) {
				if ( ! empty( $ids[ $place['key'] ] ) ) {
					$places[] = array(
						'ttbm_city_place_id' => (string) $ids[ $place['key'] ],
						'ttbm_place_label'   => $place['label'],
					);
				}
			}
			update_post_meta( $tour_id, 'ttbm_hiphop_places', $places );

			$related = array();
			foreach ( (array) $row['related'] as $key ) {
				if ( ! empty( $ids[ $key ] ) ) {
					$related[] = (string) $ids[ $key ];
				}
			}
			update_post_meta( $tour_id, 'ttbm_related_tour', $related );

			if ( ! empty( $row['gallery'] ) ) {
				$gallery = array();
				foreach ( $row['gallery'] as $image ) {
					$att = self::sideload_local_image( $image );
					if ( $att ) {
						$gallery[] = $att;
					}
				}
				if ( $gallery ) {
					update_post_meta( $tour_id, 'ttbm_gallery_images', $gallery );
				}
			}
			delete_post_meta( $tour_id, '_travail_sample_state' );
		}

		return sprintf(
			/* translators: 1: tours, 2: hotels, 3: guides, 4: places */
			__( 'Sample content ready: %1$d tours, %2$d hotels, %3$d guides and %4$d places.', 'travail' ),
			$count['tours'],
			$count['hotels'],
			$count['guides'],
			$count['places']
		);
	}

	/**
	 * Create-or-reuse a sample post. An existing post of the same type and title is reused (so the plugin's own
	 * sample data is never duplicated); new tours are flagged so the second pass fills in their meta.
	 *
	 * @param string $post_type Post type.
	 * @param array  $row       Exported row.
	 * @param int    $nth       Which occurrence of this title this row is (hotels share a title).
	 * @return int Post ID, or 0.
	 */
	protected static function sample_upsert_post( $post_type, $row, $nth = 1 ) {
		$map = self::get_map();
		if ( ! empty( $map['samples'][ $row['key'] ] ) && get_post( $map['samples'][ $row['key'] ] ) ) {
			return (int) $map['samples'][ $row['key'] ];
		}

		$existing = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'title'          => $row['title'],
				'posts_per_page' => $nth,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		if ( count( $existing ) >= $nth ) {
			$post_id = (int) $existing[ $nth - 1 ];
			$map['samples'][ $row['key'] ] = $post_id;
			self::save_map( $map );
			return $post_id;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => $post_type,
				'post_title'   => $row['title'],
				'post_content' => isset( $row['content'] ) ? $row['content'] : '',
				'post_excerpt' => isset( $row['excerpt'] ) ? $row['excerpt'] : '',
				'post_status'  => 'publish',
			),
			true
		);
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return 0;
		}

		if ( ! empty( $row['thumb'] ) ) {
			$att = self::sideload_local_image( $row['thumb'] );
			if ( $att ) {
				set_post_thumbnail( $post_id, $att );
			}
		}
		if ( ! in_array( $post_type, array( 'ttbm_tour', 'ttbm_hotel' ), true ) && ! empty( $row['meta'] ) ) {
			foreach ( $row['meta'] as $meta_key => $meta_value ) {
				update_post_meta( $post_id, $meta_key, $meta_value );
			}
		}
		if ( in_array( $post_type, array( 'ttbm_tour', 'ttbm_hotel' ), true ) ) {
			update_post_meta( $post_id, '_travail_sample_state', 'created' );
		}

		$map['samples'][ $row['key'] ] = $post_id;
		self::save_map( $map );
		return (int) $post_id;
	}

	/**
	 * Write an exported row's meta onto a post, moving any dates forward by $offset_days.
	 *
	 * @param int   $post_id     Post ID.
	 * @param array $row         Exported row.
	 * @param int   $offset_days Days to add to Y-m-d values.
	 */
	protected static function sample_apply_meta( $post_id, $row, $offset_days ) {
		if ( empty( $row['meta'] ) ) {
			return;
		}
		foreach ( $row['meta'] as $meta_key => $meta_value ) {
			if ( $offset_days > 0 && false !== strpos( $meta_key, 'date' ) ) {
				$meta_value = self::sample_shift_dates( $meta_value, $offset_days );
			}
			update_post_meta( $post_id, $meta_key, $meta_value );
		}
	}

	/**
	 * Add days to every Y-m-d string inside a value (scalar or nested array).
	 *
	 * @param mixed $value Meta value.
	 * @param int   $days  Days to add.
	 * @return mixed
	 */
	protected static function sample_shift_dates( $value, $days ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				$value[ $k ] = self::sample_shift_dates( $v, $days );
			}
			return $value;
		}
		if ( is_string( $value ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			return gmdate( 'Y-m-d', strtotime( $value . ' +' . (int) $days . ' days' ) );
		}
		return $value;
	}

	/**
	 * Create-or-fetch a term by name (plus slug when given), set any term meta that is still empty.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $name     Term name.
	 * @param string $slug     Optional slug.
	 * @param array  $meta     Optional term meta.
	 * @return int Term ID, or 0.
	 */
	protected static function sample_upsert_term( $taxonomy, $name, $slug = '', $meta = array() ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return 0;
		}
		$term = get_term_by( 'name', $name, $taxonomy );
		if ( ( ! $term || is_wp_error( $term ) ) && $slug ) {
			$term = get_term_by( 'slug', $slug, $taxonomy );
		}
		if ( $term && ! is_wp_error( $term ) ) {
			$term_id = (int) $term->term_id;
		} else {
			$result = wp_insert_term( $name, $taxonomy, $slug ? array( 'slug' => $slug ) : array() );
			if ( is_wp_error( $result ) ) {
				return 0;
			}
			$term_id = (int) $result['term_id'];
		}
		foreach ( (array) $meta as $meta_key => $meta_value ) {
			if ( '' === (string) get_term_meta( $term_id, $meta_key, true ) ) {
				update_term_meta( $term_id, $meta_key, $meta_value );
			}
		}
		return $term_id;
	}

	/**
	 * Add a photo that ships inside the theme (demo-import/images/) to the media library.
	 *
	 * @param array $image Exported image: file (relative to demo-import/), alt, title.
	 * @return int Attachment ID, or 0.
	 */
	protected static function sideload_local_image( $image ) {
		if ( empty( $image['file'] ) ) {
			return 0;
		}
		$source = TRAVAIL_DIR . '/demo-import/' . ltrim( $image['file'], '/' );
		if ( ! file_exists( $source ) ) {
			return 0;
		}

		$map = self::get_map();
		$key = md5( $image['file'] );
		if ( ! empty( $map['images'][ $key ] ) && get_post( $map['images'][ $key ] ) ) {
			return (int) $map['images'][ $key ];
		}

		if ( ! function_exists( 'wp_tempnam' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		// media_handle_sideload() moves the file it is given, so hand it a temporary copy.
		$tmp = wp_tempnam( basename( $source ) );
		if ( ! $tmp || ! copy( $source, $tmp ) ) {
			return 0;
		}
		$attachment_id = media_handle_sideload(
			array(
				'name'     => basename( $source ),
				'tmp_name' => $tmp,
			),
			0,
			! empty( $image['title'] ) ? $image['title'] : ''
		);
		if ( is_wp_error( $attachment_id ) ) {
			wp_delete_file( $tmp );
			return 0;
		}
		if ( ! empty( $image['alt'] ) ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $image['alt'] ) );
		}

		$map['images'][ $key ] = $attachment_id;
		self::save_map( $map );
		return (int) $attachment_id;
	}

}

Travail_Demo_Importer::init();
