<?php
/**
 * Single footer column: prefers a populated widget area, falls back to
 * an assigned nav menu, falls back to nothing (never a broken empty box).
 *
 * Expected $args: sidebar (string), menu (string theme_location), fallback_title (string).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_sidebar_id = isset( $args['sidebar'] ) ? $args['sidebar'] : '';
$travail_menu_loc   = isset( $args['menu'] ) ? $args['menu'] : '';
$travail_title      = isset( $args['fallback_title'] ) ? $args['fallback_title'] : '';

if ( $travail_sidebar_id && is_active_sidebar( $travail_sidebar_id ) ) :
	?>
	<div class="travail-footer-col">
		<?php dynamic_sidebar( $travail_sidebar_id ); ?>
	</div>
	<?php
elseif ( $travail_menu_loc && has_nav_menu( $travail_menu_loc ) ) :
	?>
	<div class="travail-footer-col">
		<?php if ( $travail_title ) : ?>
			<h4><?php echo esc_html( $travail_title ); ?></h4>
		<?php endif; ?>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => $travail_menu_loc,
				'container'      => false,
				'items_wrap'     => '<ul>%3$s</ul>',
				'depth'          => 1,
			)
		);
		?>
	</div>
	<?php
endif;
