<?php
/**
 * Travello homepage — "Your journey starts in three steps".
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_steps = apply_filters(
	'travail_travello_how_it_works_steps',
	array(
		array(
			'title' => __( 'Discover', 'travail' ),
			'text'  => __( 'Explore destinations and experiences tailored to your style of travel.', 'travail' ),
		),
		array(
			'title' => __( 'Choose', 'travail' ),
			'text'  => __( 'Compare tours, prices, dates and genuine traveler reviews side by side.', 'travail' ),
		),
		array(
			'title' => __( 'Book', 'travail' ),
			'text'  => __( "Reserve securely in just a few clicks — then dream about what's next.", 'travail' ),
		),
	)
);
?>
<section class="travail-travello-section travail-travello-section--surface-alt travail-travello-section--center">
	<div class="travail-travello-container">
		<span class="travail-travello-eyebrow travail-travello-eyebrow--ink"><?php esc_html_e( 'Simple Process', 'travail' ); ?></span>
		<h2 class="travail-travello-section-title"><?php esc_html_e( 'Your journey starts in', 'travail' ); ?> <span class="travail-travello-hero__em"><?php esc_html_e( 'three steps', 'travail' ); ?></span></h2>
		<p class="travail-travello-how-sub"><?php esc_html_e( 'From inspiration to booking — we make it effortless.', 'travail' ); ?></p>

		<div class="travail-travello-steps-grid">
			<?php foreach ( $travail_steps as $travail_index => $travail_step ) : ?>
				<div class="travail-travello-step">
					<div class="travail-travello-step__num-wrap"><span class="travail-travello-step__num"><?php echo esc_html( sprintf( '%02d', $travail_index + 1 ) ); ?></span></div>
					<h3 class="travail-travello-step__title"><?php echo esc_html( $travail_step['title'] ); ?></h3>
					<p class="travail-travello-step__desc"><?php echo esc_html( $travail_step['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
