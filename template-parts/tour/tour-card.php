<?php
/**
 * Single tour card — matches the wanderly.html reference design exactly.
 * Expects $args['tour_id'] and optional $args['review_count'].
 *
 * Price/duration/rating are read via the same public, read-only
 * accessor methods the plugin's own card templates call
 * (TTBM_Function::get_tour_start_price() etc.) so this never disagrees
 * with what the plugin itself considers the price/duration to be.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TTBM_Function' ) ) {
	return;
}

$travail_tour_id = isset( $args['tour_id'] ) ? absint( $args['tour_id'] ) : get_the_ID();
if ( ! $travail_tour_id ) {
	return;
}

$travail_rating       = (float) get_post_meta( $travail_tour_id, 'ttbm_tour_rating', true );
$travail_review_count = isset( $args['review_count'] ) ? absint( $args['review_count'] ) : 0;
$travail_location     = method_exists( 'TTBM_Function', 'get_full_location' ) ? TTBM_Function::get_full_location( $travail_tour_id ) : '';
$travail_duration     = method_exists( 'TTBM_Function', 'get_duration' ) ? TTBM_Function::get_duration( $travail_tour_id ) : '';
$travail_duration_type = get_post_meta( $travail_tour_id, 'ttbm_travel_duration_type', true );
$travail_price        = method_exists( 'TTBM_Function', 'get_tour_start_price' ) ? TTBM_Function::get_tour_start_price( $travail_tour_id ) : '';

$travail_duration_label = '';
if ( $travail_duration ) {
	if ( 'min' === $travail_duration_type ) {
		/* translators: %s: number of minutes. */
		$travail_duration_label = sprintf( _n( '%s minute', '%s minutes', $travail_duration, 'travail' ), $travail_duration );
	} elseif ( 'hour' === $travail_duration_type ) {
		/* translators: %s: number of hours. */
		$travail_duration_label = sprintf( _n( '%s hour', '%s hours', $travail_duration, 'travail' ), $travail_duration );
	} else {
		/* translators: %s: number of days. */
		$travail_duration_label = sprintf( _n( '%s day', '%s days', $travail_duration, 'travail' ), $travail_duration );
	}
}

$travail_wishlisted = class_exists( 'TTBM_Wishlist' ) && is_user_logged_in() && TTBM_Wishlist::is_in_wishlist( $travail_tour_id );
?>
<article class="travail-tour-card">
	<div class="travail-tour-card__img">
		<a href="<?php echo esc_url( get_permalink( $travail_tour_id ) ); ?>" tabindex="-1" aria-hidden="true">
			<img src="<?php echo esc_url( travail_get_featured_image_url( $travail_tour_id, 'travail-card-wide' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $travail_tour_id ) ); ?>" loading="lazy" />
		</a>
		<?php if ( class_exists( 'TTBM_Wishlist' ) ) : ?>
			<button
				type="button"
				class="travail-wish-btn"
				data-travail-wishlist-toggle
				data-tour-id="<?php echo esc_attr( $travail_tour_id ); ?>"
				aria-pressed="<?php echo $travail_wishlisted ? 'true' : 'false'; ?>"
				aria-label="<?php esc_attr_e( 'Save to wishlist', 'travail' ); ?>"
			>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="<?php echo $travail_wishlisted ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
			</button>
		<?php endif; ?>
	</div>
	<div class="travail-tour-card__body">
		<?php if ( $travail_rating > 0 ) : ?>
			<div class="travail-tour-card__rating">
				<span class="star" aria-hidden="true">★</span>
				<strong><?php echo esc_html( number_format_i18n( $travail_rating, 1 ) ); ?></strong>
				<?php if ( $travail_review_count > 0 ) : ?>
					<span>(<?php echo esc_html( number_format_i18n( $travail_review_count ) ); ?>)</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h3 class="travail-tour-card__title">
			<a href="<?php echo esc_url( get_permalink( $travail_tour_id ) ); ?>"><?php echo esc_html( get_the_title( $travail_tour_id ) ); ?></a>
		</h3>

		<?php if ( $travail_location ) : ?>
			<div class="travail-tour-card__loc">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
				<?php echo esc_html( $travail_location ); ?>
			</div>
		<?php endif; ?>

		<div class="travail-tour-card__footer">
			<?php if ( $travail_duration_label ) : ?>
				<div class="travail-tour-card__duration">
					<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
					<?php echo esc_html( $travail_duration_label ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $travail_price ) : ?>
				<div class="travail-tour-card__price">
					<small><?php esc_html_e( 'From', 'travail' ); ?> </small><?php echo wp_kses_post( travail_format_price( $travail_price ) ); ?><small> / <?php esc_html_e( 'person', 'travail' ); ?></small>
				</div>
			<?php endif; ?>
		</div>
	</div>
</article>
