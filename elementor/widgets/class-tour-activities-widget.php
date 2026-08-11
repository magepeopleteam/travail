<?php
/**
 * Elementor widget: Travail Tour Activities.
 *
 * Renders ttbm_tour_activities taxonomy terms as a horizontal
 * pill/thumbnail rail — the "Find your kind of adventure" travel-style
 * explorer from the wanderly.html reference design (same data source as
 * template-parts/tour/category-explorer.php).
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
			'title',
			array(
				'label'   => __( 'Title', 'travail' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Find your kind of adventure', 'travail' ),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => __( 'Number of Activities', 'travail' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 10,
				'min'     => 2,
				'max'     => 24,
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

		// Prefer the reference design's exact 8 terms, in its exact order,
		// when they exist — see the matching comment in
		// template-parts/tour/category-explorer.php for why.
		$terms = array();
		foreach ( array( 'Adventure', 'Beach', 'Culture', 'Hiking', 'Luxury', 'Wildlife', 'Family', 'Wellness' ) as $name ) {
			$term = get_term_by( 'name', $name, 'ttbm_tour_activities' );
			if ( $term && ! is_wp_error( $term ) ) {
				$terms[] = $term;
			}
		}

		if ( empty( $terms ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => 'ttbm_tour_activities',
					'hide_empty' => true,
					'orderby'    => 'count',
					'order'      => 'DESC',
					'number'     => ! empty( $settings['limit'] ) ? absint( $settings['limit'] ) : 8,
				)
			);
		}

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo '<div class="travail-empty-state">' . esc_html__( 'No activities yet — assign an Activity to a published tour first.', 'travail' ) . '</div>';
			}
			return;
		}
		?>
		<?php if ( $settings['title'] ) : ?>
			<h2 class="travail-serif travail-loose-title"><?php echo esc_html( $settings['title'] ); ?></h2>
		<?php endif; ?>

		<div class="travail-cat-rail travail-cat-rail--fit" data-travail-pill-group>
			<?php foreach ( $terms as $index => $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="travail-cat-card<?php echo 0 === $index ? ' is-active' : ''; ?>">
					<img src="<?php echo esc_url( travail_get_term_image_url( $term, 'travail-thumb' ) ); ?>" alt="<?php echo esc_attr( $term->name ); ?>" loading="lazy" />
					<div class="travail-cat-card__overlay"><span><?php echo esc_html( $term->name ); ?></span></div>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
