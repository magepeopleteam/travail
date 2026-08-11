<?php
/**
 * Hero trust metrics row ("12,000+ Happy Travelers" etc). Values are
 * Customizer theme mods, each optional — a metric with no value simply
 * doesn't render, so this never shows fake stats a store owner didn't set.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_metrics = array(
	array(
		'value' => travail_get_option( 'hero_metric_1_value', '' ),
		'label' => travail_get_option( 'hero_metric_1_label', __( 'Happy Travelers', 'travail' ) ),
	),
	array(
		'value' => travail_get_option( 'hero_metric_2_value', '' ),
		'label' => travail_get_option( 'hero_metric_2_label', __( 'Overall Rating', 'travail' ) ),
	),
	array(
		'value' => travail_get_option( 'hero_metric_3_value', '' ),
		'label' => travail_get_option( 'hero_metric_3_label', __( 'Destinations', 'travail' ) ),
	),
);

$travail_metrics = array_values( array_filter( $travail_metrics, function ( $m ) {
	return ! empty( $m['value'] );
} ) );

if ( empty( $travail_metrics ) ) {
	return;
}
?>
<div class="travail-hero__metrics">
	<?php foreach ( $travail_metrics as $travail_i => $travail_metric ) : ?>
		<?php if ( $travail_i > 0 ) : ?>
			<div class="travail-metric-divider" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="travail-metric">
			<div>
				<div class="travail-metric__val"><?php echo esc_html( $travail_metric['value'] ); ?></div>
				<div class="travail-metric__label"><?php echo esc_html( $travail_metric['label'] ); ?></div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
