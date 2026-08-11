<?php
/**
 * View: System Status.
 *
 * Expects $checks from Travail_Admin::render_status().
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_icons = array(
	'pass' => array( '✓', '#2e7d32' ),
	'warn' => array( '⚠', '#b8860b' ),
	'fail' => array( '✕', '#c0392b' ),
	'info' => array( '•', '#607d8b' ),
);
?>
<div class="wrap travail-admin-wrap">
	<div class="travail-admin-header">
		<h1><?php esc_html_e( 'System Status', 'travail' ); ?></h1>
		<p><?php esc_html_e( 'A quick health check of your server and the plugins Travail integrates with.', 'travail' ); ?></p>
	</div>

	<table class="widefat travail-status-table" role="table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Check', 'travail' ); ?></th>
				<th><?php esc_html_e( 'Value', 'travail' ); ?></th>
				<th><?php esc_html_e( 'Status', 'travail' ); ?></th>
				<th><?php esc_html_e( 'Notes', 'travail' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $checks as $check ) : ?>
				<?php list( $icon, $color ) = $status_icons[ $check['status'] ]; ?>
				<tr>
					<td><strong><?php echo esc_html( $check['label'] ); ?></strong></td>
					<td><?php echo esc_html( $check['value'] ); ?></td>
					<td><span style="color:<?php echo esc_attr( $color ); ?>;font-weight:700;" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
						<span class="screen-reader-text"><?php echo esc_html( ucfirst( $check['status'] ) ); ?></span>
					</td>
					<td><?php echo esc_html( $check['hint'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
