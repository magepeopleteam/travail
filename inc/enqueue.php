<?php
/**
 * Scripts & styles — conditionally loaded, properly versioned/dependencied.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache-busting version string for a local theme asset.
 *
 * Using the same static TRAVAIL_VERSION for every stylesheet meant a
 * browser (or any object/page cache) kept serving an already-fetched
 * copy of e.g. components.css under an unchanged "?ver=1.0.0" URL after
 * every CSS edit, silently masking real fixes behind stale cached CSS.
 * Appending the file's own mtime forces a fresh URL — and therefore a
 * fresh download — on every save, with TRAVAIL_VERSION as a safe
 * fallback if the file can't be stat'd (e.g. a symlinked/read-only
 * deploy where filemtime() is unreliable).
 *
 * @param string $relative_path Path relative to the theme root, e.g. '/assets/css/base.css'.
 * @return string
 */
function travail_asset_version( $relative_path ) {
	$path = TRAVAIL_DIR . $relative_path;
	$mtime = file_exists( $path ) ? filemtime( $path ) : false;
	return $mtime ? TRAVAIL_VERSION . '.' . $mtime : TRAVAIL_VERSION;
}

/**
 * Whether the current request is for a tour-related view (single tour,
 * tour archive, tour taxonomy). Used to gate booking-widget assets so
 * they never load on unrelated pages.
 *
 * @return bool
 */
function travail_is_tour_view() {
	if ( is_singular( 'ttbm_tour' ) || is_post_type_archive( 'ttbm_tour' ) ) {
		return true;
	}

	$tour_taxonomies = array( 'ttbm_tour_cat', 'ttbm_tour_location', 'ttbm_tour_tag', 'ttbm_tour_activities' );
	foreach ( $tour_taxonomies as $taxonomy ) {
		if ( is_tax( $taxonomy ) ) {
			return true;
		}
	}

	if ( is_page_template( 'templates/pages/page-tours.php' ) ) {
		return true;
	}

	// Ordinary WP Pages carrying a TTBM shortcode — e.g. the plugin's own
	// auto-created "/find/" search-results page ([ttbm-search-result]) —
	// are none of the above (not the CPT archive, not a taxonomy, not our
	// own page template), so without this check tbm-restyle.css never
	// loaded there at all and every fix in it was silently absent.
	// TTBM_Theme_Align::post_has_ttbm_shortcode() is the plugin's own
	// detection (it drives its `ttbm-page-with-shortcode` body class),
	// reused here instead of re-deriving the same shortcode list.
	if ( class_exists( 'TTBM_Theme_Align' ) && TTBM_Theme_Align::post_has_ttbm_shortcode() ) {
		return true;
	}

	return false;
}

/**
 * Enqueue front-end styles.
 */
function travail_enqueue_styles() {
	// Google Fonts — self-hosted fallback used automatically if the request fails offline;
	// site owners who need zero third-party requests can dequeue this handle from a child theme.
	if ( travail_get_option( 'load_google_fonts', true ) ) {
		wp_enqueue_style(
			'travail-fonts',
			'https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
			array(),
			null
		);
	}

	wp_enqueue_style( 'travail-base', TRAVAIL_URI . '/assets/css/base.css', array(), travail_asset_version( '/assets/css/base.css' ) );
	wp_enqueue_style( 'travail-layout', TRAVAIL_URI . '/assets/css/layout.css', array( 'travail-base' ), travail_asset_version( '/assets/css/layout.css' ) );
	wp_enqueue_style( 'travail-components', TRAVAIL_URI . '/assets/css/components.css', array( 'travail-layout' ), travail_asset_version( '/assets/css/components.css' ) );
	wp_enqueue_style( 'travail-header-footer', TRAVAIL_URI . '/assets/css/header-footer.css', array( 'travail-components' ), travail_asset_version( '/assets/css/header-footer.css' ) );

	if ( travail_is_tour_view() ) {
		wp_enqueue_style( 'travail-tour', TRAVAIL_URI . '/assets/css/tour.css', array( 'travail-components' ), travail_asset_version( '/assets/css/tour.css' ) );
	}

	if ( is_page_template( 'templates/pages/page-destinations.php' ) || is_front_page() ) {
		wp_enqueue_style( 'travail-destination', TRAVAIL_URI . '/assets/css/destination.css', array( 'travail-components' ), travail_asset_version( '/assets/css/destination.css' ) );
	}

	if ( is_singular( 'post' ) || is_home() || is_archive() ) {
		wp_enqueue_style( 'travail-blog', TRAVAIL_URI . '/assets/css/blog.css', array( 'travail-components' ), travail_asset_version( '/assets/css/blog.css' ) );
	}

	wp_enqueue_style( 'travail-style', get_stylesheet_uri(), array( 'travail-header-footer' ), travail_asset_version( '/style.css' ) );

	if ( is_rtl() ) {
		wp_enqueue_style( 'travail-rtl', TRAVAIL_URI . '/rtl.css', array( 'travail-style' ), travail_asset_version( '/rtl.css' ) );
	}

	$custom_css = travail_get_option( 'custom_css', '' );
	if ( ! empty( $custom_css ) ) {
		wp_add_inline_style( 'travail-style', wp_strip_all_tags( $custom_css ) );
	}

	// Travello homepage — only enqueued on the front page when that
	// homepage design is selected (Customizer → Homepage), never on any
	// other page.
	if ( travail_is_travello_home() ) {
		wp_enqueue_style( 'travail-travello', TRAVAIL_URI . '/assets/css/travello.css', array( 'travail-style' ), travail_asset_version( '/assets/css/travello.css' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'travail_enqueue_styles' );

/**
 * Re-skin Tour Booking Manager's own CSS classes (tbm-restyle.css).
 *
 * Hooked at a deliberately late priority: Tour Booking Manager (+ Pro)
 * enqueue their own stylesheets on `wp_enqueue_scripts` too, and — since
 * WordPress prints <link> tags in registration order — a same-priority
 * (10) callback registered here would print BEFORE the plugin's own
 * styles, letting the plugin's later, equal-specificity rules win the
 * cascade and silently undo every color/radius override below. Caught
 * visually via a screenshot during QA (buttons stayed the plugin's
 * default purple despite a seemingly-correct override). Priority 20
 * guarantees this file is always the last stylesheet enqueued.
 *
 * Priority 20 alone turned out not to be late enough: Tour Booking
 * Manager registers its own frontend style enqueuing at priority 90
 * (`TTBM_Dependencies::frontend_script()`, confirmed in its source), so
 * anything before 90 still printed first (caught empirically — 20
 * wasn't enough). PHP_INT_MAX guarantees this always runs last no
 * matter what priority a future plugin update moves to.
 */
function travail_enqueue_tbm_restyle() {
	$needs_it = travail_is_tour_view() || is_front_page();
	if ( ! $needs_it || ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() ) {
		return;
	}
	wp_enqueue_style( 'travail-tbm-restyle', TRAVAIL_URI . '/assets/css/tbm-restyle.css', array( 'travail-components' ), travail_asset_version( '/assets/css/tbm-restyle.css' ) );
}
add_action( 'wp_enqueue_scripts', 'travail_enqueue_tbm_restyle', PHP_INT_MAX );

/**
 * Enqueue front-end scripts.
 */
function travail_enqueue_scripts() {
	wp_enqueue_script( 'travail-navigation', TRAVAIL_URI . '/assets/js/navigation.js', array(), travail_asset_version( '/assets/js/navigation.js' ), true );
	wp_enqueue_script( 'travail-main', TRAVAIL_URI . '/assets/js/main.js', array(), travail_asset_version( '/assets/js/main.js' ), true );

	if ( travail_is_tour_view() ) {
		wp_enqueue_script( 'travail-tour-booking', TRAVAIL_URI . '/assets/js/tour-booking.js', array( 'travail-main' ), travail_asset_version( '/assets/js/tour-booking.js' ), true );
	}

	if ( is_front_page() && ! travail_is_travello_home() ) {
		wp_enqueue_script( 'travail-hero-search', TRAVAIL_URI . '/assets/js/hero-search.js', array( 'travail-main' ), travail_asset_version( '/assets/js/hero-search.js' ), true );
	}

	if ( travail_is_travello_home() ) {
		wp_enqueue_script( 'travail-travello', TRAVAIL_URI . '/assets/js/travello.js', array( 'travail-main' ), travail_asset_version( '/assets/js/travello.js' ), true );
	}

	if ( comments_open() || get_comments_number() ) {
		wp_enqueue_script( 'comment-reply' );
	}

	$travail_has_wishlist = class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::supports_wishlist();

	wp_localize_script(
		'travail-main',
		'travailSettings',
		array(
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'travail_ajax' ),
			// Same nonce action Tour Booking Manager's own wishlist AJAX handler
			// checks (check_ajax_referer('ttbm_frontend_nonce','nonce') in
			// inc/TTBM_Wishlist.php) — shared by name, not theme-owned.
			'wishlistNonce'  => $travail_has_wishlist ? wp_create_nonce( 'ttbm_frontend_nonce' ) : '',
			'myAccountUrl'   => class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_woocommerce_active() ? wc_get_page_permalink( 'myaccount' ) : '',
			'isRTL'          => is_rtl(),
			'i18n'           => array(
				'guestSingular' => __( 'Guest', 'travail' ),
				'guestPlural'   => __( 'Guests', 'travail' ),
				'genericError'  => __( 'Something went wrong. Please try again.', 'travail' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'travail_enqueue_scripts' );

/**
 * Add a scroll-position class to <body> so the sticky header can be styled
 * without inline styles, and add device-friendly viewport meta.
 */
function travail_body_classes( $classes ) {
	if ( ! is_singular() ) {
		$classes[] = 'travail-archive-view';
	}

	if ( is_singular( 'ttbm_tour' ) ) {
		$classes[] = 'travail-single-tour';
	}

	if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_elementor_active() && ( is_page() && get_post_meta( get_the_ID(), '_elementor_edit_mode', true ) ) ) {
		$classes[] = 'travail-elementor-page';
	}

	if ( travail_is_travello_home() ) {
		$classes[] = 'travail-travello-home';
	}

	return $classes;
}
add_filter( 'body_class', 'travail_body_classes' );

/**
 * Preload the LCP hero image on the front page to improve Core Web Vitals.
 */
function travail_preload_hero_image() {
	if ( ! is_front_page() ) {
		return;
	}

	$hero_image = travail_is_travello_home()
		? travail_get_option( 'travello_hero_image', '' )
		: travail_get_option( 'hero_image', '' );

	if ( $hero_image ) {
		printf(
			'<link rel="preload" as="image" href="%s" fetchpriority="high" />' . "\n",
			esc_url( $hero_image )
		);
	}
}
add_action( 'wp_head', 'travail_preload_hero_image', 1 );
