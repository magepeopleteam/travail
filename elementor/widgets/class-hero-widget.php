<?php
/**
 * Elementor widget: Travail Hero.
 *
 * Same markup/CSS classes as template-parts/hero/hero.php so a page
 * built with this widget looks identical to the theme's built-in
 * fallback homepage hero — but every field is a real Elementor control
 * (text/media/url), so it's fully editable per the "homepage MUST be
 * completely editable through Elementor" requirement.
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
 * Class Travail_Elementor_Hero_Widget
 */
class Travail_Elementor_Hero_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'travail-hero';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'Travail Hero', 'travail' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-slider-full-screen';
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
		return array( 'hero', 'banner', 'travail', 'search' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Content', 'travail' ),
			)
		);

		$this->add_control(
			'eyebrow',
			array(
				'label'   => __( 'Eyebrow', 'travail' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Explore · Dream · Discover', 'travail' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'travail' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Discover your next', 'travail' ),
			)
		);

		$this->add_control(
			'title_emphasis',
			array(
				'label'   => __( 'Title (emphasized word)', 'travail' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'adventure', 'travail' ),
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'   => __( 'Subtitle', 'travail' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __( 'Curated trips. Extraordinary places. Unforgettable memories.', 'travail' ),
			)
		);

		$this->add_control(
			'background_image',
			array(
				'label'   => __( 'Background Image', 'travail' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => array(
					'url' => TRAVAIL_URI . '/assets/images/placeholder-hero.svg',
				),
			)
		);

		$this->add_control(
			'show_search',
			array(
				'label'        => __( 'Show Search Widget', 'travail' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_metrics',
			array(
				'label'   => __( 'Show Trust Metrics', 'travail' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'metrics_section',
			array(
				'label'     => __( 'Trust Metrics', 'travail' ),
				'condition' => array( 'show_metrics' => 'yes' ),
			)
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'value', array( 'label' => __( 'Value', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '12,000+' ) );
		$repeater->add_control( 'label', array( 'label' => __( 'Label', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Happy Travelers', 'travail' ) ) );

		$this->add_control(
			'metrics',
			array(
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'value' => '12,000+', 'label' => __( 'Happy Travelers', 'travail' ) ),
					array( 'value' => '4.9/5', 'label' => __( 'Overall Rating', 'travail' ) ),
					array( 'value' => '120+', 'label' => __( 'Countries', 'travail' ) ),
				),
				'title_field' => '{{{ value }}} — {{{ label }}}',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$title_html = esc_html( $settings['title'] );
		if ( $settings['title_emphasis'] ) {
			$title_html .= ' <em>' . esc_html( $settings['title_emphasis'] ) . '</em>';
		}

		$bg_url = ! empty( $settings['background_image']['url'] ) ? $settings['background_image']['url'] : TRAVAIL_URI . '/assets/images/placeholder-hero.svg';
		?>
		<section class="travail-hero travail-hero--image-height">
			<img class="travail-hero__media" src="<?php echo esc_url( $bg_url ); ?>" alt="" />
			<div class="travail-hero__overlay"></div>

			<div class="travail-hero__content travail-container">
				<div class="travail-hero__inner">
					<?php if ( $settings['eyebrow'] ) : ?>
						<p class="travail-eyebrow" style="color:rgba(255,255,255,.7);"><?php echo esc_html( $settings['eyebrow'] ); ?></p>
					<?php endif; ?>

					<h1 class="travail-hero__title travail-serif"><?php echo wp_kses( $title_html, array( 'em' => array() ) ); ?></h1>

					<?php if ( $settings['subtitle'] ) : ?>
						<p class="travail-hero__sub"><?php echo esc_html( $settings['subtitle'] ); ?></p>
					<?php endif; ?>

					<?php if ( 'yes' === $settings['show_search'] ) : ?>
						<?php get_template_part( 'template-parts/search/search-widget' ); ?>
					<?php endif; ?>

					<?php if ( 'yes' === $settings['show_metrics'] && ! empty( $settings['metrics'] ) ) : ?>
						<div class="travail-hero__metrics">
							<?php foreach ( $settings['metrics'] as $index => $metric ) : ?>
								<?php if ( $index > 0 ) : ?><div class="travail-metric-divider" aria-hidden="true"></div><?php endif; ?>
								<div class="travail-metric">
									<div>
										<div class="travail-metric__val"><?php echo esc_html( $metric['value'] ); ?></div>
										<div class="travail-metric__label"><?php echo esc_html( $metric['label'] ); ?></div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
