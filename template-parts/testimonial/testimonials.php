<?php
/**
 * Homepage testimonials band. These are editorial/curated quotes (set
 * via the travail_testimonials filter or authored directly with the
 * "Travail Testimonials" Elementor widget) — intentionally decoupled
 * from Tour Booking Manager's per-tour ttbm_tour_review entries, which
 * are a different, tour-specific review system rendered on the single
 * tour page instead (see templates/tours/single-tour.php).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_testimonials = apply_filters(
	'travail_testimonials',
	array(
		array(
			'quote'    => __( 'We didn’t just visit Bali. {emphasis}', 'travail' ),
			'emphasis' => __( 'We experienced it.', 'travail' ),
			'author'   => __( 'Sarah Williams', 'travail' ),
			'meta'     => __( 'New York, USA', 'travail' ),
			'rating'   => 5,
			'tag'      => __( 'Bali · 7 day adventure', 'travail' ),
		),
	)
);

if ( empty( $travail_testimonials ) ) {
	return;
}

/**
 * Small avatar strip shown above the quote. Uses a stable, purpose-built
 * placeholder-avatar service (i.pravatar.cc) rather than photos of real
 * people — filterable so a site owner can swap in real traveler photos.
 */
$travail_avatars = apply_filters(
	'travail_testimonials_avatars',
	array(
		'https://i.pravatar.cc/88?img=11',
		'https://i.pravatar.cc/88?img=12',
		'https://i.pravatar.cc/88?img=13',
		'https://i.pravatar.cc/88?img=14',
		'https://i.pravatar.cc/88?img=15',
		'https://i.pravatar.cc/88?img=16',
	)
);

$travail_review_count = travail_get_option( 'testimonials_count_label', __( '5,000+ verified reviews', 'travail' ) );
?>
<section class="travail-section travail-section--lg travail-section--dark travail-testimonials" data-travail-testimonial-slider>
	<div class="travail-container">
		<p class="travail-eyebrow travail-eyebrow--accent"><?php esc_html_e( 'Testimonials', 'travail' ); ?></p>
		<h2 class="travail-serif" style="color:#fff;"><?php echo esc_html( travail_get_option( 'testimonials_title', __( 'Loved by travelers worldwide', 'travail' ) ) ); ?></h2>

		<?php if ( ! empty( $travail_avatars ) ) : ?>
			<div class="travail-testi-avatars">
				<?php foreach ( $travail_avatars as $travail_avatar_url ) : ?>
					<img src="<?php echo esc_url( $travail_avatar_url ); ?>" alt="" loading="lazy" width="44" height="44" />
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php foreach ( $travail_testimonials as $travail_index => $travail_item ) : ?>
			<div class="travail-testi-slide<?php echo 0 === $travail_index ? ' is-active' : ''; ?>">
				<blockquote class="travail-testi-quote travail-serif">
					<?php
					$travail_quote = esc_html( $travail_item['quote'] );
					if ( ! empty( $travail_item['emphasis'] ) ) {
						$travail_quote = str_replace( '{emphasis}', '<em>' . esc_html( $travail_item['emphasis'] ) . '</em>', $travail_quote );
					}
					echo wp_kses( $travail_quote, array( 'em' => array() ) );
					?>
				</blockquote>
				<?php if ( ! empty( $travail_item['rating'] ) ) : ?>
					<div class="travail-testi-stars" aria-hidden="true"><?php echo esc_html( str_repeat( '★', (int) $travail_item['rating'] ) ); ?></div>
				<?php endif; ?>
				<p class="travail-testi-author"><strong><?php echo esc_html( $travail_item['author'] ); ?></strong> <?php echo ! empty( $travail_item['meta'] ) ? '· ' . esc_html( $travail_item['meta'] ) : ''; ?></p>
				<?php if ( ! empty( $travail_item['tag'] ) ) : ?>
					<div class="travail-testi-tags">
						<span class="travail-tag"><?php esc_html_e( 'Verified traveler', 'travail' ); ?></span>
						<span><?php echo esc_html( $travail_item['tag'] ); ?></span>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<?php if ( count( $travail_testimonials ) > 1 ) : ?>
			<div class="travail-testi-dots">
				<?php foreach ( $travail_testimonials as $travail_index => $travail_item ) : ?>
					<button type="button" class="<?php echo 0 === $travail_index ? 'is-active' : ''; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Testimonial %d', 'travail' ), $travail_index + 1 ) ); ?>"></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $travail_review_count ) : ?>
			<p class="travail-testi-count"><?php echo esc_html( $travail_review_count ); ?></p>
		<?php endif; ?>
	</div>
</section>
