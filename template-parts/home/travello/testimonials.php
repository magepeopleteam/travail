<?php
/**
 * Travello homepage — "Loved by travelers around the world" review grid.
 *
 * Uses initials-in-a-circle avatars rather than photos (matching
 * travello.html exactly), which conveniently sidesteps needing any
 * placeholder headshot assets. Filterable so a site owner can swap in
 * real reviews without a template override — same pattern as
 * travail_how_it_works_steps on the default homepage.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_reviews = apply_filters(
	'travail_travello_reviews',
	array(
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
	)
);

if ( empty( $travail_reviews ) ) {
	return;
}
?>
<section class="travail-travello-section">
	<div class="travail-travello-container">
		<div class="travail-travello-section-head travail-travello-section-head--center">
			<h2 class="travail-travello-section-title"><?php esc_html_e( 'Loved by travelers', 'travail' ); ?> <span class="travail-travello-hero__em"><?php esc_html_e( 'around the world', 'travail' ); ?></span></h2>
			<p class="travail-travello-section-sub"><?php echo esc_html( travail_get_option( 'travello_why_us_stat_value', __( '50,000+', 'travail' ) ) ); ?> <?php esc_html_e( 'happy travelers and counting.', 'travail' ); ?></p>
		</div>

		<div class="travail-travello-reviews-grid">
			<?php foreach ( $travail_reviews as $travail_review ) : ?>
				<?php
				$travail_words   = preg_split( '/\s+/', trim( $travail_review['name'] ) );
				$travail_initials = '';
				foreach ( array_slice( $travail_words, 0, 2 ) as $travail_word ) {
					$travail_initials .= mb_substr( $travail_word, 0, 1 );
				}
				?>
				<div class="travail-travello-review-card">
					<div class="travail-travello-review-stars" aria-hidden="true">★★★★★</div>
					<p class="travail-travello-review-text">&ldquo;<?php echo esc_html( $travail_review['text'] ); ?>&rdquo;</p>
					<div class="travail-travello-review-footer">
						<div class="travail-travello-review-avatar" aria-hidden="true"><?php echo esc_html( $travail_initials ); ?></div>
						<div>
							<p class="travail-travello-review-name"><?php echo esc_html( $travail_review['name'] ); ?></p>
							<p class="travail-travello-review-loc"><?php echo esc_html( $travail_review['loc'] ); ?></p>
						</div>
						<span class="travail-travello-review-verified"><?php esc_html_e( 'Verified', 'travail' ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
