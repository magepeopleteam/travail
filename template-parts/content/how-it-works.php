<?php
/**
 * "Plan your trip in 3 simple steps" section.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_steps = apply_filters(
	'travail_how_it_works_steps',
	array(
		array(
			'title' => __( 'Discover', 'travail' ),
			'text'  => __( 'Find experiences you’ll love from our curated collection of tours.', 'travail' ),
		),
		array(
			'title' => __( 'Choose', 'travail' ),
			'text'  => __( 'Compare dates, prices and reviews to find the perfect adventure.', 'travail' ),
		),
		array(
			'title' => __( 'Book', 'travail' ),
			'text'  => __( 'Reserve your adventure securely with instant confirmation.', 'travail' ),
		),
	)
);
?>
<section class="travail-section travail-section--lg travail-section--muted">
	<div class="travail-container">
		<div class="travail-section-head travail-section-head--center">
			<h2 class="travail-serif"><?php echo esc_html( travail_get_option( 'how_it_works_title', __( 'Plan your trip in 3 simple steps', 'travail' ) ) ); ?></h2>
		</div>

		<div class="travail-steps-grid">
			<?php foreach ( $travail_steps as $travail_index => $travail_step ) : ?>
				<div class="travail-step">
					<div class="travail-step-num travail-serif"><?php echo esc_html( sprintf( '%02d', $travail_index + 1 ) ); ?></div>
					<h3 class="travail-serif"><?php echo esc_html( $travail_step['title'] ); ?></h3>
					<p><?php echo esc_html( $travail_step['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
