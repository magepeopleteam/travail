<?php
/**
 * Travail admin menu: Dashboard, Setup Wizard, Demo Import, Recommended
 * Plugins, System Status, Documentation. "Theme Settings" deliberately
 * links straight into the native Customizer instead of duplicating it.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Travail_Admin
 */
class Travail_Admin {

	const CAPABILITY = 'edit_theme_options';

	/**
	 * Boot.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_action( 'admin_notices', array( __CLASS__, 'maybe_show_activation_notice' ) );
		add_action( 'wp_ajax_travail_install_plugin', array( __CLASS__, 'ajax_install_plugin' ) );
		add_action( 'wp_ajax_travail_activate_plugin', array( __CLASS__, 'ajax_activate_plugin' ) );
	}

	/**
	 * The only plugins this screen is allowed to install/activate via
	 * AJAX. Keeping this as a hardcoded allow-list (rather than trusting
	 * any slug posted by the browser) is what makes the install/activate
	 * endpoints below safe to expose.
	 *
	 * @return array<string, array{name:string, description:string, type:string, wporg_slug:string, file:string}>
	 */
	public static function get_recommended_plugins() {
		$definitions = array(
			'elementor'   => array(
				'name'        => __( 'Elementor', 'travail' ),
				'description' => __( 'The visual page builder that powers every editable section of the Travail homepage.', 'travail' ),
				'type'        => 'required',
				'wporg_slug'  => 'elementor',
				'file'        => 'elementor/elementor.php',
				'is_active'   => 'Travail_Plugin_Compatibility::is_elementor_active',
			),
			'woocommerce' => array(
				'name'        => __( 'WooCommerce', 'travail' ),
				'description' => __( 'Optional — adds cart, checkout and a full My Account area for paid tour bookings.', 'travail' ),
				'type'        => 'recommended',
				'wporg_slug'  => 'woocommerce',
				'file'        => 'woocommerce/woocommerce.php',
				'is_active'   => 'Travail_Plugin_Compatibility::is_woocommerce_active',
			),
			'tour-booking-manager' => array(
				'name'        => __( 'Tour Booking Manager', 'travail' ),
				'description' => __( 'The tour listings, search and booking engine Travail is designed around.', 'travail' ),
				'type'        => 'recommended',
				'wporg_slug'  => '', // Not distributed on WordPress.org — licensed product, installed manually.
				'file'        => 'tour-booking-manager/tour-booking-manager.php',
				'is_active'   => 'Travail_Plugin_Compatibility::is_tour_booking_manager_active',
			),
			'tour-booking-manager-pro' => array(
				'name'        => __( 'Tour Booking Manager Pro', 'travail' ),
				'description' => __( 'Adds extra services, coupons, partial payments, wishlist and richer availability.', 'travail' ),
				'type'        => 'recommended',
				'wporg_slug'  => '',
				'file'        => 'tour-booking-manager-pro/tour-booking-manager-pro.php',
				'is_active'   => 'Travail_Plugin_Compatibility::is_tour_booking_manager_pro_active',
			),
		);

		$plugins = array();
		foreach ( $definitions as $slug => $plugin ) {
			$active    = is_callable( $plugin['is_active'] ) ? call_user_func( $plugin['is_active'] ) : false;
			$installed = file_exists( WP_PLUGIN_DIR . '/' . $plugin['file'] );

			$plugins[ $slug ] = array_merge(
				$plugin,
				array(
					'active'    => (bool) $active,
					'installed' => $installed,
				)
			);
		}

		return apply_filters( 'travail_recommended_plugins', $plugins );
	}

	/**
	 * AJAX: install + activate a plugin from WordPress.org.
	 *
	 * Only slugs present in get_recommended_plugins() with a non-empty
	 * wporg_slug are ever accepted — nothing arbitrary from $_POST is
	 * passed to the installer.
	 */
	public static function ajax_install_plugin() {
		check_ajax_referer( 'travail_admin', 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to install plugins.', 'travail' ) ), 403 );
		}

		$requested_slug = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
		$allowed_slugs  = wp_list_pluck( self::get_recommended_plugins(), 'wporg_slug' );
		if ( ! $requested_slug || ! in_array( $requested_slug, $allowed_slugs, true ) ) {
			wp_send_json_error( array( 'message' => __( 'That plugin is not on the approved list.', 'travail' ) ), 400 );
		}

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$api = plugins_api( 'plugin_information', array( 'slug' => $requested_slug, 'fields' => array( 'sections' => false ) ) );
		if ( is_wp_error( $api ) ) {
			wp_send_json_error( array( 'message' => $api->get_error_message() ) );
		}

		$skin     = new Automatic_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );

		if ( is_wp_error( $result ) || ! $result ) {
			wp_send_json_error( array( 'message' => is_wp_error( $result ) ? $result->get_error_message() : __( 'Installation failed.', 'travail' ) ) );
		}

		$plugin_file = $upgrader->plugin_info();
		if ( ! $plugin_file ) {
			wp_send_json_error( array( 'message' => __( 'Plugin installed but could not be located for activation.', 'travail' ) ) );
		}

		$activated = activate_plugin( $plugin_file );
		if ( is_wp_error( $activated ) ) {
			wp_send_json_error( array( 'message' => $activated->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Installed and activated.', 'travail' ) ) );
	}

	/**
	 * AJAX: activate an already-installed plugin.
	 */
	public static function ajax_activate_plugin() {
		check_ajax_referer( 'travail_admin', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to activate plugins.', 'travail' ) ), 403 );
		}

		$requested_file = isset( $_POST['plugin_file'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_file'] ) ) : '';
		$allowed_files   = wp_list_pluck( self::get_recommended_plugins(), 'file' );
		if ( ! $requested_file || ! in_array( $requested_file, $allowed_files, true ) ) {
			wp_send_json_error( array( 'message' => __( 'That plugin is not on the approved list.', 'travail' ) ), 400 );
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$result = activate_plugin( $requested_file );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Activated.', 'travail' ) ) );
	}

	/**
	 * Register the top-level "Travail" admin menu and its submenus.
	 */
	public static function register_menu() {
		$parent_slug = 'travail-dashboard';

		add_menu_page(
			__( 'Travail', 'travail' ),
			__( 'Travail', 'travail' ),
			self::CAPABILITY,
			$parent_slug,
			array( __CLASS__, 'render_dashboard' ),
			'dashicons-palmtree',
			61
		);

		add_submenu_page( $parent_slug, __( 'Dashboard', 'travail' ), __( 'Dashboard', 'travail' ), self::CAPABILITY, $parent_slug, array( __CLASS__, 'render_dashboard' ) );
		add_submenu_page( $parent_slug, __( 'Setup Wizard', 'travail' ), __( 'Setup Wizard', 'travail' ), self::CAPABILITY, 'travail-setup-wizard', array( 'Travail_Onboarding', 'render_page' ) );
		add_submenu_page( $parent_slug, __( 'Demo Import', 'travail' ), __( 'Demo Import', 'travail' ), self::CAPABILITY, 'travail-demo-import', array( 'Travail_Demo_Importer', 'render_page' ) );
		add_submenu_page( $parent_slug, __( 'Recommended Plugins', 'travail' ), __( 'Recommended Plugins', 'travail' ), self::CAPABILITY, 'travail-plugins', array( __CLASS__, 'render_plugins' ) );
		add_submenu_page( $parent_slug, __( 'System Status', 'travail' ), __( 'System Status', 'travail' ), self::CAPABILITY, 'travail-status', array( __CLASS__, 'render_status' ) );
		add_submenu_page( $parent_slug, __( 'Documentation', 'travail' ), __( 'Documentation', 'travail' ), self::CAPABILITY, 'travail-docs', array( __CLASS__, 'render_docs' ) );

		// "Theme Settings" -> deep link straight into the Customizer panel.
		add_submenu_page( $parent_slug, __( 'Theme Settings', 'travail' ), __( 'Theme Settings', 'travail' ), self::CAPABILITY, 'travail-theme-settings-redirect', array( __CLASS__, 'redirect_to_customizer' ) );
	}

	/**
	 * Enqueue the single small admin stylesheet on Travail's own pages only.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue_admin_assets( $hook ) {
		if ( false === strpos( $hook, 'travail-' ) && false === strpos( $hook, 'page_travail' ) ) {
			return;
		}
		wp_enqueue_style( 'travail-admin', TRAVAIL_URI . '/assets/css/admin.css', array(), TRAVAIL_VERSION );
		wp_enqueue_script( 'travail-admin', TRAVAIL_URI . '/assets/js/admin.js', array( 'jquery' ), TRAVAIL_VERSION, true );
		wp_localize_script(
			'travail-admin',
			'travailAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'travail_admin' ),
			)
		);
	}

	/**
	 * Redirect the "Theme Settings" submenu straight to the Customizer.
	 */
	public static function redirect_to_customizer() {
		wp_safe_redirect( admin_url( 'customize.php?autofocus[panel]=travail_options' ) );
		exit;
	}

	/**
	 * Dashboard screen: plugin status cards + quick links.
	 */
	public static function render_dashboard() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		$scenario = class_exists( 'Travail_Plugin_Compatibility' ) ? Travail_Plugin_Compatibility::get_scenario() : 'bare';
		include TRAVAIL_DIR . '/inc/admin/views/dashboard.php';
	}

	/**
	 * Recommended plugins screen.
	 */
	public static function render_plugins() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		include TRAVAIL_DIR . '/inc/admin/views/plugins.php';
	}

	/**
	 * System status screen.
	 */
	public static function render_status() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		$checks = self::get_system_checks();
		include TRAVAIL_DIR . '/inc/admin/views/status.php';
	}

	/**
	 * Documentation screen.
	 */
	public static function render_docs() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		include TRAVAIL_DIR . '/inc/admin/views/docs.php';
	}

	/**
	 * Build the System Status data set used by both the admin screen and
	 * (optionally) a "copy for support" text export.
	 *
	 * @return array<int, array{label:string, value:string, status:string, hint:string}>
	 */
	public static function get_system_checks() {
		global $wp_version;

		$checks = array();

		$checks[] = array(
			'label'  => __( 'WordPress Version', 'travail' ),
			'value'  => $wp_version,
			'status' => version_compare( $wp_version, '6.0', '>=' ) ? 'pass' : 'warn',
			'hint'   => __( 'Travail is tested on WordPress 6.0+.', 'travail' ),
		);

		$checks[] = array(
			'label'  => __( 'PHP Version', 'travail' ),
			'value'  => PHP_VERSION,
			'status' => version_compare( PHP_VERSION, '7.4', '>=' ) ? 'pass' : 'fail',
			'hint'   => __( 'PHP 7.4 or higher is required; PHP 8.1+ recommended.', 'travail' ),
		);

		$memory_limit = ini_get( 'memory_limit' );
		$checks[]     = array(
			'label'  => __( 'Memory Limit', 'travail' ),
			'value'  => $memory_limit,
			'status' => ( wp_convert_hr_to_bytes( $memory_limit ) >= wp_convert_hr_to_bytes( '128M' ) ) ? 'pass' : 'warn',
			'hint'   => __( '128M or higher recommended, especially for demo import and Elementor.', 'travail' ),
		);

		$max_execution = (int) ini_get( 'max_execution_time' );
		$checks[]      = array(
			'label'  => __( 'Max Execution Time', 'travail' ),
			'value'  => 0 === $max_execution ? __( 'Unlimited', 'travail' ) : $max_execution . 's',
			'status' => ( 0 === $max_execution || $max_execution >= 60 ) ? 'pass' : 'warn',
			'hint'   => __( '60s or higher recommended for the demo importer.', 'travail' ),
		);

		$plugin_rows = array(
			'elementor'    => array( __( 'Elementor', 'travail' ), Travail_Plugin_Compatibility::is_elementor_active() ),
			'tbm'          => array( __( 'Tour Booking Manager', 'travail' ), Travail_Plugin_Compatibility::is_tour_booking_manager_active() ),
			'tbm_pro'      => array( __( 'Tour Booking Manager Pro', 'travail' ), Travail_Plugin_Compatibility::is_tour_booking_manager_pro_active() ),
			'woocommerce'  => array( __( 'WooCommerce', 'travail' ), Travail_Plugin_Compatibility::is_woocommerce_active() ),
		);
		foreach ( $plugin_rows as $row ) {
			$checks[] = array(
				'label'  => $row[0],
				'value'  => $row[1] ? __( 'Active', 'travail' ) : __( 'Not active', 'travail' ),
				'status' => $row[1] ? 'pass' : ( 'elementor' === $row[0] ? 'warn' : 'info' ),
				'hint'   => '',
			);
		}

		$upload_dir = wp_upload_dir();
		$checks[]   = array(
			'label'  => __( 'Uploads Directory Writable', 'travail' ),
			'value'  => wp_is_writable( $upload_dir['basedir'] ) ? __( 'Yes', 'travail' ) : __( 'No', 'travail' ),
			'status' => wp_is_writable( $upload_dir['basedir'] ) ? 'pass' : 'fail',
			'hint'   => __( 'Required for the Demo Importer to download and attach media.', 'travail' ),
		);

		$permalink_structure = get_option( 'permalink_structure' );
		$checks[]             = array(
			'label'  => __( 'Permalink Structure', 'travail' ),
			'value'  => $permalink_structure ? $permalink_structure : __( 'Plain (not recommended)', 'travail' ),
			'status' => $permalink_structure ? 'pass' : 'warn',
			'hint'   => __( 'A "pretty" permalink structure is required for the tour/destination archives.', 'travail' ),
		);

		foreach ( array( 'curl', 'gd', 'mbstring', 'dom' ) as $extension ) {
			$loaded   = extension_loaded( $extension );
			$checks[] = array(
				'label'  => sprintf( 'PHP Extension: %s', $extension ),
				'value'  => $loaded ? __( 'Loaded', 'travail' ) : __( 'Missing', 'travail' ),
				'status' => $loaded ? 'pass' : 'warn',
				'hint'   => '',
			);
		}

		return apply_filters( 'travail_system_checks', $checks );
	}

	/**
	 * One-time welcome notice pointing to the Setup Wizard, shown only on
	 * the Themes screen right after activation.
	 */
	public static function maybe_show_activation_notice() {
		if ( ! current_user_can( self::CAPABILITY ) || ! get_option( 'travail_show_activation_notice' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 'themes' === $screen->id ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<strong><?php esc_html_e( 'Welcome to Travail!', 'travail' ); ?></strong>
					<?php esc_html_e( 'Run the Setup Wizard to install recommended plugins and import demo content.', 'travail' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=travail-setup-wizard' ) ); ?>" class="button button-primary" style="margin-inline-start:8px;"><?php esc_html_e( 'Start Setup Wizard', 'travail' ); ?></a>
				</p>
			</div>
			<?php
			delete_option( 'travail_show_activation_notice' );
		}
	}
}

Travail_Admin::init();

/**
 * Flip the "show activation notice" flag once, on theme activation.
 */
function travail_admin_on_activation() {
	update_option( 'travail_show_activation_notice', 1 );
}
add_action( 'after_switch_theme', 'travail_admin_on_activation' );
