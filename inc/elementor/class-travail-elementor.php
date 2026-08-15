<?php
/**
 * Elementor bootstrap: category registration + widget loading.
 *
 * Everything in this file is a no-op when Elementor isn't active — it
 * only ever hooks into elementor/* actions, which simply never fire
 * without the plugin, so there is zero risk of a fatal error
 * (Scenario B/D/E from the spec).
 *
 * The one exception is skip_onboarding_redirect(), which hooks the
 * generic `admin_init` action because that's where Elementor itself
 * fires the redirect we're cancelling — see its doc-comment.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Travail_Elementor
 */
class Travail_Elementor {

	/**
	 * Boot the integration.
	 */
	public static function init() {
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/frontend/after_enqueue_styles', array( __CLASS__, 'enqueue_editor_assets' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'enqueue_editor_assets' ) );
		add_action( 'admin_notices', array( __CLASS__, 'maybe_show_missing_notice' ) );

		// Priority 5 so this runs before Elementor's own `admin_init`
		// callback (registered at the default priority 10).
		add_action( 'admin_init', array( __CLASS__, 'skip_onboarding_redirect' ), 5 );
	}

	/**
	 * Register the "Travail" widget category so all our widgets group
	 * together in the Elementor panel instead of scattering under
	 * "General".
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 */
	public static function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'travail',
			array(
				'title' => __( 'Travail', 'travail' ),
				'icon'  => 'eicon-compass',
			)
		);
	}

	/**
	 * Register every Travail widget. Each widget file guards itself with
	 * a class_exists( '\Elementor\Widget_Base' ) check as a second line
	 * of defence, but we already know Elementor is loaded at this point
	 * because this callback only runs on the elementor/widgets/register
	 * hook.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public static function register_widgets( $widgets_manager ) {
		$widget_files = array(
			'class-hero-widget.php'             => 'Travail_Elementor_Hero_Widget',
			'class-tour-search-widget.php'      => 'Travail_Elementor_Tour_Search_Widget',
			// Tour Grid covers Grid/Carousel/Featured/Popular/Deals via its "Source" + "Layout"
			// controls — see the class doc-comment for why five near-duplicate widgets were
			// consolidated into one.
			'class-tour-grid-widget.php'         => 'Travail_Elementor_Tour_Grid_Widget',
			'class-destination-grid-widget.php'  => 'Travail_Elementor_Destination_Grid_Widget',
			'class-tour-activities-widget.php'   => 'Travail_Elementor_Tour_Activities_Widget',
			'class-testimonials-widget.php'      => 'Travail_Elementor_Testimonials_Widget',
			'class-blog-grid-widget.php'         => 'Travail_Elementor_Blog_Grid_Widget',
			'class-cta-widget.php'               => 'Travail_Elementor_CTA_Widget',
			'class-features-widget.php'          => 'Travail_Elementor_Features_Widget',
			'class-newsletter-widget.php'        => 'Travail_Elementor_Newsletter_Widget',
		);

		foreach ( $widget_files as $file => $class ) {
			$path = TRAVAIL_DIR . '/elementor/widgets/' . $file;
			if ( ! is_readable( $path ) ) {
				continue;
			}
			require_once $path;
			if ( class_exists( $class ) ) {
				$widgets_manager->register( new $class() );
			}
		}
	}

	/**
	 * Shared editor/frontend CSS for widgets (kept separate from the
	 * main theme stylesheet so Elementor's own preview iframe loads only
	 * what it needs).
	 */
	public static function enqueue_editor_assets() {
		wp_enqueue_style( 'travail-base', TRAVAIL_URI . '/assets/css/base.css', array(), TRAVAIL_VERSION );
		wp_enqueue_style( 'travail-layout', TRAVAIL_URI . '/assets/css/layout.css', array( 'travail-base' ), TRAVAIL_VERSION );
		wp_enqueue_style( 'travail-components', TRAVAIL_URI . '/assets/css/components.css', array( 'travail-layout' ), TRAVAIL_VERSION );
		wp_enqueue_style( 'travail-destination', TRAVAIL_URI . '/assets/css/destination.css', array( 'travail-components' ), TRAVAIL_VERSION );
		wp_enqueue_style( 'travail-travello', TRAVAIL_URI . '/assets/css/travello.css', array( 'travail-components' ), TRAVAIL_VERSION );
	}

	/**
	 * Cancel Elementor's own "redirect to the onboarding wizard" after
	 * install/activation.
	 *
	 * Elementor's activation hook (Elementor\Maintenance::activation())
	 * sets a 1-minute transient, `elementor_activation_redirect`, and its
	 * Admin component checks that transient on `admin_init`
	 * (Elementor\Core\Admin\Admin::maybe_redirect_to_getting_started(),
	 * hooked at the default priority 10) to bounce the very next admin
	 * request to `admin.php?page=elementor-app#onboarding`.
	 *
	 * We run one tick earlier — priority 5 — and clear that transient
	 * before Elementor's callback ever checks it, so activating Elementor
	 * leaves the admin on the Plugins screen instead of being redirected
	 * into the onboarding wizard. Deleting a transient that doesn't exist
	 * is a harmless no-op, so this is safe to run on every admin request.
	 */
	public static function skip_onboarding_redirect() {
		delete_transient( 'elementor_activation_redirect' );
	}

	/**
	 * Friendly (non-fatal) admin notice when Elementor is missing —
	 * required by spec section 35, dismissible, capability-checked.
	 */
	public static function maybe_show_missing_notice() {
		if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_elementor_active() ) {
			return;
		}
		if ( ! current_user_can( 'install_plugins' ) || get_option( 'travail_dismiss_elementor_notice' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'themes' !== $screen->id ) {
			return;
		}
		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<?php
				printf(
					/* translators: 1: opening link tag, 2: closing link tag */
					esc_html__( 'Travail is built for Elementor. %1$sInstall Elementor%2$s to unlock the fully visual homepage builder and every Travail widget.', 'travail' ),
					'<a href="' . esc_url( admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' ) ) . '">',
					'</a>'
				);
				?>
			</p>
		</div>
		<?php
	}
}

Travail_Elementor::init();
