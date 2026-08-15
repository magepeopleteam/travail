<?php
/**
 * Travello homepage — "Editorial Feature" banner: one large image with
 * a single editorially-picked tour. Same best-seller-first, most-recent
 * -fallback selection as template-parts/tour/featured-journey.php (the
 * default homepage's equivalent section) — see that file for why.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Travail_Plugin_Compatibility' ) || ! Travail_Plugin_Compatibility::is_tour_booking_manager_active() || ! class_exists( 'TTBM_Function' ) ) {
	return;
}

$travail_args = travail_section_args(
	isset( $args ) ? $args : array(),
	array(
		'tour_id'    => 0,
		'title'      => '',
		'badge_text' => __( "Editor's Pick", 'travail' ),
		'cta_text'   => __( 'Explore journey →', 'travail' ),
		'image'      => '',
	)
);

$travail_query_args = array(
	'post_type'      => 'ttbm_tour',
	'posts_per_page' => 1,
	'no_found_rows'  => true,
);

if ( ! empty( $travail_args['tour_id'] ) ) {
	$travail_query_args['p'] = absint( $travail_args['tour_id'] );
} else {
	$travail_query_args['meta_key']   = 'ttbm_best_seller';
	$travail_query_args['meta_value'] = 'on';
}

$travail_query = new WP_Query( $travail_query_args );

if ( ! $travail_query->have_posts() && empty( $travail_args['tour_id'] ) ) {
	$travail_query = new WP_Query(
		array(
			'post_type'      => 'ttbm_tour',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		)
	);
}

if ( ! $travail_query->have_posts() ) {
	return;
}

$travail_query->the_post();
$travail_tour_id = get_the_ID();

$travail_start_price = method_exists( 'TTBM_Function', 'get_tour_start_price' ) ? TTBM_Function::get_tour_start_price( $travail_tour_id ) : '';
$travail_duration     = method_exists( 'TTBM_Function', 'get_duration' ) ? TTBM_Function::get_duration( $travail_tour_id ) : '';
$travail_duration_type = get_post_meta( $travail_tour_id, 'ttbm_travel_duration_type', true );
$travail_night        = get_post_meta( $travail_tour_id, 'ttbm_travel_duration_night', true );
$travail_rating       = get_post_meta( $travail_tour_id, 'ttbm_tour_rating', true );

$travail_duration_label = '';
if ( $travail_duration ) {
	if ( 'day' === $travail_duration_type ) {
		/* translators: %s: number of days. */
		$travail_duration_label = sprintf( _n( '%s Day', '%s Days', $travail_duration, 'travail' ), $travail_duration );
		if ( $travail_night ) {
			/* translators: %s: number of nights. */
			$travail_duration_label .= ' · ' . sprintf( _n( '%s Night', '%s Nights', $travail_night, 'travail' ), $travail_night );
		}
	} elseif ( 'hour' === $travail_duration_type ) {
		/* translators: %s: number of hours. */
		$travail_duration_label = sprintf( _n( '%s Hour', '%s Hours', $travail_duration, 'travail' ), $travail_duration );
	}
}
?>
<div class="travail-travello-editorial">
	<div class="travail-travello-editorial__inner">
		<?php if ( $travail_args['image'] ) : ?>
			<img src="<?php echo esc_url( $travail_args['image'] ); ?>" alt="" loading="lazy" />
		<?php elseif ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'travail-hero', array( 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( TRAVAIL_URI . '/assets/images/placeholder-wide.svg' ); ?>" alt="" loading="lazy" />
		<?php endif; ?>
		<div class="travail-travello-editorial__gradient"></div>
		<div class="travail-travello-editorial__content">
			<div class="travail-travello-editorial__text">
				<?php if ( $travail_args['badge_text'] ) : ?>
					<span class="travail-travello-badge travail-travello-badge--stack"><?php echo esc_html( $travail_args['badge_text'] ); ?></span>
				<?php endif; ?>
				<h2 class="travail-travello-editorial__title"><?php echo $travail_args['title'] ? esc_html( $travail_args['title'] ) : esc_html( get_the_title() ); ?></h2>
				<div class="travail-travello-editorial__meta">
					<?php if ( $travail_duration_label ) : ?><span><?php echo esc_html( $travail_duration_label ); ?></span><?php endif; ?>
					<?php if ( $travail_rating ) : ?><span>★ <?php echo esc_html( number_format_i18n( (float) $travail_rating, 1 ) ); ?></span><?php endif; ?>
					<?php if ( $travail_start_price ) : ?>
						<span class="travail-travello-editorial__price"><?php esc_html_e( 'From', 'travail' ); ?> <?php echo wp_kses_post( travail_format_price( $travail_start_price ) ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $travail_args['cta_text'] ) : ?>
					<a href="<?php the_permalink(); ?>" class="travail-travello-editorial-cta"><?php echo esc_html( $travail_args['cta_text'] ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php
wp_reset_postdata();
