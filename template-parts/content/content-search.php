<?php
/**
 * Search-result card — like content.php but includes an excerpt and
 * shows the post type as a badge so tours/pages/posts are distinguishable
 * in mixed results.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_post_type_obj = get_post_type_object( get_post_type() );
?>
<article <?php post_class( 'travail-blog-card' ); ?> id="post-<?php the_ID(); ?>">
	<a href="<?php the_permalink(); ?>" class="travail-blog-card__img" aria-hidden="true" tabindex="-1">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'travail-card-wide', array( 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( TRAVAIL_URI . '/assets/images/placeholder-blog.svg' ); ?>" alt="" loading="lazy" />
		<?php endif; ?>
	</a>
	<div class="travail-blog-card__body">
		<?php if ( $travail_post_type_obj ) : ?>
			<p class="travail-blog-card__cat"><?php echo esc_html( $travail_post_type_obj->labels->singular_name ); ?></p>
		<?php endif; ?>

		<h3 class="travail-blog-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<p><?php echo esc_html( travail_excerpt( get_the_excerpt(), 18 ) ); ?></p>
	</div>
</article>
