<?php
/**
 * "Trending destinations" grid — one card per ttbm_tour_location term,
 * ranked by tour count. Renders nothing (rather than fake destinations)
 * when Tour Booking Manager isn't active or no location has any
 * published tours yet.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() ) {
	return;
}

$travail_limit = isset( $args['limit'] ) ? absint( $args['limit'] ) : 4;

$travail_terms = get_terms(
	array(
		'taxonomy'   => 'ttbm_tour_location',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => $travail_limit,
	)
);

if ( is_wp_error( $travail_terms ) || empty( $travail_terms ) ) {
	return;
}
?>
<section class="travail-section travail-section--surface">
	<div class="travail-container">
		<div class="travail-section-head">
			<div>
				<h2 class="travail-serif"><?php echo esc_html( travail_get_option( 'destinations_title', __( 'Trending destinations', 'travail' ) ) ); ?></h2>
				<p><?php echo esc_html( travail_get_option( 'destinations_subtitle', __( 'Popular places loved by travelers around the world.', 'travail' ) ) ); ?></p>
			</div>
			<?php $travail_dest_page = get_page_by_path( 'destinations' ); ?>
			<?php if ( $travail_dest_page ) : ?>
				<a href="<?php echo esc_url( get_permalink( $travail_dest_page ) ); ?>" class="travail-view-all travail-link-arrow">
					<?php esc_html_e( 'View all destinations', 'travail' ); ?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
				</a>
			<?php endif; ?>
		</div>

		<div class="travail-dest-grid">
			<?php foreach ( $travail_terms as $travail_index => $travail_term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $travail_term ) ); ?>" class="travail-dest-card<?php echo $travail_index < 2 ? ' travail-dest-card--tall' : ''; ?>">
					<img src="<?php echo esc_url( travail_get_term_image_url( $travail_term, $travail_index < 2 ? 'travail-card-tall' : 'travail-card-wide' ) ); ?>" alt="<?php echo esc_attr( $travail_term->name ); ?>" loading="lazy" />
					<div class="travail-dest-card__gradient"></div>
					<div class="travail-dest-card__info">
						<div>
							<h4><?php echo esc_html( $travail_term->name ); ?></h4>
							<p>
								<?php $travail_country = travail_get_term_country( $travail_term ); ?>
								<?php if ( $travail_country ) : ?>
									<?php echo esc_html( $travail_country ); ?> ·
								<?php endif; ?>
								<?php
								printf(
									/* translators: %d: number of tours in this destination. */
									esc_html( _n( '%d experience', '%d experiences', $travail_term->count, 'travail' ) ),
									(int) $travail_term->count
								);
								?>
							</p>
						</div>
						<span class="travail-dest-arrow" aria-hidden="true">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
						</span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
