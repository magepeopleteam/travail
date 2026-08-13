<?php
/**
 * Site logo / site title fallback.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="travail-logo" rel="home">
	<?php if ( has_custom_logo() ) : ?>
		<?php the_custom_logo(); ?>
	<?php else : ?>
		<span class="travail-logo-icon" aria-hidden="true">
			<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="15" style="fill:var(--travail-color-primary)"/><path d="M16 6 L22 20 L16 17 L10 20 Z" style="fill:var(--travail-color-bg,#F7F7F4)" opacity="0.9"/><circle cx="16" cy="22" r="2" style="fill:var(--travail-color-accent)"/></svg>
		</span>
		<span><?php bloginfo( 'name' ); ?></span>
	<?php endif; ?>
</a>
