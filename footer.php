<?php
/**
 * The footer for our theme.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

</div><!-- #travail-content -->

<?php
/**
 * Fires before the site footer markup.
 */
do_action( 'travail_before_footer' );
?>

<footer id="colophon" class="travail-footer">
	<div class="travail-container">
		<div class="travail-footer-top">

			<div class="travail-footer-brand">
				<?php get_template_part( 'template-parts/header/logo' ); ?>
				<p><?php echo esc_html( travail_get_option( 'footer_description', __( "Curating the world's finest travel experiences. Every trip, a new story.", 'travail' ) ) ); ?></p>
				<?php get_template_part( 'template-parts/footer/socials' ); ?>
			</div>

			<?php get_template_part( 'template-parts/footer/column', null, array( 'sidebar' => 'footer-1', 'menu' => 'footer-1', 'fallback_title' => __( 'Discover', 'travail' ) ) ); ?>
			<?php get_template_part( 'template-parts/footer/column', null, array( 'sidebar' => 'footer-2', 'menu' => 'footer-2', 'fallback_title' => __( 'Company', 'travail' ) ) ); ?>
			<?php get_template_part( 'template-parts/footer/column', null, array( 'sidebar' => 'footer-3', 'menu' => 'footer-3', 'fallback_title' => __( 'Support', 'travail' ) ) ); ?>

		</div>

		<div class="travail-footer-bottom">
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
							'items_wrap'     => '<ul class="travail-footer-links">%3$s</ul>',
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<?php if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_woocommerce_active() && travail_get_option( 'show_footer_payment_icons', true ) ) : ?>
				<div class="travail-footer-payments" aria-hidden="true">
					<?php get_template_part( 'template-parts/footer/payment-icons' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php get_template_part( 'template-parts/footer/mobile-bottom-nav' ); ?>

<?php
/**
 * Fires after the site footer markup.
 */
do_action( 'travail_after_footer' );
?>

<div id="travail-search-modal" class="travail-search-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Site search', 'travail' ); ?>" hidden>
	<div class="travail-search-modal__inner">
		<button type="button" class="travail-search-modal__close" id="travail-search-close" aria-label="<?php esc_attr_e( 'Close search', 'travail' ); ?>">&times;</button>
		<?php get_search_form(); ?>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
