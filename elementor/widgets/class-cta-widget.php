<?php
/**
 * Elementor widget: Travail CTA.
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
 * Class Travail_Elementor_CTA_Widget
 */
class Travail_Elementor_CTA_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'travail-cta';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'Travail CTA', 'travail' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-call-to-action';
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

		$this->add_control( 'title', array( 'label' => __( 'Title', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Ready for your next adventure?', 'travail' ) ) );
		$this->add_control( 'description', array( 'label' => __( 'Description', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'Browse handpicked tours and book securely in minutes.', 'travail' ) ) );
		$this->add_control( 'button_text', array( 'label' => __( 'Button Text', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Explore Tours', 'travail' ) ) );
		$this->add_control( 'button_url', array( 'label' => __( 'Button URL', 'travail' ), 'type' => \Elementor\Controls_Manager::URL ) );

		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="travail-cta-banner">
			<div>
				<?php if ( $settings['title'] ) : ?><h2 class="travail-serif"><?php echo esc_html( $settings['title'] ); ?></h2><?php endif; ?>
				<?php if ( $settings['description'] ) : ?><p><?php echo esc_html( $settings['description'] ); ?></p><?php endif; ?>
			</div>
			<?php if ( $settings['button_text'] && ! empty( $settings['button_url']['url'] ) ) : ?>
				<a href="<?php echo esc_url( $settings['button_url']['url'] ); ?>" class="travail-btn travail-btn--accent" <?php echo ! empty( $settings['button_url']['is_external'] ) ? 'target="_blank"' : ''; ?> <?php echo ! empty( $settings['button_url']['nofollow'] ) ? 'rel="nofollow"' : ''; ?>>
					<?php echo esc_html( $settings['button_text'] ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}
}
