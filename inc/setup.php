<?php
/**
 * Core theme setup: add_theme_support, nav menus, sidebars, image sizes.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function travail_setup() {
	// Translation ready.
	load_theme_textdomain( 'travail', TRAVAIL_DIR . '/languages' );

	// Let WordPress manage the document title.
	add_theme_support( 'title-tag' );

	// Featured images.
	add_theme_support( 'post-thumbnails' );

	// HTML5 markup for search form, comment form, gallery, caption, widgets.
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	// Custom logo.
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
			'header-text' => array( 'site-title', 'site-description' ),
		)
	);

	// Selective refresh for widgets in the Customizer.
	add_theme_support( 'customize-selective-refresh-widgets' );

	// Editor & block-related supports (works whether or not the theme uses full-site editing).
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor-style.css' );

	// Automatic feed links.
	add_theme_support( 'automatic-feed-links' );

	// Post formats used by the blog templates.
	add_theme_support( 'post-formats', array( 'gallery', 'video', 'quote' ) );

	// WooCommerce declared support — theme still works fine if WC is inactive.
	add_theme_support( 'woocommerce' );
	add_theme_support(
		'wc-product-gallery-zoom',
	);
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Content width for oEmbeds / media.
	global $content_width;
	if ( ! isset( $content_width ) ) {
		$content_width = 900; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	}

	// Nav menus.
	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'travail' ),
			'mobile'  => __( 'Mobile Menu (optional — falls back to Primary)', 'travail' ),
			'footer-1' => __( 'Footer Column 1', 'travail' ),
			'footer-2' => __( 'Footer Column 2', 'travail' ),
			'footer-3' => __( 'Footer Column 3', 'travail' ),
			'legal'   => __( 'Footer Legal Links', 'travail' ),
		)
	);

	// Image sizes used by tour/destination/blog cards.
	add_image_size( 'travail-card', 600, 450, true );
	add_image_size( 'travail-card-wide', 900, 600, true );
	add_image_size( 'travail-card-tall', 900, 900, true );
	add_image_size( 'travail-hero', 1920, 1080, true );
	add_image_size( 'travail-thumb', 160, 160, true );
}
add_action( 'after_setup_theme', 'travail_setup' );

/**
 * Register widget areas.
 */
function travail_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Blog Sidebar', 'travail' ),
			'id'            => 'sidebar-blog',
			'description'   => __( 'Displayed alongside blog posts and the blog archive.', 'travail' ),
			'before_widget' => '<section id="%1$s" class="widget travail-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title travail-serif">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Footer Column 1', 'travail' ),
			'id'            => 'footer-1',
			'description'   => __( 'First footer widget column (falls back to the Discover links if empty).', 'travail' ),
			'before_widget' => '<div id="%1$s" class="widget travail-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="footer-col-title">',
			'after_title'   => '</h4>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Footer Column 2', 'travail' ),
			'id'            => 'footer-2',
			'description'   => __( 'Second footer widget column.', 'travail' ),
			'before_widget' => '<div id="%1$s" class="widget travail-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="footer-col-title">',
			'after_title'   => '</h4>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Footer Column 3', 'travail' ),
			'id'            => 'footer-3',
			'description'   => __( 'Third footer widget column.', 'travail' ),
			'before_widget' => '<div id="%1$s" class="widget travail-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="footer-col-title">',
			'after_title'   => '</h4>',
		)
	);
}
add_action( 'widgets_init', 'travail_widgets_init' );

/**
 * Register block pattern category so future block patterns group nicely.
 */
function travail_register_pattern_category() {
	if ( function_exists( 'register_block_pattern_category' ) ) {
		register_block_pattern_category(
			'travail',
			array( 'label' => __( 'Travail', 'travail' ) )
		);
	}
}
add_action( 'init', 'travail_register_pattern_category' );

/**
 * Flush rewrite rules once on activation — cheap insurance so any
 * plugin-registered endpoints (e.g. Tour Booking Manager's wishlist/
 * bookings My Account tabs) and the tour CPT/taxonomy permalinks resolve
 * immediately instead of needing a manual Settings → Permalinks save.
 */
function travail_flush_rewrite_rules_on_activation() {
	if ( ! get_option( 'travail_rewrite_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'travail_rewrite_flushed', TRAVAIL_VERSION );
	}
}
add_action( 'after_switch_theme', 'travail_flush_rewrite_rules_on_activation' );
