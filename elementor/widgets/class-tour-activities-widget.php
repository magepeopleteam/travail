<?php
/**
 * Elementor widget: Travail Tour Activities.
 *
 * Delegates entirely to the real template-part for whichever design is
 * selected (template-parts/tour/category-explorer.php for Classic,
 * template-parts/home/travello/categories.php for Travello) instead of
 * re-implementing their markup inline. The Classic path used to
 * duplicate category-explorer.php's term-fetch/render logic here, but
 * was missing its `<section class="travail-section--tight
 * travail-section--muted"><div class="travail-container">` wrapper
 * entirely — the pill rail rendered with no side padding and no muted
 * background band, visibly different from "Find your kind of adventure"
 * in the reference design. A direct call is both simpler and guaranteed
 * byte-identical.
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
 * Class Travail_Elementor_Tour_Activities_Widget
 */
class Travail_Elementor_Tour_Activities_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'travail-tour-activities';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'Travail Tour Activities', 'travail' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
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
		return array( 'activities', 'category', 'travel style', 'adventure' );
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
				'label'       => __( 'Design', 'travail' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'classic',
				'options'     => array(
					'classic'  => __( 'Travail Classic (image-thumbnail pills)', 'travail' ),
					'travello' => __( 'Travello (slim emoji pill nav)', 'travail' ),
				),
				'description' => __( 'Title copy for Classic comes from the Customizer (travail_get_option(\'activities_title\')), matching every other Classic section.', 'travail' ),
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
				echo '<div class="travail-empty-state">' . esc_html__( 'Install and activate Tour Booking Manager to list activities here.', 'travail' ) . '</div>';
			}
			return;
		}

		get_template_part( 'travello' === $settings['style'] ? 'template-parts/home/travello/categories' : 'template-parts/tour/category-explorer' );
	}
}
