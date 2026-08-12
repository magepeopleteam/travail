<?php
/**
 * The default page template.
 *
 * Elementor registers its own "Canvas" / "Full Width" page templates that
 * bypass this file entirely when a page explicitly picks one of them, so
 * *those* pages never need special-casing here — the_content() already
 * returns Elementor's rendered output untouched.
 *
 * But a page left on WordPress's own "Default" template (the common case:
 * nobody remembers to change the template dropdown before opening a page
 * in Elementor for the first time) still runs this file, and the
 * `.travail-container.travail-section--tight` wrapper below is meant for
 * ordinary prose content — it caps Elementor's full-width sections to the
 * theme's narrow text column. On the live frontend that's usually masked
 * by Elementor silently falling back to the site Kit's default page
 * template for such pages, but that fallback doesn't reliably apply to
 * the *editor's own* preview iframe, so the exact same page can render
 * full-width on the frontend and squeezed while editing it. Skipping the
 * wrapper whenever the page was built with Elementor — same check already
 * used for the `travail-elementor-page` body class in inc/enqueue.php —
 * removes the discrepancy at the source instead of chasing it in CSS.
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

		$travail_is_elementor_page = class_exists( 'Travail_Plugin_Compatibility' )
			&& Travail_Plugin_Compatibility::is_elementor_active()
			&& 'builder' === get_post_meta( get_the_ID(), '_elementor_edit_mode', true );

		if ( $travail_is_elementor_page ) :
			// Elementor owns 100% of this page's layout — same unwrapped
			// the_content() call front-page.php already uses for a static
			// front page, so an Elementor-built page renders identically
			// in the Elementor editor's preview and on the live frontend,
			// instead of being boxed into the theme's regular-content
			// column width in one context but not the other.
			the_content();
		else :
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
			<?php
		endif;
	endwhile;
	?>

	<?php do_action( 'travail_after_content' ); ?>

</main>

<?php
get_footer();
