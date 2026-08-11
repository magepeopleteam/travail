<?php
/**
 * Travello homepage — site header (masthead + mobile nav drawer).
 *
 * Rendered by header.php instead of the default masthead only when
 * travail_is_travello_home() is true — see header.php. Reuses the sticky
 * -header and search-modal behaviour already wired in navigation.js by
 * sharing the same [data-travail-header]/data-sticky contract and
 * #travail-search-toggle / #travail-search-modal ids as the default
 * header, so no extra JS is needed for either. The mobile drawer uses
 * its own ids/markup (the visual treatment differs from the default
 * header's slide-out panel) — see assets/js/travello.js.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_menu_location = has_nav_menu( 'travello-primary' ) ? 'travello-primary' : 'primary';

$travail_account_url = '#';
if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_woocommerce_active() ) {
	$travail_account_url = wc_get_page_permalink( 'myaccount' );
} elseif ( is_user_logged_in() ) {
	$travail_account_url = admin_url( 'profile.php' );
}

$travail_cta_text = travail_get_option( 'header_cta_text', __( 'List Your Property', 'travail' ) );
$travail_cta_url  = travail_get_option( 'header_cta_url', '#' );
?>
<header class="travail-travello-header" id="travello-header" data-travail-header data-sticky="1">
	<div class="travail-travello-header__inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="travail-travello-logo" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<svg viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false">
					<circle cx="16" cy="16" r="15" class="travail-travello-logo__ring" />
					<path d="M16 6 L22 20 L16 17 L10 20 Z" class="travail-travello-logo__sail" />
					<circle cx="16" cy="22" r="2" class="travail-travello-logo__dot" />
				</svg>
				<span class="travail-travello-logo__text"><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>
		</a>

		<nav class="travail-travello-nav" aria-label="<?php esc_attr_e( 'Primary', 'travail' ); ?>">
			<?php
			if ( has_nav_menu( $travail_menu_location ) ) {
				wp_nav_menu(
					array(
						'theme_location' => $travail_menu_location,
						'container'      => false,
						'items_wrap'     => '<ul class="travail-travello-nav__list">%3$s</ul>',
						'depth'          => 1,
					)
				);
			} else {
				$travail_tours_link = post_type_exists( 'ttbm_tour' ) ? get_post_type_archive_link( 'ttbm_tour' ) : '#';
				$travail_dest_page  = get_page_by_path( 'destinations' );
				$travail_fallback   = array(
					__( 'Explore', 'travail' )      => home_url( '/' ),
					__( 'Destinations', 'travail' ) => $travail_dest_page ? get_permalink( $travail_dest_page ) : '#',
					__( 'Tours', 'travail' )        => $travail_tours_link ? $travail_tours_link : '#',
					__( 'Activities', 'travail' )   => '#',
					__( 'Hotels', 'travail' )       => '#',
					__( 'Transport', 'travail' )    => '#',
				);
				foreach ( $travail_fallback as $travail_label => $travail_url ) {
					printf( '<a href="%s">%s</a>', esc_url( $travail_url ), esc_html( $travail_label ) );
				}
			}
			?>
		</nav>

		<div class="travail-travello-header__actions">
			<button type="button" class="travail-travello-icon-btn" id="travail-search-toggle" aria-haspopup="dialog" aria-expanded="false" aria-controls="travail-search-modal" aria-label="<?php esc_attr_e( 'Search', 'travail' ); ?>">
				<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><circle cx="8.5" cy="8.5" r="5.5"/><path d="M14.5 14.5 18 18"/></svg>
			</button>

			<?php if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::has_wishlist_page() ) : ?>
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'ttbm-wishlist' ) ); ?>" class="travail-travello-icon-btn" aria-label="<?php esc_attr_e( 'Wishlist', 'travail' ); ?>">
					<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17S2 11.5 2 6.5A4 4 0 0110 4.06 4 4 0 0118 6.5C18 11.5 10 17 10 17Z"/></svg>
				</a>
			<?php endif; ?>

			<a href="<?php echo esc_url( $travail_account_url ); ?>" class="travail-travello-signin"><?php echo is_user_logged_in() ? esc_html__( 'My Account', 'travail' ) : esc_html__( 'Sign in', 'travail' ); ?></a>

			<?php if ( $travail_cta_text ) : ?>
				<a href="<?php echo esc_url( $travail_cta_url ); ?>" class="travail-travello-list-btn"><?php echo esc_html( $travail_cta_text ); ?></a>
			<?php endif; ?>
		</div>

		<button type="button" class="travail-travello-menu-btn" id="travello-mobile-menu-btn" aria-expanded="false" aria-controls="travello-mobile-nav" aria-label="<?php esc_attr_e( 'Menu', 'travail' ); ?>">
			<svg width="22" height="22" viewBox="0 0 22 22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M3 5.5h16M3 11h16M3 16.5h16"/></svg>
		</button>
	</div>

	<nav class="travail-travello-mobile-nav" id="travello-mobile-nav" aria-label="<?php esc_attr_e( 'Primary (mobile)', 'travail' ); ?>">
		<?php
		if ( has_nav_menu( $travail_menu_location ) ) {
			wp_nav_menu(
				array(
					'theme_location' => $travail_menu_location,
					'container'      => false,
					'items_wrap'     => '<ul class="travail-travello-mobile-nav__list">%3$s</ul>',
					'depth'          => 1,
				)
			);
		}
		?>
		<div class="travail-travello-mobile-nav__actions">
			<a href="<?php echo esc_url( $travail_account_url ); ?>"><?php echo is_user_logged_in() ? esc_html__( 'My Account', 'travail' ) : esc_html__( 'Sign in', 'travail' ); ?></a>
			<?php if ( $travail_cta_text ) : ?>
				<a href="<?php echo esc_url( $travail_cta_url ); ?>" class="travail-travello-list-btn"><?php echo esc_html( $travail_cta_text ); ?></a>
			<?php endif; ?>
		</div>
	</nav>
</header>
