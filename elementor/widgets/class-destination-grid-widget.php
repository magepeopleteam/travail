<?php
/**
 * Elementor widget: Travail Destination Grid.
 *
 * Every heading, link, query option and card is a real Elementor control
 * — same idea as the Hero widget — so the Popular Destinations section
 * is fully editable in the editor instead of dumping a hardcoded
 * template-part. Markup still comes from the real template-parts
 * (destination-grid.php / travello/destinations.php) so the section
 * stays pixel-identical to each reference design.
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
 * Class Travail_Elementor_Destination_Grid_Widget
 */
class Travail_Elementor_Destination_Grid_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'travail-destination-grid';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'Travail Destinations', 'travail' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-google-maps';
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
		return array( 'destination', 'location', 'places', 'grid', 'travail' );
	}

	/**
	 * Location term id => name, for the SELECT2 picker.
	 *
	 * @return array<int, string>
	 */
	protected function get_destination_options() {
		$options = array();
		if ( ! taxonomy_exists( 'ttbm_tour_location' ) ) {
			return $options;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => 'ttbm_tour_location',
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return $options;
		}
		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}
		return $options;
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->register_content_controls();
		$this->register_query_controls();
		$this->register_custom_cards_controls();
		$this->register_style_controls();
	}

	/**
	 * Heading / "view all" copy.
	 */
	protected function register_content_controls() {
		$this->start_controls_section(
			'content_section',
			array( 'label' => __( 'Content', 'travail' ) )
		);

		$this->add_control(
			'style',
			array(
				'label'   => __( 'Design', 'travail' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'classic',
				'options' => array(
					'classic'  => __( 'Travail Classic (4-card grid)', 'travail' ),
					'travello' => __( 'Travello (bento grid)', 'travail' ),
				),
			)
		);

		$this->add_control(
			'show_header',
			array(
				'label'        => __( 'Show Section Header', 'travail' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'travail' ),
				'label_off'    => __( 'Hide', 'travail' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'title',
			array(
				'label'     => __( 'Title', 'travail' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'Popular', 'travail' ),
				'condition' => array( 'show_header' => 'yes' ),
			)
		);

		$this->add_control(
			'title_emphasis',
			array(
				'label'       => __( 'Title (emphasized word)', 'travail' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'destinations', 'travail' ),
				'condition'   => array(
					'show_header' => 'yes',
					'style'       => 'travello',
				),
				'description' => __( 'Rendered in the accent italic after the title — e.g. Popular destinations.', 'travail' ),
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'     => __( 'Subtitle', 'travail' ),
				'type'      => \Elementor\Controls_Manager::TEXTAREA,
				'default'   => __( 'Places travelers are loving right now.', 'travail' ),
				'condition' => array( 'show_header' => 'yes' ),
			)
		);

		$this->add_control(
			'view_all_text',
			array(
				'label'     => __( '"View all" Text', 'travail' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'View all destinations →', 'travail' ),
				'condition' => array( 'show_header' => 'yes' ),
			)
		);

		$this->add_control(
			'view_all_url',
			array(
				'label'       => __( '"View all" Link', 'travail' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => __( 'https://', 'travail' ),
				'condition'   => array( 'show_header' => 'yes' ),
			)
		);

		$this->add_control(
			'show_country',
			array(
				'label'        => __( 'Show Country', 'travail' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'travail' ),
				'label_off'    => __( 'Hide', 'travail' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_count',
			array(
				'label'        => __( 'Show Tour Count', 'travail' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'travail' ),
				'label_off'    => __( 'Hide', 'travail' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Which destinations to list.
	 */
	protected function register_query_controls() {
		$this->start_controls_section(
			'query_section',
			array( 'label' => __( 'Destinations', 'travail' ) )
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Source', 'travail' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'     => __( 'Tour locations (automatic)', 'travail' ),
					'selected' => __( 'Selected tour locations', 'travail' ),
					'custom'   => __( 'Custom cards', 'travail' ),
				),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'     => __( 'Number of Destinations', 'travail' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 4,
				'min'       => 1,
				'max'       => 12,
				'condition' => array( 'source!' => 'custom' ),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'     => __( 'Order By', 'travail' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'count',
				'options'   => array(
					'count' => __( 'Most tours', 'travail' ),
					'name'  => __( 'Name (A–Z)', 'travail' ),
				),
				'condition' => array( 'source' => 'auto' ),
			)
		);

		$this->add_control(
			'term_ids',
			array(
				'label'       => __( 'Locations', 'travail' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => $this->get_destination_options(),
				'multiple'    => true,
				'label_block' => true,
				'condition'   => array( 'source' => 'selected' ),
				'description' => __( 'Pick which tour locations to show, in this order. Leave empty to fall back to the automatic list.', 'travail' ),
			)
		);

		$this->add_control(
			'hide_empty',
			array(
				'label'        => __( 'Hide Locations With No Tours', 'travail' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Hide', 'travail' ),
				'label_off'    => __( 'Show', 'travail' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'source!' => 'custom' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Manual destination cards (image, name, country, link).
	 */
	protected function register_custom_cards_controls() {
		$this->start_controls_section(
			'custom_cards_section',
			array(
				'label'     => __( 'Custom Cards', 'travail' ),
				'condition' => array( 'source' => 'custom' ),
			)
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'image',
			array(
				'label'   => __( 'Image', 'travail' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => array(
					'url' => TRAVAIL_URI . '/assets/images/placeholder-tour.svg',
				),
			)
		);
		$repeater->add_control(
			'name',
			array(
				'label'   => __( 'Name', 'travail' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Bali', 'travail' ),
			)
		);
		$repeater->add_control(
			'country',
			array(
				'label'   => __( 'Country', 'travail' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Indonesia', 'travail' ),
			)
		);
		$repeater->add_control(
			'count',
			array(
				'label'   => __( 'Tour Count', 'travail' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 12,
				'min'     => 0,
			)
		);
		$repeater->add_control(
			'url',
			array(
				'label'         => __( 'Link', 'travail' ),
				'type'          => \Elementor\Controls_Manager::URL,
				'placeholder'   => __( 'https://', 'travail' ),
				'show_external' => true,
				'default'       => array(
					'url'         => '#',
					'is_external' => false,
					'nofollow'    => false,
				),
			)
		);

		$this->add_control(
			'custom_cards',
			array(
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array(
						'name'    => __( 'Bali', 'travail' ),
						'country' => __( 'Indonesia', 'travail' ),
						'count'   => 24,
					),
					array(
						'name'    => __( 'Santorini', 'travail' ),
						'country' => __( 'Greece', 'travail' ),
						'count'   => 18,
					),
					array(
						'name'    => __( 'Kyoto', 'travail' ),
						'country' => __( 'Japan', 'travail' ),
						'count'   => 15,
					),
					array(
						'name'    => __( 'Marrakech', 'travail' ),
						'country' => __( 'Morocco', 'travail' ),
						'count'   => 12,
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab — typography, colors, card chrome.
	 */
	protected function register_style_controls() {
		$this->start_controls_section(
			'style_header_section',
			array(
				'label' => __( 'Header', 'travail' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Title Color', 'travail' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .travail-travello-section-title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .travail-section-head h2'        => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'title_emphasis_color',
			array(
				'label'     => __( 'Emphasis Color', 'travail' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .travail-travello-hero__em' => 'color: {{VALUE}};',
				),
				'condition' => array( 'style' => 'travello' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .travail-travello-section-title, {{WRAPPER}} .travail-section-head h2',
			)
		);

		$this->add_control(
			'subtitle_color',
			array(
				'label'     => __( 'Subtitle Color', 'travail' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .travail-travello-section-sub' => 'color: {{VALUE}};',
					'{{WRAPPER}} .travail-section-head p'       => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'subtitle_typography',
				'selector' => '{{WRAPPER}} .travail-travello-section-sub, {{WRAPPER}} .travail-section-head p',
			)
		);

		$this->add_control(
			'view_all_color',
			array(
				'label'     => __( '"View all" Color', 'travail' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .travail-travello-link-more' => 'color: {{VALUE}};',
					'{{WRAPPER}} .travail-view-all'           => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'view_all_hover_color',
			array(
				'label'     => __( '"View all" Hover Color', 'travail' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .travail-travello-link-more:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .travail-view-all:hover'           => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_cards_section',
			array(
				'label' => __( 'Cards', 'travail' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'cards_gap',
			array(
				'label'      => __( 'Gap', 'travail' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 48,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .travail-travello-dest-grid' => 'gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .travail-dest-grid'          => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'card_radius',
			array(
				'label'      => __( 'Border Radius', 'travail' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 48,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .travail-travello-dest-card' => 'border-radius: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .travail-dest-card'          => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'card_overlay',
			array(
				'label'     => __( 'Overlay Color', 'travail' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .travail-travello-dest-card__overlay' => 'background: {{VALUE}};',
					'{{WRAPPER}} .travail-dest-card__gradient'         => 'background: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'card_name_color',
			array(
				'label'     => __( 'Name Color', 'travail' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .travail-travello-dest-card__info h3' => 'color: {{VALUE}};',
					'{{WRAPPER}} .travail-dest-card__info h4'          => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'card_name_typography',
				'selector' => '{{WRAPPER}} .travail-travello-dest-card__info h3, {{WRAPPER}} .travail-dest-card__info h4',
			)
		);

		$this->add_control(
			'card_meta_color',
			array(
				'label'     => __( 'Country / Count Color', 'travail' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .travail-travello-dest-card__info p' => 'color: {{VALUE}};',
					'{{WRAPPER}} .travail-dest-card__info p'          => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Map widget settings to the $args both template-parts understand.
	 *
	 * @param array $settings get_settings_for_display() output.
	 * @return array
	 */
	protected function get_template_args( $settings ) {
		$dest_page = get_page_by_path( 'destinations' );
		$view_url  = ! empty( $settings['view_all_url']['url'] ) ? $settings['view_all_url']['url'] : '';
		if ( ! $view_url && $dest_page ) {
			$view_url = get_permalink( $dest_page );
		}

		$args = array(
			'title'          => isset( $settings['title'] ) ? $settings['title'] : '',
			'title_emphasis' => isset( $settings['title_emphasis'] ) ? $settings['title_emphasis'] : '',
			'subtitle'       => isset( $settings['subtitle'] ) ? $settings['subtitle'] : '',
			'view_all_text'  => isset( $settings['view_all_text'] ) ? $settings['view_all_text'] : '',
			'view_all_url'   => $view_url,
			'show_header'    => 'yes' === $settings['show_header'],
			'show_count'     => 'yes' === $settings['show_count'],
			'show_country'   => 'yes' === $settings['show_country'],
			'limit'          => ! empty( $settings['limit'] ) ? absint( $settings['limit'] ) : 4,
			'orderby'        => ! empty( $settings['orderby'] ) ? $settings['orderby'] : 'count',
			'term_ids'       => array(),
			'hide_empty'     => 'yes' === $settings['hide_empty'],
			'cards'          => array(),
		);

		if ( 'selected' === $settings['source'] && ! empty( $settings['term_ids'] ) ) {
			$args['term_ids'] = array_map( 'absint', (array) $settings['term_ids'] );
		}

		if ( 'custom' === $settings['source'] && ! empty( $settings['custom_cards'] ) ) {
			foreach ( $settings['custom_cards'] as $card ) {
				$args['cards'][] = array(
					'name'    => isset( $card['name'] ) ? $card['name'] : '',
					'country' => isset( $card['country'] ) ? $card['country'] : '',
					'url'     => ! empty( $card['url']['url'] ) ? $card['url']['url'] : '#',
					'image'   => ! empty( $card['image']['url'] ) ? $card['image']['url'] : TRAVAIL_URI . '/assets/images/placeholder-tour.svg',
					'count'   => isset( $card['count'] ) ? absint( $card['count'] ) : 0,
				);
			}
		}

		return $args;
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$source   = isset( $settings['source'] ) ? $settings['source'] : 'auto';

		if ( 'custom' !== $source && ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() ) ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo '<div class="travail-empty-state">' . esc_html__( 'Install and activate Tour Booking Manager to list destinations here, or switch Source to Custom cards.', 'travail' ) . '</div>';
			}
			return;
		}

		$template_args = $this->get_template_args( $settings );
		$template      = 'travello' === $settings['style']
			? 'template-parts/home/travello/destinations'
			: 'template-parts/destination/destination-grid';

		ob_start();
		get_template_part( $template, null, $template_args );
		$html = ob_get_clean();

		$is_editor = class_exists( '\Elementor\Plugin' )
			&& isset( \Elementor\Plugin::$instance->editor )
			&& \Elementor\Plugin::$instance->editor->is_edit_mode();

		if ( '' === trim( $html ) && ( $is_editor || current_user_can( 'edit_theme_options' ) ) ) {
			echo '<div class="travail-empty-state">' . esc_html__( 'No destinations to show yet. Add tour locations, pick specific ones, or switch Source to Custom cards.', 'travail' ) . '</div>';
			return;
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- template-part already escaped.
	}
}
