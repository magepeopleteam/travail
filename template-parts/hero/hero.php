<?php
/**
 * Homepage hero: background image/video, heading, sub-copy, search
 * widget and trust metrics. All content comes from Customizer theme
 * mods so it's editable without touching code, and the same markup is
 * reproduced by the "Travail Hero" Elementor widget (see
 * elementor/widgets/class-hero-widget.php) for users who prefer to
 * rebuild the homepage visually.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_image    = travail_get_option( 'hero_image', TRAVAIL_URI . '/assets/images/placeholder-hero.svg' );
$travail_eyebrow  = travail_get_option( 'hero_eyebrow', __( 'Explore · Dream · Discover', 'travail' ) );
$travail_title    = travail_get_option( 'hero_title', __( 'Discover your{break}next {emphasis}', 'travail' ) );
$travail_emphasis = travail_get_option( 'hero_emphasis', __( 'adventure', 'travail' ) );
$travail_sub      = travail_get_option( 'hero_subtitle', __( 'Curated trips. Extraordinary places. Unforgettable memories.', 'travail' ) );

// "{emphasis}"/"{break}" are tiny author-friendly placeholder tokens so the
// italic accent word and the line break can sit anywhere in the title
// without storing raw HTML in the theme_mod value.
$travail_title_html = esc_html( $travail_title );
$travail_title_html = str_replace( '{break}', '<br />', $travail_title_html );
if ( false !== strpos( $travail_title_html, '{emphasis}' ) ) {
	$travail_title_html = str_replace( '{emphasis}', '<em>' . esc_html( $travail_emphasis ) . '</em>', $travail_title_html );
} else {
	$travail_title_html .= ' <em>' . esc_html( $travail_emphasis ) . '</em>';
}
?>
<section class="travail-hero travail-hero--image-height">
	<img class="travail-hero__media" src="<?php echo esc_url( $travail_image ); ?>" alt="" fetchpriority="high" />
	<div class="travail-hero__overlay"></div>

	<div class="travail-hero__content travail-container">
		<div class="travail-hero__inner">

			<?php if ( $travail_eyebrow ) : ?>
				<p class="travail-eyebrow travail-hero__eyebrow" style="color:rgba(255,255,255,.7);"><?php echo esc_html( $travail_eyebrow ); ?></p>
			<?php endif; ?>

			<h1 class="travail-hero__title travail-serif">
				<?php echo wp_kses( $travail_title_html, array( 'em' => array(), 'br' => array() ) ); ?>
			</h1>

			<?php if ( $travail_sub ) : ?>
				<p class="travail-hero__sub"><?php echo esc_html( $travail_sub ); ?></p>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/search/search-widget' ); ?>

			<?php get_template_part( 'template-parts/hero/trust-metrics' ); ?>

		</div>
	</div>
</section>
