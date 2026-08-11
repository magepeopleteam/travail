<?php
/**
 * View: Travail Homepages.
 *
 * Expects $designs, $homepage_ids, $active_id, $elementor_active from
 * Travail_Admin::render_homepages().
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap travail-admin-wrap">
	<div class="travail-admin-header">
		<h1><?php esc_html_e( 'Homepages', 'travail' ); ?></h1>
		<p><?php esc_html_e( 'Pick which homepage design is live on your site, then edit any section of it visually in Elementor.', 'travail' ); ?></p>
	</div>

	<?php if ( ! $elementor_active ) : ?>
		<div class="notice notice-warning inline">
			<p>
				<?php esc_html_e( 'Elementor is not active — install and activate it to build and edit these homepages visually.', 'travail' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=travail-plugins' ) ); ?>"><?php esc_html_e( 'Recommended Plugins →', 'travail' ); ?></a>
			</p>
		</div>
	<?php elseif ( empty( $homepage_ids ) ) : ?>
		<div class="notice notice-info inline">
			<p>
				<?php esc_html_e( 'No homepage pages yet — run Demo Import once to generate two ready-made, fully editable homepage designs.', 'travail' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=travail-demo-import' ) ); ?>"><?php esc_html_e( 'Run Demo Import →', 'travail' ); ?></a>
			</p>
		</div>
	<?php endif; ?>

	<div class="travail-homepage-grid">
		<?php
		foreach ( $designs as $design_key => $design ) :
			$page_id   = isset( $homepage_ids[ $design_key ] ) ? (int) $homepage_ids[ $design_key ] : 0;
			$exists    = $page_id && get_post( $page_id );
			$is_active = $exists && $page_id === $active_id;
			?>
			<div class="travail-homepage-card<?php echo $is_active ? ' is-active' : ''; ?>">
				<?php if ( $exists ) : ?>
					<div class="travail-homepage-card__preview">
						<iframe src="<?php echo esc_url( get_permalink( $page_id ) ); ?>" loading="lazy" title="<?php echo esc_attr( $design['title'] ); ?>"></iframe>
					</div>
				<?php else : ?>
					<div class="travail-homepage-card__preview travail-homepage-card__preview--empty">
						<span class="dashicons dashicons-admin-appearance" aria-hidden="true"></span>
					</div>
				<?php endif; ?>

				<div class="travail-homepage-card__body">
					<div class="travail-homepage-card__title-row">
						<h2><?php echo esc_html( $design['title'] ); ?></h2>
						<?php if ( $is_active ) : ?>
							<span class="travail-homepage-badge"><?php esc_html_e( 'Active', 'travail' ); ?></span>
						<?php endif; ?>
					</div>
					<p><?php echo esc_html( $design['description'] ); ?></p>

					<div class="travail-homepage-card__actions">
						<?php if ( $exists ) : ?>
							<button
								type="button"
								class="button button-primary travail-set-homepage"
								data-page-id="<?php echo esc_attr( $page_id ); ?>"
								<?php disabled( $is_active ); ?>
							>
								<?php echo $is_active ? esc_html__( 'Currently Active', 'travail' ) : esc_html__( 'Set as Homepage', 'travail' ); ?>
							</button>

							<?php if ( $elementor_active ) : ?>
								<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $page_id . '&action=elementor' ) ); ?>" class="button" target="_blank" rel="noopener">
									<?php esc_html_e( 'Edit with Elementor', 'travail' ); ?>
								</a>
							<?php endif; ?>

							<a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>" class="button" target="_blank" rel="noopener">
								<?php esc_html_e( 'View', 'travail' ); ?>
							</a>
						<?php else : ?>
							<button type="button" class="button" disabled>
								<?php esc_html_e( 'Not generated yet', 'travail' ); ?>
							</button>
						<?php endif; ?>
					</div>
					<p class="travail-homepage-card__message"></p>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
