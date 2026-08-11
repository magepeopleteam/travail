<?php
/**
 * Builds the Elementor element tree for Travail's two ready-made
 * homepage designs ("Travello" and the original "Travail Classic"
 * wanderly.html-reference design) entirely out of the 10 widgets already
 * registered under the "Travail" Elementor category (see
 * inc/elementor/class-travail-elementor.php) — no new widgets, no
 * hard-coded markup. Every setting used below is a real registered
 * control on the target widget; see elementor/widgets/*.php for the
 * authoritative list.
 *
 * Consumed by Travail_Demo_Importer::step_homepage(), which writes the
 * returned element tree into a real WordPress Page's `_elementor_data`
 * post meta so the page opens directly in Elementor's editor and every
 * section can be rearranged, restyled or swapped like any other
 * Elementor page — this file only decides the *starting* layout.
 *
 * A handful of sections in the original hook-based homepages don't have
 * a matching widget (e.g. the slim "popular searches" chip strip, or the
 * large split "Featured Journey" card's exact layout) — those are
 * approximated with the closest existing widget rather than left out or
 * turned into brand-new widgets the project didn't ask for; each
 * substitution is called out in the comment next to it below.
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
	 *                           defaults instead of the widgets' own generic
	 *                           placeholders — see the keys read below.
	 *                           Anything omitted just falls back to each
	 *                           widget's own registered control default.
	 * @return array Elementor element tree, ready for wp_json_encode().
	 */
	public static function build_elements( $design_key, $context = array() ) {
		$context = wp_parse_args(
			$context,
			array(
				'destinations_url'          => '',
				'hero_image'                => '',
				'newsletter_image'          => '',
				'travello_hero_image'       => '',
				'travello_newsletter_image' => '',
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

		// Hero — every field below already equals the widget's own
		// registered default (see class-hero-widget.php), which was
		// authored to match this exact design, so only the background
		// image (once a real photo has been sideloaded by the demo
		// importer) needs overriding here.
		$hero_settings = array();
		if ( $context['hero_image'] ) {
			$hero_settings['background_image'] = array( 'url' => $context['hero_image'] );
		}
		$elements[] = self::section( 'travail-hero', $hero_settings );

		// No widget wraps template-parts/search/popular-searches.php (a
		// thin chip strip, not a full section) — the hero widget's own
		// embedded search bar above already covers the same "search from
		// the homepage" need, so this one strip is intentionally skipped
		// rather than built into a dedicated widget for one small chip row.

		$destination_settings = array();
		if ( $context['destinations_url'] ) {
			$destination_settings['view_all_url'] = array( 'url' => $context['destinations_url'] );
		}
		$elements[] = self::section( 'travail-destination-grid', $destination_settings );

		$elements[] = self::section( 'travail-tour-activities', array() );

		// "Popular experiences" tour rail. Carries the #experiences
		// anchor the demo importer's own primary menu links to
		// (see Travail_Demo_Importer::step_menus()).
		$elements[] = self::section(
			'travail-tour-grid',
			array(
				'source' => 'latest',
				'title'  => __( 'Popular experiences', 'travail' ),
				'limit'  => 8,
			),
			array( '_element_id' => 'experiences' )
		);

		// "Featured Journey" — the closest available widget to the large
		// split editorial card in template-parts/tour/featured-journey.php
		// is this same grid widget limited to the one best-seller tour;
		// it renders as a single card rather than that exact split
		// layout, but stays fully editable without a new widget.
		$elements[] = self::section(
			'travail-tour-grid',
			array(
				'source' => 'best_seller',
				'limit'  => 1,
			)
		);

		$elements[] = self::section(
			'travail-features',
			array(
				'title'  => __( 'Travel with confidence', 'travail' ),
				'layout' => 'numbered',
				'items'  => array(
					array( 'heading' => __( 'Handpicked experiences', 'travail' ), 'text' => __( 'Every tour and destination is carefully vetted by our team of expert travelers.', 'travail' ) ),
					array( 'heading' => __( 'Local experts', 'travail' ), 'text' => __( 'Connect with passionate local guides who know their destinations intimately.', 'travail' ) ),
					array( 'heading' => __( 'Secure booking', 'travail' ), 'text' => __( 'Your payment is protected with industry-leading security and fraud prevention.', 'travail' ) ),
					array( 'heading' => __( 'Flexible plans', 'travail' ), 'text' => __( 'Modify or cancel your booking up to 48 hours before departure.', 'travail' ) ),
				),
			)
		);

		$elements[] = self::section(
			'travail-features',
			array(
				'title'  => __( 'Plan your trip in 3 simple steps', 'travail' ),
				'layout' => 'numbered',
				'items'  => array(
					array( 'heading' => __( 'Discover', 'travail' ), 'text' => __( "Find experiences you'll love from our curated collection of tours.", 'travail' ) ),
					array( 'heading' => __( 'Choose', 'travail' ), 'text' => __( 'Compare dates, prices and reviews to find the perfect adventure.', 'travail' ) ),
					array( 'heading' => __( 'Book', 'travail' ), 'text' => __( 'Reserve your adventure securely with instant confirmation.', 'travail' ) ),
				),
			)
		);

		// Deals grid. The widget itself renders template-parts/tour/deals-grid.php
		// (its own full heading + grid) for 'on_sale', so no title setting
		// is needed here. Carries the #deals anchor from step_menus().
		$elements[] = self::section(
			'travail-tour-grid',
			array( 'source' => 'on_sale' ),
			array( '_element_id' => 'deals' )
		);

		$elements[] = self::section( 'travail-testimonials', array() );
		$elements[] = self::section( 'travail-blog-grid', array() );

		$newsletter_settings = array();
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

		// Hero — travello/hero.php has no embedded search bar (Travello
		// puts search in its own section below) but does have its own
		// 3-stat row, so show_search is off and the generic metrics
		// repeater is repointed at Travello's real stat labels instead of
		// the hero widget's own "Happy Travelers / Rating / Countries" set.
		$hero_settings = array(
			'eyebrow'        => __( 'Travel Beyond Ordinary', 'travail' ),
			'title'          => __( 'Explore the world.', 'travail' ),
			'title_emphasis' => __( 'Create unforgettable memories.', 'travail' ),
			'subtitle'       => __( 'Discover handpicked tours, extraordinary destinations and experiences designed for curious travelers.', 'travail' ),
			'show_search'    => 'no',
			'show_metrics'   => 'yes',
			'metrics'        => array(
				array( 'value' => __( '12K+', 'travail' ), 'label' => __( 'Tours', 'travail' ) ),
				array( 'value' => __( '180+', 'travail' ), 'label' => __( 'Destinations', 'travail' ) ),
				array( 'value' => __( '50K+', 'travail' ), 'label' => __( 'Travelers', 'travail' ) ),
			),
		);
		if ( $context['travello_hero_image'] ) {
			$hero_settings['background_image'] = array( 'url' => $context['travello_hero_image'] );
		}
		$elements[] = self::section( 'travail-hero', $hero_settings );

		$elements[] = self::section(
			'travail-tour-search',
			array(
				'title'    => __( 'Where do you want to go?', 'travail' ),
				'subtitle' => __( 'Search destinations, tours or experiences', 'travail' ),
			)
		);

		// Categories pill nav. The closest available widget to
		// travello/categories.php's slim, title-less pill strip is this
		// same activities widget with its title suppressed.
		$elements[] = self::section( 'travail-tour-activities', array( 'title' => '' ) );

		$destination_settings = array();
		if ( $context['destinations_url'] ) {
			$destination_settings['view_all_url'] = array( 'url' => $context['destinations_url'] );
		}
		$elements[] = self::section( 'travail-destination-grid', $destination_settings );

		$elements[] = self::section(
			'travail-tour-grid',
			array(
				'source' => 'latest',
				'title'  => __( 'Popular experiences', 'travail' ),
				'limit'  => 8,
			),
			array( '_element_id' => 'experiences' )
		);

		// Editorial feature card — see the matching note in build_default_elements().
		$elements[] = self::section(
			'travail-tour-grid',
			array(
				'source' => 'best_seller',
				'limit'  => 1,
			)
		);

		$elements[] = self::section(
			'travail-features',
			array(
				'title'  => __( 'Everything you need for your journey', 'travail' ),
				'layout' => 'grid',
				'items'  => array(
					array( 'icon' => array( 'value' => 'fas fa-map', 'library' => 'fa-solid' ), 'heading' => __( 'Tours', 'travail' ), 'text' => __( 'Find unforgettable experiences.', 'travail' ) ),
					array( 'icon' => array( 'value' => 'fas fa-hotel', 'library' => 'fa-solid' ), 'heading' => __( 'Hotels', 'travail' ), 'text' => __( 'Stay somewhere extraordinary.', 'travail' ) ),
					array( 'icon' => array( 'value' => 'fas fa-plane', 'library' => 'fa-solid' ), 'heading' => __( 'Transport', 'travail' ), 'text' => __( "Get where you're going.", 'travail' ) ),
					array( 'icon' => array( 'value' => 'fas fa-compass', 'library' => 'fa-solid' ), 'heading' => __( 'Activities', 'travail' ), 'text' => __( 'Make every moment count.', 'travail' ) ),
				),
			)
		);

		$elements[] = self::section(
			'travail-features',
			array(
				'title'  => __( 'We make great trips happen', 'travail' ),
				'layout' => 'numbered',
				'items'  => array(
					array( 'heading' => __( 'Handpicked experiences', 'travail' ), 'text' => __( 'Every tour is vetted by our team of expert travelers who know what makes a journey truly memorable.', 'travail' ) ),
					array( 'heading' => __( 'Trusted local experts', 'travail' ), 'text' => __( 'We partner with guides who know their destinations intimately — not just the highlights.', 'travail' ) ),
					array( 'heading' => __( 'Secure payments', 'travail' ), 'text' => __( 'Bank-level encryption keeps every transaction safe. Book with complete confidence.', 'travail' ) ),
					array( 'heading' => __( 'Flexible cancellation', 'travail' ), 'text' => __( 'Plans change — most tours offer free cancellation up to 24 hours before departure.', 'travail' ) ),
				),
			)
		);

		// Deals grid — renders the same deals-grid.php as the Classic
		// design (the grid widget has no Travello-styled variant), still
		// carrying the #deals anchor from step_menus().
		$elements[] = self::section(
			'travail-tour-grid',
			array( 'source' => 'on_sale' ),
			array( '_element_id' => 'deals' )
		);

		$elements[] = self::section(
			'travail-features',
			array(
				'title'  => __( 'Your journey starts in three steps', 'travail' ),
				'layout' => 'numbered',
				'items'  => array(
					array( 'heading' => __( 'Discover', 'travail' ), 'text' => __( 'Explore destinations and experiences tailored to your style of travel.', 'travail' ) ),
					array( 'heading' => __( 'Choose', 'travail' ), 'text' => __( 'Compare tours, prices, dates and genuine traveler reviews side by side.', 'travail' ) ),
					array( 'heading' => __( 'Book', 'travail' ), 'text' => __( "Reserve securely in just a few clicks — then dream about what's next.", 'travail' ) ),
				),
			)
		);

		$elements[] = self::section( 'travail-testimonials', array() );
		$elements[] = self::section( 'travail-blog-grid', array() );

		$newsletter_settings = array();
		if ( $context['travello_newsletter_image'] ) {
			$newsletter_settings['background_image'] = array( 'url' => $context['travello_newsletter_image'] );
		}
		$elements[] = self::section( 'travail-newsletter', $newsletter_settings );

		return $elements;
	}

	/**
	 * Wrap one widget in a full-width section + column — the standard
	 * Elementor document shape (elType section > column > widget).
	 *
	 * @param string $widget_type       Registered widget name (Widget_Base::get_name()).
	 * @param array  $settings          Widget control values; anything omitted uses that
	 *                                  control's own registered default.
	 * @param array  $section_settings  Optional section-level settings (e.g. '_element_id'
	 *                                  for the #experiences / #deals anchors the demo
	 *                                  importer's menu items link to).
	 * @return array
	 */
	protected static function section( $widget_type, $settings = array(), $section_settings = array() ) {
		return array(
			'id'       => self::new_id(),
			'elType'   => 'section',
			'settings' => $section_settings,
			'elements' => array(
				array(
					'id'       => self::new_id(),
					'elType'   => 'column',
					'settings' => array( '_column_size' => 100 ),
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
