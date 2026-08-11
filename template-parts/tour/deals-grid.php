<?php
/**
 * "Adventure doesn't have to wait" — grid of tours currently on sale.
 * A tour "is on sale" is decided entirely by
 * TTBM_Function::check_discount_price_exit() — the exact same accessor
 * the plugin's own sale ribbon (templates/layout/sale_price.php) calls —
 * so this never disagrees with what the plugin itself considers a deal.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() || ! class_exists( 'TTBM_Function' ) || ! method_exists( 'TTBM_Function', 'check_discount_price_exit' ) ) {
	return;
}

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
	if ( count( $travail_deals ) >= 3 ) {
		break;
	}
}

if ( empty( $travail_deals ) ) {
	return;
}

$travail_anchor = isset( $args['anchor'] ) ? $args['anchor'] : '';
?>
<section class="travail-section travail-section--surface"<?php echo $travail_anchor ? ' id="' . esc_attr( $travail_anchor ) . '"' : ''; ?>>
	<div class="travail-container">
		<div class="travail-section-head">
			<div>
				<h2 class="travail-serif"><?php echo esc_html( travail_get_option( 'deals_title', __( "Adventure doesn't have to wait.", 'travail' ) ) ); ?></h2>
				<p><?php echo esc_html( travail_get_option( 'deals_subtitle', __( 'Limited-time offers on handpicked tours.', 'travail' ) ) ); ?></p>
			</div>
		</div>

		<div class="travail-deals-grid">
			<?php foreach ( $travail_deals as $travail_tour_id ) : ?>
				<?php
				$travail_start_price   = TTBM_Function::get_tour_start_price( $travail_tour_id );
				$travail_regular_price = method_exists( 'TTBM_Function', 'get_tour_start_regular_price' ) ? TTBM_Function::get_tour_start_regular_price( $travail_tour_id ) : 0;
				$travail_pct           = ( $travail_regular_price && $travail_start_price ) ? round( ( 1 - ( (float) $travail_start_price / (float) $travail_regular_price ) ) * 100 ) : 0;
				?>
				<a href="<?php echo esc_url( get_permalink( $travail_tour_id ) ); ?>" class="travail-deal-card">
					<img src="<?php echo esc_url( travail_get_featured_image_url( $travail_tour_id, 'travail-card-wide' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $travail_tour_id ) ); ?>" loading="lazy" />
					<div class="travail-deal-card__gradient"></div>
					<span class="travail-badge travail-badge--sale travail-deal-card__badge"><?php esc_html_e( 'Limited Offer', 'travail' ); ?></span>
					<div class="travail-deal-card__body">
						<h4><?php echo esc_html( get_the_title( $travail_tour_id ) ); ?></h4>
						<div class="travail-deal-pricing">
							<div>
								<?php if ( $travail_regular_price ) : ?>
									<span class="travail-old"><?php echo wp_kses_post( travail_format_price( $travail_regular_price ) ); ?></span>
								<?php endif; ?>
								<span class="travail-new"><?php echo wp_kses_post( travail_format_price( $travail_start_price ) ); ?></span>
							</div>
							<?php if ( $travail_pct > 0 ) : ?>
								<span class="travail-deal-pct">-<?php echo esc_html( $travail_pct ); ?>%</span>
							<?php endif; ?>
						</div>
						<span class="travail-btn-book-now"><?php esc_html_e( 'Explore →', 'travail' ); ?></span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
