<?php
/**
 * "Trending destinations" grid — one card per ttbm_tour_location term,
 * ranked by tour count. Renders nothing (rather than fake destinations)
 * when Tour Booking Manager isn't active or no location has any
 * published tours yet.
 *
 * Accepts $args from the Elementor Destinations widget so every heading,
 * link and card can be edited there; called with no args from the
 * Classic homepage it keeps reading title/subtitle from the Customizer.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_dest_page = get_page_by_path( 'destinations' );
$travail_args      = wp_parse_args(
	isset( $args ) && is_array( $args ) ? $args : array(),
	array(
		'title'         => travail_get_option( 'destinations_title', __( 'Trending destinations', 'travail' ) ),
		'subtitle'      => travail_get_option( 'destinations_subtitle', __( 'Popular places loved by travelers around the world.', 'travail' ) ),
		'view_all_text' => __( 'View all destinations', 'travail' ),
		'view_all_url'  => $travail_dest_page ? get_permalink( $travail_dest_page ) : '',
		'show_header'   => true,
		'show_count'    => true,
		'show_country'  => true,
		'limit'         => 4,
		'orderby'       => 'count',
		'term_ids'      => array(),
		'hide_empty'    => true,
		'cards'         => array(),
	)
);

$travail_cards = function_exists( 'travail_get_destination_cards' )
	? travail_get_destination_cards(
		array(
			'limit'      => $travail_args['limit'],
			'orderby'    => $travail_args['orderby'],
			'term_ids'   => $travail_args['term_ids'],
			'hide_empty' => $travail_args['hide_empty'],
			'style'      => 'classic',
			'cards'      => $travail_args['cards'],
		)
	)
	: array();

if ( empty( $travail_cards ) ) {
	return;
}
?>
<section class="travail-section travail-section--surface">
	<div class="travail-container">
		<?php if ( $travail_args['show_header'] ) : ?>
			<div class="travail-section-head">
				<div>
					<?php if ( $travail_args['title'] ) : ?>
						<h2 class="travail-serif"><?php echo esc_html( $travail_args['title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( $travail_args['subtitle'] ) : ?>
						<p><?php echo esc_html( $travail_args['subtitle'] ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $travail_args['view_all_text'] && $travail_args['view_all_url'] ) : ?>
					<a href="<?php echo esc_url( $travail_args['view_all_url'] ); ?>" class="travail-view-all travail-link-arrow">
						<?php echo esc_html( $travail_args['view_all_text'] ); ?>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="travail-dest-grid">
			<?php foreach ( $travail_cards as $travail_index => $travail_card ) : ?>
				<a href="<?php echo esc_url( $travail_card['url'] ); ?>" class="travail-dest-card<?php echo $travail_index < 2 ? ' travail-dest-card--tall' : ''; ?>">
					<img src="<?php echo esc_url( $travail_card['image'] ); ?>" alt="<?php echo esc_attr( $travail_card['name'] ); ?>" loading="lazy" />
					<div class="travail-dest-card__gradient"></div>
					<div class="travail-dest-card__info">
						<div>
							<h4><?php echo esc_html( $travail_card['name'] ); ?></h4>
							<p>
								<?php if ( $travail_args['show_country'] && $travail_card['country'] ) : ?>
									<?php echo esc_html( $travail_card['country'] ); ?>
									<?php if ( $travail_args['show_count'] && $travail_card['count'] > 0 ) : ?> · <?php endif; ?>
								<?php endif; ?>
								<?php
								if ( $travail_args['show_count'] && $travail_card['count'] > 0 ) {
									printf(
										/* translators: %d: number of tours in this destination. */
										esc_html( _n( '%d experience', '%d experiences', $travail_card['count'], 'travail' ) ),
										(int) $travail_card['count']
									);
								}
								?>
							</p>
						</div>
						<span class="travail-dest-arrow" aria-hidden="true">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
						</span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
