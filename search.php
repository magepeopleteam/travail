<?php
/**
 * The template for displaying search results.
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
		<h1 class="travail-serif">
			<?php
			printf(
				/* translators: %s: search query. */
				esc_html__( 'Search results for: %s', 'travail' ),
				'<span>' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>
	</div>
</header>

<main id="main" class="travail-main travail-section" role="main">
	<div class="travail-container">
		<div class="travail-search-page-form">
			<?php get_search_form(); ?>
		</div>

		<?php if ( have_posts() ) : ?>

			<p class="travail-results-count">
				<?php
				printf(
					/* translators: %d: number of results. */
					esc_html( _n( '%d result found', '%d results found', $wp_query->found_posts, 'travail' ) ),
					(int) $wp_query->found_posts
				);
				?>
			</p>

			<div class="travail-blog-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/content-search' );
				endwhile;
				?>
			</div>

			<?php travail_pagination(); ?>

		<?php else : ?>

			<?php get_template_part( 'template-parts/content/content-none' ); ?>

		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
