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
			'style',
			array(
				'label'       => __( 'Design', 'travail' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'classic',
				'options'     => array(
					'classic'  => __( 'Travail Classic (3-field search bar)', 'travail' ),
					'travello' => __( 'Travello (tabbed search panel)', 'travail' ),
					'generic'  => __( 'Generic ([ttbm-top-search] shortcode)', 'travail' ),
				),
				'description' => __( '"Classic"/"Travello" render that reference design\'s exact search UI (its copy comes from the Customizer, matching every other Classic/Travello section); "Generic" embeds the plugin\'s own shortcode as-is with the title/subtitle below, useful outside the homepage.', 'travail' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'     => __( 'Title', 'travail' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'Where do you want to go?', 'travail' ),
				'condition' => array( 'style' => 'generic' ),
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'     => __( 'Subtitle', 'travail' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'Search destinations, tours or experiences', 'travail' ),
				'condition' => array( 'style' => 'generic' ),
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
				'condition'    => array( 'style' => 'generic' ),
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
				echo '<div class="travail-empty-state">' . esc_html__( 'Install and activate Tour Booking Manager to enable the tour search widget.', 'travail' ) . '</div>';
			}
			return;
		}

		if ( 'travello' === $settings['style'] ) {
			get_template_part( 'template-parts/home/travello/search' );
			return;
		}

		if ( 'classic' === $settings['style'] ) {
			// search-widget.php reads its title/subtitle from the
			// Customizer (travail_get_option('search_widget_title'/
			// '_subtitle')) rather than $args, and its defaults already
			// match this widget's own Title/Subtitle control defaults —
			// site-wide copy changes belong in the Customizer, consistent
			// with every other Classic template-part this theme delegates to.
			get_template_part( 'template-parts/search/search-widget' );
			return;
		}

		if ( ! shortcode_exists( 'ttbm-top-search' ) ) {
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
