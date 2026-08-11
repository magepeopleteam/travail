<?php
/**
 * Breadcrumb trail — schema-friendly markup that plays nicely alongside
 * SEO plugins (they usually detect and suppress their own breadcrumb
 * block when a theme already renders one, but we don't emit JSON-LD here
 * to avoid ever duplicating an SEO plugin's structured data).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! travail_get_option( 'show_breadcrumbs', true ) ) {
	return;
}

$travail_crumbs = travail_get_breadcrumbs();
if ( count( $travail_crumbs ) < 2 ) {
	return;
}
?>
<nav class="travail-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'travail' ); ?>">
	<ol>
		<?php foreach ( $travail_crumbs as $travail_index => $travail_crumb ) : ?>
			<li <?php echo ( $travail_index === count( $travail_crumbs ) - 1 ) ? 'aria-current="page"' : ''; ?>>
				<?php if ( ! empty( $travail_crumb['url'] ) ) : ?>
					<a href="<?php echo esc_url( $travail_crumb['url'] ); ?>"><?php echo esc_html( $travail_crumb['label'] ); ?></a>
				<?php else : ?>
					<span><?php echo esc_html( $travail_crumb['label'] ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
