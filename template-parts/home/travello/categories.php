<?php
/**
 * Travello homepage — horizontal category pill nav, just under the hero.
 *
 * Pulls real ttbm_tour_activities terms (top by tour count) rather than
 * travello.html's 9 hardcoded category names, so it never shows an empty
 * "Cruise" pill with zero tours behind it. Emoji come from
 * travail_travello_category_icons() (name-matched, case-insensitive),
 * falling back to a plain compass emoji for any activity term that
 * isn't in that filterable map yet.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() ) {
	return;
}

$travail_terms = get_terms(
	array(
		'taxonomy'   => 'ttbm_tour_activities',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 9,
	)
);

if ( is_wp_error( $travail_terms ) || empty( $travail_terms ) ) {
	return;
}

$travail_icons = travail_travello_category_icons();
?>
<div class="travail-travello-category-nav">
	<div class="travail-travello-category-scroll" data-travello-pill-group>
		<?php foreach ( $travail_terms as $travail_index => $travail_term ) : ?>
			<?php $travail_icon = isset( $travail_icons[ strtolower( $travail_term->name ) ] ) ? $travail_icons[ strtolower( $travail_term->name ) ] : '🌍'; ?>
			<a href="<?php echo esc_url( get_term_link( $travail_term ) ); ?>" class="travail-travello-cat-pill<?php echo 0 === $travail_index ? ' is-active' : ''; ?>">
				<span aria-hidden="true"><?php echo esc_html( $travail_icon ); ?></span>
				<?php echo esc_html( $travail_term->name ); ?>
			</a>
		<?php endforeach; ?>
	</div>
</div>
