<?php
/**
 * View: Setup Wizard.
 *
 * Expects $steps and $current from Travail_Onboarding::render_page().
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$step_keys = array_keys( $steps );
$current_index = array_search( $current, $step_keys, true );
?>
<div class="wrap travail-admin-wrap">
	<div class="travail-admin-header">
		<h1><?php esc_html_e( 'Travail Setup Wizard', 'travail' ); ?></h1>
		<p><?php esc_html_e( 'A few guided steps to get your travel site production-ready.', 'travail' ); ?></p>
	</div>

	<nav class="travail-wizard-steps" aria-label="<?php esc_attr_e( 'Setup Wizard steps', 'travail' ); ?>">
		<?php foreach ( $steps as $slug => $label ) : ?>
			<?php
			$index = array_search( $slug, $step_keys, true );
			$class = $slug === $current ? 'is-current' : ( $index < $current_index ? 'is-done' : '' );
			?>
			<a href="<?php echo esc_url( Travail_Onboarding::step_url( $slug ) ); ?>" class="<?php echo esc_attr( $class ); ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="travail-wizard-panel">

		<?php if ( 'welcome' === $current ) : ?>

			<h2><?php esc_html_e( 'Welcome to Travail', 'travail' ); ?></h2>
			<p><?php esc_html_e( 'This wizard checks your server, helps install the recommended plugins, and imports demo content so you can see the full design in minutes. You can exit at any time — nothing here is required to use the theme.', 'travail' ); ?></p>

		<?php elseif ( 'system' === $current ) : ?>

			<h2><?php esc_html_e( 'System Check', 'travail' ); ?></h2>
			<p><?php esc_html_e( 'Travail runs fine on any modern WordPress host. Here is your current environment:', 'travail' ); ?></p>
			<?php
			$checks       = Travail_Admin::get_system_checks();
			$status_icons = array(
				'pass' => array( '✓', '#2e7d32' ),
				'warn' => array( '⚠', '#b8860b' ),
				'fail' => array( '✕', '#c0392b' ),
				'info' => array( '•', '#607d8b' ),
			);
			?>
			<table class="widefat travail-status-table">
				<tbody>
					<?php foreach ( $checks as $check ) : ?>
						<?php list( $icon, $color ) = $status_icons[ $check['status'] ]; ?>
						<tr>
							<td><strong><?php echo esc_html( $check['label'] ); ?></strong></td>
							<td><?php echo esc_html( $check['value'] ); ?></td>
							<td style="color:<?php echo esc_attr( $color ); ?>;font-weight:700;"><?php echo esc_html( $icon ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

		<?php elseif ( 'plugins' === $current ) : ?>

			<h2><?php esc_html_e( 'Install Plugins', 'travail' ); ?></h2>
			<p><?php esc_html_e( 'Elementor is required for the visual homepage builder. Tour Booking Manager and WooCommerce are recommended.', 'travail' ); ?></p>
			<?php $plugins = Travail_Admin::get_recommended_plugins(); ?>
			<?php include TRAVAIL_DIR . '/inc/admin/views/partials/plugin-cards.php'; ?>

		<?php elseif ( 'demo-import' === $current ) : ?>

			<h2><?php esc_html_e( 'Import Demo Content', 'travail' ); ?></h2>
			<p><?php esc_html_e( 'Creates a starter homepage, pages, menus, footer widgets and theme settings so you can explore Travail with real content. Safe to run more than once.', 'travail' ); ?></p>
			<?php Travail_Demo_Importer::render_import_widget(); ?>

		<?php elseif ( 'finish' === $current ) : ?>

			<h2><?php esc_html_e( "You're all set!", 'travail' ); ?></h2>
			<p><?php esc_html_e( 'Here is where to go next:', 'travail' ); ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=travail_options' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Customize Theme Settings', 'travail' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button" target="_blank" rel="noopener"><?php esc_html_e( 'View Your Site', 'travail' ); ?></a>
			</p>

		<?php endif; ?>

		<div class="travail-wizard-actions">
			<?php if ( $current_index > 0 ) : ?>
				<a href="<?php echo esc_url( Travail_Onboarding::step_url( $step_keys[ $current_index - 1 ] ) ); ?>" class="button"><?php esc_html_e( '← Back', 'travail' ); ?></a>
			<?php else : ?>
				<span></span>
			<?php endif; ?>

			<?php if ( false !== $current_index && $current_index < count( $step_keys ) - 1 ) : ?>
				<a href="<?php echo esc_url( Travail_Onboarding::step_url( $step_keys[ $current_index + 1 ] ) ); ?>" class="button button-primary"><?php esc_html_e( 'Continue →', 'travail' ); ?></a>
			<?php else : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=travail-dashboard' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Go to Dashboard', 'travail' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>
