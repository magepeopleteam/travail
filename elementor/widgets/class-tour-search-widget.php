<?php
/**
 * Elementor widget: Travail Tour Search.
 *
 * A thin, styled wrapper around Tour Booking Manager's own
 * [ttbm-top-search] shortcode — see the note in
 * template-parts/search/search-widget.php for why the theme never
 * reimplements the search form's fields/nonce/destination itself.
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
 * Class Travail_Elementor_Tour_Search_Widget
 */
class Travail_Elementor_Tour_Search_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'travail-tour-search';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'Travail Tour Search', 'travail' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-site-search';
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
			'title',
			array(
				'label'   => __( 'Title', 'travail' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Where do you want to go?', 'travail' ),
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'   => __( 'Subtitle', 'travail' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Search destinations, tours or experiences', 'travail' ),
			)
		);

		$this->add_control(
			'card_style',
			array(
				'label'        => __( 'Card Background', 'travail' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'White card', 'travail' ),
				'label_off'    => __( 'Transparent', 'travail' ),
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() || ! shortcode_exists( 'ttbm-top-search' ) ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo '<div class="travail-empty-state">' . esc_html__( 'Install and activate Tour Booking Manager to enable the tour search widget.', 'travail' ) . '</div>';
			}
			return;
		}
		?>
		<div class="travail-search-widget travail-search-widget--tbm<?php echo 'yes' !== $settings['card_style'] ? ' travail-search-widget--flat' : ''; ?>">
			<?php if ( $settings['title'] ) : ?>
				<h3><?php echo esc_html( $settings['title'] ); ?></h3>
			<?php endif; ?>
			<?php if ( $settings['subtitle'] ) : ?>
				<p class="travail-search-widget__hint"><?php echo esc_html( $settings['subtitle'] ); ?></p>
			<?php endif; ?>
			<?php echo do_shortcode( '[ttbm-top-search]' ); ?>
		</div>
		<?php
	}
}
