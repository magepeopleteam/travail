<?php
/**
 * Travello homepage — "Everything you need for your journey" service
 * teaser grid (Tours / Hotels / Transport / Activities).
 *
 * Tour Booking Manager only covers Tours + Activities in this plugin
 * stack — there's no hotel or transport booking module to link to, so
 * this intentionally doesn't invent one. Tours links to the real tour
 * archive; the others default to '#' and are meant to be pointed at
 * whatever hotel/transport system (if any) a site owner adds later via
 * the travail_travello_services filter, exactly like
 * travail_how_it_works_steps already lets the default homepage's steps
 * be edited without a template override.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_tours_link = post_type_exists( 'ttbm_tour' ) ? get_post_type_archive_link( 'ttbm_tour' ) : '#';
$travail_activities_terms = taxonomy_exists( 'ttbm_tour_activities' ) ? get_terms( array( 'taxonomy' => 'ttbm_tour_activities', 'hide_empty' => true, 'number' => 1 ) ) : array();
$travail_activities_link  = ( ! is_wp_error( $travail_activities_terms ) && ! empty( $travail_activities_terms ) ) ? get_term_link( $travail_activities_terms[0] ) : '#';

$travail_placeholder = TRAVAIL_URI . '/assets/images/placeholder-tour.svg';

$travail_services = apply_filters(
	'travail_travello_services',
	array(
		'tours'      => array(
			'icon'  => '🗺️',
			'title' => __( 'Tours', 'travail' ),
			'desc'  => __( 'Find unforgettable experiences.', 'travail' ),
			'image' => travail_get_option( 'travello_service_tours_image', $travail_placeholder ),
			'url'   => $travail_tours_link ? $travail_tours_link : '#',
		),
		'hotels'     => array(
			'icon'  => '🏨',
			'title' => __( 'Hotels', 'travail' ),
			'desc'  => __( 'Stay somewhere extraordinary.', 'travail' ),
			'image' => travail_get_option( 'travello_service_hotels_image', $travail_placeholder ),
			'url'   => '#',
		),
		'transport'  => array(
			'icon'  => '✈️',
			'title' => __( 'Transport', 'travail' ),
			'desc'  => __( "Get where you're going.", 'travail' ),
			'image' => travail_get_option( 'travello_service_transport_image', $travail_placeholder ),
			'url'   => '#',
		),
		'activities' => array(
			'icon'  => '🎯',
			'title' => __( 'Activities', 'travail' ),
			'desc'  => __( 'Make every moment count.', 'travail' ),
			'image' => travail_get_option( 'travello_service_activities_image', $travail_placeholder ),
			'url'   => $travail_activities_link ? $travail_activities_link : '#',
		),
	)
);

if ( empty( $travail_services ) ) {
	return;
}

$travail_args = travail_section_args(
	isset( $args ) ? $args : array(),
	array(
		'title'          => __( 'Everything you need for your', 'travail' ),
		'title_emphasis' => __( 'journey', 'travail' ),
		'subtitle'       => __( 'One platform, every travel need.', 'travail' ),
		'services'       => array(),
	)
);

if ( ! empty( $travail_args['services'] ) ) {
	$travail_services = $travail_args['services'];
}
?>
<section class="travail-travello-section travail-travello-section--surface travail-travello-section--center">
	<div class="travail-travello-container">
		<?php if ( $travail_args['title'] || $travail_args['title_emphasis'] ) : ?>
			<h2 class="travail-travello-section-title">
				<?php echo esc_html( $travail_args['title'] ); ?>
				<?php if ( $travail_args['title_emphasis'] ) : ?>
					<span class="travail-travello-hero__em"><?php echo esc_html( $travail_args['title_emphasis'] ); ?></span>
				<?php endif; ?>
			</h2>
		<?php endif; ?>
		<?php if ( $travail_args['subtitle'] ) : ?>
			<p class="travail-travello-section-sub"><?php echo esc_html( $travail_args['subtitle'] ); ?></p>
		<?php endif; ?>

		<div class="travail-travello-services-grid">
			<?php foreach ( $travail_services as $travail_service ) : ?>
				<a href="<?php echo esc_url( $travail_service['url'] ); ?>" class="travail-travello-service-card">
					<img src="<?php echo esc_url( $travail_service['image'] ); ?>" alt="" loading="lazy" />
					<div class="travail-travello-service-card__overlay"></div>
					<div class="travail-travello-service-card__info">
						<p class="travail-travello-service-card__icon" aria-hidden="true"><?php echo esc_html( $travail_service['icon'] ); ?></p>
						<h3><?php echo esc_html( $travail_service['title'] ); ?></h3>
						<p><?php echo esc_html( $travail_service['desc'] ); ?></p>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
