<?php
/**
 * Travello homepage — "We make great trips happen" (image + floating
 * stat badge + 4 numbered reasons).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_image = travail_get_option( 'travello_why_us_image', '' );
if ( ! $travail_image ) {
	$travail_image = TRAVAIL_URI . '/assets/images/placeholder-wide.svg';
}

$travail_reasons = apply_filters(
	'travail_travello_why_us_reasons',
	array(
		array(
			'title' => __( 'Handpicked experiences', 'travail' ),
			'text'  => __( 'Every tour is vetted by our team of expert travelers who know what makes a journey truly memorable.', 'travail' ),
		),
		array(
			'title' => __( 'Trusted local experts', 'travail' ),
			'text'  => __( 'We partner with guides who know their destinations intimately — not just the highlights.', 'travail' ),
		),
		array(
			'title' => __( 'Secure payments', 'travail' ),
			'text'  => __( 'Bank-level encryption keeps every transaction safe. Book with complete confidence.', 'travail' ),
		),
		array(
			'title' => __( 'Flexible cancellation', 'travail' ),
			'text'  => __( 'Plans change — most tours offer free cancellation up to 24 hours before departure.', 'travail' ),
		),
	)
);
?>
<section class="travail-travello-section">
	<div class="travail-travello-container">
		<div class="travail-travello-why-grid">
			<div class="travail-travello-why-img">
				<img src="<?php echo esc_url( $travail_image ); ?>" alt="" loading="lazy" />
				<div class="travail-travello-why-badge">
					<p><?php echo esc_html( travail_get_option( 'travello_why_us_stat_value', __( '50,000+', 'travail' ) ) ); ?></p>
					<p><?php echo esc_html( travail_get_option( 'travello_why_us_stat_label', __( 'Happy travelers worldwide', 'travail' ) ) ); ?></p>
				</div>
			</div>
			<div>
				<span class="travail-travello-eyebrow travail-travello-eyebrow--ink"><?php esc_html_e( 'Why Travel With Us', 'travail' ); ?></span>
				<h2 class="travail-travello-section-title"><?php esc_html_e( 'We make great', 'travail' ); ?> <span class="travail-travello-hero__em"><?php esc_html_e( 'trips happen.', 'travail' ); ?></span></h2>

				<div class="travail-travello-reasons">
					<?php foreach ( $travail_reasons as $travail_index => $travail_reason ) : ?>
						<div class="travail-travello-reason">
							<span class="travail-travello-reason__num"><?php echo esc_html( sprintf( '%02d', $travail_index + 1 ) ); ?></span>
							<div>
								<p class="travail-travello-reason__title"><?php echo esc_html( $travail_reason['title'] ); ?></p>
								<p class="travail-travello-reason__desc"><?php echo esc_html( $travail_reason['text'] ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>
