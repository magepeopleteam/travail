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
