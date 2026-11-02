<?php
/**
 * Functions that affect front-end markup/output but aren't simple
 * value-formatting helpers (those live in inc/helpers.php).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom excerpt length for archive/blog cards.
 *
 * @param int $length Default length.
 * @return int
 */
function travail_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}
	return 20;
}
add_filter( 'excerpt_length', 'travail_excerpt_length' );

/**
 * Custom excerpt "read more" ellipsis.
 *
 * @param string $more Default string.
 * @return string
 */
function travail_excerpt_more( $more ) {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'travail_excerpt_more' );

/**
 * Numeric pagination markup shared by archive/search/taxonomy templates.
 */
function travail_pagination() {
	$travail_pagination = paginate_links(
		array(
			'prev_text' => '<span aria-hidden="true">&larr;</span><span class="screen-reader-text">' . esc_html__( 'Previous page', 'travail' ) . '</span>',
			'next_text' => '<span aria-hidden="true">&rarr;</span><span class="screen-reader-text">' . esc_html__( 'Next page', 'travail' ) . '</span>',
			'type'      => 'array',
		)
	);

	if ( empty( $travail_pagination ) ) {
		return;
	}

	echo '<nav class="travail-pagination" aria-label="' . esc_attr__( 'Pagination', 'travail' ) . '">';
	foreach ( $travail_pagination as $travail_link ) {
		echo wp_kses_post( $travail_link );
	}
	echo '</nav>';
}

/**
 * Comment callback — used by wp_list_comments() in comments.php.
 *
 * @param WP_Comment $comment Comment object.
 * @param array      $args    Args passed to wp_list_comments().
 * @param int        $depth   Depth.
 */
function travail_comment_callback( $comment, $args, $depth ) {
	$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
	?>
	<<?php echo esc_attr( $tag ); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( 'travail-comment', $comment ); ?>>
		<?php echo get_avatar( $comment, 64 ); ?>
		<div class="travail-comment-body">
			<div class="travail-comment-meta">
				<strong><?php echo esc_html( get_comment_author( $comment ) ); ?></strong>
				<span class="travail-comment-date">
					<a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
						<?php echo esc_html( get_comment_date( '', $comment ) ); ?>
					</a>
				</span>
				<?php if ( '0' === $comment->comment_approved ) : ?>
					<em class="travail-comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'travail' ); ?></em>
				<?php endif; ?>
			</div>
			<div class="travail-comment-content">
				<?php comment_text(); ?>
			</div>
			<?php
			comment_reply_link(
				array_merge(
					$args,
					array(
						'depth'     => $depth,
						'max_depth' => $args['max_depth'],
						'add_below' => 'comment',
					)
				)
			);
			?>
		</div>
	<?php
	// Closing tag intentionally omitted — wp_list_comments() self-closes via the walker.
}

/*
 * Note on wishlist: no custom REST/AJAX route lives here on purpose.
 * Tour Booking Manager's own [ttbm-tour-list]/[travel-list] card markup
 * already ships a fully working wishlist heart button wired to the
 * plugin's real `wp_ajax_ttbm_wishlist_toggle` action — duplicating that
 * with a second, theme-owned endpoint would be exactly the kind of fake
 * integration the project brief warns against. Travail only reskins the
 * plugin's own button (see .ttbm-gc-wishlist in assets/css/tbm-restyle.css)
 * and links to its real "My Wishlist" page — see
 * template-parts/header/actions.php + Travail_Plugin_Compatibility::has_wishlist_page().
 */

/**
 * Adjust the main query on the tour archive/taxonomy pages to respect a
 * "tours per page" theme setting, without touching search/other archives.
 *
 * @param WP_Query $query Main query.
 */
function travail_tour_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$is_tour_context = $query->is_post_type_archive( 'ttbm_tour' ) || $query->is_tax( array( 'ttbm_tour_cat', 'ttbm_tour_location', 'ttbm_tour_tag', 'ttbm_tour_activities' ) );

	if ( $is_tour_context ) {
		$query->set( 'posts_per_page', (int) travail_get_option( 'tours_per_page', 9 ) );
	}
}
add_action( 'pre_get_posts', 'travail_tour_archive_query' );


/**
 * Keep Tour Booking Manager's activity meta in step with the activity taxonomy.
 *
 * The plugin's editor saves activities as post meta (`ttbm_tour_activities`, a list of term ids as strings)
 * and its activity pages / [travel-list activity=""] read that meta. Tours created by the demo import or any
 * other code that only sets the taxonomy have an empty meta, so every activity page showed "Showing 0 of 0".
 * Runs once on init and aligns the meta to the taxonomy; after that, saving a tour only fills EMPTY meta.
 */
function travail_sync_tour_activity_meta() {
	if ( get_option( 'travail_activity_meta_synced_v2' ) || ! post_type_exists( 'ttbm_tour' ) || ! taxonomy_exists( 'ttbm_tour_activities' ) ) {
		return;
	}
	$tour_ids = get_posts(
		array(
			'post_type'      => 'ttbm_tour',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	foreach ( $tour_ids as $tour_id ) {
		// The taxonomy is what visitors see on tour cards and pages, so it wins when the two disagree.
		$term_ids = wp_get_post_terms( $tour_id, 'ttbm_tour_activities', array( 'fields' => 'ids' ) );
		if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
			continue;
		}
		$wanted  = array_map( 'strval', $term_ids );
		$current = array_map( 'strval', (array) get_post_meta( $tour_id, 'ttbm_tour_activities', true ) );
		sort( $wanted );
		sort( $current );
		if ( $wanted !== $current ) {
			update_post_meta( $tour_id, 'ttbm_tour_activities', $wanted );
		}
	}
	update_option( 'travail_activity_meta_synced_v2', 1 );
}
add_action( 'init', 'travail_sync_tour_activity_meta', 30 );

/** Tours that have no activity meta yet pick it up from the taxonomy when they are saved. */
function travail_sync_activity_meta_on_save( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || ! taxonomy_exists( 'ttbm_tour_activities' ) ) {
		return;
	}
	$meta = get_post_meta( $post_id, 'ttbm_tour_activities', true );
	if ( ! empty( $meta ) ) {
		return;
	}
	$term_ids = wp_get_post_terms( $post_id, 'ttbm_tour_activities', array( 'fields' => 'ids' ) );
	if ( ! is_wp_error( $term_ids ) && ! empty( $term_ids ) ) {
		update_post_meta( $post_id, 'ttbm_tour_activities', array_map( 'strval', $term_ids ) );
	}
}
add_action( 'save_post_ttbm_tour', 'travail_sync_activity_meta_on_save', 99 );

/**
 * Site search also finds tours by destination, country, activity, category, organizer and feature names.
 * Core search only looks at titles and text, so a search for "Rangamati" or "Hiking" returned nothing even
 * though tours are located / tagged there.
 *
 * @param string   $search SQL search clause.
 * @param WP_Query $query  Current query.
 * @return string
 */
function travail_search_tours_by_terms( $search, $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() || '' === $search ) {
		return $search;
	}
	$term = trim( (string) $query->get( 's' ) );
	if ( '' === $term || ! post_type_exists( 'ttbm_tour' ) ) {
		return $search;
	}

	global $wpdb;
	$like = '%' . $wpdb->esc_like( $term ) . '%';
	$ids  = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT p.ID FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
			LEFT JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				AND tt.taxonomy IN ( 'ttbm_tour_location', 'ttbm_tour_activities', 'ttbm_tour_cat', 'ttbm_tour_org', 'ttbm_tour_features_list' )
			LEFT JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
			LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
				AND pm.meta_key IN ( 'ttbm_location_name', 'ttbm_country_name', 'ttbm_full_location_name' )
			WHERE p.post_type = 'ttbm_tour' AND p.post_status = 'publish'
			AND ( t.name LIKE %s OR pm.meta_value LIKE %s )
			LIMIT 200",
			$like,
			$like
		)
	);
	if ( empty( $ids ) ) {
		return $search;
	}

	// Keep core's own conditions (title/content/excerpt + password rule) and OR the matching tours in.
	$inner = preg_replace( '/^\s*AND\s*/i', '', $search );
	return ' AND ( ' . $wpdb->posts . '.ID IN (' . implode( ',', array_map( 'absint', $ids ) ) . ') OR ( ' . $inner . ' ) ) ';
}
add_filter( 'posts_search', 'travail_search_tours_by_terms', 20, 2 );
