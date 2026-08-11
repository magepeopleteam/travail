<?php
/**
 * "Featured Journey" — one editorially-picked tour in a large split
 * card. Reads the tour's own data with the same public, read-only
 * accessor methods Tour Booking Manager's own templates call
 * (TTBM_Function::get_tour_start_price() etc. — see
 * templates/layout/gc_card_footer.php in the plugin for the identical
 * pattern), wrapped in method_exists() guards so a future plugin
 * refactor degrades gracefully instead of fataling.
 *
 * The featured tour is whichever published tour has the "Best Seller"
 * toggle (ttbm_best_seller meta) turned on; falls back to the most
 * recent tour so the section never has nothing to show.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() || ! class_exists( 'TTBM_Function' ) ) {
	return;
}

$travail_query = new WP_Query(
	array(
		'post_type'      => 'ttbm_tour',
		'posts_per_page' => 1,
		'meta_key'       => 'ttbm_best_seller',
		'meta_value'     => 'on',
		'no_found_rows'  => true,
	)
);

if ( ! $travail_query->have_posts() ) {
	$travail_query = new WP_Query(
		array(
			'post_type'      => 'ttbm_tour',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		)
	);
}

if ( ! $travail_query->have_posts() ) {
	return;
}

$travail_query->the_post();
$travail_tour_id = get_the_ID();

$travail_start_price   = method_exists( 'TTBM_Function', 'get_tour_start_price' ) ? TTBM_Function::get_tour_start_price( $travail_tour_id ) : '';
$travail_regular_price = method_exists( 'TTBM_Function', 'get_tour_start_regular_price' ) ? TTBM_Function::get_tour_start_regular_price( $travail_tour_id ) : '';
$travail_duration      = method_exists( 'TTBM_Function', 'get_duration' ) ? TTBM_Function::get_duration( $travail_tour_id ) : '';
$travail_night         = get_post_meta( $travail_tour_id, 'ttbm_travel_duration_night', true );
$travail_rating        = get_post_meta( $travail_tour_id, 'ttbm_tour_rating', true );
$travail_features      = method_exists( 'TTBM_Function', 'get_feature_list' ) ? array_slice( (array) TTBM_Function::get_feature_list( $travail_tour_id, 'ttbm_service_included_in_price' ), 0, 3 ) : array();
?>
<section class="travail-section--tight">
	<div class="travail-container">
		<div class="travail-featured-card">
			<div class="travail-featured-card__img">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'travail-card-wide', array( 'loading' => 'lazy' ) ); ?>
				<?php else : ?>
					<img src="<?php echo esc_url( TRAVAIL_URI . '/assets/images/placeholder-wide.svg' ); ?>" alt="" loading="lazy" />
				<?php endif; ?>
				<div class="travail-featured-card__img-overlay"></div>
				<span class="travail-badge travail-badge--outline travail-featured-badge"><?php esc_html_e( 'Featured Journey', 'travail' ); ?></span>
			</div>
			<div class="travail-featured-card__body">
				<p class="travail-eyebrow travail-eyebrow--accent"><?php esc_html_e( "Editor's Pick", 'travail' ); ?></p>
				<h3 class="travail-serif"><?php the_title(); ?></h3>

				<div class="travail-featured-meta">
					<?php if ( $travail_duration ) : ?>
						<span>
							<?php
							echo esc_html( $travail_duration );
							echo ' ' . esc_html( _n( 'Day', 'Days', $travail_duration, 'travail' ) );
							if ( $travail_night ) {
								echo ' · ' . esc_html( $travail_night ) . ' ' . esc_html( _n( 'Night', 'Nights', $travail_night, 'travail' ) );
							}
							?>
						</span>
					<?php endif; ?>
					<?php if ( $travail_rating ) : ?>
						<span class="travail-rating-inline">★ <strong><?php echo esc_html( number_format_i18n( (float) $travail_rating, 1 ) ); ?></strong></span>
					<?php endif; ?>
				</div>

				<p class="travail-featured-desc"><?php echo esc_html( travail_excerpt( get_the_excerpt(), 28 ) ); ?></p>

				<div class="travail-featured-price-row">
					<?php if ( $travail_start_price ) : ?>
						<div>
							<div class="travail-featured-price-from"><?php esc_html_e( 'Starting from', 'travail' ); ?></div>
							<div class="travail-featured-price-amt"><?php echo wp_kses_post( travail_format_price( $travail_start_price ) ); ?></div>
							<div class="travail-featured-price-pp"><?php esc_html_e( 'per person', 'travail' ); ?></div>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $travail_features ) ) : ?>
						<ul class="travail-featured-checklist">
							<?php foreach ( $travail_features as $travail_feature_slug ) : ?>
								<?php $travail_term = get_term_by( 'slug', $travail_feature_slug, 'ttbm_tour_features_list' ); ?>
								<?php if ( $travail_term ) : ?>
									<li><?php echo esc_html( $travail_term->name ); ?></li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<a href="<?php the_permalink(); ?>" class="travail-btn travail-btn--primary">
					<?php esc_html_e( 'Explore trip', 'travail' ); ?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
				</a>
			</div>
		</div>
	</div>
</section>
<?php
wp_reset_postdata();
