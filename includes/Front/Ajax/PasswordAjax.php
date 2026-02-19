<?php
namespace SBKBoard\Front\Ajax;

use SBKBoard\DB\PostRepository;
use SBKBoard\DB\BoardRepository;
use SBKBoard\DB\FileRepository;
use SBKBoard\Core\Sanitizer;
use SBKBoard\Skin\SkinLoader;

class PasswordAjax extends BaseAjax {
	public function register(): void {
		$this->add( 'sbkboard_check_password', 'handle' );
	}

	public function handle(): void {
		$this->verify_nonce();
		$post_id  = $this->int( 'post_id' );
		$password = Sanitizer::text( $_POST['post_password'] ?? '' );

		$post = PostRepository::get_by_id( $post_id );
		if ( ! $post ) wp_send_json_error( __( '게시글을 찾을 수 없습니다.', 'sbk-board' ) );

		if ( ! $post->is_secret || ! $post->password ) {
			wp_send_json_error( __( '비밀글이 아닙니다.', 'sbk-board' ) );
		}

		if ( ! wp_check_password( $password, $post->password ) ) {
			wp_send_json_error( __( '비밀번호가 틀렸습니다.', 'sbk-board' ) );
		}

		PostRepository::increment_view( $post_id );

		$board    = BoardRepository::get_by_id( $post->board_id );
		$settings = BoardRepository::get_settings( $post->board_id );
		$skin     = $settings['skin'] ?? 'basic';
		$files    = FileRepository::get_by_post( $post_id );

		$html = SkinLoader::render( $skin, 'view', compact( 'post', 'board', 'settings', 'files' ) );
		wp_send_json_success( [ 'html' => $html ] );
	}
}
