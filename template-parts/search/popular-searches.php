<?php
/**
 * "Popular searches" strip that overlaps the bottom of the hero.
 * Pulls the most-booked-looking locations (highest term count) when
 * Tour Booking Manager is active; otherwise hides itself rather than
 * showing fabricated destinations. The avatar stack + traveler count on
 * the right matches the wanderly.html reference design exactly.
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
		'taxonomy'   => 'ttbm_tour_location',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 6,
	)
);

if ( is_wp_error( $travail_terms ) || empty( $travail_terms ) ) {
	return;
}

$travail_avatars = apply_filters(
	'travail_searches_bar_avatars',
	array(
		'https://i.pravatar.cc/32?img=1',
		'https://i.pravatar.cc/32?img=2',
		'https://i.pravatar.cc/32?img=3',
		'https://i.pravatar.cc/32?img=4',
	)
);
?>
<div class="travail-container travail-searches-bar-wrap">
	<div class="travail-searches-bar">
		<div class="travail-searches-left" data-travail-pill-group>
			<span class="travail-searches-label"><?php esc_html_e( 'Popular searches:', 'travail' ); ?></span>
			<?php foreach ( $travail_terms as $travail_term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $travail_term ) ); ?>" class="travail-pill"><?php echo esc_html( $travail_term->name ); ?></a>
			<?php endforeach; ?>
		</div>

		<div class="travail-searches-right">
			<?php if ( ! empty( $travail_avatars ) ) : ?>
				<div class="travail-avatars">
					<?php foreach ( $travail_avatars as $travail_avatar_url ) : ?>
						<img src="<?php echo esc_url( $travail_avatar_url ); ?>" alt="<?php esc_attr_e( 'Traveler', 'travail' ); ?>" loading="lazy" width="28" height="28" />
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<span><?php echo esc_html( travail_get_option( 'searches_bar_note', __( '5K+ travelers this week', 'travail' ) ) ); ?></span>
		</div>
	</div>
</div>
