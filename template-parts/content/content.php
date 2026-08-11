<?php
/**
 * Blog card used in index/archive/home loops. For a custom post type
 * that has no more specific content-{post_type}.php, WordPress falls
 * back to this file automatically (get_template_part()'s slug/name
 * pattern), so it stays generic rather than assuming "post".
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_cat = travail_get_primary_term( get_the_ID(), get_post_type() );
?>
<article <?php post_class( 'travail-blog-card' ); ?> id="post-<?php the_ID(); ?>">
	<a href="<?php the_permalink(); ?>" class="travail-blog-card__img" aria-hidden="true" tabindex="-1">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'travail-card-wide', array( 'loading' => 'lazy', 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( TRAVAIL_URI . '/assets/images/placeholder-blog.svg' ); ?>" alt="" loading="lazy" />
		<?php endif; ?>
	</a>
	<div class="travail-blog-card__body">
		<?php if ( $travail_cat ) : ?>
			<p class="travail-blog-card__cat"><?php echo esc_html( $travail_cat->name ); ?></p>
		<?php endif; ?>

		<h3 class="travail-blog-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<div class="travail-blog-card__meta">
			<span><?php echo esc_html( get_the_date() ); ?></span>
			<span class="travail-dot" aria-hidden="true">&middot;</span>
			<span>
				<?php
				/* translators: %s: estimated reading time in minutes. */
				printf( esc_html__( '%s min read', 'travail' ), esc_html( max( 1, (int) round( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) ) ) );
				?>
			</span>
		</div>
	</div>
</article>
