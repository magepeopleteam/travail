<?php
/**
 * Template for the "Posts page" (Settings → Reading → Posts page), used
 * only when a static front page is also configured — otherwise
 * front-page.php / index.php already cover the blog view.
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
		<h1 class="travail-serif"><?php single_post_title(); ?></h1>
	</div>
</header>

<main id="main" class="travail-main travail-section" role="main">
	<div class="travail-container">
		<?php
		$travail_layout = travail_get_option( 'blog_layout', 'grid' );
		?>
		<div class="<?php echo 'list' === $travail_layout ? 'travail-layout-content-sidebar' : ''; ?>">
			<div>
				<?php if ( have_posts() ) : ?>
					<div class="travail-blog-grid">
						<?php
						while ( have_posts() ) :
							the_post();
							get_template_part( 'template-parts/content/content', 'post' );
						endwhile;
						?>
					</div>
					<?php travail_pagination(); ?>
				<?php else : ?>
					<?php get_template_part( 'template-parts/content/content-none' ); ?>
				<?php endif; ?>
			</div>
			<?php if ( 'list' === $travail_layout && is_active_sidebar( 'sidebar-blog' ) ) : ?>
				<?php get_sidebar(); ?>
			<?php endif; ?>
		</div>
	</div>
</main>

<?php
get_footer();
