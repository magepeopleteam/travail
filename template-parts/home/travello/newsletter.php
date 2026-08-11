<?php
/**
 * Travello homepage — newsletter signup band.
 *
 * The form posts to a real, nonced WordPress AJAX action
 * (travail_travello_newsletter, see inc/homepage-travello.php) that
 * fires a `travail_newsletter_subscribed` hook rather than faking a
 * client-side-only success message with nothing behind it — a child
 * theme/mu-plugin hooks that action to whatever email provider the site
 * actually uses.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_image = travail_get_option( 'travello_newsletter_image', '' );
if ( ! $travail_image ) {
	$travail_image = TRAVAIL_URI . '/assets/images/placeholder-wide.svg';
}
?>
<section class="travail-travello-newsletter">
	<img src="<?php echo esc_url( $travail_image ); ?>" alt="" loading="lazy" />
	<div class="travail-travello-newsletter__overlay"></div>
	<div class="travail-travello-newsletter__content">
		<span class="travail-travello-eyebrow"><?php esc_html_e( 'Stay Inspired', 'travail' ); ?></span>
		<h2 class="travail-travello-newsletter__title"><?php esc_html_e( 'Your next adventure is closer', 'travail' ); ?> <span class="travail-travello-hero__em"><?php esc_html_e( 'than you think.', 'travail' ); ?></span></h2>
		<p class="travail-travello-newsletter__sub"><?php esc_html_e( 'Get destination inspiration, travel tips and exclusive offers.', 'travail' ); ?></p>

		<form class="travail-travello-newsletter__form" id="travello-newsletter-form" data-travello-newsletter-form>
			<?php wp_nonce_field( 'travail_travello_newsletter', 'travello_newsletter_nonce' ); ?>
			<label class="screen-reader-text" for="travello-nl-email"><?php esc_html_e( 'Email address', 'travail' ); ?></label>
			<input type="email" class="travail-travello-newsletter__input" placeholder="<?php esc_attr_e( 'Your email address', 'travail' ); ?>" required id="travello-nl-email" name="email" />
			<button type="submit" class="travail-travello-newsletter__btn"><?php esc_html_e( 'Subscribe', 'travail' ); ?></button>
		</form>
		<p class="travail-travello-newsletter__status" id="travello-newsletter-status" role="status" aria-live="polite"></p>
	</div>
</section>
