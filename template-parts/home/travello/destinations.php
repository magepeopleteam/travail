<?php
/**
 * Travello homepage — "Popular destinations" bento grid (1 large 2×2
 * card, 2 regular cards, 1 wide 2×1 card), matching travello.html's
 * exact card-size pattern by grid position (index 0/1/2/3).
 *
 * Accepts $args from the Elementor Destinations widget so every heading,
 * link and card can be edited there; called with no args from
 * inc/homepage-travello.php it keeps the original hardcoded defaults.
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
		'title'          => __( 'Popular', 'travail' ),
		'title_emphasis' => __( 'destinations', 'travail' ),
		'subtitle'       => __( 'Places travelers are loving right now.', 'travail' ),
		'view_all_text'  => __( 'View all destinations →', 'travail' ),
		'view_all_url'   => $travail_dest_page ? get_permalink( $travail_dest_page ) : '',
		'show_header'    => true,
		'show_count'     => true,
		'show_country'   => true,
		'limit'          => 4,
		'orderby'        => 'count',
		'term_ids'       => array(),
		'hide_empty'     => true,
		'cards'          => array(),
	)
);

$travail_cards = function_exists( 'travail_get_destination_cards' )
	? travail_get_destination_cards(
		array(
			'limit'      => $travail_args['limit'],
			'orderby'    => $travail_args['orderby'],
			'term_ids'   => $travail_args['term_ids'],
			'hide_empty' => $travail_args['hide_empty'],
			'style'      => 'travello',
			'cards'      => $travail_args['cards'],
		)
	)
	: array();

if ( empty( $travail_cards ) ) {
	return;
}

$travail_size_classes = array( 'large', '', '', 'wide' );
?>
<section class="travail-travello-section">
	<div class="travail-travello-container">
		<?php if ( $travail_args['show_header'] ) : ?>
			<div class="travail-travello-section-head">
				<div>
					<?php if ( $travail_args['title'] || $travail_args['title_emphasis'] ) : ?>
						<h2 class="travail-travello-section-title">
							<?php echo esc_html( $travail_args['title'] ); ?>
							<?php if ( $travail_args['title_emphasis'] ) : ?>
								<span class="travail-travello-hero__em"><?php echo esc_html( $travail_args['title_emphasis'] ); ?></span>
							<?php endif; ?>
						</h2>
					<?php endif; ?>
					<?php if ( $travail_args['subtitle'] ) : ?>
						<p class="travail-travello-section-sub"><?php echo esc_html( $travail_args['subtitle'] ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $travail_args['view_all_text'] && $travail_args['view_all_url'] ) : ?>
					<a href="<?php echo esc_url( $travail_args['view_all_url'] ); ?>" class="travail-travello-link-more"><?php echo esc_html( $travail_args['view_all_text'] ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="travail-travello-dest-grid">
			<?php foreach ( $travail_cards as $travail_index => $travail_card ) : ?>
				<?php $travail_size = isset( $travail_size_classes[ $travail_index ] ) ? $travail_size_classes[ $travail_index ] : ''; ?>
				<a href="<?php echo esc_url( $travail_card['url'] ); ?>" class="travail-travello-dest-card<?php echo $travail_size ? ' travail-travello-dest-card--' . esc_attr( $travail_size ) : ''; ?>">
					<img src="<?php echo esc_url( $travail_card['image'] ); ?>" alt="<?php echo esc_attr( $travail_card['name'] ); ?>" loading="lazy" />
					<div class="travail-travello-dest-card__overlay"></div>
					<div class="travail-travello-dest-card__info">
						<div>
							<h3><?php echo esc_html( $travail_card['name'] ); ?></h3>
							<?php if ( $travail_args['show_country'] && $travail_card['country'] ) : ?>
								<p><?php echo esc_html( $travail_card['country'] ); ?></p>
							<?php endif; ?>
							<?php if ( $travail_args['show_count'] && $travail_card['count'] > 0 ) : ?>
								<p class="travail-travello-dest-card__count">
									<?php
									printf(
										/* translators: %d: number of tours in this destination. */
										esc_html( _n( '%d tour', '%d tours', $travail_card['count'], 'travail' ) ),
										(int) $travail_card['count']
									);
									?>
								</p>
							<?php endif; ?>
						</div>
						<span class="travail-travello-dest-arrow" aria-hidden="true">→</span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
