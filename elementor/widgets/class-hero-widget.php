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
			'style',
			array(
				'label'   => __( 'Design', 'travail' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'classic',
				'options' => array(
					'classic'  => __( 'Travail Classic (search-first hero)', 'travail' ),
					'travello' => __( 'Travello (editorial hero with CTAs)', 'travail' ),
				),
				'description' => __( 'Matches the exact markup/CSS of whichever reference design you pick — the Travello style adds its own CTA buttons below instead of an embedded search bar.', 'travail' ),
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
				'condition'    => array( 'style' => 'classic' ),
			)
		);

		$this->add_control(
			'show_metrics',
			array(
				'label'   => __( 'Show Trust Metrics / Stats', 'travail' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'travello_cta_section',
			array(
				'label'     => __( 'Travello CTA Buttons', 'travail' ),
				'condition' => array( 'style' => 'travello' ),
			)
		);

		$this->add_control( 'cta_primary_text', array( 'label' => __( 'Primary Button Text', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Explore tours', 'travail' ) ) );
		$this->add_control( 'cta_primary_url', array( 'label' => __( 'Primary Button URL', 'travail' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );
		$this->add_control( 'cta_secondary_text', array( 'label' => __( 'Secondary Button Text', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'View destinations', 'travail' ) ) );
		$this->add_control( 'cta_secondary_url', array( 'label' => __( 'Secondary Button URL', 'travail' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );

		$this->end_controls_section();

		$this->start_controls_section(
			'metrics_section',
			array(
				'label'     => __( 'Trust Metrics / Stats', 'travail' ),
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

		if ( 'travello' === $settings['style'] ) {
			$this->render_travello( $settings );
			return;
		}

		$title_html = esc_html( $settings['title'] );
		if ( $settings['title_emphasis'] ) {
			$title_html .= ' <em>' . esc_html( $settings['title_emphasis'] ) . '</em>';
		}

		$bg_url = ! empty( $settings['background_image']['url'] ) ? $settings['background_image']['url'] : TRAVAIL_URI . '/assets/images/placeholder-hero.svg';
		?>
		<section class="travail-hero travail-hero--image-height">
			<img class="travail-hero__media" src="<?php echo esc_url( $bg_url ); ?>" alt="" fetchpriority="high" />
			<div class="travail-hero__overlay"></div>

			<div class="travail-hero__content travail-container">
				<div class="travail-hero__inner">
					<?php if ( $settings['eyebrow'] ) : ?>
						<p class="travail-eyebrow travail-hero__eyebrow" style="color:rgba(255,255,255,.7);"><?php echo esc_html( $settings['eyebrow'] ); ?></p>
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

	/**
	 * Travello-styled render — same markup/CSS classes as
	 * template-parts/home/travello/hero.php, but sourced entirely from
	 * this widget's own settings (rather than delegating to that
	 * template-part, which reads Customizer theme_mods) so every field
	 * — including the two CTA buttons and the stats row — stays editable
	 * in Elementor for this specific instance.
	 *
	 * @param array $settings get_settings_for_display() output.
	 */
	protected function render_travello( $settings ) {
		$headline_html  = esc_html( $settings['title'] );
		if ( $settings['title_emphasis'] ) {
			$headline_html .= '<br />' . '<span class="travail-travello-hero__em">' . esc_html( $settings['title_emphasis'] ) . '</span>';
		}
		$bg_url         = ! empty( $settings['background_image']['url'] ) ? $settings['background_image']['url'] : TRAVAIL_URI . '/assets/images/placeholder-wide.svg';
		$primary_url    = ! empty( $settings['cta_primary_url']['url'] ) ? $settings['cta_primary_url']['url'] : '#';
		$secondary_url  = ! empty( $settings['cta_secondary_url']['url'] ) ? $settings['cta_secondary_url']['url'] : '#';
		?>
		<section class="travail-travello-hero">
			<div class="travail-travello-hero__img">
				<img src="<?php echo esc_url( $bg_url ); ?>" alt="" loading="eager" fetchpriority="high" />
				<div class="travail-travello-hero__overlay"></div>

				<div class="travail-travello-hero__content">
					<div class="travail-travello-hero__content-inner">
						<div class="travail-travello-hero__text">
							<?php if ( $settings['eyebrow'] ) : ?>
								<span class="travail-travello-eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></span>
							<?php endif; ?>

							<h1 class="travail-travello-hero__headline"><?php echo wp_kses( $headline_html, array( 'br' => array(), 'span' => array( 'class' => array() ) ) ); ?></h1>

							<?php if ( $settings['subtitle'] ) : ?>
								<p class="travail-travello-hero__sub"><?php echo esc_html( $settings['subtitle'] ); ?></p>
							<?php endif; ?>

							<div class="travail-travello-hero__ctas">
								<?php if ( $settings['cta_primary_text'] ) : ?>
									<a href="<?php echo esc_url( $primary_url ); ?>" class="travail-travello-btn-primary">
										<?php echo esc_html( $settings['cta_primary_text'] ); ?> <span aria-hidden="true">→</span>
									</a>
								<?php endif; ?>
								<?php if ( $settings['cta_secondary_text'] ) : ?>
									<a href="<?php echo esc_url( $secondary_url ); ?>" class="travail-travello-btn-ghost">
										<?php echo esc_html( $settings['cta_secondary_text'] ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>

						<?php if ( 'yes' === $settings['show_metrics'] && ! empty( $settings['metrics'] ) ) : ?>
							<div class="travail-travello-hero__stats">
								<?php foreach ( $settings['metrics'] as $metric ) : ?>
									<div class="travail-travello-hero__stat">
										<p><?php echo esc_html( $metric['value'] ); ?></p>
										<p><?php echo esc_html( $metric['label'] ); ?></p>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}
