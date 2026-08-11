<?php
/**
 * Builds the Elementor element tree for Travail's two ready-made
 * homepage designs ("Travello" and the original "Travail Classic"
 * wanderly.html-reference design) entirely out of the 10 widgets already
 * registered under the "Travail" Elementor category (see
 * inc/elementor/class-travail-elementor.php) — no new widgets, no
 * hard-coded markup.
 *
 * Consumed by Travail_Demo_Importer::step_homepage(), which writes the
 * returned element tree into a real WordPress Page's `_elementor_data`
 * post meta so the page opens directly in Elementor's editor and every
 * section can be rearranged, restyled or swapped like any other
 * Elementor page — this file only decides the *starting* layout.
 *
 * Every widget used below has a 'style' (or, for the shared "Features"
 * slot, 'section') control that routes straight to the real
 * template-part for whichever reference design is selected — see each
 * elementor/widgets/class-*.php file's own doc-comment — so what a site
 * owner sees immediately after import already matches wanderly.html /
 * travello.html pixel-for-pixel, without needing any settings copied in
 * here. This file's only real job is choosing which widget goes in which
 * order, and supplying the handful of values no widget default can know
 * (real image URLs, real page/archive links).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Travail_Elementor_Page_Builder
 */
class Travail_Elementor_Page_Builder {

	/**
	 * The two design keys this class knows how to build. Deliberately
	 * reuses the same 'travello' / 'default' vocabulary as the legacy
	 * Customizer setting (travail_get_option('homepage_style')) instead
	 * of inventing new ones, so a generated page's `_travail_homepage_design`
	 * post meta value always lines up with that older setting's values.
	 *
	 * @return array<string, array{title:string, description:string}>
	 */
	public static function get_designs() {
		return array(
			'travello' => array(
				'title'       => __( 'Travello', 'travail' ),
				'description' => __( 'Bold, image-led design with an editorial hero, a service teaser grid and a 3-step booking flow.', 'travail' ),
			),
			'default'  => array(
				'title'       => __( 'Travail Classic', 'travail' ),
				'description' => __( "Travail's original reference design — search-first hero, a destination grid and curated deals.", 'travail' ),
			),
		);
	}

	/**
	 * Build the element tree for one design.
	 *
	 * @param string $design_key One of the keys from get_designs().
	 * @param array  $context    Optional live values to bake in as widget
	 *                           defaults instead of each widget's own generic
	 *                           placeholder — see the keys read below. Anything
	 *                           omitted just falls back to that widget control's
	 *                           own registered default.
	 * @return array Elementor element tree, ready for wp_json_encode().
	 */
	public static function build_elements( $design_key, $context = array() ) {
		$context = wp_parse_args(
			$context,
			array(
				'hero_image'          => '',
				'newsletter_image'    => '',
				'travello_hero_image' => '',
				'destinations_url'    => '',
				'tours_url'           => '',
			)
		);

		if ( 'travello' === $design_key ) {
			return self::build_travello_elements( $context );
		}

		return self::build_default_elements( $context );
	}

	/**
	 * "Travail Classic" — matches travail_default_homepage_sections()'s
	 * order in inc/template-hooks.php.
	 *
	 * @param array $context See build_elements().
	 * @return array
	 */
	protected static function build_default_elements( $context ) {
		$elements = array();

		$hero_settings = array( 'style' => 'classic' );
		if ( $context['hero_image'] ) {
			$hero_settings['background_image'] = array( 'url' => $context['hero_image'] );
		}
		$elements[] = self::section( 'travail-hero', $hero_settings );

		// No widget wraps template-parts/search/popular-searches.php (a
		// thin chip strip, not a full section) — the hero widget's own
		// embedded search bar above already covers the same "search from
		// the homepage" need, so this one strip is intentionally skipped
		// rather than built into a dedicated widget for one small chip row.

		$elements[] = self::section( 'travail-destination-grid', array( 'style' => 'classic' ) );
		$elements[] = self::section( 'travail-tour-activities', array( 'style' => 'classic' ) );

		// "Popular experiences" tour rail. Carries the #experiences
		// anchor the demo importer's own primary menu links to
		// (see Travail_Demo_Importer::step_menus()).
		$elements[] = self::section(
			'travail-tour-grid',
			array(
				'style'  => 'classic',
				'source' => 'latest',
				'limit'  => 8,
			),
			array( '_element_id' => 'experiences' )
		);

		$elements[] = self::section( 'travail-tour-grid', array( 'style' => 'classic', 'source' => 'best_seller' ) );
		$elements[] = self::section( 'travail-features', array( 'section' => 'why_choose_us' ) );
		$elements[] = self::section( 'travail-features', array( 'section' => 'how_it_works' ) );

		// Deals grid. Carries the #deals anchor from step_menus().
		$elements[] = self::section(
			'travail-tour-grid',
			array( 'style' => 'classic', 'source' => 'on_sale' ),
			array( '_element_id' => 'deals' )
		);

		$elements[] = self::section( 'travail-testimonials', array( 'style' => 'classic' ) );
		$elements[] = self::section( 'travail-blog-grid', array( 'style' => 'classic' ) );

		$newsletter_settings = array( 'style' => 'classic' );
		if ( $context['newsletter_image'] ) {
			$newsletter_settings['background_image'] = array( 'url' => $context['newsletter_image'] );
		}
		$elements[] = self::section( 'travail-newsletter', $newsletter_settings );

		return $elements;
	}

	/**
	 * "Travello" — matches travail_travello_homepage_sections()'s order
	 * in inc/homepage-travello.php.
	 *
	 * @param array $context See build_elements().
	 * @return array
	 */
	protected static function build_travello_elements( $context ) {
		$elements = array();

		$hero_settings = array(
			'style'              => 'travello',
			'eyebrow'            => __( 'Travel Beyond Ordinary', 'travail' ),
			'title'              => __( 'Explore the world.', 'travail' ),
			'title_emphasis'     => __( 'Create unforgettable memories.', 'travail' ),
			'subtitle'           => __( 'Discover handpicked tours, extraordinary destinations and experiences designed for curious travelers.', 'travail' ),
			'show_metrics'       => 'yes',
			'metrics'            => array(
				array( 'value' => __( '12K+', 'travail' ), 'label' => __( 'Tours', 'travail' ) ),
				array( 'value' => __( '180+', 'travail' ), 'label' => __( 'Destinations', 'travail' ) ),
				array( 'value' => __( '50K+', 'travail' ), 'label' => __( 'Travelers', 'travail' ) ),
			),
			'cta_primary_text'   => __( 'Explore tours', 'travail' ),
			'cta_secondary_text' => __( 'View destinations', 'travail' ),
		);
		if ( $context['travello_hero_image'] ) {
			$hero_settings['background_image'] = array( 'url' => $context['travello_hero_image'] );
		}
		if ( $context['tours_url'] ) {
			$hero_settings['cta_primary_url'] = array( 'url' => $context['tours_url'] );
		}
		if ( $context['destinations_url'] ) {
			$hero_settings['cta_secondary_url'] = array( 'url' => $context['destinations_url'] );
		}
		$elements[] = self::section( 'travail-hero', $hero_settings );

		$elements[] = self::section( 'travail-tour-search', array( 'style' => 'travello' ) );
		$elements[] = self::section( 'travail-tour-activities', array( 'style' => 'travello' ) );
		$elements[] = self::section( 'travail-destination-grid', array( 'style' => 'travello' ) );

		$elements[] = self::section(
			'travail-tour-grid',
			array(
				'style'  => 'travello',
				'source' => 'latest',
				'limit'  => 8,
			),
			array( '_element_id' => 'experiences' )
		);

		$elements[] = self::section( 'travail-tour-grid', array( 'style' => 'travello', 'source' => 'best_seller' ) );
		$elements[] = self::section( 'travail-features', array( 'section' => 'travello_services' ) );
		$elements[] = self::section( 'travail-features', array( 'section' => 'travello_why_us' ) );

		$elements[] = self::section(
			'travail-tour-grid',
			array( 'style' => 'travello', 'source' => 'on_sale' ),
			array( '_element_id' => 'deals' )
		);

		$elements[] = self::section( 'travail-features', array( 'section' => 'travello_how_it_works' ) );
		$elements[] = self::section( 'travail-testimonials', array( 'style' => 'travello' ) );
		$elements[] = self::section( 'travail-blog-grid', array( 'style' => 'travello' ) );
		$elements[] = self::section( 'travail-newsletter', array( 'style' => 'travello' ) );

		return $elements;
	}

	/**
	 * Wrap one widget in a full-width section + column — the standard
	 * Elementor document shape (elType section > column > widget).
	 *
	 * Explicitly forces Elementor's own "Content Width" to full_width and
	 * zeroes its section padding: every one of the 10 widgets already
	 * renders full-bleed markup styled by the theme's own CSS (see
	 * inc/elementor/class-travail-elementor.php's enqueue_editor_assets()),
	 * so leaving Elementor's *own* default boxed/padded section wrapper in
	 * place double-boxes and double-spaces every section instead of
	 * matching the reference design edge-to-edge.
	 *
	 * @param string $widget_type       Registered widget name (Widget_Base::get_name()).
	 * @param array  $settings          Widget control values; anything omitted uses that
	 *                                  control's own registered default.
	 * @param array  $section_settings  Optional section-level setting overrides (e.g.
	 *                                  '_element_id' for the #experiences / #deals
	 *                                  anchors the demo importer's menu items link to).
	 *                                  Merged over — never replaces — the full_width/
	 *                                  zero-padding defaults below.
	 * @return array
	 */
	protected static function section( $widget_type, $settings = array(), $section_settings = array() ) {
		$zero_padding = array(
			'unit'     => 'px',
			'top'      => '0',
			'right'    => '0',
			'bottom'   => '0',
			'left'     => '0',
			'isLinked' => true,
		);

		$section_settings = wp_parse_args(
			$section_settings,
			array(
				'layout'  => 'full_width',
				// 'Columns Gap' → 'No Gap': without this, Elementor's own
				// base CSS still adds inset padding to the column's inner
				// `.elementor-element-populated` wrapper (a DIFFERENT rule
				// from the section-level padding above, confirmed via
				// DevTools — `.elementor-column-gap-default >
				// .elementor-column > .elementor-element-populated`),
				// insetting every widget's content from the section's own
				// full-bleed edges.
				'gap'     => 'no',
				'padding' => $zero_padding,
			)
		);

		return array(
			'id'       => self::new_id(),
			'elType'   => 'section',
			'settings' => $section_settings,
			'elements' => array(
				array(
					'id'       => self::new_id(),
					'elType'   => 'column',
					// Belt-and-suspenders alongside the section's 'gap' =>
					// 'no' above: the column's own Advanced-tab padding is
					// zeroed directly too, in case a future Elementor
					// version changes which selector carries that inset.
					'settings' => array(
						'_column_size' => 100,
						'_padding'     => $zero_padding,
					),
					'elements' => array(
						array(
							'id'         => self::new_id(),
							'elType'     => 'widget',
							'settings'   => $settings,
							'elements'   => array(),
							'widgetType' => $widget_type,
						),
					),
					'isInner'  => false,
				),
			),
			'isInner'  => false,
		);
	}

	/**
	 * A unique-enough 7-character id in Elementor's own element-id shape
	 * (lowercase alphanumeric). Uniqueness only needs to hold within a
	 * single generated page, not globally, so wp_generate_password() is a
	 * perfectly safe source — it isn't used for anything security-sensitive.
	 *
	 * @return string
	 */
	protected static function new_id() {
		return strtolower( substr( wp_generate_password( 10, false, false ), 0, 7 ) );
	}
}
