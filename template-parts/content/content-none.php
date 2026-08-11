<?php
/**
 * Empty state shown when a loop has no posts (archive/search/blog).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="travail-empty-state">
	<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>

	<?php if ( is_search() ) : ?>
		<h2 class="travail-serif"><?php esc_html_e( 'Nothing found', 'travail' ); ?></h2>
		<p><?php esc_html_e( 'Nothing matched your search. Try different keywords.', 'travail' ); ?></p>
		<?php get_search_form(); ?>
	<?php else : ?>
		<h2 class="travail-serif"><?php esc_html_e( 'No posts found', 'travail' ); ?></h2>
		<p><?php esc_html_e( 'It seems we can’t find what you’re looking for.', 'travail' ); ?></p>
	<?php endif; ?>
</div>
