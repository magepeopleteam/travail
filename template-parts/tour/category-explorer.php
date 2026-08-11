<?php
/**
 * "Find your kind of adventure" — ttbm_tour_activities taxonomy term
 * rail (travel-style explorer: Adventure/Beach/Culture/Hiking/Luxury/
 * Wildlife/Family/Wellness, per the wanderly.html reference design).
 *
 * Shows exactly those 8 reference terms, in the reference's exact
 * order, when they exist (Travail's own demo importer creates them —
 * see Travail_Demo_Importer::create_demo_taxonomy_terms()) regardless
 * of what OTHER activity terms a real site may also have, so this
 * section always matches the reference rather than drifting based on
 * tour counts. Falls back to the site's actual top activities (by tour
 * count) when none of the 8 reference terms exist at all, so the
 * section still works on a site that never ran the demo importer.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() ) {
	return;
}

$travail_reference_order = array( 'Adventure', 'Beach', 'Culture', 'Hiking', 'Luxury', 'Wildlife', 'Family', 'Wellness' );

$travail_terms = array();
foreach ( $travail_reference_order as $travail_name ) {
	$travail_term = get_term_by( 'name', $travail_name, 'ttbm_tour_activities' );
	if ( $travail_term && ! is_wp_error( $travail_term ) ) {
		$travail_terms[] = $travail_term;
	}
}

if ( empty( $travail_terms ) ) {
	$travail_fallback = get_terms(
		array(
			'taxonomy'   => 'ttbm_tour_activities',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => 8,
		)
	);
	$travail_terms = is_wp_error( $travail_fallback ) ? array() : $travail_fallback;
}

if ( empty( $travail_terms ) ) {
	return;
}
?>
<section class="travail-section--tight travail-section--muted">
	<div class="travail-container">
		<h2 class="travail-serif travail-loose-title"><?php echo esc_html( travail_get_option( 'activities_title', __( 'Find your kind of adventure', 'travail' ) ) ); ?></h2>

		<div class="travail-cat-rail travail-cat-rail--fit" data-travail-pill-group>
			<?php foreach ( $travail_terms as $travail_index => $travail_term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $travail_term ) ); ?>" class="travail-cat-card<?php echo 0 === $travail_index ? ' is-active' : ''; ?>">
					<img src="<?php echo esc_url( travail_get_term_image_url( $travail_term, 'travail-thumb' ) ); ?>" alt="<?php echo esc_attr( $travail_term->name ); ?>" loading="lazy" />
					<div class="travail-cat-card__overlay"><span><?php echo esc_html( $travail_term->name ); ?></span></div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
