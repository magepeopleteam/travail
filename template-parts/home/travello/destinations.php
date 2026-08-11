<?php
/**
 * Travello homepage — "Popular destinations" bento grid (1 large 2×2
 * card, 2 regular cards, 1 wide 2×1 card), matching travello.html's
 * exact card-size pattern by grid position (index 0/1/2/3).
 *
 * Same real ttbm_tour_location term data + helpers as the default
 * homepage's destination-grid.php (travail_get_term_image_url(),
 * travail_get_term_country()) — only the layout/markup differs.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() ) {
	return;
}

$travail_terms = get_terms(
	array(
		'taxonomy'   => 'ttbm_tour_location',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 4,
	)
);

if ( is_wp_error( $travail_terms ) || empty( $travail_terms ) ) {
	return;
}

$travail_size_classes = array( 'large', '', '', 'wide' );
$travail_dest_page    = get_page_by_path( 'destinations' );
?>
<section class="travail-travello-section">
	<div class="travail-travello-container">
		<div class="travail-travello-section-head">
			<div>
				<h2 class="travail-travello-section-title"><?php esc_html_e( 'Popular', 'travail' ); ?> <span class="travail-travello-hero__em"><?php esc_html_e( 'destinations', 'travail' ); ?></span></h2>
				<p class="travail-travello-section-sub"><?php esc_html_e( 'Places travelers are loving right now.', 'travail' ); ?></p>
			</div>
			<?php if ( $travail_dest_page ) : ?>
				<a href="<?php echo esc_url( get_permalink( $travail_dest_page ) ); ?>" class="travail-travello-link-more"><?php esc_html_e( 'View all destinations →', 'travail' ); ?></a>
			<?php endif; ?>
		</div>

		<div class="travail-travello-dest-grid">
			<?php foreach ( $travail_terms as $travail_index => $travail_term ) : ?>
				<?php
				$travail_size  = isset( $travail_size_classes[ $travail_index ] ) ? $travail_size_classes[ $travail_index ] : '';
				$travail_image_size = 'large' === $travail_size ? 'travail-card-tall' : 'travail-card-wide';
				?>
				<a href="<?php echo esc_url( get_term_link( $travail_term ) ); ?>" class="travail-travello-dest-card<?php echo $travail_size ? ' travail-travello-dest-card--' . esc_attr( $travail_size ) : ''; ?>">
					<img src="<?php echo esc_url( travail_get_term_image_url( $travail_term, $travail_image_size ) ); ?>" alt="<?php echo esc_attr( $travail_term->name ); ?>" loading="lazy" />
					<div class="travail-travello-dest-card__overlay"></div>
					<div class="travail-travello-dest-card__info">
						<div>
							<h3><?php echo esc_html( $travail_term->name ); ?></h3>
							<?php $travail_country = travail_get_term_country( $travail_term ); ?>
							<?php if ( $travail_country ) : ?>
								<p><?php echo esc_html( $travail_country ); ?></p>
							<?php endif; ?>
							<p class="travail-travello-dest-card__count">
								<?php
								printf(
									/* translators: %d: number of tours in this destination. */
									esc_html( _n( '%d tour', '%d tours', $travail_term->count, 'travail' ) ),
									(int) $travail_term->count
								);
								?>
							</p>
						</div>
						<span class="travail-travello-dest-arrow" aria-hidden="true">→</span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
