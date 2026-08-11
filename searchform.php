<?php
/**
 * Search form template used by get_search_form().
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_unique_id = wp_unique_id( 'travail-search-form-' );
?>
<form role="search" method="get" class="travail-site-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $travail_unique_id ); ?>" class="screen-reader-text"><?php esc_html_e( 'Search for:', 'travail' ); ?></label>
	<div class="travail-search-field" style="background:#fff;">
		<span class="travail-search-field__icon" aria-hidden="true">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
		</span>
		<input type="search" id="<?php echo esc_attr( $travail_unique_id ); ?>" class="search-field" placeholder="<?php echo esc_attr__( 'Search tours, destinations, articles…', 'travail' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	</div>
	<button type="submit" class="travail-btn-search">
		<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'travail' ); ?></span>
	</button>
</form>
