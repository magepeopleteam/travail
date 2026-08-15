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
				'description' => __( 'Pick a reference-design section. Its heading, cards and copy stay editable below without changing the layout.', 'travail' ),
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
					'grid'     => __( 'Icon Grid', 'travail' ),
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

		$placeholder = TRAVAIL_URI . '/assets/images/placeholder-tour.svg';

		$this->start_controls_section(
			'services_section',
			array(
				'label'     => __( 'Services', 'travail' ),
				'condition' => array( 'section' => 'travello_services' ),
			)
		);
		$this->add_control( 'services_title', array( 'label' => __( 'Title', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Everything you need for your', 'travail' ) ) );
		$this->add_control( 'services_title_emphasis', array( 'label' => __( 'Title (emphasized word)', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'journey', 'travail' ) ) );
		$this->add_control( 'services_subtitle', array( 'label' => __( 'Subtitle', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'One platform, every travel need.', 'travail' ) ) );

		$service_rep = new \Elementor\Repeater();
		$service_rep->add_control( 'icon', array( 'label' => __( 'Icon', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '🗺️' ) );
		$service_rep->add_control( 'title', array( 'label' => __( 'Title', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Tours', 'travail' ) ) );
		$service_rep->add_control( 'desc', array( 'label' => __( 'Description', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'Find unforgettable experiences.', 'travail' ) ) );
		$service_rep->add_control( 'image', array( 'label' => __( 'Image', 'travail' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => $placeholder ) ) );
		$service_rep->add_control( 'url', array( 'label' => __( 'Link', 'travail' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );

		$this->add_control(
			'services_items',
			array(
				'label'       => __( 'Cards', 'travail' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $service_rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'icon' => '🗺️', 'title' => __( 'Tours', 'travail' ), 'desc' => __( 'Find unforgettable experiences.', 'travail' ) ),
					array( 'icon' => '🏨', 'title' => __( 'Hotels', 'travail' ), 'desc' => __( 'Stay somewhere extraordinary.', 'travail' ) ),
					array( 'icon' => '✈️', 'title' => __( 'Transport', 'travail' ), 'desc' => __( "Get where you're going.", 'travail' ) ),
					array( 'icon' => '🎯', 'title' => __( 'Activities', 'travail' ), 'desc' => __( 'Make every moment count.', 'travail' ) ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'why_section',
			array(
				'label'     => __( 'Why Travel With Us', 'travail' ),
				'condition' => array( 'section' => 'travello_why_us' ),
			)
		);
		$this->add_control( 'why_eyebrow', array( 'label' => __( 'Eyebrow', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Why Travel With Us', 'travail' ) ) );
		$this->add_control( 'why_title', array( 'label' => __( 'Title', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'We make great', 'travail' ) ) );
		$this->add_control( 'why_title_emphasis', array( 'label' => __( 'Title (emphasized word)', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'trips happen.', 'travail' ) ) );
		$this->add_control(
			'why_image',
			array(
				'label' => __( 'Side Image', 'travail' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
			)
		);
		$this->add_control( 'why_stat_value', array( 'label' => __( 'Stat Value', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( '50,000+', 'travail' ) ) );
		$this->add_control( 'why_stat_label', array( 'label' => __( 'Stat Label', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Happy travelers worldwide', 'travail' ) ) );

		$reason_rep = new \Elementor\Repeater();
		$reason_rep->add_control( 'title', array( 'label' => __( 'Heading', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Handpicked experiences', 'travail' ) ) );
		$reason_rep->add_control( 'text', array( 'label' => __( 'Text', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXTAREA ) );

		$this->add_control(
			'why_reasons',
			array(
				'label'       => __( 'Reasons', 'travail' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $reason_rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'title' => __( 'Handpicked experiences', 'travail' ), 'text' => __( 'Every tour is vetted by our team of expert travelers who know what makes a journey truly memorable.', 'travail' ) ),
					array( 'title' => __( 'Trusted local experts', 'travail' ), 'text' => __( 'We partner with guides who know their destinations intimately — not just the highlights.', 'travail' ) ),
					array( 'title' => __( 'Secure payments', 'travail' ), 'text' => __( 'Bank-level encryption keeps every transaction safe. Book with complete confidence.', 'travail' ) ),
					array( 'title' => __( 'Flexible cancellation', 'travail' ), 'text' => __( 'Plans change — most tours offer free cancellation up to 24 hours before departure.', 'travail' ) ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'how_section',
			array(
				'label'     => __( 'How It Works', 'travail' ),
				'condition' => array( 'section' => 'travello_how_it_works' ),
			)
		);
		$this->add_control( 'how_eyebrow', array( 'label' => __( 'Eyebrow', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Simple Process', 'travail' ) ) );
		$this->add_control( 'how_title', array( 'label' => __( 'Title', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Your journey starts in', 'travail' ) ) );
		$this->add_control( 'how_title_emphasis', array( 'label' => __( 'Title (emphasized word)', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'three steps', 'travail' ) ) );
		$this->add_control( 'how_subtitle', array( 'label' => __( 'Subtitle', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'From inspiration to booking — we make it effortless.', 'travail' ) ) );

		$step_rep = new \Elementor\Repeater();
		$step_rep->add_control( 'title', array( 'label' => __( 'Heading', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Discover', 'travail' ) ) );
		$step_rep->add_control( 'text', array( 'label' => __( 'Text', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXTAREA ) );

		$this->add_control(
			'how_steps',
			array(
				'label'       => __( 'Steps', 'travail' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $step_rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'title' => __( 'Discover', 'travail' ), 'text' => __( 'Explore destinations and experiences tailored to your style of travel.', 'travail' ) ),
					array( 'title' => __( 'Choose', 'travail' ), 'text' => __( 'Compare tours, prices, dates and genuine traveler reviews side by side.', 'travail' ) ),
					array( 'title' => __( 'Book', 'travail' ), 'text' => __( "Reserve securely in just a few clicks — then dream about what's next.", 'travail' ) ),
				),
			)
		);
		$this->end_controls_section();

		if ( class_exists( 'Travail_Elementor' ) ) {
			Travail_Elementor::add_header_style_controls(
				$this,
				array(
					'condition'     => array( 'section' => array( 'travello_services', 'travello_why_us', 'travello_how_it_works' ) ),
					'sub_selector'  => '{{WRAPPER}} .travail-travello-section-sub, {{WRAPPER}} .travail-travello-how-sub',
					'link_selector' => '',
				)
			);
		}
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings    = $this->get_settings_for_display();
		$section_map = $this->get_section_map();
		$section     = isset( $settings['section'] ) ? $settings['section'] : '';

		if ( 'travello_services' === $section ) {
			$services = array();
			if ( ! empty( $settings['services_items'] ) ) {
				foreach ( $settings['services_items'] as $item ) {
					$services[] = array(
						'icon'  => isset( $item['icon'] ) ? $item['icon'] : '',
						'title' => isset( $item['title'] ) ? $item['title'] : '',
						'desc'  => isset( $item['desc'] ) ? $item['desc'] : '',
						'image' => ! empty( $item['image']['url'] ) ? $item['image']['url'] : TRAVAIL_URI . '/assets/images/placeholder-tour.svg',
						'url'   => ! empty( $item['url']['url'] ) ? $item['url']['url'] : '#',
					);
				}
			}
			get_template_part(
				'template-parts/home/travello/services',
				null,
				array(
					'title'          => isset( $settings['services_title'] ) ? $settings['services_title'] : '',
					'title_emphasis' => isset( $settings['services_title_emphasis'] ) ? $settings['services_title_emphasis'] : '',
					'subtitle'       => isset( $settings['services_subtitle'] ) ? $settings['services_subtitle'] : '',
					'services'       => $services,
				)
			);
			return;
		}

		if ( 'travello_why_us' === $section ) {
			$reasons = array();
			if ( ! empty( $settings['why_reasons'] ) ) {
				foreach ( $settings['why_reasons'] as $item ) {
					$reasons[] = array(
						'title' => isset( $item['title'] ) ? $item['title'] : '',
						'text'  => isset( $item['text'] ) ? $item['text'] : '',
					);
				}
			}
			get_template_part(
				'template-parts/home/travello/why-us',
				null,
				array(
					'eyebrow'        => isset( $settings['why_eyebrow'] ) ? $settings['why_eyebrow'] : '',
					'title'          => isset( $settings['why_title'] ) ? $settings['why_title'] : '',
					'title_emphasis' => isset( $settings['why_title_emphasis'] ) ? $settings['why_title_emphasis'] : '',
					'image'          => ! empty( $settings['why_image']['url'] ) ? $settings['why_image']['url'] : '',
					'stat_value'     => isset( $settings['why_stat_value'] ) ? $settings['why_stat_value'] : '',
					'stat_label'     => isset( $settings['why_stat_label'] ) ? $settings['why_stat_label'] : '',
					'reasons'        => $reasons,
				)
			);
			return;
		}

		if ( 'travello_how_it_works' === $section ) {
			$steps = array();
			if ( ! empty( $settings['how_steps'] ) ) {
				foreach ( $settings['how_steps'] as $item ) {
					$steps[] = array(
						'title' => isset( $item['title'] ) ? $item['title'] : '',
						'text'  => isset( $item['text'] ) ? $item['text'] : '',
					);
				}
			}
			get_template_part(
				'template-parts/home/travello/how-it-works',
				null,
				array(
					'eyebrow'        => isset( $settings['how_eyebrow'] ) ? $settings['how_eyebrow'] : '',
					'title'          => isset( $settings['how_title'] ) ? $settings['how_title'] : '',
					'title_emphasis' => isset( $settings['how_title_emphasis'] ) ? $settings['how_title_emphasis'] : '',
					'subtitle'       => isset( $settings['how_subtitle'] ) ? $settings['how_subtitle'] : '',
					'steps'          => $steps,
				)
			);
			return;
		}

		if ( ! empty( $section ) && isset( $section_map[ $section ] ) ) {
			get_template_part( $section_map[ $section ]['template'] );
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
