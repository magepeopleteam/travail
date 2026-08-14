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

/**
 * Real term/tour counts for the subtitle — fetched here (rather than down
 * where the grid used to fetch it) so the header can show them too, matching
 * the "X tours across Y destinations" real-data subtitle pattern in
 * templates/tours/archive-tour.php instead of always needing page-editor
 * copy to say anything at all.
 */
$travail_terms = $travail_has_tbm ? get_terms(
	array(
		'taxonomy'   => 'ttbm_tour_location',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
) : array();
$travail_terms_valid  = ! is_wp_error( $travail_terms ) && ! empty( $travail_terms );
$travail_dest_count   = $travail_terms_valid ? count( $travail_terms ) : 0;
$travail_tours_total  = $travail_terms_valid ? (int) array_sum( wp_list_pluck( $travail_terms, 'count' ) ) : 0;
$travail_page_content = trim( wp_strip_all_tags( get_the_content() ) );
?>

<?php /* Same clean white hero as the tour archive (templates/tours/archive-tour.php)
   — breadcrumb, italic-serif title, real-count subtitle — instead of the old
   420px image-and-overlay hero, which needed a manual margin-top:120px hack
   to clear the fixed site header and never matched the rest of the site's
   directory-style pages. .travail-archive-header etc. are duplicated into
   destination.css (kept in sync with tour.css by hand) rather than loading
   all of tour.css's unrelated tour/ticket styles just for this page. */ ?>
<header class="travail-archive-header">
	<div class="travail-container">
		<?php get_template_part( 'template-parts/content/breadcrumbs' ); ?>

		<h1 class="travail-page-title"><em class="travail-hero-em"><?php the_title(); ?></em></h1>

		<p class="travail-page-sub">
			<?php
			if ( $travail_page_content ) {
				echo esc_html( $travail_page_content );
			} elseif ( $travail_terms_valid ) {
				echo esc_html(
					sprintf(
						/* translators: 1: "N destination(s)" phrase, 2: "N tour(s)" phrase */
						__( '%1$s to explore, %2$s waiting for you', 'travail' ),
						sprintf( _n( '%s destination', '%s destinations', $travail_dest_count, 'travail' ), number_format_i18n( $travail_dest_count ) ),
						sprintf( _n( '%s tour', '%s tours', $travail_tours_total, 'travail' ), number_format_i18n( $travail_tours_total ) )
					)
				);
			}
			?>
		</p>
	</div>
</header>

<main id="main" class="travail-main travail-section" role="main">
	<div class="travail-container">

		<?php if ( ! $travail_has_tbm ) : ?>

			<?php get_template_part( 'template-parts/content/content-none' ); ?>

		<?php else : ?>

			<?php if ( ! $travail_terms_valid ) : ?>
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
