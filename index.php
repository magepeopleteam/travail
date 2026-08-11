<?php
/**
 * The main template file — fallback for any request WordPress can't
 * match to a more specific template (single.php, archive.php, etc).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

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
