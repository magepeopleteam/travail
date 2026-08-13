<?php
/**
 * Desktop header actions: search toggle, wishlist, account, CTA.
 *
 * Wishlist/account links only render when the relevant plugin actually
 * supports the feature — see Travail_Plugin_Compatibility.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_account_url = '#';
if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_woocommerce_active() ) {
	$travail_account_url = wc_get_page_permalink( 'myaccount' );
} elseif ( is_user_logged_in() ) {
	$travail_account_url = admin_url( 'profile.php' );
}
?>
<button type="button" class="travail-icon-btn" id="travail-search-toggle" aria-haspopup="dialog" aria-expanded="false" aria-controls="travail-search-modal" aria-label="<?php esc_attr_e( 'Search', 'travail' ); ?>">
	<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><circle cx="8.5" cy="8.5" r="5.5"/><path d="M14.5 14.5 L18 18"/></svg>
</button>

<?php if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::has_wishlist_page() ) : ?>
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'ttbm-wishlist' ) ); ?>" class="travail-icon-btn" id="travail-wishlist-link" aria-label="<?php esc_attr_e( 'Wishlist', 'travail' ); ?>">
		<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17S2 11.5 2 6.5A4 4 0 0110 4.06 4 4 0 0118 6.5C18 11.5 10 17 10 17Z"/></svg>
	</a>
<?php endif; ?>

<?php if ( is_user_logged_in() ) : ?>
	<a href="<?php echo esc_url( $travail_account_url ); ?>" class="travail-signin-link"><?php esc_html_e( 'My Account', 'travail' ); ?></a>
<?php else : ?>
	<a href="<?php echo esc_url( $travail_account_url ); ?>" class="travail-signin-link"><?php esc_html_e( 'Sign in', 'travail' ); ?></a>
<?php endif; ?>

<?php
$travail_cta_text = travail_get_option( 'header_cta_text', __( 'List Your Tour', 'travail' ) );
$travail_cta_url  = travail_get_option( 'header_cta_url', '#' );
if ( $travail_cta_text ) :
	?>
	<a href="<?php echo esc_url( $travail_cta_url ); ?>" class="travail-btn-list"><?php echo esc_html( $travail_cta_text ); ?></a>
<?php endif; ?>
