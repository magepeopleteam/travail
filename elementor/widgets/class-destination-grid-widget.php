<?php
/**
 * Elementor widget: Travail Destination Grid.
 *
 * Delegates entirely to the real template-part for whichever design is
 * selected (template-parts/destination/destination-grid.php for Classic,
 * template-parts/home/travello/destinations.php for Travello) instead of
 * re-implementing their markup inline, so this section always matches
 * the reference design pixel-for-pixel. Title/subtitle copy for Classic
 * lives in the Customizer (travail_get_option('destinations_title'/
 * '_subtitle')) since destination-grid.php reads it from there, not from
 * this widget's settings.
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
	 * Controls.
	 */
	protected function register_controls() {
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
			'limit',
			array(
				'label'       => __( 'Number of Destinations', 'travail' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 4,
				'min'         => 2,
				'max'         => 12,
				'condition'   => array( 'style' => 'classic' ),
				'description' => __( 'Classic only — Travello\'s bento layout always shows exactly 4.', 'travail' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo '<div class="travail-empty-state">' . esc_html__( 'Install and activate Tour Booking Manager to list destinations here.', 'travail' ) . '</div>';
			}
			return;
		}

		if ( 'travello' === $settings['style'] ) {
			get_template_part( 'template-parts/home/travello/destinations' );
			return;
		}

		// Classic style below delegates entirely to the real
		// template-part too (rather than the inline markup this widget
		// used to duplicate) — its section-head markup differs just
		// enough from a hand-copy (missing wrapper div + "view all" arrow
		// icon) to throw off the flex layout, so a direct call is both
		// simpler and guaranteed byte-identical to the reference design.
		get_template_part( 'template-parts/destination/destination-grid', null, array( 'limit' => ! empty( $settings['limit'] ) ? absint( $settings['limit'] ) : 4 ) );
	}
}
