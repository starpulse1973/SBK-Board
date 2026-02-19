<?php
/**
 * Basic skin – password prompt for secret posts.
 * Available vars: $post, $board, $settings
 */
defined( 'ABSPATH' ) || exit;
$css_vars = \SBKBoard\Core\Style::build_css_vars( (array) $settings );
?>
<div class="sbkboard-password-form-wrap" style="<?php echo esc_attr( $css_vars ); ?>">
	<form class="sbkboard-password-form"
	      data-post-id="<?php echo esc_attr( $post->id ); ?>"
	      data-board-id="<?php echo esc_attr( $board->id ); ?>">
		<p><?php esc_html_e( '이 글은 비밀글입니다.', 'sbk-board' ); ?></p>
		<p><?php esc_html_e( '비밀번호를 입력하세요.', 'sbk-board' ); ?></p>
		<input type="password" name="post_password"
		       placeholder="<?php esc_attr_e( '비밀번호', 'sbk-board' ); ?>" autocomplete="off">
		<button type="submit" class="sbkboard-btn sbkboard-btn-primary" style="width:100%; margin-top:8px;">
			<?php esc_html_e( '확인', 'sbk-board' ); ?>
		</button>
	</form>
</div>
