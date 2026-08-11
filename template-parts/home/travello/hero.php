<?php
/**
 * Travello homepage — hero.
 *
 * Content comes from Customizer → Travail Theme Options → Travello
 * Homepage (hero copy/image) with defaults matching travello.html
 * exactly; the 3 trailing stats mirror the same "curated marketing
 * number, not a live count" pattern the default homepage's own hero
 * metrics already use (see inc/importer/class-travail-demo-importer.php)
 * rather than pretending a demo-sized dataset is a 12,000-tour catalog.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_eyebrow  = travail_get_option( 'travello_hero_eyebrow', __( 'Travel Beyond Ordinary', 'travail' ) );
$travail_headline = travail_get_option( 'travello_hero_headline', __( 'Explore the world.{break}{emphasis}Create unforgettable memories.{/emphasis}', 'travail' ) );
$travail_sub      = travail_get_option( 'travello_hero_sub', __( 'Discover handpicked tours, extraordinary destinations and experiences designed for curious travelers.', 'travail' ) );
$travail_image    = travail_get_option( 'travello_hero_image', '' );

if ( ! $travail_image ) {
	$travail_image = TRAVAIL_URI . '/assets/images/placeholder-wide.svg';
}

// {break} → <br>, {emphasis}...{/emphasis} → <span class="serif-italic">...</span> — same
// token-replacement approach as the default homepage's hero.php, so headline copy
// stays a single translatable string instead of being split into fragile pieces.
$travail_headline_html = esc_html( $travail_headline );
$travail_headline_html = str_replace( '{break}', '<br />', $travail_headline_html );
$travail_headline_html = preg_replace( '#\{emphasis\}(.*?)\{/emphasis\}#', '<span class="travail-travello-hero__em">$1</span>', $travail_headline_html );

$travail_find_page  = get_page_by_path( 'find' );
$travail_tours_link = $travail_find_page ? get_permalink( $travail_find_page ) : ( post_type_exists( 'ttbm_tour' ) ? get_post_type_archive_link( 'ttbm_tour' ) : '#' );
$travail_dest_page  = get_page_by_path( 'destinations' );
$travail_dest_link  = $travail_dest_page ? get_permalink( $travail_dest_page ) : '#';
?>
<section class="travail-travello-hero">
	<div class="travail-travello-hero__img">
		<img src="<?php echo esc_url( $travail_image ); ?>" alt="" loading="eager" fetchpriority="high" />
		<div class="travail-travello-hero__overlay"></div>

		<div class="travail-travello-hero__content">
			<div class="travail-travello-hero__content-inner">
			<div class="travail-travello-hero__text">
				<?php if ( $travail_eyebrow ) : ?>
					<span class="travail-travello-eyebrow"><?php echo esc_html( $travail_eyebrow ); ?></span>
				<?php endif; ?>

				<h1 class="travail-travello-hero__headline"><?php echo wp_kses( $travail_headline_html, array( 'br' => array(), 'span' => array( 'class' => array() ) ) ); ?></h1>

				<?php if ( $travail_sub ) : ?>
					<p class="travail-travello-hero__sub"><?php echo esc_html( $travail_sub ); ?></p>
				<?php endif; ?>

				<div class="travail-travello-hero__ctas">
					<a href="<?php echo esc_url( $travail_tours_link ); ?>" class="travail-travello-btn-primary">
						<?php esc_html_e( 'Explore tours', 'travail' ); ?> <span aria-hidden="true">→</span>
					</a>
					<a href="<?php echo esc_url( $travail_dest_link ); ?>" class="travail-travello-btn-ghost">
						<?php esc_html_e( 'View destinations', 'travail' ); ?>
					</a>
				</div>
			</div>

			<div class="travail-travello-hero__stats">
				<div class="travail-travello-hero__stat">
					<p><?php echo esc_html( travail_get_option( 'travello_hero_stat_1_value', __( '12K+', 'travail' ) ) ); ?></p>
					<p><?php echo esc_html( travail_get_option( 'travello_hero_stat_1_label', __( 'Tours', 'travail' ) ) ); ?></p>
				</div>
				<div class="travail-travello-hero__stat">
					<p><?php echo esc_html( travail_get_option( 'travello_hero_stat_2_value', __( '180+', 'travail' ) ) ); ?></p>
					<p><?php echo esc_html( travail_get_option( 'travello_hero_stat_2_label', __( 'Destinations', 'travail' ) ) ); ?></p>
				</div>
				<div class="travail-travello-hero__stat">
					<p><?php echo esc_html( travail_get_option( 'travello_hero_stat_3_value', __( '50K+', 'travail' ) ) ); ?></p>
					<p><?php echo esc_html( travail_get_option( 'travello_hero_stat_3_label', __( 'Travelers', 'travail' ) ) ); ?></p>
				</div>
			</div>
			</div>
		</div>
	</div>
</section>
