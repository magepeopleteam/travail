<?php
/**
 * The front page template.
 *
 * This is the file that makes "the homepage MUST be completely editable
 * through Elementor" true: when Settings → Reading points the front page
 * at a real WordPress Page, we simply run that page's the_content() —
 * exactly like page.php would — so Elementor's own output (or the block
 * editor's) is rendered untouched, with zero theme markup in the way.
 *
 * Only when no static front page has been assigned yet (a fresh install,
 * "Scenario E" from the spec) do we render the theme's built-in demo-style
 * homepage sections, each of which also exists as a standalone Elementor
 * widget so the user can rebuild the same page visually at any time.
 *
 * A site owner can also opt into the alternate "Travello" homepage design
 * from Customizer → Travail Theme Options → Homepage
 * (travail_get_option('homepage_style')). That check runs first and, when
 * active, takes priority even over an assigned static front page — it's
 * an explicit, single-click choice, so it should always win rather than
 * silently doing nothing because a page happens to be assigned in Reading
 * settings. See inc/homepage-travello.php for what's hooked to it.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$travail_static_front = ( 'page' === get_option( 'show_on_front' ) ) && (int) get_option( 'page_on_front' ) > 0;
?>

<main id="main" class="travail-main" role="main">

	<?php if ( travail_is_travello_home() ) : ?>

		<?php
		/**
		 * Renders the Travello homepage sections.
		 * See inc/homepage-travello.php for what's hooked here.
		 */
		do_action( 'travail_travello_homepage' );
		?>

	<?php elseif ( $travail_static_front && have_posts() ) : ?>

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<?php if ( has_post_thumbnail() && ! has_blocks() ) : ?>
				<div class="travail-post-thumbnail travail-container">
					<?php the_post_thumbnail( 'travail-hero' ); ?>
				</div>
			<?php endif; ?>

			<?php the_content(); ?>
		<?php endwhile; ?>

	<?php else : ?>

		<?php
		/**
		 * Renders the default (non-Elementor) homepage sections.
		 * See inc/template-hooks.php for what's hooked here.
		 */
		do_action( 'travail_default_homepage' );
		?>

	<?php endif; ?>

</main>

<?php
get_footer();
