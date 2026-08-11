<?php
/**
 * "Stories from the road" — latest blog posts grid for the homepage.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_posts = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $travail_posts->have_posts() ) {
	return;
}
?>
<section class="travail-section travail-section--lg travail-section--muted">
	<div class="travail-container">
		<div class="travail-section-head">
			<div>
				<h2 class="travail-serif"><?php echo esc_html( travail_get_option( 'blog_section_title', __( 'Stories from the road', 'travail' ) ) ); ?></h2>
			</div>
			<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ? get_permalink( get_option( 'page_for_posts' ) ) : get_post_type_archive_link( 'post' ) ); ?>" class="travail-view-all travail-link-arrow">
				<?php esc_html_e( 'View all stories', 'travail' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
			</a>
		</div>

		<div class="travail-blog-grid">
			<?php
			while ( $travail_posts->have_posts() ) :
				$travail_posts->the_post();
				get_template_part( 'template-parts/content/content', 'post' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
