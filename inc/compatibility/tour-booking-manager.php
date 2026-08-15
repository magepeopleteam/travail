<?php
/**
 * Tour Booking Manager (+ Pro) integration layer.
 *
 * This file intentionally contains presentation-only glue: template
 * routing for the ttbm_tour CPT/taxonomies, and a term-image field for
 * ttbm_tour_location / ttbm_tour_cat (the plugin stores no image on a
 * taxonomy term, so the theme adds one — that's UI, not business logic).
 * Pricing, availability, booking submission and payment stay entirely
 * inside the plugin; the theme only ever calls its public
 * shortcodes/functions/hooks (see templates/tours/*.php).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * No "is the plugin active?" early-return guard here on purpose: this
 * file is required from functions.php's top-level loop, which runs
 * before the `init` hook — i.e. before Tour Booking Manager has
 * registered its post types/taxonomies. A guard checked at that point
 * would false-negative and silently skip every hook registration below
 * (this was a real bug, caught during QA). Every hook/filter here keys
 * off ttbm_-prefixed identifiers that only ever exist/fire when the
 * plugin actually provides them, so registering them unconditionally is
 * safe even when the plugin is inactive — the callbacks simply never run.
 */

/**
 * Route ONLY the ttbm_tour post-type ARCHIVE to the organized
 * /templates/tours/ folder (standard, supported archive_template
 * filter). The single tour view and every tour taxonomy archive are
 * deliberately left alone: Tour Booking Manager already forces its own
 * templates for those via its own `single_template` / `template_include`
 * filters (see TTBM_Frontend::load_single_template() /
 * TTBM_Frontend::load_template()), and fighting that would either be a
 * no-op or break the plugin's own JS (booking widget, price calculator,
 * collapsible FAQ/itinerary) which targets that exact markup. Per the
 * "prefer hooks/filters, only override templates when necessary" rule,
 * those views are customized with CSS only — see assets/css/tbm-restyle.css.
 */
add_filter(
	'archive_template',
	function ( $template ) {
		if ( is_post_type_archive( 'ttbm_tour' ) ) {
			$override = TRAVAIL_DIR . '/templates/tours/archive-tour.php';
			if ( file_exists( $override ) ) {
				return $override;
			}
		}
		return $template;
	}
);

/**
 * Taxonomies the theme adds a "Card Image" field to: Destinations
 * (ttbm_tour_location), Categories (ttbm_tour_cat) and Activities
 * (ttbm_tour_activities — the "Find your kind of adventure" rail).
 *
 * @return string[]
 */
function travail_term_image_taxonomies() {
	return array( 'ttbm_tour_location', 'ttbm_tour_cat', 'ttbm_tour_activities' );
}

/**
 * Register a "Featured Image" style term-meta field on the taxonomies
 * above, so their cards can show a photo instead of a solid color
 * block, plus a "Country" text field on Destinations only (used as the
 * card subtitle, e.g. "Indonesia" under "Bali").
 */
function travail_register_term_image_meta() {
	foreach ( travail_term_image_taxonomies() as $taxonomy ) {
		register_term_meta(
			$taxonomy,
			'travail_term_image_id',
			array(
				'type'              => 'integer',
				'single'            => true,
				'sanitize_callback' => 'absint',
				'show_in_rest'      => true,
			)
		);
	}

	register_term_meta(
		'ttbm_tour_location',
		'travail_term_country',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		)
	);
}
add_action( 'init', 'travail_register_term_image_meta', 20 );

/**
 * Get the "country" subtitle for a destination term (e.g. "Indonesia"
 * for "Bali"), empty string when not set.
 *
 * @param WP_Term|int $term Term object or ID.
 * @return string
 */
function travail_get_term_country( $term ) {
	$term_id = is_object( $term ) ? $term->term_id : absint( $term );
	return (string) get_term_meta( $term_id, 'travail_term_country', true );
}

/**
 * Get the image URL for a term, falling back to a theme placeholder.
 *
 * @param WP_Term|int $term Term object or ID.
 * @param string      $size Image size.
 * @return string
 */
function travail_get_term_image_url( $term, $size = 'travail-card' ) {
	$term_id = is_object( $term ) ? $term->term_id : absint( $term );
	$attachment_id = absint( get_term_meta( $term_id, 'travail_term_image_id', true ) );

	if ( $attachment_id ) {
		$url = wp_get_attachment_image_url( $attachment_id, $size );
		if ( $url ) {
			return $url;
		}
	}

	return TRAVAIL_URI . '/assets/images/placeholder-tour.svg';
}

/**
 * Destination cards for the homepage / Elementor Destinations widget.
 *
 * Returns a normalized list so both destination templates (Classic grid
 * and Travello bento) and the Elementor widget can render the same
 * structure — either live `ttbm_tour_location` terms or a pre-built
 * custom-card array from Elementor.
 *
 * @param array $args {
 *     @type int      $limit      Max cards. Default 4.
 *     @type string   $orderby    Term orderby when querying. Default 'count'.
 *     @type int[]    $term_ids   Optional include list (preserves this order).
 *     @type bool     $hide_empty Skip locations with no published tours.
 *     @type string   $style      'travello' uses tall image on the first card.
 *     @type array    $cards      Pre-built cards; when non-empty, skips the query.
 * }
 * @return array<int, array{name:string, country:string, url:string, image:string, count:int}>
 */
function travail_get_destination_cards( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'limit'      => 4,
			'orderby'    => 'count',
			'term_ids'   => array(),
			'hide_empty' => true,
			'style'      => 'classic',
			'cards'      => array(),
		)
	);

	if ( ! empty( $args['cards'] ) && is_array( $args['cards'] ) ) {
		$normalized = array();
		foreach ( $args['cards'] as $card ) {
			if ( empty( $card['name'] ) ) {
				continue;
			}
			$normalized[] = array(
				'name'    => (string) $card['name'],
				'country' => isset( $card['country'] ) ? (string) $card['country'] : '',
				'url'     => ! empty( $card['url'] ) ? (string) $card['url'] : '#',
				'image'   => ! empty( $card['image'] ) ? (string) $card['image'] : TRAVAIL_URI . '/assets/images/placeholder-tour.svg',
				'count'   => isset( $card['count'] ) ? absint( $card['count'] ) : 0,
			);
		}
		return $normalized;
	}

	if ( ! taxonomy_exists( 'ttbm_tour_location' ) ) {
		return array();
	}

	$query = array(
		'taxonomy'   => 'ttbm_tour_location',
		'hide_empty' => (bool) $args['hide_empty'],
		'number'     => max( 1, absint( $args['limit'] ) ),
	);

	$term_ids = array_filter( array_map( 'absint', (array) $args['term_ids'] ) );
	if ( $term_ids ) {
		$query['include'] = $term_ids;
		$query['orderby'] = 'include';
		$query['number']  = count( $term_ids );
	} else {
		$query['orderby'] = sanitize_key( $args['orderby'] ) ? sanitize_key( $args['orderby'] ) : 'count';
		$query['order']   = 'count' === $query['orderby'] ? 'DESC' : 'ASC';
	}

	$terms = get_terms( $query );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	if ( $term_ids && absint( $args['limit'] ) < count( $terms ) ) {
		$terms = array_slice( $terms, 0, absint( $args['limit'] ) );
	}

	$cards = array();
	foreach ( $terms as $index => $term ) {
		$is_tall = ( 'travello' === $args['style'] && 0 === $index ) || ( 'classic' === $args['style'] && $index < 2 );
		$link    = get_term_link( $term );
		$cards[] = array(
			'name'    => $term->name,
			'country' => travail_get_term_country( $term ),
			'url'     => is_wp_error( $link ) ? '#' : $link,
			'image'   => travail_get_term_image_url( $term, $is_tall ? 'travail-card-tall' : 'travail-card-wide' ),
			'count'   => (int) $term->count,
		);
	}

	return $cards;
}

/**
 * Term image field (+ Country field on Destinations only): add form.
 *
 * @param string $taxonomy Taxonomy slug.
 */
function travail_term_image_add_field( $taxonomy ) {
	?>
	<div class="form-field term-group">
		<label for="travail-term-image-id"><?php esc_html_e( 'Card Image', 'travail' ); ?></label>
		<input type="hidden" id="travail-term-image-id" name="travail_term_image_id" value="" />
		<button type="button" class="button travail-term-image-select"><?php esc_html_e( 'Choose Image', 'travail' ); ?></button>
		<div class="travail-term-image-preview"></div>
		<p><?php esc_html_e( 'Shown on destination/category/activity cards across the homepage and archive.', 'travail' ); ?></p>
	</div>
	<?php if ( 'ttbm_tour_location' === $taxonomy ) : ?>
		<div class="form-field term-group">
			<label for="travail-term-country"><?php esc_html_e( 'Country', 'travail' ); ?></label>
			<input type="text" id="travail-term-country" name="travail_term_country" value="" placeholder="<?php esc_attr_e( 'e.g. Indonesia', 'travail' ); ?>" />
			<p><?php esc_html_e( 'Shown as the card subtitle, e.g. "Indonesia" under "Bali".', 'travail' ); ?></p>
		</div>
	<?php endif; ?>
	<?php wp_nonce_field( 'travail_save_term_image', 'travail_term_image_nonce' ); ?>
	<?php
}
foreach ( travail_term_image_taxonomies() as $travail_tax ) {
	add_action( "{$travail_tax}_add_form_fields", 'travail_term_image_add_field' );
}

/**
 * Term image field (+ Country field on Destinations only): edit form.
 *
 * @param WP_Term $term Term being edited.
 */
function travail_term_image_edit_field( $term ) {
	$image_id  = absint( get_term_meta( $term->term_id, 'travail_term_image_id', true ) );
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
	?>
	<tr class="form-field term-group-wrap">
		<th scope="row"><label for="travail-term-image-id"><?php esc_html_e( 'Card Image', 'travail' ); ?></label></th>
		<td>
			<input type="hidden" id="travail-term-image-id" name="travail_term_image_id" value="<?php echo esc_attr( $image_id ); ?>" />
			<button type="button" class="button travail-term-image-select"><?php esc_html_e( 'Choose Image', 'travail' ); ?></button>
			<button type="button" class="button travail-term-image-remove" <?php echo $image_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Remove', 'travail' ); ?></button>
			<div class="travail-term-image-preview">
				<?php if ( $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-width:120px;margin-top:10px;display:block;" />
				<?php endif; ?>
			</div>
			<p class="description"><?php esc_html_e( 'Shown on destination/category/activity cards across the homepage and archive.', 'travail' ); ?></p>
			<?php wp_nonce_field( 'travail_save_term_image', 'travail_term_image_nonce' ); ?>
		</td>
	</tr>
	<?php if ( 'ttbm_tour_location' === $term->taxonomy ) : ?>
		<tr class="form-field term-group-wrap">
			<th scope="row"><label for="travail-term-country"><?php esc_html_e( 'Country', 'travail' ); ?></label></th>
			<td>
				<input type="text" id="travail-term-country" name="travail_term_country" value="<?php echo esc_attr( travail_get_term_country( $term ) ); ?>" placeholder="<?php esc_attr_e( 'e.g. Indonesia', 'travail' ); ?>" />
				<p class="description"><?php esc_html_e( 'Shown as the card subtitle, e.g. "Indonesia" under "Bali".', 'travail' ); ?></p>
			</td>
		</tr>
	<?php endif; ?>
	<?php
}
foreach ( travail_term_image_taxonomies() as $travail_tax ) {
	add_action( "{$travail_tax}_edit_form_fields", 'travail_term_image_edit_field' );
}

/**
 * Save the term image (+ country) meta, with nonce + capability checks.
 *
 * @param int $term_id Term ID.
 */
function travail_save_term_image( $term_id ) {
	if ( ! isset( $_POST['travail_term_image_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['travail_term_image_nonce'] ), 'travail_save_term_image' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	if ( isset( $_POST['travail_term_image_id'] ) ) {
		update_term_meta( $term_id, 'travail_term_image_id', absint( $_POST['travail_term_image_id'] ) );
	}
	if ( isset( $_POST['travail_term_country'] ) ) {
		update_term_meta( $term_id, 'travail_term_country', sanitize_text_field( wp_unslash( $_POST['travail_term_country'] ) ) );
	}
}
foreach ( travail_term_image_taxonomies() as $travail_tax ) {
	add_action( "edited_{$travail_tax}", 'travail_save_term_image' );
	add_action( "created_{$travail_tax}", 'travail_save_term_image' );
}

/**
 * Enqueue the media uploader on the term add/edit screens for the
 * taxonomies above only.
 *
 * @param string $hook Current admin page hook.
 */
function travail_term_image_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->taxonomy, travail_term_image_taxonomies(), true ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'travail-term-image', TRAVAIL_URI . '/assets/js/term-image.js', array( 'jquery' ), TRAVAIL_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'travail_term_image_admin_assets' );
