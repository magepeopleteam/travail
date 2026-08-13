<?php
/**
 * Tour archive ("/tour/") — served via the archive_template filter in
 * inc/compatibility/tour-booking-manager.php.
 *
 * Tour Booking Manager does not intercept its own CPT archive (verified:
 * no is_post_type_archive('ttbm_tour') handling in TTBM_Frontend), so
 * this page is fully theme-owned. Rather than re-implementing the
 * plugin's query/pricing/availability logic, the actual listing is
 * rendered by the plugin's own [ttbm-tour-list] shortcode — the theme
 * only supplies the hero band, search bar and page chrome around it,
 * and reskins the shortcode's real output classes in
 * assets/css/tbm-restyle.css.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$travail_columns = (int) travail_get_option( 'tours_per_row', 3 );

/**
 * Real counts for the hero subtitle — "X tours across Y destinations" —
 * rather than the static reference design's fabricated numbers.
 */
$travail_tour_count = (int) wp_count_posts( 'ttbm_tour' )->publish;
$travail_destination_terms = get_terms(
	array(
		'taxonomy'   => 'ttbm_tour_location',
		'hide_empty' => true,
	)
);
$travail_destination_count = is_array( $travail_destination_terms ) ? count( $travail_destination_terms ) : 0;
?>

<header class="travail-archive-header">
	<div class="travail-container">
		<?php get_template_part( 'template-parts/content/breadcrumbs' ); ?>

		<h1 class="travail-page-title">
			<?php esc_html_e( 'All', 'travail' ); ?>
			<em class="travail-hero-em"><?php echo esc_html( post_type_archive_title( '', false ) ); ?></em>
		</h1>
		<?php
		/* Tour count and destination count pluralize independently, so this
		   needs two _n() calls rather than one shared plural/singular form. */
		$travail_tour_phrase = sprintf( _n( '%s tour', '%s tours', $travail_tour_count, 'travail' ), number_format_i18n( $travail_tour_count ) );
		$travail_dest_phrase = sprintf( _n( '%s destination', '%s destinations', $travail_destination_count, 'travail' ), number_format_i18n( $travail_destination_count ) );
		?>
		<p class="travail-page-sub">
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: "N tour(s)" phrase, 2: "N destination(s)" phrase */
					__( '%1$s across %2$s', 'travail' ),
					$travail_tour_phrase,
					$travail_dest_phrase
				)
			);
			?>
		</p>

		<?php if ( shortcode_exists( 'ttbm-top-search' ) ) : ?>
			<div class="travail-tour-archive-search">
				<?php echo do_shortcode( '[ttbm-top-search]' ); ?>
			</div>
		<?php endif; ?>
	</div>
</header>

<main id="main" class="travail-main travail-section" role="main">
	<div class="travail-container">

		<?php do_action( 'travail_before_tour_archive' ); ?>

		<?php if ( shortcode_exists( 'ttbm-tour-list' ) ) : ?>
			<div class="travail-tbm-grid-wrap">
				<?php
				/**
				 * Reference-design sidebar (tour-list.html) uses exactly 6 real,
				 * data-backed filter blocks: Category (Activities taxonomy), Price,
				 * Duration, Rating, Destination (Location taxonomy), Tour Type
				 * (Category taxonomy). Feature/Tag/Month filters exist in the plugin
				 * too but aren't part of that design, so they're explicitly switched
				 * off here instead of left cluttering the sidebar.
				 */
				echo do_shortcode(
					sprintf(
						'[ttbm-tour-list style="grid" column="%d" pagination="yes" category-filter="yes" duration-filter="yes" rating-filter="yes" price-filter="yes" feature-filter="no" tag-filter="no" month-filter="no"]',
						max( 1, $travail_columns )
					)
				);
				?>
			</div>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/content-none' ); ?>
		<?php endif; ?>

		<?php do_action( 'travail_after_tour_archive' ); ?>

	</div>
</main>

<?php
get_footer();
