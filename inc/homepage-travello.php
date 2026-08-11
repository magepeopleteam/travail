<?php
/**
 * "Travello" homepage — an alternate, selectable homepage design (see
 * Customizer → Travail Theme Options → Homepage) built from
 * travello.html. Kept in its own file (mirroring inc/template-hooks.php's
 * do_action() pattern for the default homepage) so the whole feature is
 * easy to find, and just as easy to remove, without touching any other
 * part of the theme.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the Travello homepage sections in order. Every section is a
 * standalone template-part under template-parts/home/travello/ so a
 * child theme can override any one of them individually.
 */
function travail_travello_homepage_sections() {
	get_template_part( 'template-parts/home/travello/hero' );
	get_template_part( 'template-parts/home/travello/search' );
	get_template_part( 'template-parts/home/travello/categories' );
	get_template_part( 'template-parts/home/travello/destinations' );
	get_template_part( 'template-parts/home/travello/tours' );
	get_template_part( 'template-parts/home/travello/featured-tour' );
	get_template_part( 'template-parts/home/travello/services' );
	get_template_part( 'template-parts/home/travello/why-us' );
	get_template_part( 'template-parts/home/travello/deals' );
	get_template_part( 'template-parts/home/travello/how-it-works' );
	get_template_part( 'template-parts/home/travello/testimonials' );
	get_template_part( 'template-parts/home/travello/blog' );
	get_template_part( 'template-parts/home/travello/newsletter' );
}
add_action( 'travail_travello_homepage', 'travail_travello_homepage_sections' );

/**
 * Emoji + label for each ttbm_tour_activities term shown in the
 * category pill nav. Filterable so a site owner can swap the emoji or
 * add entries for activity terms this list doesn't already know about;
 * any term not listed here still renders, just without a leading emoji.
 *
 * @return array<string, string> Term name (case-insensitive match) => emoji.
 */
function travail_travello_category_icons() {
	return apply_filters(
		'travail_travello_category_icons',
		array(
			'adventure' => '🧗',
			'beach'     => '🏖️',
			'cultural'  => '🏛️',
			'culture'   => '🏛️',
			'hiking'    => '⛰️',
			'wildlife'  => '🦁',
			'luxury'    => '✨',
			'family'    => '👨‍👩‍👧',
			'wellness'  => '🧘',
			'cruise'    => '🚢',
		)
	);
}

/**
 * Estimated reading time for a post, used by
 * template-parts/home/travello/blog.php's post meta lines.
 *
 * @param int $post_id Post ID.
 * @return int Whole minutes, minimum 1.
 */
function travail_travello_reading_time( $post_id ) {
	$content    = get_post_field( 'post_content', $post_id );
	$word_count = str_word_count( wp_strip_all_tags( $content ) );
	return max( 1, (int) ceil( $word_count / 200 ) );
}

/**
 * Newsletter signup — AJAX handler for template-parts/home/travello/newsletter.php.
 *
 * Doesn't call any email-marketing API itself (the base theme has no
 * such integration to duplicate); it validates + nonce-checks the
 * submission and fires `travail_newsletter_subscribed`, which is the
 * real integration point for a child theme/mu-plugin to hand off to
 * Mailchimp, ActiveCampaign, etc. Kept genuinely functional rather than
 * a client-side-only illusion of success.
 */
function travail_travello_handle_newsletter_signup() {
	check_ajax_referer( 'travail_travello_newsletter', 'nonce' );

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! $email || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'travail' ) ), 400 );
	}

	/**
	 * Fires when a visitor submits the Travello newsletter form.
	 *
	 * @param string $email Submitted, validated email address.
	 */
	do_action( 'travail_newsletter_subscribed', $email );

	wp_send_json_success( array( 'message' => __( "You're subscribed — welcome aboard!", 'travail' ) ) );
}
add_action( 'wp_ajax_travail_travello_newsletter', 'travail_travello_handle_newsletter_signup' );
add_action( 'wp_ajax_nopriv_travail_travello_newsletter', 'travail_travello_handle_newsletter_signup' );
