<?php
/**
 * Elementor widget: Travail Features.
 *
 * Generic icon+title+text repeater — covers both the homepage
 * "Why Choose Us" style list and a simple 4-column feature grid.
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
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array( 'label' => __( 'Content', 'travail' ) )
		);

		$this->add_control( 'title', array( 'label' => __( 'Title', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Travel with confidence', 'travail' ) ) );

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'travail' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid'    => __( 'Icon Grid', 'travail' ),
					'numbered' => __( 'Numbered List', 'travail' ),
				),
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
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

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
