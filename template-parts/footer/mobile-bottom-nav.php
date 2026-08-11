<?php
/**
 * Mobile bottom tab bar — Explore / Bookings / Wishlist / Account.
 * Hidden entirely on desktop via CSS; gracefully drops the Bookings/
 * Wishlist tabs when the relevant plugin/feature isn't available.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! travail_get_option( 'show_mobile_bottom_nav', true ) ) {
	return;
}

$travail_account_url = home_url( '/' );
if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_woocommerce_active() ) {
	$travail_account_url = wc_get_page_permalink( 'myaccount' );
}

$travail_tabs = array(
	array(
		'label'   => __( 'Explore', 'travail' ),
		'url'     => home_url( '/' ),
		'active'  => is_front_page(),
		'icon'    => '<circle cx="12" cy="12" r="10"/><line x1="2" x2="22" y1="12" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
	),
);

if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_tour_booking_manager_active() ) {
	$travail_tabs[] = array(
		'label'  => __( 'Tours', 'travail' ),
		'url'    => get_post_type_archive_link( 'ttbm_tour' ),
		'active' => is_post_type_archive( 'ttbm_tour' ),
		'icon'   => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>',
	);
}

if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::supports_wishlist() ) {
	$travail_tabs[] = array(
		'label'  => __( 'Favorites', 'travail' ),
		'url'    => apply_filters( 'travail_wishlist_url', '#' ),
		'active' => false,
		'icon'   => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
	);
}

$travail_tabs[] = array(
	'label'  => is_user_logged_in() ? __( 'Account', 'travail' ) : __( 'Sign in', 'travail' ),
	'url'    => $travail_account_url,
	'active' => false,
	'icon'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>',
);
?>
<nav class="travail-mobile-bottom-nav" aria-label="<?php esc_attr_e( 'Mobile navigation', 'travail' ); ?>">
	<ul>
		<?php foreach ( $travail_tabs as $travail_tab ) : ?>
			<li>
				<a href="<?php echo esc_url( $travail_tab['url'] ? $travail_tab['url'] : '#' ); ?>" class="<?php echo $travail_tab['active'] ? 'is-active' : ''; ?>" <?php echo $travail_tab['active'] ? 'aria-current="page"' : ''; ?>>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $travail_tab['icon']; // phpcs:ignore WordPress.Security.EscapeOutput -- static inline SVG path data defined above, not user input. ?></svg>
					<?php echo esc_html( $travail_tab['label'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
