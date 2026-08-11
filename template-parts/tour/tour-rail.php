<?php
/**
 * "Popular experiences" homepage rail — matches the wanderly.html
 * reference tour-card design exactly (image + wishlist heart, rating,
 * title, location, duration + price footer).
 *
 * Renders real ttbm_tour posts via the same public, read-only accessor
 * methods Tour Booking Manager's own templates call
 * (TTBM_Function::get_tour_start_price() etc. — identical pattern to
 * templates/layout/gc_card_footer.php in the plugin), rather than the
 * plugin's own [ttbm-tour-list] shortcode markup, specifically so this
 * homepage section can match the reference design pixel-for-pixel. The
 * wishlist heart wires into the plugin's real AJAX action
 * (wp_ajax_ttbm_wishlist_toggle, confirmed in inc/TTBM_Wishlist.php) so
 * it actually saves — see assets/js/tour-booking.js.
 *
 * Expects optional $args['title'] / $args['subtitle'] / $args['limit'].
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() || ! class_exists( 'TTBM_Function' ) ) {
	return;
}

$travail_title    = isset( $args['title'] ) ? $args['title'] : __( 'Popular experiences', 'travail' );
$travail_subtitle = isset( $args['subtitle'] ) ? $args['subtitle'] : __( 'Handpicked adventures travelers love.', 'travail' );
$travail_limit    = isset( $args['limit'] ) ? absint( $args['limit'] ) : 8;
$travail_anchor   = isset( $args['anchor'] ) ? $args['anchor'] : '';

// "Popular experiences" is meant to be a third bucket alongside Featured
// Journey (the ttbm_best_seller tour) and the Deals grid (tours currently
// discounted) — the reference design treats all three as mutually
// exclusive curated sections. Without excluding those two, a best-seller
// or on-sale tour created after the "popular" ones (so it sorts first on
// orderby=date DESC) leaked into this rail instead of its own section.
// Over-fetch candidates, then apply the same real, read-only
// TTBM_Function::check_discount_price_exit() accessor deals-grid.php
// uses (rather than re-deriving "is this on sale" ourselves) so this
// never disagrees with what the plugin itself — or the Deals grid —
// considers a deal.
$travail_candidate_ids = get_posts(
	array(
		'post_type'      => 'ttbm_tour',
		'posts_per_page' => max( 20, $travail_limit * 4 ),
		'orderby'        => 'date',
		'order'          => 'DESC',
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => 'ttbm_best_seller',
				'compare' => 'NOT EXISTS',
			),
		),
	)
);

$travail_ids = array();
foreach ( $travail_candidate_ids as $travail_candidate_id ) {
	if ( class_exists( 'TTBM_Function' ) && method_exists( 'TTBM_Function', 'check_discount_price_exit' ) && TTBM_Function::check_discount_price_exit( $travail_candidate_id ) ) {
		continue;
	}
	$travail_ids[] = $travail_candidate_id;
	if ( count( $travail_ids ) >= $travail_limit ) {
		break;
	}
}

if ( empty( $travail_ids ) ) {
	return;
}

$travail_query = new WP_Query(
	array(
		'post_type'      => 'ttbm_tour',
		'post__in'       => $travail_ids,
		'orderby'        => 'post__in',
		'posts_per_page' => $travail_limit,
		'no_found_rows'  => true,
	)
);

if ( ! $travail_query->have_posts() ) {
	return;
}

// One batched query for every review count in this rail, instead of one
// query per card — cheap, and only runs when reviews are available at all.
$travail_review_counts = array();
if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::supports_reviews() ) {
	global $wpdb;
	$travail_ids = wp_list_pluck( $travail_query->posts, 'ID' );
	if ( ! empty( $travail_ids ) ) {
		$travail_placeholders = implode( ',', array_fill( 0, count( $travail_ids ), '%d' ) );
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- placeholders built above, IN() list only.
		$travail_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.meta_value AS tour_id, COUNT(*) AS review_count
				FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = 'ttbm_tour_id'
				AND pm.meta_value IN ($travail_placeholders)
				AND p.post_type = 'ttbm_tour_review'
				AND p.post_status = 'publish'
				GROUP BY pm.meta_value",
				$travail_ids
			)
		);
		foreach ( (array) $travail_rows as $travail_row ) {
			$travail_review_counts[ (int) $travail_row->tour_id ] = (int) $travail_row->review_count;
		}
	}
}
?>
<section class="travail-section"<?php echo $travail_anchor ? ' id="' . esc_attr( $travail_anchor ) . '"' : ''; ?>>
	<div class="travail-container">
		<div class="travail-section-head">
			<div>
				<h2 class="travail-serif"><?php echo esc_html( $travail_title ); ?></h2>
				<?php if ( $travail_subtitle ) : ?>
					<p><?php echo esc_html( $travail_subtitle ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="travail-rail">
			<?php
			while ( $travail_query->have_posts() ) :
				$travail_query->the_post();
				$travail_tour_id = get_the_ID();
				get_template_part(
					'template-parts/tour/tour-card',
					null,
					array(
						'tour_id'      => $travail_tour_id,
						'review_count' => isset( $travail_review_counts[ $travail_tour_id ] ) ? $travail_review_counts[ $travail_tour_id ] : 0,
					)
				);
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
