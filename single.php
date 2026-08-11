<?php
/**
 * The template for displaying single blog posts.
 *
 * ttbm_tour has its own single-ttbm_tour.php (see the templates/ folder
 * loaded via inc/compatibility/tour-booking-manager.php), so this file
 * only needs to handle regular blog posts and any other custom post type
 * that doesn't ship a dedicated template.
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
		<article <?php post_class( 'travail-single-post' ); ?> id="post-<?php the_ID(); ?>">

			<header class="travail-single-post-header">
				<div class="travail-container">
					<?php
					$travail_cat = travail_get_primary_term( get_the_ID(), 'post' );
					if ( $travail_cat ) :
						?>
						<p class="travail-eyebrow travail-eyebrow--accent">
							<a href="<?php echo esc_url( get_term_link( $travail_cat ) ); ?>"><?php echo esc_html( $travail_cat->name ); ?></a>
						</p>
					<?php endif; ?>

					<h1 class="travail-serif"><?php the_title(); ?></h1>

					<div class="travail-post-meta">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 32 ); ?>
						<span><?php the_author(); ?></span>
						<span aria-hidden="true">&middot;</span>
						<span><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></span>
						<span aria-hidden="true">&middot;</span>
						<span>
							<?php
							/* translators: %s: estimated reading time in minutes. */
							printf( esc_html__( '%s min read', 'travail' ), esc_html( max( 1, (int) round( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) ) ) );
							?>
						</span>
					</div>
				</div>
			</header>

			<div class="travail-container">
				<div class="<?php echo 'list' === travail_get_option( 'blog_layout', 'grid' ) ? 'travail-layout-content-sidebar' : ''; ?>">
					<div>
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="travail-post-thumbnail">
								<?php the_post_thumbnail( 'travail-hero' ); ?>
							</div>
						<?php endif; ?>

						<div class="travail-entry-content">
							<?php
							the_content();
							wp_link_pages(
								array(
									'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'travail' ) . '">' . esc_html__( 'Pages:', 'travail' ),
									'after'  => '</nav>',
								)
							);
							?>
						</div>

						<?php
						$travail_tags = get_the_tags();
						if ( $travail_tags ) :
							?>
							<div class="travail-post-tags">
								<?php foreach ( $travail_tags as $travail_tag ) : ?>
									<a href="<?php echo esc_url( get_tag_link( $travail_tag ) ); ?>" class="travail-pill">#<?php echo esc_html( $travail_tag->name ); ?></a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<div class="travail-author-box">
							<?php echo get_avatar( get_the_author_meta( 'ID' ), 64 ); ?>
							<div>
								<strong><?php the_author(); ?></strong>
								<p><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></p>
							</div>
						</div>

						<?php
						the_post_navigation(
							array(
								'prev_text' => '<span class="travail-eyebrow">' . esc_html__( 'Previous', 'travail' ) . '</span><span>%title</span>',
								'next_text' => '<span class="travail-eyebrow">' . esc_html__( 'Next', 'travail' ) . '</span><span>%title</span>',
							)
						);
						?>

						<?php if ( comments_open() || get_comments_number() ) : ?>
							<?php comments_template(); ?>
						<?php endif; ?>
					</div>

					<?php if ( 'list' === travail_get_option( 'blog_layout', 'grid' ) && is_active_sidebar( 'sidebar-blog' ) ) : ?>
						<?php get_sidebar(); ?>
					<?php endif; ?>
				</div>
			</div>
		</article>
	<?php endwhile; ?>

	<?php do_action( 'travail_after_content' ); ?>

</main>

<?php
get_footer();
