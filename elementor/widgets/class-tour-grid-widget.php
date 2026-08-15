<?php
/**
 * Elementor widget: Travail Tour Grid.
 *
 * Covers "Tour Grid", "Tour Carousel", "Featured Tours", "Popular
 * Tours" and "Tour Deals" from the spec's suggested widget list as one
 * flexible widget with a Source control — five near-identical widgets
 * that all render the same [ttbm-tour-list] shortcode with different
 * arguments would be unnecessary duplication (per "create custom
 * widgets only when necessary" / "avoid duplicated queries").
 *
 * For the three sources each reference design actually uses on its
 * homepage (latest / best_seller / on_sale), this delegates to the real
 * template-part for the selected Design style instead of the plugin's
 * generic [ttbm-tour-list] shortcode grid — that shortcode renders its
 * own default archive-style layout (including a filter sidebar), which
 * doesn't match either reference design's homepage rail/card at all.
 * The shortcode path is kept only for the two ad-hoc sources
 * (category/location) that neither homepage design uses, where there's
 * no reference layout to match.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return;
}

/**
 * Class Travail_Elementor_Tour_Grid_Widget
 */
class Travail_Elementor_Tour_Grid_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'travail-tour-grid';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'Travail Tour Grid', 'travail' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-posts-grid';
	}

	/**
	 * @return array
	 */
	public function get_categories() {
		return array( 'travail' );
	}

	/**
	 * @return array
	 */
	public function get_keywords() {
		return array( 'tour', 'grid', 'carousel', 'featured', 'popular', 'deals' );
	}

	/**
	 * Tour id => title for the featured-section picker.
	 *
	 * @return array<int|string, string>
	 */
	protected function get_tour_options() {
		$options = array( '0' => __( 'Automatic (best seller, then latest)', 'travail' ) );
		if ( ! post_type_exists( 'ttbm_tour' ) ) {
			return $options;
		}
		$tours = get_posts(
			array(
				'post_type'      => 'ttbm_tour',
				'posts_per_page' => 50,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		foreach ( $tours as $tour ) {
			$options[ $tour->ID ] = $tour->post_title;
		}
		return $options;
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'query_section',
			array( 'label' => __( 'Query', 'travail' ) )
		);

		$this->add_control(
			'style',
			array(
				'label'       => __( 'Design', 'travail' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'classic',
				'options'     => array(
					'classic'  => __( 'Travail Classic', 'travail' ),
					'travello' => __( 'Travello', 'travail' ),
				),
				'condition'   => array( 'source' => array( 'latest', 'best_seller', 'on_sale' ) ),
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Show', 'travail' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'latest',
				'options' => array(
					'latest'      => __( 'Latest Tours', 'travail' ),
					'best_seller' => __( 'Popular / Best-Selling Tours', 'travail' ),
					'on_sale'     => __( 'Special Deals (on sale)', 'travail' ),
					'category'    => __( 'Specific Category', 'travail' ),
					'location'    => __( 'Specific Destination', 'travail' ),
				),
			)
		);

		$this->add_control(
			'term',
			array(
				'label'       => __( 'Category / Destination Slug', 'travail' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'condition'   => array( 'source' => array( 'category', 'location' ) ),
				'description' => __( 'Enter the term slug (found under Tours → Categories / Locations).', 'travail' ),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'     => __( 'Number of Tours', 'travail' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 8,
				'min'       => 1,
				'max'       => 24,
				'condition' => array( 'source!' => 'best_seller' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'latest_content_section',
			array(
				'label'     => __( 'Content', 'travail' ),
				'condition' => array( 'source' => 'latest' ),
			)
		);

		$this->add_control( 'latest_title', array( 'label' => __( 'Title', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Popular tours &', 'travail' ) ) );
		$this->add_control(
			'latest_title_emphasis',
			array(
				'label'     => __( 'Title (emphasized word)', 'travail' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'experiences', 'travail' ),
				'condition' => array( 'style' => 'travello' ),
			)
		);
		$this->add_control(
			'latest_subtitle',
			array(
				'label'     => __( 'Subtitle', 'travail' ),
				'type'      => \Elementor\Controls_Manager::TEXTAREA,
				'default'   => __( 'Discover unforgettable experiences curated for every kind of traveler.', 'travail' ),
				'condition' => array( 'style' => 'travello' ),
			)
		);
		$this->add_control( 'latest_view_all_text', array( 'label' => __( '"View all" Text', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'All tours →', 'travail' ), 'condition' => array( 'style' => 'travello' ) ) );
		$this->add_control( 'latest_view_all_url', array( 'label' => __( '"View all" Link', 'travail' ), 'type' => \Elementor\Controls_Manager::URL, 'condition' => array( 'style' => 'travello' ) ) );

		$this->end_controls_section();

		$this->start_controls_section(
			'featured_content_section',
			array(
				'label'     => __( 'Featured Tour', 'travail' ),
				'condition' => array( 'source' => 'best_seller' ),
			)
		);

		$this->add_control(
			'featured_tour_id',
			array(
				'label'   => __( 'Tour', 'travail' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '0',
				'options' => $this->get_tour_options(),
			)
		);
		$this->add_control(
			'featured_title',
			array(
				'label'       => __( 'Title override', 'travail' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'Leave empty to use the selected tour\'s name (e.g. Discover Patagonia).', 'travail' ),
				'condition'   => array( 'style' => 'travello' ),
			)
		);
		$this->add_control( 'featured_badge', array( 'label' => __( 'Badge', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( "Editor's Pick", 'travail' ), 'condition' => array( 'style' => 'travello' ) ) );
		$this->add_control( 'featured_cta', array( 'label' => __( 'Button Text', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Explore journey →', 'travail' ), 'condition' => array( 'style' => 'travello' ) ) );
		$this->add_control(
			'featured_image',
			array(
				'label'     => __( 'Image override', 'travail' ),
				'type'      => \Elementor\Controls_Manager::MEDIA,
				'condition' => array( 'style' => 'travello' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'deals_content_section',
			array(
				'label'     => __( 'Content', 'travail' ),
				'condition' => array(
					'source' => 'on_sale',
					'style'  => 'travello',
				),
			)
		);

		$this->add_control( 'deals_title', array( 'label' => __( 'Title', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( "Don't miss these", 'travail' ) ) );
		$this->add_control( 'deals_title_emphasis', array( 'label' => __( 'Title (emphasized word)', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'adventures', 'travail' ) ) );
		$this->add_control( 'deals_subtitle', array( 'label' => __( 'Subtitle', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'Limited-time deals on top-rated experiences.', 'travail' ) ) );
		$this->add_control( 'deals_badge', array( 'label' => __( 'Badge', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Limited Offer', 'travail' ) ) );
		$this->add_control( 'deals_cta', array( 'label' => __( 'Button Text', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Book now →', 'travail' ) ) );

		$this->end_controls_section();

		$this->start_controls_section(
			'layout_section',
			array(
				'label'     => __( 'Layout', 'travail' ),
				'condition' => array( 'source' => array( 'category', 'location' ) ),
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'travail' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid' => __( 'Grid', 'travail' ),
					'rail' => __( 'Horizontal scroll (carousel-style)', 'travail' ),
				),
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'     => __( 'Columns', 'travail' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '4',
				'options'   => array( '2' => '2', '3' => '3', '4' => '4' ),
				'condition' => array( 'layout' => 'grid' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Section Title', 'travail' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$this->add_control(
			'view_all_url',
			array(
				'label' => __( '"View all" Link', 'travail' ),
				'type'  => \Elementor\Controls_Manager::URL,
			)
		);

		$this->end_controls_section();

		if ( class_exists( 'Travail_Elementor' ) ) {
			Travail_Elementor::add_header_style_controls(
				$this,
				array(
					'condition' => array(
						'style'  => 'travello',
						'source' => array( 'latest', 'on_sale' ),
					),
				)
			);
		}
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo '<div class="travail-empty-state">' . esc_html__( 'Install and activate Tour Booking Manager to display tours here.', 'travail' ) . '</div>';
			}
			return;
		}

		$style = 'travello' === $settings['style'] ? 'travello' : 'classic';
		$limit = ! empty( $settings['limit'] ) ? absint( $settings['limit'] ) : 8;

		switch ( $settings['source'] ) {
			case 'latest':
				if ( 'travello' === $style ) {
					get_template_part(
						'template-parts/home/travello/tours',
						null,
						array(
							'title'          => isset( $settings['latest_title'] ) ? $settings['latest_title'] : '',
							'title_emphasis' => isset( $settings['latest_title_emphasis'] ) ? $settings['latest_title_emphasis'] : '',
							'subtitle'       => isset( $settings['latest_subtitle'] ) ? $settings['latest_subtitle'] : '',
							'view_all_text'  => isset( $settings['latest_view_all_text'] ) ? $settings['latest_view_all_text'] : '',
							'view_all_url'   => ! empty( $settings['latest_view_all_url']['url'] ) ? $settings['latest_view_all_url']['url'] : '',
							'limit'          => $limit,
						)
					);
				} else {
					get_template_part(
						'template-parts/tour/tour-rail',
						null,
						array(
							'title' => ! empty( $settings['latest_title'] ) ? $settings['latest_title'] : ( $settings['title'] ? $settings['title'] : __( 'Popular experiences', 'travail' ) ),
							'limit' => $limit,
						)
					);
				}
				return;

			case 'best_seller':
				if ( 'travello' === $style ) {
					get_template_part(
						'template-parts/home/travello/featured-tour',
						null,
						array(
							'tour_id'    => ! empty( $settings['featured_tour_id'] ) ? absint( $settings['featured_tour_id'] ) : 0,
							'title'      => isset( $settings['featured_title'] ) ? $settings['featured_title'] : '',
							'badge_text' => isset( $settings['featured_badge'] ) ? $settings['featured_badge'] : '',
							'cta_text'   => isset( $settings['featured_cta'] ) ? $settings['featured_cta'] : '',
							'image'      => ! empty( $settings['featured_image']['url'] ) ? $settings['featured_image']['url'] : '',
						)
					);
				} else {
					get_template_part( 'template-parts/tour/featured-journey' );
				}
				return;

			case 'on_sale':
				if ( 'travello' === $style ) {
					get_template_part(
						'template-parts/home/travello/deals',
						null,
						array(
							'title'          => isset( $settings['deals_title'] ) ? $settings['deals_title'] : '',
							'title_emphasis' => isset( $settings['deals_title_emphasis'] ) ? $settings['deals_title_emphasis'] : '',
							'subtitle'       => isset( $settings['deals_subtitle'] ) ? $settings['deals_subtitle'] : '',
							'badge_text'     => isset( $settings['deals_badge'] ) ? $settings['deals_badge'] : '',
							'cta_text'       => isset( $settings['deals_cta'] ) ? $settings['deals_cta'] : '',
							'limit'          => $limit,
						)
					);
				} else {
					get_template_part( 'template-parts/tour/deals-grid' );
				}
				return;
		}

		if ( ! shortcode_exists( 'ttbm-tour-list' ) ) {
			return;
		}

		$columns = ! empty( $settings['columns'] ) ? absint( $settings['columns'] ) : 4;

		$atts = array(
			'style'      => 'grid',
			'column'     => $columns,
			'show'       => $limit,
			'pagination' => 'no',
		);

		if ( 'category' === $settings['source'] && ! empty( $settings['term'] ) ) {
			$atts['cat'] = sanitize_title( $settings['term'] );
		} elseif ( 'location' === $settings['source'] && ! empty( $settings['term'] ) ) {
			$atts['city'] = sanitize_title( $settings['term'] );
		}

		$shortcode_atts_str = '';
		foreach ( $atts as $key => $value ) {
			$shortcode_atts_str .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
		}

		$wrap_class = 'rail' === $settings['layout'] ? 'travail-rail' : 'travail-tbm-grid-wrap';
		?>
		<?php if ( $settings['title'] ) : ?>
			<div class="travail-section-head">
				<h2 class="travail-serif"><?php echo esc_html( $settings['title'] ); ?></h2>
				<?php if ( ! empty( $settings['view_all_url']['url'] ) ) : ?>
					<a href="<?php echo esc_url( $settings['view_all_url']['url'] ); ?>" class="travail-view-all travail-link-arrow"><?php esc_html_e( 'View all', 'travail' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $wrap_class ); ?>">
			<?php echo do_shortcode( '[ttbm-tour-list' . $shortcode_atts_str . ']' ); ?>
		</div>
		<?php
	}
}
