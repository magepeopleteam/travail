<?php
/**
 * Elementor widget: Travail Newsletter.
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
 * Class Travail_Elementor_Newsletter_Widget
 */
class Travail_Elementor_Newsletter_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'travail-newsletter';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'Travail Newsletter', 'travail' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-email-field';
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
					'classic'  => __( 'Travail Classic', 'travail' ),
					'travello' => __( 'Travello (AJAX signup, own styling)', 'travail' ),
				),
				'description' => __( 'Travello uses the theme AJAX signup form. Every heading and the background image stay editable below.', 'travail' ),
			)
		);

		$this->add_control( 'title', array( 'label' => __( 'Title', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Your next adventure starts here.', 'travail' ), 'condition' => array( 'style' => 'classic' ) ) );
		$this->add_control( 'text', array( 'label' => __( 'Text', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'Get travel inspiration, exclusive deals and new destinations delivered to your inbox.', 'travail' ), 'condition' => array( 'style' => 'classic' ) ) );
		$this->add_control(
			'background_image',
			array(
				'label'   => __( 'Background Image', 'travail' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => array( 'url' => TRAVAIL_URI . '/assets/images/placeholder-wide.svg' ),
			)
		);
		$this->add_control(
			'action_url',
			array(
				'label'       => __( 'Form Action URL', 'travail' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'description' => __( 'Point this at your email-marketing provider\'s subscribe endpoint.', 'travail' ),
				'condition'   => array( 'style' => 'classic' ),
			)
		);

		$this->add_control( 'travello_eyebrow', array( 'label' => __( 'Eyebrow', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Stay Inspired', 'travail' ), 'condition' => array( 'style' => 'travello' ) ) );
		$this->add_control( 'travello_title', array( 'label' => __( 'Title', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Your next adventure is closer', 'travail' ), 'condition' => array( 'style' => 'travello' ) ) );
		$this->add_control( 'travello_title_emphasis', array( 'label' => __( 'Title (emphasized word)', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'than you think.', 'travail' ), 'condition' => array( 'style' => 'travello' ) ) );
		$this->add_control( 'travello_subtitle', array( 'label' => __( 'Subtitle', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'Get destination inspiration, travel tips and exclusive offers.', 'travail' ), 'condition' => array( 'style' => 'travello' ) ) );
		$this->add_control( 'travello_placeholder', array( 'label' => __( 'Email Placeholder', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Your email address', 'travail' ), 'condition' => array( 'style' => 'travello' ) ) );
		$this->add_control( 'travello_button_text', array( 'label' => __( 'Button Text', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Subscribe', 'travail' ), 'condition' => array( 'style' => 'travello' ) ) );

		$this->end_controls_section();

		if ( class_exists( 'Travail_Elementor' ) ) {
			Travail_Elementor::add_header_style_controls(
				$this,
				array(
					'condition'      => array( 'style' => 'travello' ),
					'title_selector' => '{{WRAPPER}} .travail-travello-newsletter__title',
					'sub_selector'   => '{{WRAPPER}} .travail-travello-newsletter__sub',
					'link_selector'  => '',
				)
			);
		}
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( 'travello' === $settings['style'] ) {
			get_template_part(
				'template-parts/home/travello/newsletter',
				null,
				array(
					'eyebrow'        => isset( $settings['travello_eyebrow'] ) ? $settings['travello_eyebrow'] : '',
					'title'          => isset( $settings['travello_title'] ) ? $settings['travello_title'] : '',
					'title_emphasis' => isset( $settings['travello_title_emphasis'] ) ? $settings['travello_title_emphasis'] : '',
					'subtitle'       => isset( $settings['travello_subtitle'] ) ? $settings['travello_subtitle'] : '',
					'image'          => ( ! empty( $settings['background_image']['id'] ) && ! empty( $settings['background_image']['url'] ) ) ? $settings['background_image']['url'] : '',
					'placeholder'    => isset( $settings['travello_placeholder'] ) ? $settings['travello_placeholder'] : '',
					'button_text'    => isset( $settings['travello_button_text'] ) ? $settings['travello_button_text'] : '',
				)
			);
			return;
		}

		$bg_url = ! empty( $settings['background_image']['url'] ) ? $settings['background_image']['url'] : TRAVAIL_URI . '/assets/images/placeholder-wide.svg';
		$action   = ! empty( $settings['action_url']['url'] ) ? $settings['action_url']['url'] : apply_filters( 'travail_newsletter_action_url', '' );
		?>
		<section class="travail-newsletter">
			<div class="travail-newsletter__bg">
				<img src="<?php echo esc_url( $bg_url ); ?>" alt="" loading="lazy" />
				<div class="travail-newsletter__overlay"></div>
			</div>
			<div class="travail-newsletter__content travail-container">
				<?php if ( $settings['title'] ) : ?><h2 class="travail-serif"><?php echo esc_html( $settings['title'] ); ?></h2><?php endif; ?>
				<?php if ( $settings['text'] ) : ?><p><?php echo esc_html( $settings['text'] ); ?></p><?php endif; ?>

				<?php if ( $action ) : ?>
					<form class="travail-newsletter-form" method="post" action="<?php echo esc_url( $action ); ?>">
						<label for="travail-newsletter-email-<?php echo esc_attr( $this->get_id() ); ?>" class="screen-reader-text"><?php esc_html_e( 'Your email address', 'travail' ); ?></label>
						<input type="email" id="travail-newsletter-email-<?php echo esc_attr( $this->get_id() ); ?>" name="email" required placeholder="<?php esc_attr_e( 'Your email address', 'travail' ); ?>" />
						<?php wp_nonce_field( 'travail_newsletter_subscribe', 'travail_newsletter_nonce' ); ?>
						<button type="submit" class="travail-btn-coral travail-btn"><?php esc_html_e( 'Subscribe', 'travail' ); ?></button>
					</form>
				<?php elseif ( current_user_can( 'edit_theme_options' ) ) : ?>
					<p class="travail-newsletter-note"><?php esc_html_e( 'Set a Form Action URL to activate this form.', 'travail' ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
