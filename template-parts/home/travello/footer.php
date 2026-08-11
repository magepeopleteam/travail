<?php
/**
 * Travello homepage — site footer + mobile bottom nav.
 *
 * Rendered by footer.php instead of the default footer only when
 * travail_is_travello_home() is true. Reuses the exact same footer
 * widget areas (footer-1/2/3) and social-link settings as the default
 * footer via the shared template-parts/footer/{column,socials}.php
 * partials, so a site owner configures footer links/socials once and
 * both homepages stay in sync — only the "Preferences" column and the
 * bottom bar are Travello-specific (the reference has no real
 * multi-language/currency backend to wire to, so these selects are the
 * same decorative-only controls as travello.html itself).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer class="travail-travello-footer" id="travello-colophon">
	<div class="travail-travello-footer__inner">
		<div class="travail-travello-footer__grid">

			<div class="travail-travello-footer__brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="travail-travello-footer__logo" rel="home">
					<svg width="28" height="28" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false">
						<circle cx="16" cy="16" r="15" class="travail-travello-logo__ring" />
						<path d="M16 6 L22 20 L16 17 L10 20 Z" class="travail-travello-logo__sail" />
						<circle cx="16" cy="22" r="2" class="travail-travello-logo__dot" />
					</svg>
					<span><?php bloginfo( 'name' ); ?></span>
				</a>
				<p class="travail-travello-footer__desc"><?php echo esc_html( travail_get_option( 'footer_description', __( 'The modern travel marketplace for curious explorers.', 'travail' ) ) ); ?></p>
				<?php get_template_part( 'template-parts/footer/socials' ); ?>
			</div>

			<?php get_template_part( 'template-parts/footer/column', null, array( 'sidebar' => 'footer-1', 'menu' => 'footer-1', 'fallback_title' => __( 'Explore', 'travail' ) ) ); ?>
			<?php get_template_part( 'template-parts/footer/column', null, array( 'sidebar' => 'footer-2', 'menu' => 'footer-2', 'fallback_title' => __( 'Company', 'travail' ) ) ); ?>
			<?php get_template_part( 'template-parts/footer/column', null, array( 'sidebar' => 'footer-3', 'menu' => 'footer-3', 'fallback_title' => __( 'Support', 'travail' ) ) ); ?>

			<div class="travail-travello-footer__col">
				<h4><?php esc_html_e( 'Preferences', 'travail' ); ?></h4>
				<select class="travail-travello-footer__select" aria-label="<?php esc_attr_e( 'Language', 'travail' ); ?>">
					<option><?php esc_html_e( '🌐 English', 'travail' ); ?></option>
					<option><?php esc_html_e( '🌐 Español', 'travail' ); ?></option>
					<option><?php esc_html_e( '🌐 Français', 'travail' ); ?></option>
				</select>
				<select class="travail-travello-footer__select" aria-label="<?php esc_attr_e( 'Currency', 'travail' ); ?>">
					<option><?php echo esc_html( get_option( 'woocommerce_currency', 'USD' ) ); ?></option>
					<option>EUR</option>
					<option>GBP</option>
				</select>
			</div>

		</div>

		<div class="travail-travello-footer__bottom">
			<p>
				<?php
				printf(
					/* translators: 1: current year, 2: site name */
					esc_html__( '© %1$s %2$s. All rights reserved.', 'travail' ),
					esc_html( gmdate( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</p>
			<?php if ( has_nav_menu( 'legal' ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Legal', 'travail' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'legal',
							'container'      => false,
							'items_wrap'     => '<ul class="travail-travello-footer__bottom-links">%3$s</ul>',
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</footer>

<nav class="travail-travello-mobile-bottom-nav" aria-label="<?php esc_attr_e( 'Quick links', 'travail' ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="travail-travello-mobile-bottom-nav__item is-active">
		<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><circle cx="8.5" cy="8.5" r="5.5"/><path d="M14.5 14.5 18 18"/></svg>
		<span><?php esc_html_e( 'Explore', 'travail' ); ?></span>
	</a>
	<a href="<?php echo esc_url( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_woocommerce_active() ? wc_get_account_endpoint_url( 'ttbm-bookings' ) : '#' ); ?>" class="travail-travello-mobile-bottom-nav__item">
		<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="14" height="14" rx="2"/><path d="M7 2v4M13 2v4M3 8h14"/></svg>
		<span><?php esc_html_e( 'Bookings', 'travail' ); ?></span>
	</a>
	<a href="<?php echo esc_url( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::has_wishlist_page() ? wc_get_account_endpoint_url( 'ttbm-wishlist' ) : '#' ); ?>" class="travail-travello-mobile-bottom-nav__item">
		<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17S2 11.5 2 6.5A4 4 0 0110 4.06 4 4 0 0118 6.5C18 11.5 10 17 10 17Z"/></svg>
		<span><?php esc_html_e( 'Favorites', 'travail' ); ?></span>
	</a>
	<a href="<?php echo esc_url( is_user_logged_in() ? ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_woocommerce_active() ? wc_get_page_permalink( 'myaccount' ) : admin_url( 'profile.php' ) ) : wp_login_url( home_url( '/' ) ) ); ?>" class="travail-travello-mobile-bottom-nav__item">
		<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="10" cy="7" r="3.5"/><path d="M2.5 18c0-4 3.4-7 7.5-7s7.5 3 7.5 7"/></svg>
		<span><?php esc_html_e( 'Profile', 'travail' ); ?></span>
	</a>
</nav>
