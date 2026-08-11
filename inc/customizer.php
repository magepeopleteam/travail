<?php
/**
 * Customizer: panel, sections, settings & controls.
 *
 * Kept as plain functions using core WP_Customize_Manager APIs (no custom
 * control classes) so the theme has zero JS build step and stays fast in
 * the Customizer preview. Values are read back via travail_get_option().
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Travail Customizer panel/sections/settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function travail_customize_register( $wp_customize ) {

	$wp_customize->add_panel(
		'travail_options',
		array(
			'title'    => __( 'Travail Theme Options', 'travail' ),
			'priority' => 30,
		)
	);

	/* ---------------------------------------------------------------
	 * Homepage — which homepage design the front page renders.
	 * Kept as its own section (rather than a General checkbox) since
	 * it's the single most consequential setting in this panel and
	 * benefits from its own description text.
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'travail_homepage',
		array(
			'title'       => __( 'Homepage', 'travail' ),
			'panel'       => 'travail_options',
			'priority'    => 5,
			'description' => __( 'Choose which homepage design your site\'s front page shows. "Travello" (the default) always wins, even over a page assigned in Settings → Reading. Switch to "Travail" to fall back to that assigned page\'s own Elementor/block content, or to the built-in Travail demo sections when no page is assigned.', 'travail' ),
		)
	);

	travail_add_setting(
		$wp_customize,
		'homepage_style',
		array(
			'default'           => 'travello',
			'sanitize_callback' => 'sanitize_key',
			'control'           => array(
				'type'    => 'radio',
				'section' => 'travail_homepage',
				'label'   => __( 'Homepage design', 'travail' ),
				'choices' => array(
					'travello' => __( 'Travello (default homepage)', 'travail' ),
					'default'  => __( 'Travail (alternate demo homepage)', 'travail' ),
				),
			),
		)
	);

	/* ---------------------------------------------------------------
	 * Travello homepage content — only the handful of fields that
	 * can't reasonably come from real WordPress/tour data (hero copy,
	 * newsletter background). Everything else on that homepage (tours,
	 * destinations, activities, deals, blog posts) is live data; see
	 * inc/homepage-travello.php.
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'travail_travello',
		array( 'title' => __( 'Travello Homepage', 'travail' ), 'panel' => 'travail_options' )
	);

	travail_add_setting(
		$wp_customize,
		'travello_hero_eyebrow',
		array(
			'default'           => __( 'Travel Beyond Ordinary', 'travail' ),
			'sanitize_callback' => 'sanitize_text_field',
			'control'           => array( 'type' => 'text', 'section' => 'travail_travello', 'label' => __( 'Hero eyebrow text', 'travail' ) ),
		)
	);

	travail_add_setting(
		$wp_customize,
		'travello_hero_headline',
		array(
			'default'           => __( 'Explore the world.{break}{emphasis}Create unforgettable memories.{/emphasis}', 'travail' ),
			'sanitize_callback' => 'sanitize_text_field',
			'control'           => array(
				'type'        => 'text',
				'section'     => 'travail_travello',
				'label'       => __( 'Hero headline', 'travail' ),
				'description' => __( 'Use {break} for a line break and {emphasis}...{/emphasis} to italicize part of the headline.', 'travail' ),
			),
		)
	);

	travail_add_setting(
		$wp_customize,
		'travello_hero_sub',
		array(
			'default'           => __( 'Discover handpicked tours, extraordinary destinations and experiences designed for curious travelers.', 'travail' ),
			'sanitize_callback' => 'sanitize_text_field',
			'control'           => array( 'type' => 'textarea', 'section' => 'travail_travello', 'label' => __( 'Hero subtitle', 'travail' ) ),
		)
	);

	travail_add_setting(
		$wp_customize,
		'travello_hero_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'control_class'     => 'WP_Customize_Image_Control',
			'control'           => array( 'section' => 'travail_travello', 'label' => __( 'Hero background image', 'travail' ) ),
		)
	);

	travail_add_setting(
		$wp_customize,
		'travello_newsletter_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'control_class'     => 'WP_Customize_Image_Control',
			'control'           => array( 'section' => 'travail_travello', 'label' => __( 'Newsletter background image', 'travail' ) ),
		)
	);

	/* ---------------------------------------------------------------
	 * General
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'travail_general',
		array( 'title' => __( 'General', 'travail' ), 'panel' => 'travail_options' )
	);

	travail_add_setting(
		$wp_customize,
		'container_width',
		array(
			'default'           => 1280,
			'sanitize_callback' => 'absint',
			'control'           => array(
				'type'    => 'number',
				'section' => 'travail_general',
				'label'   => __( 'Container max-width (px)', 'travail' ),
			),
		)
	);

	travail_add_setting(
		$wp_customize,
		'border_radius',
		array(
			'default'           => 'default',
			'sanitize_callback' => 'sanitize_key',
			'control'           => array(
				'type'    => 'select',
				'section' => 'travail_general',
				'label'   => __( 'Corner roundness', 'travail' ),
				'choices' => array(
					'square'  => __( 'Square (sharp corners)', 'travail' ),
					'default' => __( 'Default (matches design)', 'travail' ),
					'round'   => __( 'Extra round', 'travail' ),
				),
			),
		)
	);

	travail_add_setting(
		$wp_customize,
		'load_google_fonts',
		array(
			'default'           => true,
			'sanitize_callback' => 'travail_sanitize_checkbox',
			'control'           => array(
				'type'    => 'checkbox',
				'section' => 'travail_general',
				'label'   => __( 'Load DM Serif Display / Plus Jakarta Sans from Google Fonts', 'travail' ),
				'description' => __( 'Disable to serve fonts from your own CDN/child theme for full GDPR/offline compliance.', 'travail' ),
			),
		)
	);

	/* ---------------------------------------------------------------
	 * Colors & Typography
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'travail_colors',
		array( 'title' => __( 'Colors & Typography', 'travail' ), 'panel' => 'travail_options' )
	);

	travail_add_setting(
		$wp_customize,
		'color_primary',
		array(
			'default'           => '#16352d',
			'sanitize_callback' => 'sanitize_hex_color',
			'control_class'     => 'WP_Customize_Color_Control',
			'control'           => array( 'section' => 'travail_colors', 'label' => __( 'Primary color', 'travail' ) ),
		)
	);

	travail_add_setting(
		$wp_customize,
		'color_accent',
		array(
			'default'           => '#ff6b4a',
			'sanitize_callback' => 'sanitize_hex_color',
			'control_class'     => 'WP_Customize_Color_Control',
			'control'           => array( 'section' => 'travail_colors', 'label' => __( 'Accent color', 'travail' ) ),
		)
	);

	travail_add_setting(
		$wp_customize,
		'color_bg',
		array(
			'default'           => '#f8f7f3',
			'sanitize_callback' => 'sanitize_hex_color',
			'control_class'     => 'WP_Customize_Color_Control',
			'control'           => array( 'section' => 'travail_colors', 'label' => __( 'Page background', 'travail' ) ),
		)
	);

	/* ---------------------------------------------------------------
	 * Header
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'travail_header',
		array( 'title' => __( 'Header', 'travail' ), 'panel' => 'travail_options' )
	);

	travail_add_setting(
		$wp_customize,
		'header_style',
		array(
			'default'           => 'transparent',
			'sanitize_callback' => 'sanitize_key',
			'control'           => array(
				'type'    => 'select',
				'section' => 'travail_header',
				'label'   => __( 'Header style', 'travail' ),
				'choices' => array(
					'transparent' => __( 'Transparent over hero, solid on scroll', 'travail' ),
					'solid'       => __( 'Always solid', 'travail' ),
				),
			),
		)
	);

	travail_add_setting(
		$wp_customize,
		'header_cta_text',
		array(
			'default'           => __( 'List Your Tour', 'travail' ),
			'sanitize_callback' => 'sanitize_text_field',
			'control'           => array( 'type' => 'text', 'section' => 'travail_header', 'label' => __( 'Header CTA button text', 'travail' ) ),
		)
	);

	travail_add_setting(
		$wp_customize,
		'header_cta_url',
		array(
			'default'           => '#',
			'sanitize_callback' => 'esc_url_raw',
			'control'           => array( 'type' => 'url', 'section' => 'travail_header', 'label' => __( 'Header CTA button URL', 'travail' ) ),
		)
	);

	travail_add_setting(
		$wp_customize,
		'announcement_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'wp_kses_post',
			'control'           => array( 'type' => 'text', 'section' => 'travail_header', 'label' => __( 'Announcement bar text (leave empty to hide)', 'travail' ) ),
		)
	);

	travail_add_setting(
		$wp_customize,
		'announcement_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'control'           => array( 'type' => 'url', 'section' => 'travail_header', 'label' => __( 'Announcement "Learn more" URL', 'travail' ) ),
		)
	);

	/* ---------------------------------------------------------------
	 * Footer
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'travail_footer',
		array( 'title' => __( 'Footer', 'travail' ), 'panel' => 'travail_options' )
	);

	travail_add_setting(
		$wp_customize,
		'footer_description',
		array(
			'default'           => __( "Curating the world's finest travel experiences. Every trip, a new story.", 'travail' ),
			'sanitize_callback' => 'sanitize_text_field',
			'control'           => array( 'type' => 'textarea', 'section' => 'travail_footer', 'label' => __( 'Footer brand description', 'travail' ) ),
		)
	);

	travail_add_setting(
		$wp_customize,
		'show_footer_payment_icons',
		array(
			'default'           => true,
			'sanitize_callback' => 'travail_sanitize_checkbox',
			'control'           => array( 'type' => 'checkbox', 'section' => 'travail_footer', 'label' => __( 'Show accepted-payments row (WooCommerce only)', 'travail' ) ),
		)
	);

	travail_add_setting(
		$wp_customize,
		'show_mobile_bottom_nav',
		array(
			'default'           => true,
			'sanitize_callback' => 'travail_sanitize_checkbox',
			'control'           => array( 'type' => 'checkbox', 'section' => 'travail_footer', 'label' => __( 'Show mobile bottom navigation bar', 'travail' ) ),
		)
	);

	/* ---------------------------------------------------------------
	 * Social links
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'travail_social',
		array( 'title' => __( 'Social', 'travail' ), 'panel' => 'travail_options' )
	);

	foreach ( array( 'facebook', 'instagram', 'youtube', 'twitter' ) as $travail_network ) {
		travail_add_setting(
			$wp_customize,
			'social_' . $travail_network,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'control'           => array(
					'type'    => 'url',
					'section' => 'travail_social',
					/* translators: %s: social network name. */
					'label'   => sprintf( __( '%s URL', 'travail' ), ucfirst( $travail_network ) ),
				),
			)
		);
	}

	/* ---------------------------------------------------------------
	 * Blog
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'travail_blog',
		array( 'title' => __( 'Blog', 'travail' ), 'panel' => 'travail_options' )
	);

	travail_add_setting(
		$wp_customize,
		'blog_layout',
		array(
			'default'           => 'grid',
			'sanitize_callback' => 'sanitize_key',
			'control'           => array(
				'type'    => 'select',
				'section' => 'travail_blog',
				'label'   => __( 'Blog archive layout', 'travail' ),
				'choices' => array(
					'grid' => __( 'Grid', 'travail' ),
					'list' => __( 'List with sidebar', 'travail' ),
				),
			),
		)
	);

	/* ---------------------------------------------------------------
	 * Tours & Booking
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'travail_tours',
		array( 'title' => __( 'Tours & Booking', 'travail' ), 'panel' => 'travail_options' )
	);

	travail_add_setting(
		$wp_customize,
		'tours_per_page',
		array(
			'default'           => 9,
			'sanitize_callback' => 'absint',
			'control'           => array( 'type' => 'number', 'section' => 'travail_tours', 'label' => __( 'Tours per archive page', 'travail' ) ),
		)
	);

	travail_add_setting(
		$wp_customize,
		'tour_card_style',
		array(
			'default'           => 'grid',
			'sanitize_callback' => 'sanitize_key',
			'control'           => array(
				'type'    => 'select',
				'section' => 'travail_tours',
				'label'   => __( 'Default tour archive view', 'travail' ),
				'choices' => array(
					'grid' => __( 'Grid', 'travail' ),
					'list' => __( 'List', 'travail' ),
				),
			),
		)
	);

	travail_add_setting(
		$wp_customize,
		'sticky_booking_card',
		array(
			'default'           => true,
			'sanitize_callback' => 'travail_sanitize_checkbox',
			'control'           => array( 'type' => 'checkbox', 'section' => 'travail_tours', 'label' => __( 'Sticky booking card on single tour (desktop)', 'travail' ) ),
		)
	);

	/* ---------------------------------------------------------------
	 * Breadcrumbs
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'travail_breadcrumbs',
		array( 'title' => __( 'Breadcrumbs', 'travail' ), 'panel' => 'travail_options' )
	);

	travail_add_setting(
		$wp_customize,
		'show_breadcrumbs',
		array(
			'default'           => true,
			'sanitize_callback' => 'travail_sanitize_checkbox',
			'control'           => array( 'type' => 'checkbox', 'section' => 'travail_breadcrumbs', 'label' => __( 'Show breadcrumb trail', 'travail' ) ),
		)
	);

	/* ---------------------------------------------------------------
	 * Performance
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'travail_performance',
		array( 'title' => __( 'Performance', 'travail' ), 'panel' => 'travail_options' )
	);

	travail_add_setting(
		$wp_customize,
		'lazy_load_images',
		array(
			'default'           => true,
			'sanitize_callback' => 'travail_sanitize_checkbox',
			'control'           => array( 'type' => 'checkbox', 'section' => 'travail_performance', 'label' => __( 'Lazy-load below-the-fold images', 'travail' ) ),
		)
	);

	/* ---------------------------------------------------------------
	 * Custom CSS (in addition to core's own Additional CSS panel)
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'travail_custom_css',
		array( 'title' => __( 'Travail Custom CSS', 'travail' ), 'panel' => 'travail_options', 'description' => __( 'Prefer the core "Additional CSS" panel below for most tweaks — this field exists for import/export portability.', 'travail' ) )
	);

	travail_add_setting(
		$wp_customize,
		'custom_css',
		array(
			'default'           => '',
			'sanitize_callback' => 'wp_strip_all_tags',
			'control'           => array( 'type' => 'textarea', 'section' => 'travail_custom_css', 'label' => __( 'Custom CSS', 'travail' ) ),
		)
	);
}
add_action( 'customize_register', 'travail_customize_register' );

/**
 * Small helper so every add_setting()+add_control() pair above is one
 * readable call instead of two, and so 'theme_mod' + transport defaults
 * don't need repeating.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 * @param string               $id           Setting id (without travail_ prefix).
 * @param array                $config       default, sanitize_callback, control (array), control_class (optional).
 */
function travail_add_setting( $wp_customize, $id, $config ) {
	$wp_customize->add_setting(
		'travail_' . $id,
		array(
			'default'           => isset( $config['default'] ) ? $config['default'] : '',
			'sanitize_callback' => isset( $config['sanitize_callback'] ) ? $config['sanitize_callback'] : 'sanitize_text_field',
			'transport'         => isset( $config['transport'] ) ? $config['transport'] : 'refresh',
		)
	);

	$control_args = array_merge(
		array( 'settings' => 'travail_' . $id ),
		$config['control']
	);

	if ( ! empty( $config['control_class'] ) && class_exists( $config['control_class'] ) ) {
		$control_class = $config['control_class'];
		$wp_customize->add_control( new $control_class( $wp_customize, 'travail_' . $id . '_control', $control_args ) );
	} else {
		$wp_customize->add_control( 'travail_' . $id . '_control', $control_args );
	}
}

/**
 * Checkbox sanitizer used by every boolean Customizer setting above.
 *
 * @param mixed $input Raw value.
 * @return bool
 */
function travail_sanitize_checkbox( $input ) {
	return (bool) $input;
}

/**
 * Output the color/typography/radius settings as CSS custom-property
 * overrides so the Customizer never needs its own stylesheet regenerator.
 */
function travail_customizer_css_vars() {
	$primary = travail_get_option( 'color_primary', '#16352d' );
	$accent  = travail_get_option( 'color_accent', '#ff6b4a' );
	$bg      = travail_get_option( 'color_bg', '#f8f7f3' );
	$width   = absint( travail_get_option( 'container_width', 1280 ) );
	$radius  = travail_get_option( 'border_radius', 'default' );

	$radius_map = array(
		'square' => array( '0px', '0px', '0px', '0px', '0px' ),
		'default' => array( '12px', '16px', '20px', '24px', '28px' ),
		'round'  => array( '18px', '22px', '26px', '30px', '34px' ),
	);
	$radii = isset( $radius_map[ $radius ] ) ? $radius_map[ $radius ] : $radius_map['default'];

	$css = ':root{';
	$css .= '--travail-color-primary:' . esc_html( $primary ) . ';';
	$css .= '--travail-forest:' . esc_html( $primary ) . ';';
	$css .= '--travail-color-accent:' . esc_html( $accent ) . ';';
	$css .= '--travail-coral:' . esc_html( $accent ) . ';';
	$css .= '--travail-color-bg:' . esc_html( $bg ) . ';';
	$css .= '--travail-parchment:' . esc_html( $bg ) . ';';
	$css .= '--travail-container:' . absint( $width ) . 'px;';
	$css .= '--travail-radius-sm:' . esc_html( $radii[0] ) . ';';
	$css .= '--travail-radius-md:' . esc_html( $radii[1] ) . ';';
	$css .= '--travail-radius-lg:' . esc_html( $radii[2] ) . ';';
	$css .= '--travail-radius-xl:' . esc_html( $radii[3] ) . ';';
	$css .= '--travail-radius-2xl:' . esc_html( $radii[4] ) . ';';
	$css .= '}';

	wp_add_inline_style( 'travail-style', $css );
}
add_action( 'wp_enqueue_scripts', 'travail_customizer_css_vars', 20 );

/**
 * Live-preview support (selective refresh partials could be added later;
 * for now we simply bind JS in the Customizer preview for the settings
 * that are safe to reflect instantly).
 */
function travail_customize_preview_js() {
	wp_enqueue_script( 'travail-customizer-preview', TRAVAIL_URI . '/assets/js/customizer-preview.js', array( 'customize-preview' ), TRAVAIL_VERSION, true );
}
add_action( 'customize_preview_init', 'travail_customize_preview_js' );
