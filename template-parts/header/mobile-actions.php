<?php
/**
 * Mobile menu footer actions (sign in + CTA).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_account_url = '#';
if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_woocommerce_active() ) {
	$travail_account_url = wc_get_page_permalink( 'myaccount' );
}
?>
<a href="<?php echo esc_url( $travail_account_url ); ?>"><?php echo is_user_logged_in() ? esc_html__( 'My Account', 'travail' ) : esc_html__( 'Sign in', 'travail' ); ?></a>
<?php
$travail_cta_text = travail_get_option( 'header_cta_text', __( 'List Your Tour', 'travail' ) );
$travail_cta_url  = travail_get_option( 'header_cta_url', '#' );
if ( $travail_cta_text ) :
	?>
	<a href="<?php echo esc_url( $travail_cta_url ); ?>" class="travail-btn-list" style="border-radius:12px;padding:8px 18px;"><?php echo esc_html( $travail_cta_text ); ?></a>
<?php endif; ?>
