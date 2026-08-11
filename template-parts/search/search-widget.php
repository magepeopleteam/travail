<?php
/**
 * Hero search widget: "Where do you want to go?" — matches the
 * wanderly.html reference design exactly (3 fields: Where / When /
 * Guests + a "Search tours" button), while submitting to Tour Booking
 * Manager's real search results page with its real field names and
 * nonce (confirmed by reading inc/TTBM_Filter_Pagination.php /
 * inc/TTBM_Query.php — GET form, action home_url('/find/'), nonce
 * action "ttbm_search_nonce", fields location_filter / people_filter /
 * ttbm_date_start_end_input). This is a purpose-built form rather than
 * an embed of the plugin's own [ttbm-top-search] shortcode specifically
 * so the hero can match the reference pixel-for-pixel; every other
 * search entry point (archive page, Elementor "Tour Search" widget)
 * still uses the plugin's own shortcode.
 *
 * Falls back to a plain site-search field when Tour Booking Manager
 * isn't active, so the widget is never a dead end (Scenario C/E).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_has_tbm = class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_tour_booking_manager_active();
$travail_locations = array();

if ( $travail_has_tbm ) {
	$travail_terms = get_terms(
		array(
			'taxonomy'   => 'ttbm_tour_location',
			'hide_empty' => true,
			'number'     => 200,
		)
	);
	if ( ! is_wp_error( $travail_terms ) ) {
		$travail_locations = $travail_terms;
	}
}

$travail_find_page   = get_page_by_path( 'find' );
$travail_search_url  = $travail_find_page ? get_permalink( $travail_find_page ) : home_url( '/find/' );
?>
<div class="travail-search-widget">
	<h3><?php echo esc_html( travail_get_option( 'search_widget_title', __( 'Where do you want to go?', 'travail' ) ) ); ?></h3>
	<p class="travail-search-widget__hint"><?php echo esc_html( travail_get_option( 'search_widget_subtitle', __( 'Search destinations, tours or experiences', 'travail' ) ) ); ?></p>

	<?php if ( $travail_has_tbm ) : ?>
		<form class="travail-search-fields" method="get" action="<?php echo esc_url( $travail_search_url ); ?>">
			<?php wp_nonce_field( 'ttbm_search_nonce', 'ttbm_search_nonce' ); ?>

			<div class="travail-search-field">
				<span class="travail-search-field__icon" aria-hidden="true">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
				</span>
				<div style="width:100%;">
					<label class="travail-search-field__label" for="travail-search-where"><?php esc_html_e( 'Where', 'travail' ); ?></label>
					<?php if ( ! empty( $travail_locations ) ) : ?>
						<select id="travail-search-where" name="location_filter">
							<option value=""><?php esc_html_e( 'Anywhere', 'travail' ); ?></option>
							<?php foreach ( $travail_locations as $travail_location ) : ?>
								<option value="<?php echo esc_attr( $travail_location->term_id ); ?>"><?php echo esc_html( $travail_location->name ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<input type="text" id="travail-search-where" name="title_filter" placeholder="<?php esc_attr_e( 'Anywhere', 'travail' ); ?>" />
					<?php endif; ?>
				</div>
			</div>

			<div class="travail-search-field">
				<span class="travail-search-field__icon" aria-hidden="true">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
				</span>
				<div style="width:100%;">
					<label class="travail-search-field__label" for="travail-search-when"><?php esc_html_e( 'When', 'travail' ); ?></label>
					<input type="text" id="travail-search-when" name="ttbm_date_start_end_input" placeholder="<?php esc_attr_e( 'Add dates', 'travail' ); ?>" autocomplete="off" />
				</div>
			</div>

			<div class="travail-search-field" style="flex:0 0 auto;min-width:130px;">
				<span class="travail-search-field__icon" aria-hidden="true">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
				</span>
				<div style="width:100%;">
					<label class="travail-search-field__label" for="travail-search-guests"><?php esc_html_e( 'Guests', 'travail' ); ?></label>
					<input type="number" id="travail-search-guests" name="people_filter" min="1" max="20" placeholder="<?php esc_attr_e( '2 Guests', 'travail' ); ?>" />
				</div>
			</div>

			<button type="submit" class="travail-btn-search">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
				<?php esc_html_e( 'Search tours', 'travail' ); ?>
			</button>
		</form>
	<?php else : ?>
		<?php get_search_form(); ?>
	<?php endif; ?>
</div>
