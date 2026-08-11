<?php
/**
 * WooCommerce integration: layout wrappers + a handful of hook-based
 * tweaks. No WooCommerce templates are copied into /woocommerce/ unless
 * genuinely necessary, per the "avoid unnecessary template overrides"
 * rule — everything here uses WooCommerce's own action hooks instead.
 *
 * Note on "My Account" tabs: Tour Booking Manager Pro already registers
 * real "My Bookings" (`ttbm-bookings` endpoint) and "Wishlist"
 * (`ttbm-wishlist` endpoint, free plugin) tabs into
 * `woocommerce_account_menu_items` itself. This class deliberately does
 * NOT add a second/duplicate tab — it only reskins whatever the plugins
 * render there via assets/css/tbm-restyle.css, so there is exactly one
 * "My Bookings" tab, and it's the real one.
 *
 * Entirely inert if WooCommerce is not active: every method is only
 * ever called from a woocommerce_* hook, which simply doesn't fire.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Travail_WooCommerce
 */
class Travail_WooCommerce {

	/**
	 * Boot.
	 */
	public static function init() {
		if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_woocommerce_active() ) {
			return;
		}

		// Swap WooCommerce's default wrappers for Travail's own container/section markup.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		add_action( 'woocommerce_before_main_content', array( __CLASS__, 'content_wrapper_start' ), 10 );
		add_action( 'woocommerce_after_main_content', array( __CLASS__, 'content_wrapper_end' ), 10 );

		// Product grid columns/count.
		add_filter( 'loop_shop_columns', array( __CLASS__, 'loop_columns' ) );
		add_filter( 'woocommerce_product_thumbnails_columns', array( __CLASS__, 'thumbnail_columns' ) );

		// Re-style the "Add to cart" / price markup classes to match Travail buttons without touching templates.
		add_filter( 'woocommerce_loop_add_to_cart_link', array( __CLASS__, 'style_add_to_cart_link' ), 10, 2 );

		// Sale/on-sale badge — reuse Travail's badge component class.
		add_filter( 'woocommerce_sale_flash', array( __CLASS__, 'style_sale_badge' ) );

		// Breadcrumbs — disable core WC breadcrumbs, Travail already renders its own.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

		// Related products count/columns to match the theme's card grid.
		add_filter( 'woocommerce_output_related_products_args', array( __CLASS__, 'related_products_args' ) );
	}

	/**
	 * Open Travail's container/section wrapper before WooCommerce content.
	 */
	public static function content_wrapper_start() {
		echo '<main id="main" class="travail-main travail-section woocommerce-page-wrapper" role="main"><div class="travail-container">';
	}

	/**
	 * Close it.
	 */
	public static function content_wrapper_end() {
		echo '</div></main>';
	}

	/**
	 * 3 products per row matches the theme's .travail-grid--3 rhythm.
	 *
	 * @return int
	 */
	public static function loop_columns() {
		return 3;
	}

	/**
	 * @return int
	 */
	public static function thumbnail_columns() {
		return 4;
	}

	/**
	 * @param string     $html    Default markup.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public static function style_add_to_cart_link( $html, $product ) {
		return str_replace( 'button ', 'button travail-btn travail-btn--primary travail-btn--sm ', $html );
	}

	/**
	 * @param string $html Default markup.
	 * @return string
	 */
	public static function style_sale_badge( $html ) {
		return '<span class="travail-badge travail-badge--sale">' . esc_html__( 'Sale', 'travail' ) . '</span>';
	}

	/**
	 * @param array $args Related products query args.
	 * @return array
	 */
	public static function related_products_args( $args ) {
		$args['posts_per_page'] = 3;
		$args['columns']        = 3;
		return $args;
	}
}

Travail_WooCommerce::init();
