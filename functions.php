<?php
/**
 * Travail theme bootstrap file.
 *
 * This file only defines constants and requires the files under /inc/.
 * Actual logic lives in small, single-purpose files so nothing here
 * grows into an unmaintainable "God file".
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'TRAVAIL_VERSION', '1.0.0' );
define( 'TRAVAIL_DIR', get_template_directory() );
define( 'TRAVAIL_URI', get_template_directory_uri() );
define( 'TRAVAIL_MIN_PHP', '7.4' );

/**
 * Minimum PHP version guard.
 *
 * Runs before anything else touches PHP 7.4+ syntax so a host on an
 * ancient PHP build gets a clear admin notice instead of a fatal error.
 */
if ( version_compare( PHP_VERSION, TRAVAIL_MIN_PHP, '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: 1: required PHP version, 2: current PHP version */
						__( 'Travail requires PHP %1$s or higher. Your server is running PHP %2$s. Please ask your host to upgrade PHP.', 'travail' ),
						TRAVAIL_MIN_PHP,
						PHP_VERSION
					)
				)
			);
		}
	);
	return;
}

/**
 * Core includes — order matters: helpers first, then things that use them.
 */
$travail_includes = array(
	'inc/helpers.php',                 // Small pure utility functions.
	'inc/setup.php',                   // add_theme_support, nav menus, sidebars.
	'inc/enqueue.php',                 // Scripts & styles.
	'inc/template-functions.php',      // Functions used inside templates (classes, meta, pagination…).
	'inc/template-hooks.php',          // Wires template-parts to travail_* action hooks.
	'inc/homepage-travello.php',       // Alternate "Travello" homepage — see Customizer → Homepage.
	'inc/customizer.php',              // Customizer sections/settings.
	'inc/compatibility/class-travail-plugin-compatibility.php',
	'inc/compatibility/woocommerce.php',
	'inc/compatibility/tour-booking-manager.php',
	'inc/elementor/class-travail-elementor.php',
	'inc/woocommerce/class-travail-woocommerce.php',
	'inc/admin/class-travail-admin.php',
	'inc/onboarding/class-travail-onboarding.php',
	'inc/importer/class-travail-elementor-page-builder.php',
	'inc/importer/class-travail-demo-importer.php',
	'inc/blocks.php',
);

foreach ( $travail_includes as $travail_file ) {
	$travail_path = TRAVAIL_DIR . '/' . $travail_file;
	if ( is_readable( $travail_path ) ) {
		require_once $travail_path;
	}
}
unset( $travail_includes, $travail_file, $travail_path );
