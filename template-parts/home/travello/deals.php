<?php
/**
 * Travello homepage — "Don't miss these adventures" deals grid.
 *
 * A tour "is a deal" is decided entirely by
 * TTBM_Function::check_discount_price_exit() — the same accessor the
 * plugin's own sale ribbon and the default homepage's deals-grid.php
 * call — so this never disagrees with what the plugin itself considers
 * a deal.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() || ! class_exists( 'TTBM_Function' ) || ! method_exists( 'TTBM_Function', 'check_discount_price_exit' ) ) {
	return;
}

$travail_args = travail_section_args(
	isset( $args ) ? $args : array(),
	array(
		'title'          => __( "Don't miss these", 'travail' ),
		'title_emphasis' => __( 'adventures', 'travail' ),
		'subtitle'       => __( 'Limited-time deals on top-rated experiences.', 'travail' ),
		'show_header'    => true,
		'badge_text'     => __( 'Limited Offer', 'travail' ),
		'cta_text'       => __( 'Book now →', 'travail' ),
		'limit'          => 3,
	)
);

$travail_limit = max( 1, absint( $travail_args['limit'] ) );

$travail_candidates = get_posts(
	array(
		'post_type'      => 'ttbm_tour',
		'posts_per_page' => 20,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'fields'         => 'ids',
	)
);

$travail_deals = array();
foreach ( $travail_candidates as $travail_id ) {
	if ( TTBM_Function::check_discount_price_exit( $travail_id ) ) {
		$travail_deals[] = $travail_id;
	}
	if ( count( $travail_deals ) >= $travail_limit ) {
		break;
	}
}

if ( empty( $travail_deals ) ) {
	return;
}
?>
<section class="travail-travello-section travail-travello-section--surface">
	<div class="travail-travello-container">
		<?php if ( $travail_args['show_header'] ) : ?>
			<div class="travail-travello-section-head">
				<div>
					<?php if ( $travail_args['title'] || $travail_args['title_emphasis'] ) : ?>
						<h2 class="travail-travello-section-title">
							<?php echo esc_html( $travail_args['title'] ); ?>
							<?php if ( $travail_args['title_emphasis'] ) : ?>
								<span class="travail-travello-hero__em"><?php echo esc_html( $travail_args['title_emphasis'] ); ?></span>
							<?php endif; ?>
						</h2>
					<?php endif; ?>
					<?php if ( $travail_args['subtitle'] ) : ?>
						<p class="travail-travello-section-sub"><?php echo esc_html( $travail_args['subtitle'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="travail-travello-deals-grid">
			<?php foreach ( $travail_deals as $travail_tour_id ) : ?>
				<?php
				$travail_start_price   = TTBM_Function::get_tour_start_price( $travail_tour_id );
				$travail_regular_price = method_exists( 'TTBM_Function', 'get_tour_start_regular_price' ) ? TTBM_Function::get_tour_start_regular_price( $travail_tour_id ) : 0;
				$travail_pct           = ( $travail_regular_price && $travail_start_price ) ? round( ( 1 - ( (float) $travail_start_price / (float) $travail_regular_price ) ) * 100 ) : 0;
				$travail_location      = method_exists( 'TTBM_Function', 'get_full_location' ) ? TTBM_Function::get_full_location( $travail_tour_id ) : '';
				?>
				<div class="travail-travello-deal-card">
					<div class="travail-travello-deal-card__img">
						<span class="travail-travello-badge"><?php echo esc_html( $travail_args['badge_text'] ); ?></span>
						<img src="<?php echo esc_url( travail_get_featured_image_url( $travail_tour_id, 'travail-card-wide' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $travail_tour_id ) ); ?>" loading="lazy" />
					</div>
					<div class="travail-travello-deal-card__body">
						<?php if ( $travail_location ) : ?>
							<p class="travail-travello-deal-card__loc"><span aria-hidden="true">📍</span> <?php echo esc_html( $travail_location ); ?></p>
						<?php endif; ?>
						<h4 class="travail-travello-deal-card__title"><?php echo esc_html( get_the_title( $travail_tour_id ) ); ?></h4>
						<div class="travail-travello-deal-card__footer">
							<div class="travail-travello-deal-card__pricing">
								<div class="travail-travello-deal-card__old-price">
									<?php if ( $travail_regular_price ) : ?>
										<s><?php echo wp_kses_post( travail_format_price( $travail_regular_price ) ); ?></s>
									<?php endif; ?>
									<?php if ( $travail_pct > 0 ) : ?>
										<span class="travail-travello-discount-tag"><?php echo esc_html( $travail_pct ); ?>% <?php esc_html_e( 'OFF', 'travail' ); ?></span>
									<?php endif; ?>
								</div>
								<p class="travail-travello-deal-card__new-price"><?php echo wp_kses_post( travail_format_price( $travail_start_price ) ); ?><span> <?php esc_html_e( '/person', 'travail' ); ?></span></p>
							</div>
							<a href="<?php echo esc_url( get_permalink( $travail_tour_id ) ); ?>" class="travail-travello-deal-btn"><?php echo esc_html( $travail_args['cta_text'] ); ?></a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
