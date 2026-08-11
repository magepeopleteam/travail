<?php
/**
 * View: Travail Dashboard.
 *
 * Expects $scenario from Travail_Admin::render_dashboard().
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap travail-admin-wrap">
	<div class="travail-admin-header">
		<h1><?php esc_html_e( 'Travail', 'travail' ); ?></h1>
		<p><?php esc_html_e( 'A premium, Elementor-ready tour booking theme.', 'travail' ); ?></p>
	</div>

	<div class="travail-admin-cards">
		<div class="travail-admin-card">
			<h2><?php esc_html_e( 'Setup Wizard', 'travail' ); ?></h2>
			<p><?php esc_html_e( 'Install recommended plugins, import demo content and set your homepage in a few guided steps.', 'travail' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=travail-setup-wizard' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Launch Setup Wizard', 'travail' ); ?></a>
		</div>

		<div class="travail-admin-card">
			<h2><?php esc_html_e( 'Theme Settings', 'travail' ); ?></h2>
			<p><?php esc_html_e( 'Colors, typography, header/footer options and more — all in the native Customizer.', 'travail' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=travail_options' ) ); ?>" class="button"><?php esc_html_e( 'Open Theme Settings', 'travail' ); ?></a>
		</div>

		<div class="travail-admin-card">
			<h2><?php esc_html_e( 'Demo Import', 'travail' ); ?></h2>
			<p><?php esc_html_e( 'One-click import of demo pages, menus, widgets and homepage sections.', 'travail' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=travail-demo-import' ) ); ?>" class="button"><?php esc_html_e( 'Import Demo Content', 'travail' ); ?></a>
		</div>

		<div class="travail-admin-card">
			<h2><?php esc_html_e( 'System Status', 'travail' ); ?></h2>
			<p><?php esc_html_e( 'Check server requirements and plugin compatibility at a glance.', 'travail' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=travail-status' ) ); ?>" class="button"><?php esc_html_e( 'View System Status', 'travail' ); ?></a>
		</div>
	</div>

	<div class="travail-admin-scenario">
		<h2><?php esc_html_e( 'Current Setup', 'travail' ); ?></h2>
		<?php
		$scenario_labels = array(
			'full'             => __( 'Elementor + Tour Booking Manager + WooCommerce — the complete travel booking experience is unlocked.', 'travail' ),
			'tbm-elementor'    => __( 'Elementor + Tour Booking Manager — tours, search and booking cards are fully active. Add WooCommerce for cart/checkout.', 'travail' ),
			'elementor-only'   => __( 'Elementor is active but Tour Booking Manager was not detected — install it to unlock tour listings and booking.', 'travail' ),
			'woocommerce-only' => __( 'WooCommerce is active. Install Tour Booking Manager to sell bookable tours, or use Travail as a general WooCommerce theme.', 'travail' ),
			'bare'             => __( 'Running with core WordPress only. Install Elementor and Tour Booking Manager from the Recommended Plugins screen to unlock the full experience.', 'travail' ),
			'partial'          => __( 'A mix of optional plugins is active — visit System Status for the full breakdown.', 'travail' ),
		);
		$message = isset( $scenario_labels[ $scenario ] ) ? $scenario_labels[ $scenario ] : $scenario_labels['partial'];
		?>
		<p><?php echo esc_html( $message ); ?></p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=travail-plugins' ) ); ?>" class="travail-link-arrow"><?php esc_html_e( 'Manage recommended plugins →', 'travail' ); ?></a>
	</div>
</div>
