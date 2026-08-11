<?php
/**
 * Travello homepage — hero search panel (tabs + Where/When/Travelers +
 * Search button), overlapping the bottom of the hero image.
 *
 * Submits to the exact same Tour Booking Manager search endpoint and
 * real field names as template-parts/search/search-widget.php
 * (location_filter / ttbm_date_start_end_input / people_filter, GET to
 * the "find" page, ttbm_search_nonce) — see that file's docblock for
 * how those were confirmed against the plugin's own query/pagination
 * classes. The destination field is a real <select> of actual
 * ttbm_tour_location terms rather than a fake JS-only dropdown of
 * hardcoded place names, since real backing data already exists; the
 * date/travelers fields stay real inputs that a small calendar/counter
 * popup (assets/js/travello.js) merely writes formatted values into, so
 * the form keeps working even with JavaScript disabled. The 5 tabs
 * switch visually only — Destinations/Activities/Hotels/Transport have
 * no separate search backend in this plugin stack, so every tab submits
 * the same tour search rather than 404ing into a fake result page.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_has_tbm  = class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_tour_booking_manager_active();
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

$travail_find_page  = get_page_by_path( 'find' );
$travail_search_url = $travail_find_page ? get_permalink( $travail_find_page ) : home_url( '/find/' );

$travail_tabs = apply_filters(
	'travail_travello_search_tabs',
	array(
		'tours'        => __( 'Tours', 'travail' ),
		'destinations' => __( 'Destinations', 'travail' ),
		'activities'   => __( 'Activities', 'travail' ),
		'hotels'       => __( 'Hotels', 'travail' ),
		'transport'    => __( 'Transport', 'travail' ),
	)
);
?>
<div class="travail-travello-search-wrap">
	<div class="travail-travello-search-panel">

		<?php if ( count( $travail_tabs ) > 1 ) : ?>
			<?php
			/*
			 * Not a true ARIA tablist: every tab submits the same
			 * underlying tour search (see the file docblock for why), so
			 * there's no separate tabpanel per tab for aria-controls to
			 * point at. A group of pressed-state toggle buttons is the
			 * accurate ARIA pattern for "these visually switch which
			 * search context is active" without implying panel content
			 * that doesn't exist.
			 */
			?>
			<div class="travail-travello-search-tabs" data-travello-search-tabs>
				<?php foreach ( $travail_tabs as $travail_tab_key => $travail_tab_label ) : ?>
					<button type="button" class="travail-travello-search-tab<?php echo 'tours' === $travail_tab_key ? ' is-active' : ''; ?>" data-tab="<?php echo esc_attr( $travail_tab_key ); ?>" aria-pressed="<?php echo 'tours' === $travail_tab_key ? 'true' : 'false'; ?>">
						<?php echo esc_html( $travail_tab_label ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $travail_has_tbm ) : ?>
			<form class="travail-travello-search-fields" method="get" action="<?php echo esc_url( $travail_search_url ); ?>">
				<?php wp_nonce_field( 'ttbm_search_nonce', 'ttbm_search_nonce' ); ?>

				<div class="travail-travello-field-group" id="travello-dest-group">
					<label class="travail-travello-field-label" for="travello-dest-value"><?php esc_html_e( 'Where to?', 'travail' ); ?></label>
					<div class="travail-travello-field-input" id="travello-dest-input" data-travello-popup-trigger="travello-dest-dropdown">
						<svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 2a6 6 0 016 6c0 5-6 10-6 10S4 13 4 8a6 6 0 016-6Z"/><circle cx="10" cy="8" r="2"/></svg>
						<input type="text" id="travello-dest-value" placeholder="<?php esc_attr_e( 'Search destination', 'travail' ); ?>" autocomplete="off" data-travello-dest-typed />
						<input type="hidden" name="location_filter" id="travello-dest-filter" value="" />
						<input type="hidden" name="title_filter" id="travello-dest-title-filter" value="" />
					</div>
					<?php if ( ! empty( $travail_locations ) ) : ?>
						<div class="travail-travello-field-dropdown" id="travello-dest-dropdown">
							<div class="travail-travello-dropdown-section">
								<p class="travail-travello-dropdown-label"><?php esc_html_e( 'Popular Destinations', 'travail' ); ?></p>
								<?php
								$travail_popular_locations = get_terms(
									array(
										'taxonomy'   => 'ttbm_tour_location',
										'hide_empty' => true,
										'orderby'    => 'count',
										'order'      => 'DESC',
										'number'     => 5,
									)
								);
								$travail_popular_locations = is_wp_error( $travail_popular_locations ) ? array() : $travail_popular_locations;
								foreach ( $travail_popular_locations as $travail_location ) :
									?>
									<button type="button" class="travail-travello-dropdown-item" data-dest-id="<?php echo esc_attr( $travail_location->term_id ); ?>" data-dest-name="<?php echo esc_attr( $travail_location->name ); ?>">
										<span class="travail-travello-dropdown-icon" aria-hidden="true">✈</span><?php echo esc_html( $travail_location->name ); ?>
									</button>
								<?php endforeach; ?>
							</div>
							<div class="travail-travello-dropdown-section" id="travello-dest-recent-section" hidden>
								<hr class="travail-travello-dropdown-divider" />
								<p class="travail-travello-dropdown-label"><?php esc_html_e( 'Recently Searched', 'travail' ); ?></p>
								<div id="travello-dest-recent-list"></div>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<div class="travail-travello-field-group" id="travello-date-group">
					<label class="travail-travello-field-label" for="travello-date"><?php esc_html_e( 'When?', 'travail' ); ?></label>
					<div class="travail-travello-field-input" id="travello-date-input" data-travello-popup-trigger="travello-cal-popup">
						<svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><rect x="2" y="4" width="16" height="14" rx="2"/><path d="M6 2v4M14 2v4M2 9h16"/></svg>
						<input type="text" id="travello-date" name="ttbm_date_start_end_input" placeholder="<?php esc_attr_e( 'Select date', 'travail' ); ?>" autocomplete="off" readonly />
					</div>
					<div class="travail-travello-calendar-popup" id="travello-cal-popup" data-travello-calendar-for="travello-date"></div>
				</div>

				<div class="travail-travello-field-group" id="travello-travelers-group">
					<label class="travail-travello-field-label" for="travello-travelers-display"><?php esc_html_e( 'Travelers', 'travail' ); ?></label>
					<div class="travail-travello-field-input" id="travello-travelers-input" data-travello-popup-trigger="travello-travelers-popup">
						<svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="10" cy="7" r="3.5"/><path d="M2.5 18c0-4 3.4-7 7.5-7s7.5 3 7.5 7"/></svg>
						<input type="text" id="travello-travelers-display" value="<?php esc_attr_e( '2 Adults', 'travail' ); ?>" readonly />
						<input type="hidden" name="people_filter" id="travello-travelers-count" value="2" />
					</div>
					<div class="travail-travello-travelers-popup" id="travello-travelers-popup">
						<?php
						$travail_traveler_rows = array(
							'adults'   => array( __( 'Adults', 'travail' ), __( 'Ages 13+', 'travail' ), 2 ),
							'children' => array( __( 'Children', 'travail' ), __( 'Ages 2–12', 'travail' ), 0 ),
							'infants'  => array( __( 'Infants', 'travail' ), __( 'Under 2', 'travail' ), 0 ),
						);
						foreach ( $travail_traveler_rows as $travail_type => $travail_row ) :
							?>
							<div class="travail-travello-traveler-row">
								<div>
									<p><?php echo esc_html( $travail_row[0] ); ?></p>
									<p><?php echo esc_html( $travail_row[1] ); ?></p>
								</div>
								<div class="travail-travello-counter">
									<button type="button" class="travail-travello-counter-btn" data-type="<?php echo esc_attr( $travail_type ); ?>" data-action="dec" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: traveler type, e.g. Adults. */ __( 'Decrease %s', 'travail' ), $travail_row[0] ) ); ?>">−</button>
									<span class="travail-travello-counter-val" data-type-val="<?php echo esc_attr( $travail_type ); ?>"><?php echo esc_html( $travail_row[2] ); ?></span>
									<button type="button" class="travail-travello-counter-btn" data-type="<?php echo esc_attr( $travail_type ); ?>" data-action="inc" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: traveler type, e.g. Adults. */ __( 'Increase %s', 'travail' ), $travail_row[0] ) ); ?>">+</button>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<button type="submit" class="travail-travello-search-btn">
					<svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="8.5" cy="8.5" r="5.5"/><path d="M14.5 14.5 18 18"/></svg>
					<?php esc_html_e( 'Search', 'travail' ); ?>
				</button>
			</form>
		<?php else : ?>
			<div class="travail-travello-search-fields">
				<?php get_search_form(); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
