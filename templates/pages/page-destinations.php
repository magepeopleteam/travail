<?php
/**
 * Template Name: Travail: Destinations
 * Template Post Type: page
 *
 * Full destinations directory — every ttbm_tour_location term with at
 * least one published tour, as a card grid.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$travail_has_tbm = class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_tour_booking_manager_active();
?>

<header class="travail-destination-hero travail-container" style="margin-top:120px;">
	<img src="<?php echo esc_url( has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'travail-hero' ) : TRAVAIL_URI . '/assets/images/placeholder-wide.svg' ); ?>" alt="" />
	<div class="travail-destination-hero__overlay">
		<div>
			<h1 class="travail-serif"><?php the_title(); ?></h1>
			<?php if ( get_the_content() ) : ?>
				<p><?php echo esc_html( wp_strip_all_tags( get_the_content() ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</header>

<main id="main" class="travail-main travail-section" role="main">
	<div class="travail-container">

		<?php if ( ! $travail_has_tbm ) : ?>

			<?php get_template_part( 'template-parts/content/content-none' ); ?>

		<?php else : ?>

			<?php
			$travail_terms = get_terms(
				array(
					'taxonomy'   => 'ttbm_tour_location',
					'hide_empty' => true,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);
			?>

			<?php if ( is_wp_error( $travail_terms ) || empty( $travail_terms ) ) : ?>
				<div class="travail-empty-state">
					<p><?php esc_html_e( 'No destinations yet — add a Location to a tour in Tour Booking Manager to see it here.', 'travail' ); ?></p>
				</div>
			<?php else : ?>
				<div class="travail-grid travail-grid--4">
					<?php foreach ( $travail_terms as $travail_term ) : ?>
						<a href="<?php echo esc_url( get_term_link( $travail_term ) ); ?>" class="travail-dest-card" style="height:220px;">
							<img src="<?php echo esc_url( travail_get_term_image_url( $travail_term, 'travail-card' ) ); ?>" alt="<?php echo esc_attr( $travail_term->name ); ?>" loading="lazy" />
							<div class="travail-dest-card__gradient"></div>
							<div class="travail-dest-card__info">
								<div>
									<h4><?php echo esc_html( $travail_term->name ); ?></h4>
									<p>
										<?php
										printf(
											/* translators: %d: number of tours in this destination. */
											esc_html( _n( '%d experience', '%d experiences', $travail_term->count, 'travail' ) ),
											(int) $travail_term->count
										);
										?>
									</p>
								</div>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
