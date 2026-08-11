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
?>

<header class="travail-archive-header">
	<div class="travail-container">
		<h1 class="travail-serif"><?php post_type_archive_title(); ?></h1>
		<?php
		$travail_archive_desc = travail_get_option( 'tour_archive_description', __( 'Handpicked tours and experiences, curated by our travel experts.', 'travail' ) );
		if ( $travail_archive_desc ) :
			?>
			<p><?php echo esc_html( $travail_archive_desc ); ?></p>
		<?php endif; ?>
	</div>
</header>

<main id="main" class="travail-main travail-section" role="main">
	<div class="travail-container">

		<?php do_action( 'travail_before_tour_archive' ); ?>

		<?php if ( shortcode_exists( 'ttbm-top-search' ) ) : ?>
			<div class="travail-tour-archive-search">
				<?php echo do_shortcode( '[ttbm-top-search]' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( shortcode_exists( 'ttbm-tour-list' ) ) : ?>
			<div class="travail-tbm-grid-wrap">
				<?php echo do_shortcode( sprintf( '[ttbm-tour-list style="grid" column="%d" pagination="yes"]', max( 1, $travail_columns ) ) ); ?>
			</div>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/content-none' ); ?>
		<?php endif; ?>

		<?php do_action( 'travail_after_tour_archive' ); ?>

	</div>
</main>

<?php
get_footer();
