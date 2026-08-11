<?php
/**
 * Central, safe plugin-detection layer.
 *
 * Every other file in the theme should ask THIS class whether a plugin is
 * active rather than calling class_exists()/function_exists() directly,
 * so detection logic (and any future version checks) lives in one place.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Travail_Plugin_Compatibility
 */
class Travail_Plugin_Compatibility {

	/**
	 * Cache of plugin_active checks for the request, keyed by plugin file.
	 *
	 * @var array<string, bool>
	 */
	protected static $active_plugins_cache = null;

	/**
	 * Whether the free "Tour Booking Manager" plugin is active.
	 *
	 * Checked several ways on purpose: functions.php requires the
	 * compatibility files very early (before the `init` hook, i.e.
	 * before post types/taxonomies are registered), so a check that
	 * only relied on post_type_exists()/taxonomy_exists() would give a
	 * false negative at that point. defined()/class_exists() are true
	 * immediately once the plugin file has loaded; the active_plugins
	 * option check works even earlier than that.
	 *
	 * @return bool
	 */
	public static function is_tour_booking_manager_active() {
		return defined( 'TTBM_PLUGIN_VERSION' )
			|| class_exists( 'TTBM_Function' )
			|| post_type_exists( 'ttbm_tour' )
			|| self::plugin_file_active( 'tour-booking-manager/tour-booking-manager.php' );
	}

	/**
	 * Whether "Tour Booking Manager Pro" is active.
	 *
	 * @return bool
	 */
	public static function is_tour_booking_manager_pro_active() {
		return defined( 'TTBM_PRO_VERSION' ) || class_exists( 'Ttbm_Pro' ) || self::plugin_file_active( 'tour-booking-manager-pro/tour-booking-manager-pro.php' );
	}

	/**
	 * Whether WooCommerce is active.
	 *
	 * @return bool
	 */
	public static function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Whether Elementor (free) is active.
	 *
	 * @return bool
	 */
	public static function is_elementor_active() {
		return did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' );
	}

	/**
	 * Whether Elementor Pro is active (some widgets can offer richer
	 * dynamic-tag integration when it is present, but nothing requires it).
	 *
	 * @return bool
	 */
	public static function is_elementor_pro_active() {
		return defined( 'ELEMENTOR_PRO_VERSION' );
	}

	/**
	 * Whether the wishlist feature is available. Confirmed: wishlist
	 * (TTBM_Wishlist) ships in the FREE plugin, not Pro — it stores
	 * saved tours as user meta and surfaces them as a WooCommerce My
	 * Account tab ("ttbm-wishlist" endpoint) once WooCommerce is active.
	 *
	 * @return bool
	 */
	public static function supports_wishlist() {
		return class_exists( 'TTBM_Wishlist' );
	}

	/**
	 * Whether the wishlist has a page a header link can point to (the
	 * "ttbm-wishlist" endpoint only renders inside WooCommerce My Account).
	 *
	 * @return bool
	 */
	public static function has_wishlist_page() {
		return self::supports_wishlist() && self::is_woocommerce_active();
	}

	/**
	 * Whether tour reviews/ratings are available. Confirmed Pro-only:
	 * the ttbm_tour_review CPT + star-rating UI (TTBM_Review_Rating) do
	 * not exist at all in the free plugin.
	 *
	 * @return bool
	 */
	public static function supports_reviews() {
		return post_type_exists( 'ttbm_tour_review' ) && class_exists( 'TTBM_Review_Rating' );
	}

	/**
	 * Whether coupons are available. Confirmed: NOT implemented by
	 * either plugin (no coupon CPT/table/option/meta exists anywhere in
	 * tour-booking-manager or tour-booking-manager-pro). Kept as a
	 * filterable capability flag — defaults to false — so a future
	 * coupon add-on or a WooCommerce-native coupon field can announce
	 * itself without a theme update.
	 *
	 * @return bool
	 */
	public static function supports_coupons() {
		return (bool) apply_filters( 'travail_supports_coupons', false );
	}

	/**
	 * Whether partial/deposit payments are available. Confirmed: this
	 * is a capability of a separate, third-party add-on
	 * ("mage-partial-payment-pro"), not tour-booking-manager-pro itself,
	 * and that add-on is not installed in this environment. Detected
	 * safely rather than assumed.
	 *
	 * @return bool
	 */
	public static function supports_partial_payment() {
		return self::plugin_file_active( 'mage-partial-payment-pro/mage_partial_pro.php' );
	}

	/**
	 * Low-level: is a plugin file active, checked safely against the
	 * active_plugins option (works even before the plugin's own
	 * constants have loaded, e.g. during theme switch).
	 *
	 * @param string $plugin_file "folder/main-file.php" relative to the plugins dir.
	 * @return bool
	 */
	public static function plugin_file_active( $plugin_file ) {
		if ( null === self::$active_plugins_cache ) {
			self::$active_plugins_cache = array_flip( (array) get_option( 'active_plugins', array() ) );

			if ( is_multisite() ) {
				$network_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
				self::$active_plugins_cache += array_fill_keys( array_keys( $network_plugins ), true );
			}
		}

		return isset( self::$active_plugins_cache[ $plugin_file ] );
	}

	/**
	 * Convenience: returns a scenario key describing which optional
	 * plugins are active, useful for debugging/System Status.
	 *
	 * @return string One of 'full', 'tbm-elementor', 'elementor-only', 'woocommerce-only', 'bare'.
	 */
	public static function get_scenario() {
		$tbm        = self::is_tour_booking_manager_active();
		$elementor  = self::is_elementor_active();
		$woocommerce = self::is_woocommerce_active();

		if ( $tbm && $elementor && $woocommerce ) {
			return 'full';
		}
		if ( $tbm && $elementor ) {
			return 'tbm-elementor';
		}
		if ( $elementor && ! $tbm ) {
			return 'elementor-only';
		}
		if ( $woocommerce && ! $tbm ) {
			return 'woocommerce-only';
		}
		if ( ! $tbm && ! $elementor && ! $woocommerce ) {
			return 'bare';
		}
		return 'partial';
	}
}
