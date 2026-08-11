<?php
/**
 * Small, dependency-free utility functions used throughout the theme.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a theme option saved by the Customizer, with a fallback default.
 *
 * @param string $key     Option key (without the travail_ prefix).
 * @param mixed  $default Default value if not set.
 * @return mixed
 */
function travail_get_option( $key, $default = '' ) {
	$value = get_theme_mod( 'travail_' . $key, $default );

	/**
	 * Filters a Travail theme option value.
	 *
	 * @param mixed  $value   The resolved value.
	 * @param string $key     The option key requested.
	 * @param mixed  $default The default that was passed in.
	 */
	return apply_filters( 'travail_option', $value, $key, $default );
}

/**
 * Echo-safe wrapper for travail_get_option() when the value is plain text.
 *
 * @param string $key     Option key.
 * @param string $default Default value.
 */
function travail_option_esc_html( $key, $default = '' ) {
	echo esc_html( travail_get_option( $key, $default ) );
}

/**
 * Return a resized/placeholder-safe featured image URL for a post.
 *
 * Falls back to a theme placeholder so card layouts never collapse when
 * demo/plugin content has no image assigned yet.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Registered image size.
 * @return string
 */
function travail_get_featured_image_url( $post_id, $size = 'travail-card' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		$url = get_the_post_thumbnail_url( $post_id, $size );
		if ( $url ) {
			return $url;
		}
	}

	return TRAVAIL_URI . '/assets/images/placeholder-tour.svg';
}

/**
 * Trim text to a word count and append an ellipsis, HTML-safe.
 *
 * @param string $text  Raw text (may contain HTML — it will be stripped).
 * @param int    $words Number of words to keep.
 * @return string
 */
function travail_excerpt( $text, $words = 20 ) {
	$text = wp_strip_all_tags( $text );
	return wp_trim_words( $text, $words, '…' );
}

/**
 * Render a set of star icons for a given average rating (0–5, supports halves).
 *
 * @param float $rating Average rating.
 * @return string Escaped HTML.
 */
function travail_get_star_rating_html( $rating ) {
	$rating = max( 0, min( 5, (float) $rating ) );
	$full   = (int) floor( $rating );
	$half   = ( $rating - $full ) >= 0.5;

	ob_start();
	echo '<span class="travail-stars" role="img" aria-label="' . esc_attr(
		sprintf(
			/* translators: %s: numeric rating out of 5 */
			__( 'Rated %s out of 5', 'travail' ),
			number_format_i18n( $rating, 1 )
		)
	) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		if ( $i <= $full ) {
			$state = 'full';
		} elseif ( $half && $i === $full + 1 ) {
			$state = 'half';
		} else {
			$state = 'empty';
		}
		echo '<span class="travail-star travail-star--' . esc_attr( $state ) . '" aria-hidden="true"></span>';
	}
	echo '</span>';
	return ob_get_clean();
}

/**
 * Format a price for display using WooCommerce's formatter when available,
 * otherwise a sane fallback so the theme never depends on WooCommerce.
 *
 * @param float|string $amount Amount to format.
 * @return string HTML-safe formatted price.
 */
function travail_format_price( $amount ) {
	if ( '' === $amount || null === $amount ) {
		return '';
	}

	if ( function_exists( 'wc_price' ) ) {
		return wc_price( $amount ); // phpcs:ignore -- wc_price() already escapes.
	}

	$symbol = apply_filters( 'travail_currency_symbol', '$' );
	return esc_html( $symbol . number_format_i18n( (float) $amount, 2 ) );
}

/**
 * Build a breadcrumb trail as an array of ['label' => ..., 'url' => ...].
 * Kept schema-friendly (BreadcrumbList) but framework-agnostic so it
 * doesn't collide with SEO plugins that already output breadcrumb schema.
 *
 * @return array<int, array{label: string, url: string}>
 */
function travail_get_breadcrumbs() {
	$crumbs = array(
		array(
			'label' => __( 'Home', 'travail' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( is_singular() ) {
		$post_type = get_post_type();
		$archive   = get_post_type_archive_link( $post_type );

		if ( $archive ) {
			$post_type_obj = get_post_type_object( $post_type );
			$crumbs[]      = array(
				'label' => $post_type_obj ? $post_type_obj->labels->name : '',
				'url'   => $archive,
			);
		}

		$primary_term = travail_get_primary_term( get_the_ID(), $post_type );
		if ( $primary_term ) {
			$crumbs[] = array(
				'label' => $primary_term->name,
				'url'   => get_term_link( $primary_term ),
			);
		}

		$crumbs[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$crumbs[] = array(
			'label' => single_term_title( '', false ),
			'url'   => '',
		);
	} elseif ( is_post_type_archive() ) {
		$crumbs[] = array(
			'label' => post_type_archive_title( '', false ),
			'url'   => '',
		);
	} elseif ( is_search() ) {
		$crumbs[] = array(
			'label' => sprintf(
				/* translators: %s: search query */
				__( 'Search results for "%s"', 'travail' ),
				get_search_query()
			),
			'url'   => '',
		);
	} elseif ( is_404() ) {
		$crumbs[] = array(
			'label' => __( '404', 'travail' ),
			'url'   => '',
		);
	} elseif ( is_page() ) {
		$crumbs[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);
	}

	return apply_filters( 'travail_breadcrumbs', $crumbs );
}

/**
 * Get the first non-empty taxonomy term for a post, checking a preferred
 * list of taxonomies (useful for tour category/location breadcrumbs).
 *
 * @param int    $post_id   Post ID.
 * @param string $post_type Post type (used to pick a sensible taxonomy list).
 * @return WP_Term|null
 */
function travail_get_primary_term( $post_id, $post_type = '' ) {
	$post_type = $post_type ? $post_type : get_post_type( $post_id );

	$taxonomies = apply_filters(
		'travail_primary_term_taxonomies',
		array(
			'ttbm_tour' => array( 'ttbm_tour_cat', 'ttbm_tour_location' ),
			'post'      => array( 'category' ),
			'product'   => array( 'product_cat' ),
		)
	);

	if ( empty( $taxonomies[ $post_type ] ) ) {
		return null;
	}

	foreach ( $taxonomies[ $post_type ] as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			return $terms[0];
		}
	}

	return null;
}

/**
 * Safe wrapper around wp_kses_post() for small chunks of rich text coming
 * from theme mods / repeater fields, so widgets never echo raw user HTML
 * without at least the post-content allow-list applied.
 *
 * @param string $html Raw HTML.
 * @return string
 */
function travail_kses( $html ) {
	return wp_kses_post( $html );
}
