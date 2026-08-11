<?php
/**
 * Newsletter CTA band. Submits to whatever email-marketing endpoint the
 * site owner configures via the travail_newsletter_action_url filter —
 * the theme deliberately does not ship its own subscriber storage, to
 * avoid duplicating what a dedicated email plugin/service already does
 * far better (and more compliantly) than a theme could.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_action_url = apply_filters( 'travail_newsletter_action_url', '' );
$travail_image       = travail_get_option( 'newsletter_image', TRAVAIL_URI . '/assets/images/placeholder-wide.svg' );
?>
<section class="travail-newsletter">
	<div class="travail-newsletter__bg">
		<img src="<?php echo esc_url( $travail_image ); ?>" alt="" loading="lazy" />
		<div class="travail-newsletter__overlay"></div>
	</div>
	<div class="travail-newsletter__content travail-container">
		<h2 class="travail-serif">
			<?php echo wp_kses( travail_get_option( 'newsletter_title', __( 'Your next adventure <em>starts here.</em>', 'travail' ) ), array( 'em' => array() ) ); ?>
		</h2>
		<p><?php echo esc_html( travail_get_option( 'newsletter_text', __( 'Get travel inspiration, exclusive deals and new destinations delivered to your inbox.', 'travail' ) ) ); ?></p>

		<?php if ( $travail_action_url ) : ?>
			<form class="travail-newsletter-form" method="post" action="<?php echo esc_url( $travail_action_url ); ?>">
				<label for="travail-newsletter-email" class="screen-reader-text"><?php esc_html_e( 'Your email address', 'travail' ); ?></label>
				<input type="email" id="travail-newsletter-email" name="email" required placeholder="<?php esc_attr_e( 'Your email address', 'travail' ); ?>" />
				<?php wp_nonce_field( 'travail_newsletter_subscribe', 'travail_newsletter_nonce' ); ?>
				<button type="submit" class="travail-btn-coral travail-btn"><?php esc_html_e( 'Subscribe', 'travail' ); ?></button>
			</form>
			<p class="travail-newsletter-note"><?php esc_html_e( 'No spam, ever. Unsubscribe anytime.', 'travail' ); ?></p>
		<?php else : ?>
			<p class="travail-newsletter-note">
				<?php
				if ( current_user_can( 'edit_theme_options' ) ) {
					esc_html_e( 'Connect a newsletter provider (e.g. via the travail_newsletter_action_url filter, or an email-marketing plugin) to activate this form.', 'travail' );
				}
				?>
			</p>
		<?php endif; ?>
	</div>
</section>
