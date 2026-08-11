<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="travail-main travail-section" role="main">
	<div class="travail-container">
		<div class="travail-empty-state">
			<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
			<h1 class="travail-serif" style="font-size:32px;margin-bottom:12px;"><?php esc_html_e( "Looks like you've wandered off the map", 'travail' ); ?></h1>
			<p style="margin-bottom:32px;"><?php esc_html_e( "We couldn't find the page you were looking for. It may have been moved or no longer exists.", 'travail' ); ?></p>

			<div style="max-width:480px;margin:0 auto 32px;">
				<?php get_search_form(); ?>
			</div>

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="travail-btn travail-btn--primary"><?php esc_html_e( 'Back to Home', 'travail' ); ?></a>

			<?php if ( class_exists( 'Travail_Plugin_Compatibility' ) && Travail_Plugin_Compatibility::is_tour_booking_manager_active() ) : ?>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'ttbm_tour' ) ); ?>" class="travail-btn travail-btn--outline" style="margin-inline-start:12px;"><?php esc_html_e( 'Browse Tours', 'travail' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</main>

<?php
get_footer();
