<?php
/**
 * Elementor widget: Travail Features.
 *
 * Both homepage designs use this one slot for three or two genuinely
 * different, fixed sections apiece (Classic: "Why Choose Us" / "How It
 * Works"; Travello: "Services" / "Why Travel With Us" / "How It Works")
 * — each with its own distinct layout (image + floating badge, icon
 * cards with photos, numbered steps) that the generic icon/numbered-list
 * rendering below can't reproduce. The Section control routes straight
 * to the real template-part for whichever one is picked; only when it's
 * left as "Custom" does this fall back to the original generic
 * icon-grid/numbered-list repeater, which stays useful for building a
 * features section on any other page.
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
 * Class Travail_Elementor_Features_Widget
 */
class Travail_Elementor_Features_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'travail-features';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'Travail Features', 'travail' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-icon-box';
	}

	/**
	 * @return array
	 */
	public function get_categories() {
		return array( 'travail' );
	}

	/**
	 * Maps the 'section' control's non-empty values to a real
	 * template-part. Single source of truth, used by both register_controls()
	 * (for the select options) and render() (for the lookup).
	 *
	 * @return array<string, array{label:string, template:string}>
	 */
	protected function get_section_map() {
		return array(
			'why_choose_us'         => array(
				'label'    => __( 'Classic: Why Choose Us', 'travail' ),
				'template' => 'template-parts/content/why-choose-us',
			),
			'how_it_works'          => array(
				'label'    => __( 'Classic: How It Works', 'travail' ),
				'template' => 'template-parts/content/how-it-works',
			),
			'travello_services'    => array(
				'label'    => __( 'Travello: Services (Tours/Hotels/Transport/Activities)', 'travail' ),
				'template' => 'template-parts/home/travello/services',
			),
			'travello_why_us'       => array(
				'label'    => __( 'Travello: Why Travel With Us', 'travail' ),
				'template' => 'template-parts/home/travello/why-us',
			),
			'travello_how_it_works' => array(
				'label'    => __( 'Travello: 3-Step Process', 'travail' ),
				'template' => 'template-parts/home/travello/how-it-works',
			),
		);
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array( 'label' => __( 'Content', 'travail' ) )
		);

		$section_options = array( '' => __( 'Custom (use the fields below)', 'travail' ) );
		foreach ( $this->get_section_map() as $key => $section ) {
			$section_options[ $key ] = $section['label'];
		}

		$this->add_control(
			'section',
			array(
				'label'       => __( 'Section', 'travail' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $section_options,
				'description' => __( 'Pick a reference-design section to render it exactly as designed; everything below is ignored when one is picked.', 'travail' ),
			)
		);

		$this->add_control( 'title', array( 'label' => __( 'Title', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Travel with confidence', 'travail' ), 'condition' => array( 'section' => '' ) ) );

		$this->add_control(
			'layout',
			array(
				'label'     => __( 'Layout', 'travail' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'grid',
				'options'   => array(
					'grid'    => __( 'Icon Grid', 'travail' ),
					'numbered' => __( 'Numbered List', 'travail' ),
				),
				'condition' => array( 'section' => '' ),
			)
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'icon', array( 'label' => __( 'Icon', 'travail' ), 'type' => \Elementor\Controls_Manager::ICONS, 'default' => array( 'value' => 'fas fa-check', 'library' => 'fa-solid' ) ) );
		$repeater->add_control( 'heading', array( 'label' => __( 'Heading', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Handpicked experiences', 'travail' ) ) );
		$repeater->add_control( 'text', array( 'label' => __( 'Text', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXTAREA ) );

		$this->add_control(
			'items',
			array(
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'heading' => __( 'Handpicked experiences', 'travail' ), 'text' => __( 'Every tour is carefully vetted by our team.', 'travail' ) ),
					array( 'heading' => __( 'Local experts', 'travail' ), 'text' => __( 'Connect with passionate local guides.', 'travail' ) ),
					array( 'heading' => __( 'Secure booking', 'travail' ), 'text' => __( 'Your payment is fully protected.', 'travail' ) ),
					array( 'heading' => __( 'Flexible plans', 'travail' ), 'text' => __( 'Modify or cancel up to 48 hours before.', 'travail' ) ),
				),
				'title_field' => '{{{ heading }}}',
				'condition'   => array( 'section' => '' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings    = $this->get_settings_for_display();
		$section_map = $this->get_section_map();

		if ( ! empty( $settings['section'] ) && isset( $section_map[ $settings['section'] ] ) ) {
			get_template_part( $section_map[ $settings['section'] ]['template'] );
			return;
		}

		if ( empty( $settings['items'] ) ) {
			return;
		}
		?>
		<?php if ( $settings['title'] ) : ?>
			<h2 class="travail-serif travail-loose-title"><?php echo esc_html( $settings['title'] ); ?></h2>
		<?php endif; ?>

		<?php if ( 'numbered' === $settings['layout'] ) : ?>
			<div class="travail-why-items">
				<?php foreach ( $settings['items'] as $index => $item ) : ?>
					<div class="travail-why-item">
						<div class="travail-why-num travail-serif"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></div>
						<div class="travail-why-item-text">
							<strong><?php echo esc_html( $item['heading'] ); ?></strong>
							<?php if ( ! empty( $item['text'] ) ) : ?><p><?php echo esc_html( $item['text'] ); ?></p><?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="travail-features-grid">
				<?php foreach ( $settings['items'] as $item ) : ?>
					<div class="travail-feature-item">
						<div class="travail-feature-icon">
							<?php \Elementor\Icons_Manager::render_icon( $item['icon'], array( 'aria-hidden' => 'true' ) ); ?>
						</div>
						<h3><?php echo esc_html( $item['heading'] ); ?></h3>
						<?php if ( ! empty( $item['text'] ) ) : ?><p><?php echo esc_html( $item['text'] ); ?></p><?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php
	}
}
