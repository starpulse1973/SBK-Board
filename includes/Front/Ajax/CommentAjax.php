<?php
namespace SBKBoard\Front\Ajax;

use SBKBoard\DB\CommentRepository;
use SBKBoard\DB\PostRepository;
use SBKBoard\Core\Sanitizer;
use SBKBoard\Skin\SkinLoader;
use SBKBoard\DB\BoardRepository;

class CommentAjax extends BaseAjax {
	public function register(): void {
		$this->add( 'sbkboard_comment_add',    'handle_add' );
		$this->add( 'sbkboard_comment_delete', 'handle_delete' );
	}

	public function handle_add(): void {
		$this->verify_nonce();
		$post_id   = $this->int( 'post_id' );
		$parent_id = $this->int( 'parent_id' );
		$content   = Sanitizer::textarea( $_POST['content'] ?? '' );
		if ( ! $content ) wp_send_json_error( __( '내용을 입력하세요.', 'sbk-board' ) );

		$post = PostRepository::get_by_id( $post_id );
		if ( ! $post ) wp_send_json_error( __( '게시글을 찾을 수 없습니다.', 'sbk-board' ) );

		$user_id = get_current_user_id();
		$author  = '';
		$pw      = '';
		if ( ! $user_id ) {
			$author = Sanitizer::text( $_POST['author'] ?? '' );
			$raw_pw = Sanitizer::text( $_POST['password'] ?? '' );
			if ( $raw_pw ) $pw = wp_hash_password( $raw_pw );
		}

		CommentRepository::insert( [
			'post_id'     => $post_id,
			'parent_id'   => $parent_id,
			'user_id'     => $user_id,
			'author_name' => $author ?: ( $user_id ? wp_get_current_user()->display_name : '' ),
			'content'     => $content,
			'password'    => $pw,
			'status'      => 'publish',
		] );

		$comments = CommentRepository::get_by_post( $post_id );
		$board    = BoardRepository::get_by_id( $post->board_id );
		$settings = BoardRepository::get_settings( $post->board_id );
		$skin     = $settings['skin'] ?? 'basic';
		$html     = SkinLoader::render( $skin, 'comments', compact( 'comments', 'post', 'board', 'settings' ) );
		wp_send_json_success( [ 'html' => $html ] );
	}

	public function handle_delete(): void {
		$this->verify_nonce();
		$comment_id = $this->int( 'comment_id' );
		$comment    = CommentRepository::get_by_id( $comment_id );
		if ( ! $comment ) wp_send_json_error( __( '댓글을 찾을 수 없습니다.', 'sbk-board' ) );

		$user_id = get_current_user_id();
		if ( $comment->user_id && $comment->user_id !== $user_id && ! current_user_can( 'manage_options' ) ) {
			$pw = Sanitizer::text( $_POST['password'] ?? '' );
			if ( ! $comment->password || ! wp_check_password( $pw, $comment->password ) ) {
				wp_send_json_error( __( '비밀번호가 틀렸습니다.', 'sbk-board' ) );
			}
		}

		CommentRepository::delete( $comment_id );
		wp_send_json_success();
	}
}
