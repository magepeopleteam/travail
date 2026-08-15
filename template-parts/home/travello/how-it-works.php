<?php
/**
 * Travello homepage — "Your journey starts in three steps".
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_args = travail_section_args(
	isset( $args ) ? $args : array(),
	array(
		'eyebrow'        => __( 'Simple Process', 'travail' ),
		'title'          => __( 'Your journey starts in', 'travail' ),
		'title_emphasis' => __( 'three steps', 'travail' ),
		'subtitle'       => __( 'From inspiration to booking — we make it effortless.', 'travail' ),
		'steps'          => array(),
	)
);

$travail_steps = ! empty( $travail_args['steps'] )
	? $travail_args['steps']
	: apply_filters(
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
		<?php if ( $travail_args['eyebrow'] ) : ?>
			<span class="travail-travello-eyebrow travail-travello-eyebrow--ink"><?php echo esc_html( $travail_args['eyebrow'] ); ?></span>
		<?php endif; ?>
		<?php if ( $travail_args['title'] || $travail_args['title_emphasis'] ) : ?>
			<h2 class="travail-travello-section-title">
				<?php echo esc_html( $travail_args['title'] ); ?>
				<?php if ( $travail_args['title_emphasis'] ) : ?>
					<span class="travail-travello-hero__em"><?php echo esc_html( $travail_args['title_emphasis'] ); ?></span>
				<?php endif; ?>
			</h2>
		<?php endif; ?>
		<?php if ( $travail_args['subtitle'] ) : ?>
			<p class="travail-travello-how-sub"><?php echo esc_html( $travail_args['subtitle'] ); ?></p>
		<?php endif; ?>

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
