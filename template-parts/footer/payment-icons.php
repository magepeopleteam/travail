<?php
/**
 * Payment method icons — only shown when WooCommerce is active. Uses
 * simple text badges instead of bundled card-brand artwork so the theme
 * never ships third-party trademarked assets without a license.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_gateways = array();
if ( function_exists( 'WC' ) && WC()->payment_gateways() ) {
	foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $travail_gateway ) {
		$travail_gateways[] = $travail_gateway->get_title();
	}
}

if ( empty( $travail_gateways ) ) {
	return;
}
?>
<?php foreach ( array_slice( $travail_gateways, 0, 5 ) as $travail_label ) : ?>
	<span class="travail-badge travail-badge--outline" style="color:#fff;background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.15);text-transform:none;letter-spacing:0;font-weight:600;">
		<?php echo esc_html( wp_strip_all_tags( $travail_label ) ); ?>
	</span>
<?php endforeach; ?>
