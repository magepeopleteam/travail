<?php
/**
 * Partial: recommended-plugin cards. Shared by the standalone
 * "Recommended Plugins" screen and the Setup Wizard's "Plugins" step.
 *
 * Expects $plugins from Travail_Admin::get_recommended_plugins().
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="travail-admin-cards">
	<?php foreach ( $plugins as $slug => $plugin ) : ?>
		<div class="travail-admin-card travail-plugin-card" data-slug="<?php echo esc_attr( $slug ); ?>">
			<span class="travail-badge <?php echo 'required' === $plugin['type'] ? 'travail-badge--sale' : ''; ?>" style="margin-bottom:12px;">
				<?php echo esc_html( 'required' === $plugin['type'] ? __( 'Required', 'travail' ) : __( 'Recommended', 'travail' ) ); ?>
			</span>
			<h2><?php echo esc_html( $plugin['name'] ); ?></h2>
			<p><?php echo esc_html( $plugin['description'] ); ?></p>

			<?php if ( $plugin['active'] ) : ?>
				<p class="travail-plugin-status" style="color:#2e7d32;font-weight:600;">✓ <?php esc_html_e( 'Active', 'travail' ); ?></p>
			<?php elseif ( $plugin['installed'] ) : ?>
				<button type="button" class="button button-primary travail-activate-plugin" data-plugin-file="<?php echo esc_attr( $plugin['file'] ); ?>">
					<?php esc_html_e( 'Activate', 'travail' ); ?>
				</button>
			<?php elseif ( $plugin['wporg_slug'] ) : ?>
				<button type="button" class="button button-primary travail-install-plugin" data-slug="<?php echo esc_attr( $plugin['wporg_slug'] ); ?>">
					<?php esc_html_e( 'Install & Activate', 'travail' ); ?>
				</button>
			<?php else : ?>
				<p class="travail-plugin-status" style="color:#b8860b;">
					<?php esc_html_e( 'Not detected. Please install this plugin from your license account.', 'travail' ); ?>
				</p>
			<?php endif; ?>
			<span class="travail-plugin-spinner spinner" style="float:none;"></span>
			<p class="travail-plugin-message"></p>
		</div>
	<?php endforeach; ?>
</div>
