<?php
/**
 * The default page template.
 *
 * Elementor registers its own "Canvas" / "Full Width" page templates that
 * bypass this file entirely, so nothing here needs to special-case
 * Elementor-built pages — the_content() already returns Elementor's
 * rendered output when a page was built with it.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="travail-main" role="main">

	<?php do_action( 'travail_before_content' ); ?>

	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<div class="travail-container travail-section--tight">
			<?php if ( has_post_thumbnail() && ! is_front_page() ) : ?>
				<div class="travail-post-thumbnail">
					<?php the_post_thumbnail( 'travail-hero' ); ?>
				</div>
			<?php endif; ?>

			<div class="travail-entry-content">
				<?php the_content(); ?>
				<?php
				wp_link_pages(
					array(
						'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'travail' ) . '">' . esc_html__( 'Pages:', 'travail' ),
						'after'  => '</nav>',
					)
				);
				?>
			</div>

			<?php if ( comments_open() || get_comments_number() ) : ?>
				<?php comments_template(); ?>
			<?php endif; ?>
		</div>
	<?php endwhile; ?>

	<?php do_action( 'travail_after_content' ); ?>

</main>

<?php
get_footer();
