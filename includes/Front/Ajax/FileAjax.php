<?php
namespace SBKBoard\Front\Ajax;

use SBKBoard\DB\FileRepository;
use SBKBoard\DB\PostRepository;

class FileAjax extends BaseAjax {
	public function register(): void {
		$this->add( 'sbkboard_file_delete',   'handle_delete' );
		$this->add( 'sbkboard_file_download', 'handle_download' );
	}

	public function handle_delete(): void {
		$this->verify_nonce();
		$file_id = $this->int( 'file_id' );
		$file    = FileRepository::get_by_id( $file_id );
		if ( ! $file ) wp_send_json_error( __( '파일을 찾을 수 없습니다.', 'sbk-board' ) );

		$user_id = get_current_user_id();
		$post    = PostRepository::get_by_id( $file->post_id );
		if ( $post && $post->user_id && $post->user_id !== $user_id && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( '권한이 없습니다.', 'sbk-board' ) );
		}

		// Delete physical file if in uploads dir
		$upload_dir = wp_get_upload_dir();
		$rel        = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file->file_url );
		if ( file_exists( $rel ) ) {
			wp_delete_file( $rel );
		}

		FileRepository::delete( $file_id );
		wp_send_json_success();
	}

	public function handle_download(): void {
		// Not an AJAX JSON response – stream the file
		$nonce = sanitize_text_field( wp_unslash( $_GET['nonce'] ?? '' ) );
		if ( ! wp_verify_nonce( $nonce, 'sbkboard_nonce' ) ) wp_die( 'Invalid nonce', 403 );

		$file_id = (int) ( $_GET['file_id'] ?? 0 );
		$file    = FileRepository::get_by_id( $file_id );
		if ( ! $file ) wp_die( 'File not found', 404 );

		FileRepository::increment_download( $file_id );

		$upload_dir = wp_get_upload_dir();
		$local_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file->file_url );

		if ( file_exists( $local_path ) ) {
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename="' . rawurlencode( $file->file_name ) . '"' );
			header( 'Content-Length: ' . filesize( $local_path ) );
			header( 'Pragma: public' );
			ob_clean();
			flush();
			readfile( $local_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			exit;
		} else {
			wp_redirect( $file->file_url );
			exit;
		}
	}
}
