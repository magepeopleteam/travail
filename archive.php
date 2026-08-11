<?php
/**
 * Generic archive template — category/tag/date/author archives and any
 * post type archive that doesn't have a dedicated archive-{type}.php
 * (the tour archive gets its own richer template — see
 * inc/compatibility/tour-booking-manager.php for the template_include
 * filter that serves templates/tours/archive-tour.php instead).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<header class="travail-page-header travail-section--tight">
	<div class="travail-container travail-text-center">
		<?php the_archive_title( '<h1 class="travail-serif">', '</h1>' ); ?>
		<?php the_archive_description( '<div class="travail-archive-description">', '</div>' ); ?>
	</div>
</header>

<main id="main" class="travail-main travail-section" role="main">
	<div class="travail-container">
		<?php do_action( 'travail_before_content' ); ?>

		<?php if ( have_posts() ) : ?>

			<div class="travail-blog-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/content', get_post_type() );
				endwhile;
				?>
			</div>

			<?php travail_pagination(); ?>

		<?php else : ?>

			<?php get_template_part( 'template-parts/content/content-none' ); ?>

		<?php endif; ?>

		<?php do_action( 'travail_after_content' ); ?>
	</div>
</main>

<?php
get_footer();
