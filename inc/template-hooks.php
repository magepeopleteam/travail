<?php
/**
 * Wires small, optional pieces of markup to the travail_* action hooks so
 * a child theme can add/remove/reorder them with remove_action()/
 * add_action() instead of copy-pasting whole template files.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the "page/post title" block used at the top of generic pages.
 * Tour/product singles render their own headers instead (see
 * template-parts/tour/single-header.php) so this only fires on page.php.
 */
function travail_page_title() {
	if ( is_front_page() ) {
		return;
	}

	/* Theme listing-page header (same white band as Destinations):
	   breadcrumb + italic title. The plugin shortcode below this only
	   renders the grid/filters — it does not own this hero. */
	if ( travail_uses_listing_page_header() ) {
		$travail_subtitle = has_excerpt() ? get_the_excerpt() : '';
		?>
		<header class="travail-archive-header">
			<div class="travail-container">
				<?php get_template_part( 'template-parts/content/breadcrumbs' ); ?>
				<h1 class="travail-page-title"><em class="travail-hero-em"><?php the_title(); ?></em></h1>
				<?php if ( $travail_subtitle ) : ?>
					<p class="travail-page-sub"><?php echo esc_html( $travail_subtitle ); ?></p>
				<?php endif; ?>
			</div>
		</header>
		<?php
		return;
	}
	?>
	<header class="travail-page-header travail-section--tight">
		<div class="travail-container travail-text-center">
			<h1 class="travail-serif"><?php the_title(); ?></h1>
		</div>
	</header>
	<?php
}
add_action( 'travail_before_content', 'travail_page_title', 10 );

/**
 * Output the blog sidebar on singular posts / blog archives only.
 */
function travail_maybe_sidebar() {
	if ( ( is_singular( 'post' ) || is_home() || is_archive() && ! is_post_type_archive( 'ttbm_tour' ) ) && is_active_sidebar( 'sidebar-blog' ) ) {
		get_sidebar();
	}
}

/**
 * Default homepage sections when no Elementor page is assigned to
 * "front-page" — keeps the theme fully functional out of the box
 * (Scenario C/E from the spec) instead of showing a blank page.
 */
function travail_default_homepage_sections() {
	get_template_part( 'template-parts/hero/hero' );
	get_template_part( 'template-parts/search/popular-searches' );
	get_template_part( 'template-parts/destination/destination-grid' );
	get_template_part( 'template-parts/tour/category-explorer' );
	get_template_part( 'template-parts/tour/tour-rail', null, array( 'title' => __( 'Popular experiences', 'travail' ), 'anchor' => 'experiences' ) );
	get_template_part( 'template-parts/tour/featured-journey' );
	get_template_part( 'template-parts/content/why-choose-us' );
	get_template_part( 'template-parts/content/how-it-works' );
	get_template_part( 'template-parts/tour/deals-grid', null, array( 'anchor' => 'deals' ) );
	get_template_part( 'template-parts/testimonial/testimonials' );
	get_template_part( 'template-parts/blog/blog-grid' );
	get_template_part( 'template-parts/content/newsletter' );
}
add_action( 'travail_default_homepage', 'travail_default_homepage_sections' );
