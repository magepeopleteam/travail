<?php
/**
 * Elementor widget: Travail Testimonials.
 *
 * Editorial/curated quotes via an Elementor repeater — intentionally
 * decoupled from Tour Booking Manager Pro's per-tour ttbm_tour_review
 * entries (a different, tour-specific system already rendered
 * automatically on the single tour page via the `ttbm_review` hook).
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
 * Class Travail_Elementor_Testimonials_Widget
 */
class Travail_Elementor_Testimonials_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'travail-testimonials';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'Travail Testimonials', 'travail' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-testimonial';
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
			array( 'label' => __( 'Testimonials', 'travail' ) )
		);

		$this->add_control(
			'style',
			array(
				'label'       => __( 'Design', 'travail' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => '',
				'options'     => array(
					''         => __( 'Custom (use the repeater below)', 'travail' ),
					'classic'  => __( 'Travail Classic (dark slider)', 'travail' ),
					'travello' => __( 'Travello (3-card review grid)', 'travail' ),
				),
				'description' => __( 'Classic and Travello keep their reference layouts. Every heading and review below stays editable.', 'travail' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'     => __( 'Title', 'travail' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'Loved by travelers worldwide', 'travail' ),
				'condition' => array( 'style' => '' ),
			)
		);

		$this->add_control(
			'travello_title',
			array(
				'label'     => __( 'Title', 'travail' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'Loved by travelers', 'travail' ),
				'condition' => array( 'style' => 'travello' ),
			)
		);
		$this->add_control(
			'travello_title_emphasis',
			array(
				'label'     => __( 'Title (emphasized word)', 'travail' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'around the world', 'travail' ),
				'condition' => array( 'style' => 'travello' ),
			)
		);
		$this->add_control(
			'travello_subtitle',
			array(
				'label'     => __( 'Subtitle', 'travail' ),
				'type'      => \Elementor\Controls_Manager::TEXTAREA,
				'default'   => __( '50,000+ happy travelers and counting.', 'travail' ),
				'condition' => array( 'style' => 'travello' ),
			)
		);

		$travello_rep = new \Elementor\Repeater();
		$travello_rep->add_control( 'text', array( 'label' => __( 'Quote', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXTAREA ) );
		$travello_rep->add_control( 'name', array( 'label' => __( 'Name', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT ) );
		$travello_rep->add_control( 'loc', array( 'label' => __( 'Location', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT ) );

		$this->add_control(
			'travello_reviews',
			array(
				'label'       => __( 'Reviews', 'travail' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $travello_rep->get_controls(),
				'title_field' => '{{{ name }}}',
				'condition'   => array( 'style' => 'travello' ),
				'default'     => array(
					array(
						'text' => __( 'Travello found us a hidden gem in Bali we would never have discovered alone. The local guide was extraordinary and every detail was flawless.', 'travail' ),
						'name' => __( 'Sarah Mitchell', 'travail' ),
						'loc'  => __( 'London, UK', 'travail' ),
					),
					array(
						'text' => __( 'Booked our Swiss Alps trek through Travello — the itinerary was perfectly paced and our guide spoke three languages. Completely seamless.', 'travail' ),
						'name' => __( 'Kenji Tanaka', 'travail' ),
						'loc'  => __( 'Osaka, Japan', 'travail' ),
					),
					array(
						'text' => __( 'From the Santorini sunset cruise to the Oia dinner — every moment exceeded our expectations. Travello has earned a lifelong customer.', 'travail' ),
						'name' => __( 'Amélie Rousseau', 'travail' ),
						'loc'  => __( 'Paris, France', 'travail' ),
					),
				),
			)
		);

		$this->add_control(
			'show_avatars',
			array(
				'label'     => __( 'Show Avatar Strip', 'travail' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'style' => '' ),
			)
		);

		$this->add_control(
			'count_label',
			array(
				'label'     => __( 'Review Count Label', 'travail' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( '5,000+ verified reviews', 'travail' ),
				'condition' => array( 'style' => '' ),
			)
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'quote', array( 'label' => __( 'Quote', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'An unforgettable experience from start to finish.', 'travail' ) ) );
		$repeater->add_control( 'author', array( 'label' => __( 'Author Name', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Happy Traveler', 'travail' ) ) );
		$repeater->add_control( 'meta', array( 'label' => __( 'Author Location/Meta', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT ) );
		$repeater->add_control( 'tag', array( 'label' => __( 'Trip Tag', 'travail' ), 'type' => \Elementor\Controls_Manager::TEXT ) );
		$repeater->add_control( 'rating', array( 'label' => __( 'Rating (1-5)', 'travail' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 5, 'min' => 1, 'max' => 5 ) );

		$this->add_control(
			'testimonials',
			array(
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'quote'  => __( 'We didn’t just visit Bali. We experienced it.', 'travail' ),
						'author' => __( 'Sarah Williams', 'travail' ),
						'meta'   => __( 'New York, USA', 'travail' ),
						'tag'    => __( 'Bali · 7 day adventure', 'travail' ),
						'rating' => 5,
					),
				),
				'title_field' => '{{{ author }}}',
				'condition'   => array( 'style' => '' ),
			)
		);

		$this->end_controls_section();

		if ( class_exists( 'Travail_Elementor' ) ) {
			Travail_Elementor::add_header_style_controls(
				$this,
				array(
					'condition'     => array( 'style' => 'travello' ),
					'link_selector' => '',
				)
			);
		}
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( 'classic' === $settings['style'] ) {
			get_template_part( 'template-parts/testimonial/testimonials' );
			return;
		}

		if ( 'travello' === $settings['style'] ) {
			$reviews = array();
			if ( ! empty( $settings['travello_reviews'] ) ) {
				foreach ( $settings['travello_reviews'] as $item ) {
					$reviews[] = array(
						'text' => isset( $item['text'] ) ? $item['text'] : '',
						'name' => isset( $item['name'] ) ? $item['name'] : '',
						'loc'  => isset( $item['loc'] ) ? $item['loc'] : '',
					);
				}
			}
			get_template_part(
				'template-parts/home/travello/testimonials',
				null,
				array(
					'title'          => isset( $settings['travello_title'] ) ? $settings['travello_title'] : '',
					'title_emphasis' => isset( $settings['travello_title_emphasis'] ) ? $settings['travello_title_emphasis'] : '',
					'subtitle'       => isset( $settings['travello_subtitle'] ) ? $settings['travello_subtitle'] : '',
					'reviews'        => $reviews,
				)
			);
			return;
		}

		if ( empty( $settings['testimonials'] ) ) {
			return;
		}
		?>
		<div class="travail-testimonials" data-travail-testimonial-slider>
			<?php if ( $settings['title'] ) : ?>
				<h2 class="travail-serif" style="color:inherit;"><?php echo esc_html( $settings['title'] ); ?></h2>
			<?php endif; ?>

			<?php
			$travail_default_avatars = array_map(
				function ( $travail_i ) {
					return "https://i.pravatar.cc/88?img={$travail_i}";
				},
				array( 11, 12, 13, 14, 15, 16 )
			);
			$travail_avatars = apply_filters( 'travail_testimonials_avatars', $travail_default_avatars );
			?>
			<?php if ( 'yes' === $settings['show_avatars'] && ! empty( $travail_avatars ) ) : ?>
				<div class="travail-testi-avatars">
					<?php foreach ( $travail_avatars as $travail_avatar_url ) : ?>
						<img src="<?php echo esc_url( $travail_avatar_url ); ?>" alt="" loading="lazy" width="44" height="44" />
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php foreach ( $settings['testimonials'] as $index => $item ) : ?>
				<div class="travail-testi-slide<?php echo 0 === $index ? ' is-active' : ''; ?>">
					<blockquote class="travail-testi-quote travail-serif"><?php echo esc_html( $item['quote'] ); ?></blockquote>
					<?php if ( ! empty( $item['rating'] ) ) : ?>
						<div class="travail-testi-stars" aria-hidden="true"><?php echo esc_html( str_repeat( '★', (int) $item['rating'] ) ); ?></div>
					<?php endif; ?>
					<p class="travail-testi-author"><strong><?php echo esc_html( $item['author'] ); ?></strong> <?php echo ! empty( $item['meta'] ) ? '· ' . esc_html( $item['meta'] ) : ''; ?></p>
					<?php if ( ! empty( $item['tag'] ) ) : ?>
						<div class="travail-testi-tags"><span><?php echo esc_html( $item['tag'] ); ?></span></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

			<?php if ( count( $settings['testimonials'] ) > 1 ) : ?>
				<div class="travail-testi-dots">
					<?php foreach ( $settings['testimonials'] as $index => $item ) : ?>
						<?php
						/* translators: %d: testimonial slide number. */
						$travail_dot_label = sprintf( __( 'Testimonial %d', 'travail' ), $index + 1 );
						?>
						<button type="button" class="<?php echo 0 === $index ? 'is-active' : ''; ?>" aria-label="<?php echo esc_attr( $travail_dot_label ); ?>"></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $settings['count_label'] ) : ?>
				<p class="travail-testi-count"><?php echo esc_html( $settings['count_label'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}
}
