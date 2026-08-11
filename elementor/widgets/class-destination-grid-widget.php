<?php
/**
 * Elementor widget: Travail Destination Grid.
 *
 * Renders ttbm_tour_location taxonomy terms as cards (same data source
 * as template-parts/destination/destination-grid.php).
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
 * Class Travail_Elementor_Destination_Grid_Widget
 */
class Travail_Elementor_Destination_Grid_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'travail-destination-grid';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'Travail Destinations', 'travail' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-google-maps';
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
				'default' => __( 'Trending destinations', 'travail' ),
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'   => __( 'Subtitle', 'travail' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Popular places loved by travelers around the world.', 'travail' ),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => __( 'Number of Destinations', 'travail' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 4,
				'min'     => 2,
				'max'     => 12,
			)
		);

		$this->add_control(
			'view_all_url',
			array(
				'label' => __( '"View all" Link', 'travail' ),
				'type'  => \Elementor\Controls_Manager::URL,
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
				echo '<div class="travail-empty-state">' . esc_html__( 'Install and activate Tour Booking Manager to list destinations here.', 'travail' ) . '</div>';
			}
			return;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'ttbm_tour_location',
				'hide_empty' => true,
				'orderby'    => 'count',
				'order'      => 'DESC',
				'number'     => ! empty( $settings['limit'] ) ? absint( $settings['limit'] ) : 4,
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo '<div class="travail-empty-state">' . esc_html__( 'No destinations yet — assign a Location to a published tour first.', 'travail' ) . '</div>';
			}
			return;
		}
		?>
		<?php if ( $settings['title'] ) : ?>
			<div class="travail-section-head">
				<div>
					<h2 class="travail-serif"><?php echo esc_html( $settings['title'] ); ?></h2>
					<?php if ( $settings['subtitle'] ) : ?>
						<p><?php echo esc_html( $settings['subtitle'] ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $settings['view_all_url']['url'] ) ) : ?>
					<a href="<?php echo esc_url( $settings['view_all_url']['url'] ); ?>" class="travail-view-all travail-link-arrow"><?php esc_html_e( 'View all destinations', 'travail' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="travail-dest-grid">
			<?php foreach ( $terms as $index => $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="travail-dest-card<?php echo $index < 2 ? ' travail-dest-card--tall' : ''; ?>">
					<img src="<?php echo esc_url( travail_get_term_image_url( $term, $index < 2 ? 'travail-card-tall' : 'travail-card-wide' ) ); ?>" alt="<?php echo esc_attr( $term->name ); ?>" loading="lazy" />
					<div class="travail-dest-card__gradient"></div>
					<div class="travail-dest-card__info">
						<div>
							<h4><?php echo esc_html( $term->name ); ?></h4>
							<p>
								<?php $travail_country = travail_get_term_country( $term ); ?>
								<?php if ( $travail_country ) : ?>
									<?php echo esc_html( $travail_country ); ?> ·
								<?php endif; ?>
								<?php
								printf(
									/* translators: %d: number of tours in this destination. */
									esc_html( _n( '%d experience', '%d experiences', $term->count, 'travail' ) ),
									(int) $term->count
								);
								?>
							</p>
						</div>
						<span class="travail-dest-arrow" aria-hidden="true">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
						</span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
