<?php
/**
 * The sidebar containing the "Blog Sidebar" widget area.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'sidebar-blog' ) ) {
	return;
}
?>
<aside id="secondary" class="travail-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Blog sidebar', 'travail' ); ?>">
	<?php dynamic_sidebar( 'sidebar-blog' ); ?>
</aside>
