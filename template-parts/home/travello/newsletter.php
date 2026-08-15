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

$travail_args = travail_section_args(
	isset( $args ) ? $args : array(),
	array(
		'eyebrow'        => __( 'Stay Inspired', 'travail' ),
		'title'          => __( 'Your next adventure is closer', 'travail' ),
		'title_emphasis' => __( 'than you think.', 'travail' ),
		'subtitle'       => __( 'Get destination inspiration, travel tips and exclusive offers.', 'travail' ),
		'image'          => '',
		'placeholder'    => __( 'Your email address', 'travail' ),
		'button_text'    => __( 'Subscribe', 'travail' ),
	)
);

$travail_image = $travail_args['image'] ? $travail_args['image'] : travail_get_option( 'travello_newsletter_image', '' );
if ( ! $travail_image ) {
	$travail_image = TRAVAIL_URI . '/assets/images/placeholder-wide.svg';
}
?>
<section class="travail-travello-newsletter">
	<img src="<?php echo esc_url( $travail_image ); ?>" alt="" loading="lazy" />
	<div class="travail-travello-newsletter__overlay"></div>
	<div class="travail-travello-newsletter__content">
		<?php if ( $travail_args['eyebrow'] ) : ?>
			<span class="travail-travello-eyebrow"><?php echo esc_html( $travail_args['eyebrow'] ); ?></span>
		<?php endif; ?>
		<?php if ( $travail_args['title'] || $travail_args['title_emphasis'] ) : ?>
			<h2 class="travail-travello-newsletter__title">
				<?php echo esc_html( $travail_args['title'] ); ?>
				<?php if ( $travail_args['title_emphasis'] ) : ?>
					<span class="travail-travello-hero__em"><?php echo esc_html( $travail_args['title_emphasis'] ); ?></span>
				<?php endif; ?>
			</h2>
		<?php endif; ?>
		<?php if ( $travail_args['subtitle'] ) : ?>
			<p class="travail-travello-newsletter__sub"><?php echo esc_html( $travail_args['subtitle'] ); ?></p>
		<?php endif; ?>

		<form class="travail-travello-newsletter__form" id="travello-newsletter-form" data-travello-newsletter-form>
			<?php wp_nonce_field( 'travail_travello_newsletter', 'travello_newsletter_nonce' ); ?>
			<label class="screen-reader-text" for="travello-nl-email"><?php esc_html_e( 'Email address', 'travail' ); ?></label>
			<input type="email" class="travail-travello-newsletter__input" placeholder="<?php echo esc_attr( $travail_args['placeholder'] ); ?>" required id="travello-nl-email" name="email" />
			<button type="submit" class="travail-travello-newsletter__btn"><?php echo esc_html( $travail_args['button_text'] ); ?></button>
		</form>
		<p class="travail-travello-newsletter__status" id="travello-newsletter-status" role="status" aria-live="polite"></p>
	</div>
</section>
