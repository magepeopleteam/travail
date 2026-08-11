<?php
/**
 * Social icon links — pulled from Customizer settings, only rendered
 * when a URL has actually been provided.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_socials = array(
	'facebook'  => array(
		'label' => __( 'Facebook', 'travail' ),
		'abbr'  => 'Fb',
	),
	'instagram' => array(
		'label' => __( 'Instagram', 'travail' ),
		'abbr'  => 'In',
	),
	'youtube'   => array(
		'label' => __( 'YouTube', 'travail' ),
		'abbr'  => 'Yt',
	),
	'twitter'   => array(
		'label' => __( 'X (Twitter)', 'travail' ),
		'abbr'  => 'X',
	),
);

$travail_has_any = false;
foreach ( $travail_socials as $travail_key => $travail_data ) {
	if ( travail_get_option( 'social_' . $travail_key, '' ) ) {
		$travail_has_any = true;
		break;
	}
}

if ( ! $travail_has_any ) {
	return;
}
?>
<div class="travail-footer-socials">
	<?php foreach ( $travail_socials as $travail_key => $travail_data ) : ?>
		<?php $travail_url = travail_get_option( 'social_' . $travail_key, '' ); ?>
		<?php if ( $travail_url ) : ?>
			<a href="<?php echo esc_url( $travail_url ); ?>" class="travail-social-btn" aria-label="<?php echo esc_attr( $travail_data['label'] ); ?>" target="_blank" rel="noopener noreferrer">
				<?php echo esc_html( $travail_data['abbr'] ); ?>
			</a>
		<?php endif; ?>
	<?php endforeach; ?>
</div>
