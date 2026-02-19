<?php
/**
 * Basic skin – comments list partial.
 * Available vars: $comments, $post, $board, $settings
 */
defined( 'ABSPATH' ) || exit;

$user_id = get_current_user_id();
$is_admin = current_user_can( 'manage_options' );

foreach ( $comments as $comment ) :
	$is_owner = $user_id && ( $user_id === (int) $comment->user_id );
	$is_reply = (int) $comment->parent_id > 0;
?>
<div class="sbkboard-comment-item <?php echo $is_reply ? 'is-reply' : ''; ?>"
     id="sbkboard-comment-<?php echo esc_attr( $comment->id ); ?>">
	<div class="sbkboard-comment-body" style="flex:1;">
		<div class="sbkboard-comment-meta">
			<strong><?php echo esc_html( $comment->author_name ); ?></strong>
			&nbsp;&middot;&nbsp;
			<?php echo esc_html( mysql2date( 'Y.m.d H:i', $comment->created_at ) ); ?>
		</div>
		<div class="sbkboard-comment-content">
			<?php echo nl2br( esc_html( $comment->content ) ); ?>
		</div>
	</div>
	<?php if ( $is_owner || $is_admin ) : ?>
	<div>
		<button class="sbkboard-comment-delete sbkboard-btn sbkboard-btn-sm sbkboard-btn-danger"
		        data-comment-id="<?php echo esc_attr( $comment->id ); ?>">
			<?php esc_html_e( '삭제', 'sbk-board' ); ?>
		</button>
	</div>
	<?php endif; ?>
</div>
<?php endforeach; ?>
<?php if ( empty( $comments ) ) : ?>
<p style="color:#aaa; font-size:13px; padding:8px 0;"><?php esc_html_e( '첫 번째 댓글을 남겨보세요.', 'sbk-board' ); ?></p>
<?php endif; ?>
