<?php
/**
 * "Why choose us" split section. The four reasons are filterable rather
 * than a Customizer repeater — per-item editing belongs to the
 * "Travail Features" Elementor widget once the page is rebuilt visually;
 * the Customizer only owns the handful of single-value settings here.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_items = apply_filters(
	'travail_why_choose_us_items',
	array(
		array(
			'title' => __( 'Handpicked experiences', 'travail' ),
			'text'  => __( 'Every tour and destination is carefully vetted by our team of expert travelers.', 'travail' ),
		),
		array(
			'title' => __( 'Local experts', 'travail' ),
			'text'  => __( 'Connect with passionate local guides who know their destinations intimately.', 'travail' ),
		),
		array(
			'title' => __( 'Secure booking', 'travail' ),
			'text'  => __( 'Your payment is protected with industry-leading security and fraud prevention.', 'travail' ),
		),
		array(
			'title' => __( 'Flexible plans', 'travail' ),
			'text'  => __( 'Modify or cancel your booking up to 48 hours before departure.', 'travail' ),
		),
	)
);

$travail_image = travail_get_option( 'why_choose_us_image', TRAVAIL_URI . '/assets/images/placeholder-wide.svg' );
?>
<section class="travail-section travail-section--surface">
	<div class="travail-container">
		<div class="travail-why-grid">
			<div class="travail-why-img-wrap">
				<img src="<?php echo esc_url( $travail_image ); ?>" alt="" loading="lazy" />
				<?php
				$travail_float_title = travail_get_option( 'why_choose_us_float_title', __( 'Trusted by thousands of travelers', 'travail' ) );
				$travail_float_text  = travail_get_option( 'why_choose_us_float_text', __( 'Secure booking · Instant confirmation', 'travail' ) );
				if ( $travail_float_title ) :
					?>
					<div class="travail-why-float">
						<div class="travail-why-float-icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
						</div>
						<div class="travail-why-float-text">
							<strong><?php echo esc_html( $travail_float_title ); ?></strong>
							<p><?php echo esc_html( $travail_float_text ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<div class="travail-why-content">
				<p class="travail-eyebrow travail-eyebrow--accent"><?php echo esc_html( travail_get_option( 'why_choose_us_eyebrow', __( 'Why choose us', 'travail' ) ) ); ?></p>
				<h2 class="travail-serif"><?php echo esc_html( travail_get_option( 'why_choose_us_title', __( 'Travel with confidence', 'travail' ) ) ); ?></h2>

				<div class="travail-why-items">
					<?php foreach ( $travail_items as $travail_index => $travail_item ) : ?>
						<div class="travail-why-item">
							<div class="travail-why-num travail-serif"><?php echo esc_html( sprintf( '%02d', $travail_index + 1 ) ); ?></div>
							<div class="travail-why-item-text">
								<strong><?php echo esc_html( $travail_item['title'] ); ?></strong>
								<p><?php echo esc_html( $travail_item['text'] ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>
