<?php
/**
 * The template for displaying comments.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="travail-comments-area">

	<?php if ( have_comments() ) : ?>
		<h2 class="travail-comments-title travail-serif">
			<?php
			$travail_comment_count = get_comments_number();
			if ( '1' === $travail_comment_count ) {
				esc_html_e( '1 Comment', 'travail' );
			} else {
				printf(
					/* translators: %s: number of comments. */
					esc_html( _n( '%s Comment', '%s Comments', $travail_comment_count, 'travail' ) ),
					esc_html( number_format_i18n( $travail_comment_count ) )
				);
			}
			?>
		</h2>

		<ol class="travail-comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 64,
					'callback'    => 'travail_comment_callback',
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => esc_html__( 'Older Comments', 'travail' ),
				'next_text' => esc_html__( 'Newer Comments', 'travail' ),
			)
		);
		?>

	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="travail-no-comments"><?php esc_html_e( 'Comments are closed.', 'travail' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_form'         => 'travail-comment-form',
			'title_reply'        => __( 'Leave a Reply', 'travail' ),
			'comment_field'      => '<p class="travail-field"><label for="comment">' . esc_html__( 'Comment', 'travail' ) . ' *</label><textarea id="comment" name="comment" rows="6" required></textarea></p>',
			'fields'             => array(
				'author' => '<p class="travail-field"><label for="author">' . esc_html__( 'Name', 'travail' ) . ' *</label><input id="author" name="author" type="text" required /></p>',
				'email'  => '<p class="travail-field"><label for="email">' . esc_html__( 'Email', 'travail' ) . ' *</label><input id="email" name="email" type="email" required /></p>',
			),
			'submit_button'      => '<button type="submit" class="travail-btn travail-btn--primary" id="%2$s">%4$s</button>',
			'submit_field'       => '<p class="form-submit">%1$s %2$s</p>',
		)
	);
	?>
</div>
