<?php
/**
 * Elementor widget: Travail Blog Grid.
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
 * Class Travail_Elementor_Blog_Grid_Widget
 */
class Travail_Elementor_Blog_Grid_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'travail-blog-grid';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'Travail Blog Grid', 'travail' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-post-list';
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
				'default'     => '',
				'options'     => array(
					''         => __( 'Custom (use the fields below)', 'travail' ),
					'classic'  => __( 'Travail Classic ("Stories from the road" grid)', 'travail' ),
					'travello' => __( 'Travello (1 featured + 2 list posts)', 'travail' ),
				),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'     => __( 'Title', 'travail' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'Stories from the road', 'travail' ),
				'condition' => array( 'style' => '' ),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'     => __( 'Number of Posts', 'travail' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 3,
				'min'       => 1,
				'max'       => 12,
				'condition' => array( 'style' => '' ),
			)
		);

		$this->add_control(
			'category',
			array(
				'label'       => __( 'Category Slug (optional)', 'travail' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => __( 'Leave empty to show the latest posts from any category.', 'travail' ),
				'condition'   => array( 'style' => '' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( 'classic' === $settings['style'] ) {
			get_template_part( 'template-parts/blog/blog-grid' );
			return;
		}

		if ( 'travello' === $settings['style'] ) {
			get_template_part( 'template-parts/home/travello/blog' );
			return;
		}

		$query_args = array(
			'post_type'           => 'post',
			'posts_per_page'      => ! empty( $settings['limit'] ) ? absint( $settings['limit'] ) : 3,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);

		if ( ! empty( $settings['category'] ) ) {
			$query_args['category_name'] = sanitize_title( $settings['category'] );
		}

		$posts = new WP_Query( $query_args );

		if ( ! $posts->have_posts() ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo '<div class="travail-empty-state">' . esc_html__( 'No blog posts published yet.', 'travail' ) . '</div>';
			}
			return;
		}
		?>
		<?php if ( $settings['title'] ) : ?>
			<div class="travail-section-head">
				<h2 class="travail-serif"><?php echo esc_html( $settings['title'] ); ?></h2>
			</div>
		<?php endif; ?>

		<div class="travail-blog-grid">
			<?php
			while ( $posts->have_posts() ) :
				$posts->the_post();
				get_template_part( 'template-parts/content/content', 'post' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<?php
	}
}
