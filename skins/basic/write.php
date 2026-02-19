<?php
/**
 * Basic skin – write / edit form.
 * Available vars: $board, $settings, $post (null for new), $files (for edit)
 */
defined( 'ABSPATH' ) || exit;

$post     = $post ?? null;
$files    = $files ?? [];
$board_id = (int) $board->id;
$post_id  = $post ? (int) $post->id : 0;
$is_edit  = $post_id > 0;
$user_id  = get_current_user_id();
$page_url = ! empty( $page_url ) ? esc_url_raw( (string) $page_url ) : ( get_permalink() ?: home_url( '/' ) );

$allow_html    = ! empty( $settings['allow_html'] );
$allow_secret  = ! empty( $settings['allow_secret'] );
$allow_notice  = current_user_can( 'manage_options' );
$allow_attach  = ! empty( $settings['allow_attach'] );
$max_files     = (int) ( $settings['max_files'] ?? 5 );
$current_files = count( $files );
$css_vars      = \SBKBoard\Core\Style::build_css_vars( (array) $settings );
$editor_type   = (string) ( $settings['editor_type'] ?? 'textarea' );
$use_wp_editor = ( 'wp_editor' === $editor_type );
// wp_editor에서 미디어 버튼은 관리자만 허용
$media_buttons = $use_wp_editor && current_user_can( 'manage_options' );
?>
<div class="sbkboard-form-wrap" style="<?php echo esc_attr( $css_vars ); ?>">
	<h3><?php echo $is_edit ? esc_html__( '게시글 수정', 'sbk-board' ) : esc_html__( '게시글 작성', 'sbk-board' ); ?></h3>

	<form class="sbkboard-write-form"
	      data-action="sbkboard_post_save"
	      enctype="multipart/form-data">

		<?php wp_nonce_field( 'sbkboard_nonce', 'nonce' ); ?>
		<input type="hidden" name="board_id" value="<?php echo esc_attr( $board_id ); ?>">
		<input type="hidden" name="post_id"  value="<?php echo esc_attr( $post_id ); ?>">
		<input type="hidden" name="page_url" value="<?php echo esc_url( $page_url ); ?>">

		<?php if ( ! is_user_logged_in() ) : ?>
		<div class="sbkboard-form-row sbkboard-form-inline">
			<div style="flex:1;">
				<label><?php esc_html_e( '이름', 'sbk-board' ); ?></label>
				<input type="text" name="author"
				       value="<?php echo $post ? esc_attr( $post->author_name ) : ''; ?>"
				       placeholder="<?php esc_attr_e( '이름 입력', 'sbk-board' ); ?>">
			</div>
			<div style="flex:1;">
				<label><?php esc_html_e( '비밀번호', 'sbk-board' ); ?></label>
				<input type="password" name="password"
				       placeholder="<?php esc_attr_e( '비밀번호 입력', 'sbk-board' ); ?>">
			</div>
		</div>
		<?php endif; ?>

		<div class="sbkboard-form-row">
			<label><?php esc_html_e( '제목', 'sbk-board' ); ?> <span style="color:red;">*</span></label>
			<input type="text" name="subject" required
			       value="<?php echo $post ? esc_attr( $post->subject ) : ''; ?>"
			       placeholder="<?php esc_attr_e( '제목을 입력하세요.', 'sbk-board' ); ?>">
		</div>

		<div class="sbkboard-form-row">
			<label><?php esc_html_e( '내용', 'sbk-board' ); ?> <span style="color:red;">*</span></label>
			<?php if ( $use_wp_editor ) : ?>
				<?php
				$editor_id      = 'sbkboard_content_' . $board_id;
				$editor_content = $post ? $post->content : '';
				wp_editor(
					$editor_content,
					$editor_id,
					[
						'textarea_name' => 'content',
						'media_buttons' => $media_buttons,
						'teeny'         => false,
						'quicktags'     => true,
						'tinymce'       => true,
						'editor_height' => 300,
					]
				);
				?>
			<?php else : ?>
				<textarea name="content" required><?php echo $post ? esc_textarea( $post->content ) : ''; ?></textarea>
			<?php endif; ?>
		</div>

		<?php if ( $allow_secret || $allow_notice ) : ?>
		<div class="sbkboard-form-row sbkboard-form-inline">
			<?php if ( $allow_secret ) : ?>
			<label>
				<input type="checkbox" name="is_secret" value="1"
				       <?php checked( $post && $post->is_secret ); ?>>
				<?php esc_html_e( '비밀글', 'sbk-board' ); ?>
			</label>
			<?php endif; ?>
			<?php if ( $allow_notice ) : ?>
			<label>
				<input type="checkbox" name="is_notice" value="1"
				       <?php checked( $post && $post->is_notice ); ?>>
				<?php esc_html_e( '공지', 'sbk-board' ); ?>
			</label>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php if ( $allow_attach && $current_files < $max_files ) : ?>
		<?php
		$remaining   = $max_files - $current_files;
		$accept_attr = esc_attr( $settings['allowed_exts'] ?? '' );
		?>
		<div class="sbkboard-form-row">
			<label><?php esc_html_e( '파일 첨부', 'sbk-board' ); ?></label>
			<div class="sbkboard-attach-area">
				<?php for ( $i = 0; $i < $remaining; $i++ ) : ?>
				<div class="sbkboard-file-row">
					<input type="file" name="attachments[]"
					       accept="<?php echo $accept_attr; // phpcs:ignore WordPress.Security.EscapeOutput ?>">
				</div>
				<?php endfor; ?>
				<small><?php printf( esc_html__( '최대 %d개', 'sbk-board' ), $remaining ); ?></small>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $files ) ) : ?>
		<div class="sbkboard-form-row">
			<label><?php esc_html_e( '첨부된 파일', 'sbk-board' ); ?></label>
			<?php foreach ( $files as $file ) : ?>
			<div class="sbkboard-file-item" style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
				<span><?php echo esc_html( $file->file_name ); ?></span>
				<button type="button" class="sbkboard-file-delete sbkboard-btn sbkboard-btn-sm sbkboard-btn-danger"
				        data-file-id="<?php echo esc_attr( $file->id ); ?>">
					<?php esc_html_e( '삭제', 'sbk-board' ); ?>
				</button>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<div class="sbkboard-form-actions">
			<a href="<?php echo esc_url( $page_url ); ?>"
			   class="sbkboard-btn sbkboard-btn-secondary sbkboard-btn-cancel">
				<?php esc_html_e( '취소', 'sbk-board' ); ?>
			</a>
			<button type="submit" class="sbkboard-btn sbkboard-btn-primary sbkboard-btn-submit">
				<?php echo $is_edit ? esc_html__( '수정 완료', 'sbk-board' ) : esc_html__( '등록', 'sbk-board' ); ?>
			</button>
		</div>
	</form>
</div>
