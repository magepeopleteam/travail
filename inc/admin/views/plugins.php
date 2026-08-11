<?php
/**
 * View: Recommended Plugins.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugins = Travail_Admin::get_recommended_plugins();
?>
<div class="wrap travail-admin-wrap">
	<div class="travail-admin-header">
		<h1><?php esc_html_e( 'Recommended Plugins', 'travail' ); ?></h1>
		<p><?php esc_html_e( 'Travail works standalone, but these plugins unlock its full travel-booking experience.', 'travail' ); ?></p>
	</div>

	<?php include TRAVAIL_DIR . '/inc/admin/views/partials/plugin-cards.php'; ?>
</div>
