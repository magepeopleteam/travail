<?php
/**
 * The header for our theme.
 *
 * Displays the <head> tag, the announcement bar, and the primary site
 * header/navigation. Everything below <body> is filterable via the
 * travail_before_header / travail_after_header action hooks so a child
 * theme can inject markup without copying this whole file.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$travail_header_style = travail_get_option( 'header_style', 'transparent' ); // transparent | solid | sticky.
$travail_header_class = 'travail-header';
if ( 'solid' === $travail_header_style ) {
	$travail_header_class .= ' travail-header--solid';
} elseif ( ! is_front_page() ) {
	// Only the front page has a tall hero the transparent header can sit on;
	// every other page gets a solid header by default so text stays legible.
	$travail_header_class .= ' travail-header--solid';
}

$travail_is_travello = travail_is_travello_home();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="travail-skip-link screen-reader-text" href="#travail-content"><?php esc_html_e( 'Skip to content', 'travail' ); ?></a>

<?php
/**
 * Fires before the site header markup.
 */
do_action( 'travail_before_header' );

$travail_announcement = travail_get_option( 'announcement_text', '' );
if ( $travail_announcement ) :
	?>
	<div class="travail-announcement-bar" role="note">
		<?php echo wp_kses_post( $travail_announcement ); ?>
		<?php
		$travail_announcement_url = travail_get_option( 'announcement_url', '' );
		if ( $travail_announcement_url ) :
			?>
			<a href="<?php echo esc_url( $travail_announcement_url ); ?>"><?php esc_html_e( 'Learn more', 'travail' ); ?></a>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php if ( $travail_is_travello ) : ?>

	<?php get_template_part( 'template-parts/home/travello/header' ); ?>

<?php else : ?>

	<header id="masthead" class="<?php echo esc_attr( $travail_header_class ); ?>" data-travail-header data-sticky="<?php echo esc_attr( 'sticky' === $travail_header_style || 'transparent' === $travail_header_style ? '1' : '0' ); ?>">
		<div class="travail-container">
			<div class="travail-header-inner">

				<?php get_template_part( 'template-parts/header/logo' ); ?>

				<nav class="travail-nav" aria-label="<?php esc_attr_e( 'Primary', 'travail' ); ?>">
					<?php
					if ( has_nav_menu( 'primary' ) ) {
						wp_nav_menu(
							array(
								'theme_location' => 'primary',
								'container'      => false,
								'items_wrap'     => '<ul id="primary-menu">%3$s</ul>',
								'depth'          => 2,
							)
						);
					} else {
						echo '<ul id="primary-menu">';
						if ( current_user_can( 'edit_theme_options' ) ) {
							echo '<li><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Assign a Primary Menu', 'travail' ) . '</a></li>';
						}
						echo '</ul>';
					}
					?>
				</nav>

				<div class="travail-header-actions">
					<?php get_template_part( 'template-parts/header/actions' ); ?>
				</div>

				<button class="travail-menu-toggle travail-icon-btn" id="travail-menu-toggle" aria-expanded="false" aria-controls="travail-mobile-menu" aria-label="<?php esc_attr_e( 'Open menu', 'travail' ); ?>">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" x2="21" y1="6" y2="6"/><line x1="3" x2="21" y1="12" y2="12"/><line x1="3" x2="21" y1="18" y2="18"/></svg>
				</button>
			</div>
		</div>

		<div class="travail-mobile-menu" id="travail-mobile-menu">
			<?php
			if ( has_nav_menu( 'mobile' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'mobile',
						'container'      => false,
						'items_wrap'     => '%3$s',
						'depth'          => 2,
					)
				);
			} elseif ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'items_wrap'     => '%3$s',
						'depth'          => 2,
					)
				);
			}
			?>
			<div class="travail-mobile-actions">
				<?php get_template_part( 'template-parts/header/mobile-actions' ); ?>
			</div>
		</div>
	</header>

<?php endif; ?>

<?php
/**
 * Fires after the site header markup.
 */
do_action( 'travail_after_header' );
?>

<?php
/**
 * Tour archive, the Destinations page template, and the plugin's "/find/"
 * search-results page all render their own breadcrumb inside
 * .travail-archive-header (matching the reference design's page-hero, which
 * has the breadcrumb as part of the same white hero block rather than a
 * separate strip above it) — skip the global one here so it isn't shown
 * twice. See travail_page_title() in inc/template-hooks.php for the /find/
 * case specifically.
 */
if ( ! is_front_page() && ! is_post_type_archive( 'ttbm_tour' ) && ! is_page_template( 'templates/pages/page-destinations.php' ) && ! is_page( 'find' ) ) :
	?>
	<div class="travail-container">
		<?php get_template_part( 'template-parts/content/breadcrumbs' ); ?>
	</div>
<?php endif; ?>

<div id="travail-content" class="travail-site-content">
