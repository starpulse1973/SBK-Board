<?php
/**
 * Basic skin – post view.
 * Available vars: $post, $board, $settings, $files
 */
defined( 'ABSPATH' ) || exit;

use SBKBoard\DB\CommentRepository;

$page_url    = ! empty( $page_url ) ? esc_url_raw( (string) $page_url ) : ( get_permalink() ?: home_url( '/' ) );
$board_url   = add_query_arg( 'sbk_board', $board->id, $page_url );
$user_id     = get_current_user_id();
$is_owner    = $user_id && ( $user_id === (int) $post->user_id );
$is_admin    = current_user_can( 'manage_options' );
$show_votes  = ! empty( $settings['show_votes'] );
$allow_comments = ! isset( $settings['allow_comments'] ) || ! empty( $settings['allow_comments'] );
$editor_type = (string) ( $settings['editor_type'] ?? 'textarea' );
$allow_html  = ( 'wp_editor' === $editor_type ) || ! empty( $settings['allow_html'] );
$css_vars = \SBKBoard\Core\Style::build_css_vars( (array) $settings );
$date_format = (string) ( $settings['date_format'] ?? 'Y.m.d' );

$comments = $allow_comments ? CommentRepository::get_by_post( $post->id ) : [];
?>

<div class="sbkboard-post-wrap" data-post-id="<?php echo esc_attr( $post->id ); ?>" style="<?php echo esc_attr( $css_vars ); ?>">

	<!-- Header -->
	<div class="sbkboard-post-header">
		<h2 class="sbkboard-post-title">
			<?php if ( $post->is_notice ) : ?>
				<span class="sbkboard-notice-badge"><?php esc_html_e( '공지', 'sbk-board' ); ?></span>
			<?php endif; ?>
			<?php echo esc_html( $post->subject ); ?>
		</h2>
		<div class="sbkboard-post-meta">
			<span><?php echo esc_html( $post->author_name ); ?></span>
			<span><?php echo esc_html( mysql2date( $date_format . ' H:i', $post->created_at ) ); ?></span>
			<?php if ( ! empty( $settings['show_views'] ) ) : ?>
				<span><?php echo esc_html__( '조회', 'sbk-board' ); ?> <?php echo esc_html( number_format( $post->view_count ) ); ?></span>
			<?php endif; ?>
		</div>
	</div>

	<!-- Content -->
	<div class="sbkboard-post-content">
		<?php
		$content = $post->content;
		// If HTML is not allowed, convert newlines to <br>
		if ( ! $allow_html ) {
			echo nl2br( esc_html( $content ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		} else {
			echo wp_kses_post( $content );
		}
		?>
	</div>

	<!-- Attachments -->
	<?php if ( ! empty( $files ) ) : ?>
	<div class="sbkboard-attachments">
		<strong><?php esc_html_e( '첨부파일', 'sbk-board' ); ?></strong>
		<ul>
			<?php foreach ( $files as $file ) : ?>
			<li>
				<a href="<?php echo esc_url( add_query_arg( [
					'action'   => 'sbkboard_file_download',
					'file_id'  => $file->id,
					'nonce'    => wp_create_nonce( 'sbkboard_nonce' ),
				], admin_url( 'admin-ajax.php' ) ) ); ?>">
					<?php echo esc_html( $file->file_name ); ?>
				</a>
				<small>(<?php echo esc_html( size_format( $file->file_size ) ); ?>)</small>
				<?php if ( $is_owner || $is_admin ) : ?>
					<button class="sbkboard-file-delete sbkboard-btn sbkboard-btn-sm sbkboard-btn-danger"
					        data-file-id="<?php echo esc_attr( $file->id ); ?>">
						<?php esc_html_e( '삭제', 'sbk-board' ); ?>
					</button>
				<?php endif; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>

	<!-- Vote -->
	<?php if ( $show_votes ) : ?>
	<div class="sbkboard-vote-bar">
		<button class="sbkboard-vote-btn" data-post-id="<?php echo esc_attr( $post->id ); ?>" data-type="up">
			▲ <?php esc_html_e( '추천', 'sbk-board' ); ?>
		</button>
		<span class="sbkboard-vote-count"><?php echo esc_html( (int)$post->vote_up - (int)$post->vote_down ); ?></span>
		<?php if ( ! empty( $settings['allow_downvote'] ) ) : ?>
		<button class="sbkboard-vote-btn" data-post-id="<?php echo esc_attr( $post->id ); ?>" data-type="down">
			▼ <?php esc_html_e( '반대', 'sbk-board' ); ?>
		</button>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<!-- Actions -->
	<div class="sbkboard-post-actions">
		<a href="<?php echo esc_url( $board_url ); ?>" class="sbkboard-btn sbkboard-btn-secondary sbkboard-btn-list">
			<?php esc_html_e( '목록', 'sbk-board' ); ?>
		</a>
		<?php if ( $is_owner || $is_admin ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'sbk_edit', $post->id, $page_url ) ); ?>"
			   class="sbkboard-btn sbkboard-btn-secondary sbkboard-btn-edit">
				<?php esc_html_e( '수정', 'sbk-board' ); ?>
			</a>
			<button class="sbkboard-btn sbkboard-btn-danger sbkboard-post-delete sbkboard-btn-delete"
			        data-post-id="<?php echo esc_attr( $post->id ); ?>">
				<?php esc_html_e( '삭제', 'sbk-board' ); ?>
			</button>
		<?php endif; ?>
	</div>

	<!-- Comments -->
	<?php if ( $allow_comments ) : ?>
	<div class="sbkboard-comments">
		<h4><?php printf( esc_html__( '댓글 %d개', 'sbk-board' ), count( $comments ) ); ?></h4>
		<div class="sbkboard-comments-list">
			<?php include __DIR__ . '/comments.php'; ?>
		</div>
		<!-- Comment write form -->
		<form class="sbkboard-comment-form" data-post-id="<?php echo esc_attr( $post->id ); ?>">
			<?php if ( ! is_user_logged_in() ) : ?>
			<div class="sbkboard-form-inline" style="margin-bottom:8px;">
				<input type="text" name="author" placeholder="<?php esc_attr_e( '이름', 'sbk-board' ); ?>" style="width:140px;">
				<input type="password" name="password" placeholder="<?php esc_attr_e( '비밀번호', 'sbk-board' ); ?>" style="width:140px;">
			</div>
			<?php endif; ?>
			<textarea name="content" placeholder="<?php esc_attr_e( '댓글을 입력하세요.', 'sbk-board' ); ?>"></textarea>
			<div style="text-align:right;">
				<button type="submit" class="sbkboard-btn sbkboard-btn-primary sbkboard-btn-comment-submit">
					<?php esc_html_e( '댓글 등록', 'sbk-board' ); ?>
				</button>
			</div>
		</form>
	</div>
	<?php endif; ?>

</div>
