<?php
/**
 * Travello homepage — "Popular tours & experiences" grid.
 *
 * Real ttbm_tour posts via the same public, read-only accessor methods
 * used throughout the theme (TTBM_Function::get_tour_start_price() etc.
 * — see template-parts/tour/tour-card.php for the identical pattern).
 * Excludes on-sale tours so this grid and the Deals section below never
 * show the exact same card twice; the FEATURED badge reflects the
 * plugin's own real ttbm_best_seller flag rather than an arbitrary
 * alternating pattern. Wishlist buttons reuse the same
 * data-travail-wishlist-toggle contract as the default homepage's tour
 * card, so assets/js/main.js's existing handler wires them up with no
 * Travello-specific JS needed.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() || ! class_exists( 'TTBM_Function' ) ) {
	return;
}

$travail_limit = isset( $args['limit'] ) ? absint( $args['limit'] ) : 8;

$travail_candidate_ids = get_posts(
	array(
		'post_type'      => 'ttbm_tour',
		'posts_per_page' => max( 20, $travail_limit * 3 ),
		'orderby'        => 'date',
		'order'          => 'DESC',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	)
);

$travail_ids = array();
foreach ( $travail_candidate_ids as $travail_candidate_id ) {
	if ( class_exists( 'TTBM_Function' ) && method_exists( 'TTBM_Function', 'check_discount_price_exit' ) && TTBM_Function::check_discount_price_exit( $travail_candidate_id ) ) {
		continue; // Already shown in the Deals section below.
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

// One batched review-count query for the whole grid instead of one per card.
$travail_review_counts = array();
if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::supports_reviews() ) {
	global $wpdb;
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- placeholders built below, IN() list only.
	$travail_placeholders = implode( ',', array_fill( 0, count( $travail_ids ), '%d' ) );
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

$travail_tours_link = post_type_exists( 'ttbm_tour' ) ? get_post_type_archive_link( 'ttbm_tour' ) : '#';
?>
<section class="travail-travello-section travail-travello-section--top-flush">
	<div class="travail-travello-container">
		<div class="travail-travello-section-head">
			<div>
				<h2 class="travail-travello-section-title"><?php esc_html_e( 'Popular tours &', 'travail' ); ?> <span class="travail-travello-hero__em"><?php esc_html_e( 'experiences', 'travail' ); ?></span></h2>
				<p class="travail-travello-section-sub"><?php esc_html_e( 'Discover unforgettable experiences curated for every kind of traveler.', 'travail' ); ?></p>
			</div>
			<?php if ( $travail_tours_link ) : ?>
				<a href="<?php echo esc_url( $travail_tours_link ); ?>" class="travail-travello-link-more"><?php esc_html_e( 'All tours →', 'travail' ); ?></a>
			<?php endif; ?>
		</div>

		<div class="travail-travello-tours-grid">
			<?php
			while ( $travail_query->have_posts() ) :
				$travail_query->the_post();
				$travail_tour_id = get_the_ID();

				$travail_rating        = (float) get_post_meta( $travail_tour_id, 'ttbm_tour_rating', true );
				$travail_review_count  = isset( $travail_review_counts[ $travail_tour_id ] ) ? $travail_review_counts[ $travail_tour_id ] : 0;
				$travail_location      = method_exists( 'TTBM_Function', 'get_full_location' ) ? TTBM_Function::get_full_location( $travail_tour_id ) : '';
				$travail_duration      = method_exists( 'TTBM_Function', 'get_duration' ) ? TTBM_Function::get_duration( $travail_tour_id ) : '';
				$travail_duration_type = get_post_meta( $travail_tour_id, 'ttbm_travel_duration_type', true );
				$travail_price         = method_exists( 'TTBM_Function', 'get_tour_start_price' ) ? TTBM_Function::get_tour_start_price( $travail_tour_id ) : '';
				$travail_is_featured   = 'on' === get_post_meta( $travail_tour_id, 'ttbm_best_seller', true );
				$travail_wishlisted    = class_exists( 'TTBM_Wishlist' ) && is_user_logged_in() && TTBM_Wishlist::is_in_wishlist( $travail_tour_id );

				$travail_duration_label = '';
				if ( $travail_duration ) {
					if ( 'min' === $travail_duration_type ) {
						/* translators: %s: number of minutes. */
						$travail_duration_label = sprintf( _n( '%s minute', '%s minutes', $travail_duration, 'travail' ), $travail_duration );
					} elseif ( 'hour' === $travail_duration_type ) {
						/* translators: %s: number of hours. */
						$travail_duration_label = sprintf( _n( '%s hour', '%s hours', $travail_duration, 'travail' ), $travail_duration );
					} else {
						/* translators: %s: number of days. */
						$travail_duration_label = sprintf( _n( '%s day', '%s days', $travail_duration, 'travail' ), $travail_duration );
					}
				}
				?>
				<article class="travail-travello-tour-card">
					<div class="travail-travello-tour-card__img">
						<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<img src="<?php echo esc_url( travail_get_featured_image_url( $travail_tour_id, 'travail-card' ) ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
						</a>
						<div class="travail-travello-tour-card__top">
							<?php if ( $travail_is_featured ) : ?>
								<span class="travail-travello-badge"><?php esc_html_e( 'Featured', 'travail' ); ?></span>
							<?php else : ?>
								<span></span>
							<?php endif; ?>
							<?php if ( class_exists( 'TTBM_Wishlist' ) ) : ?>
								<button
									type="button"
									class="travail-travello-wishlist-btn"
									data-travail-wishlist-toggle
									data-tour-id="<?php echo esc_attr( $travail_tour_id ); ?>"
									aria-pressed="<?php echo $travail_wishlisted ? 'true' : 'false'; ?>"
									aria-label="<?php esc_attr_e( 'Save to wishlist', 'travail' ); ?>"
								>
									<svg width="15" height="15" viewBox="0 0 24 24" fill="<?php echo $travail_wishlisted ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
								</button>
							<?php endif; ?>
						</div>
					</div>
					<div class="travail-travello-tour-card__body">
						<?php if ( $travail_rating > 0 ) : ?>
							<div class="travail-travello-tour-card__rating">
								<span class="travail-travello-star" aria-hidden="true">★</span>
								<?php echo esc_html( number_format_i18n( $travail_rating, 1 ) ); ?>
								<?php if ( $travail_review_count > 0 ) : ?>
									<span class="travail-travello-tour-card__count">(<?php echo esc_html( number_format_i18n( $travail_review_count ) ); ?>)</span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<h3 class="travail-travello-tour-card__title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h3>

						<?php if ( $travail_location || $travail_duration_label ) : ?>
							<p class="travail-travello-tour-card__meta">
								<?php if ( $travail_location ) : ?><span aria-hidden="true">📍</span> <?php echo esc_html( $travail_location ); ?><?php endif; ?>
								<?php if ( $travail_location && $travail_duration_label ) : ?> · <?php endif; ?>
								<?php if ( $travail_duration_label ) : ?><span aria-hidden="true">🕐</span> <?php echo esc_html( $travail_duration_label ); ?><?php endif; ?>
							</p>
						<?php endif; ?>

						<div class="travail-travello-tour-card__footer">
							<?php if ( $travail_price ) : ?>
								<div>
									<p class="travail-travello-tour-card__price-label"><?php esc_html_e( 'From', 'travail' ); ?></p>
									<p class="travail-travello-tour-card__price"><?php echo wp_kses_post( travail_format_price( $travail_price ) ); ?><span> <?php esc_html_e( '/person', 'travail' ); ?></span></p>
								</div>
							<?php endif; ?>
							<a href="<?php the_permalink(); ?>" class="travail-travello-tour-cta"><?php esc_html_e( 'View tour →', 'travail' ); ?></a>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
	</div>
</section>
<?php
wp_reset_postdata();
