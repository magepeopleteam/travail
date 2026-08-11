<?php
/**
 * Block editor extras: a couple of ready-made patterns built from core
 * blocks only (no custom block types) so they work identically whether
 * or not Elementor is active.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register block patterns.
 */
function travail_register_block_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	register_block_pattern(
		'travail/cta-banner',
		array(
			'title'      => __( 'Travail: CTA Banner', 'travail' ),
			'categories' => array( 'travail' ),
			'content'    => '<!-- wp:group {"style":{"color":{"background":"#16352d"}},"textColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-white-color has-background" style="background-color:#16352d;padding:56px"><!-- wp:heading -->
<h2 class="wp-block-heading travail-serif">' . esc_html__( 'Ready for your next adventure?', 'travail' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Browse handpicked tours and book securely in minutes.', 'travail' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"luminous-vivid-orange"} -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Explore Tours', 'travail' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'travail/section-heading',
		array(
			'title'      => __( 'Travail: Section Heading', 'travail' ),
			'categories' => array( 'travail' ),
			'content'    => '<!-- wp:group {"layout":{"type":"flex","justifyContent":"space-between"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading travail-serif">' . esc_html__( 'Trending destinations', 'travail' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'View all destinations →', 'travail' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->',
		)
	);
}
add_action( 'init', 'travail_register_block_patterns' );

/**
 * Unregister the default "Text" and "Query" pattern categories from
 * cluttering the inserter with generic examples? No — we simply leave
 * core categories alone; nothing to do here beyond registering ours in
 * inc/setup.php (travail_register_pattern_category()).
 */
