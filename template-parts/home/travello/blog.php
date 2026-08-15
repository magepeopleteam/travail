<?php
/**
 * Travello homepage — "Travel inspiration" blog teaser (1 big featured
 * post + 2 small list posts), from real WordPress `post` data.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_blog_page = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : get_post_type_archive_link( 'post' );
if ( ! $travail_blog_page ) {
	$travail_blog_page = home_url( '/blog/' );
}

$travail_args = travail_section_args(
	isset( $args ) ? $args : array(),
	array(
		'title'          => __( 'Travel', 'travail' ),
		'title_emphasis' => __( 'inspiration', 'travail' ),
		'subtitle'       => __( 'Stories, guides and tips from our editors.', 'travail' ),
		'view_all_text'  => __( 'All articles →', 'travail' ),
		'view_all_url'   => $travail_blog_page,
		'limit'          => 3,
		'category'       => '',
	)
);

$travail_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => max( 1, absint( $travail_args['limit'] ) ),
		'orderby'             => 'date',
		'order'               => 'DESC',
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
		'category_name'       => $travail_args['category'] ? sanitize_title( $travail_args['category'] ) : '',
	)
);

if ( ! $travail_query->have_posts() ) {
	return;
}

$travail_posts = $travail_query->posts;
$travail_featured = $travail_posts[0];
$travail_small     = array_slice( $travail_posts, 1 );
?>
<section class="travail-travello-section">
	<div class="travail-travello-container">
		<div class="travail-travello-section-head">
			<div>
				<?php if ( $travail_args['title'] || $travail_args['title_emphasis'] ) : ?>
					<h2 class="travail-travello-section-title">
						<?php echo esc_html( $travail_args['title'] ); ?>
						<?php if ( $travail_args['title_emphasis'] ) : ?>
							<span class="travail-travello-hero__em"><?php echo esc_html( $travail_args['title_emphasis'] ); ?></span>
						<?php endif; ?>
					</h2>
				<?php endif; ?>
				<?php if ( $travail_args['subtitle'] ) : ?>
					<p class="travail-travello-section-sub"><?php echo esc_html( $travail_args['subtitle'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $travail_args['view_all_text'] && $travail_args['view_all_url'] ) : ?>
				<a href="<?php echo esc_url( $travail_args['view_all_url'] ); ?>" class="travail-travello-link-more"><?php echo esc_html( $travail_args['view_all_text'] ); ?></a>
			<?php endif; ?>
		</div>

		<div class="travail-travello-blog-grid">
			<a href="<?php echo esc_url( get_permalink( $travail_featured ) ); ?>" class="travail-travello-blog-featured">
				<img src="<?php echo esc_url( travail_get_featured_image_url( $travail_featured->ID, 'travail-card-wide' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $travail_featured ) ); ?>" loading="lazy" />
				<div class="travail-travello-blog-featured__overlay"></div>
				<div class="travail-travello-blog-featured__content">
					<?php $travail_cat = travail_get_primary_term( $travail_featured->ID, 'post' ); ?>
					<?php if ( $travail_cat ) : ?>
						<span class="travail-travello-badge"><?php echo esc_html( $travail_cat->name ); ?></span>
					<?php endif; ?>
					<h3 class="travail-travello-blog-featured__title"><?php echo esc_html( get_the_title( $travail_featured ) ); ?></h3>
					<div class="travail-travello-blog-featured__meta">
						<span><?php echo esc_html( get_the_date( '', $travail_featured ) ); ?></span>
						<span aria-hidden="true">·</span>
						<span>
							<?php
							printf(
								/* translators: %d: estimated reading time in minutes. */
								esc_html( _n( '%d min read', '%d min read', travail_travello_reading_time( $travail_featured->ID ), 'travail' ) ),
								travail_travello_reading_time( $travail_featured->ID )
							);
							?>
						</span>
						<?php $travail_author = get_the_author_meta( 'display_name', $travail_featured->post_author ); ?>
						<?php if ( $travail_author ) : ?>
							<span aria-hidden="true">·</span>
							<span><?php echo esc_html( $travail_author ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</a>

			<div class="travail-travello-blog-small-list">
				<?php foreach ( $travail_small as $travail_post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $travail_post ) ); ?>" class="travail-travello-blog-small">
						<div class="travail-travello-blog-small__img">
							<img src="<?php echo esc_url( travail_get_featured_image_url( $travail_post->ID, 'travail-thumb' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $travail_post ) ); ?>" loading="lazy" />
						</div>
						<div>
							<?php $travail_small_cat = travail_get_primary_term( $travail_post->ID, 'post' ); ?>
							<?php if ( $travail_small_cat ) : ?>
								<p class="travail-travello-blog-small__cat"><?php echo esc_html( $travail_small_cat->name ); ?></p>
							<?php endif; ?>
							<h4 class="travail-travello-blog-small__title"><?php echo esc_html( get_the_title( $travail_post ) ); ?></h4>
							<p class="travail-travello-blog-small__meta">
								<?php echo esc_html( get_the_date( '', $travail_post ) ); ?> ·
								<?php
								printf(
									/* translators: %d: estimated reading time in minutes. */
									esc_html( _n( '%d min read', '%d min read', travail_travello_reading_time( $travail_post->ID ), 'travail' ) ),
									travail_travello_reading_time( $travail_post->ID )
								);
								?>
							</p>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
<?php
wp_reset_postdata();
