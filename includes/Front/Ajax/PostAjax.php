<?php
namespace SBKBoard\Front\Ajax;

use SBKBoard\DB\BoardRepository;
use SBKBoard\DB\PostRepository;
use SBKBoard\DB\FileRepository;
use SBKBoard\Core\Sanitizer;
use SBKBoard\Skin\SkinLoader;

class PostAjax extends BaseAjax {
	public function register(): void {
		$this->add( 'sbkboard_post_save',   'handle_save' );
		$this->add( 'sbkboard_post_delete', 'handle_delete' );
		$this->add( 'sbkboard_post_view',   'handle_view' );
	}

	/** Save (insert or update) a post */
	public function handle_save(): void {
		$this->verify_nonce();

		$board_id  = $this->int( 'board_id' );
		$post_id   = $this->int( 'post_id' );
		$parent_id = $this->int( 'parent_id' );
		$board     = BoardRepository::get_by_id( $board_id );
		if ( ! $board ) wp_send_json_error( __( '게시판을 찾을 수 없습니다.', 'sbk-board' ) );

		$settings     = BoardRepository::get_settings( $board_id );
		$editor_type  = (string) ( $settings['editor_type'] ?? 'textarea' );
		$allow_html   = ( 'wp_editor' === $editor_type ) || ! empty( $settings['allow_html'] );
		$user_id    = get_current_user_id();

		// Guest author/password
		$author    = '';
		$password  = '';
		$is_secret = 0;
		if ( ! $user_id ) {
			$author   = Sanitizer::text( $_POST['author'] ?? '' );
			$password = Sanitizer::text( $_POST['password'] ?? '' );
			if ( $password ) $password = wp_hash_password( $password );
		}
		if ( isset( $_POST['is_secret'] ) ) $is_secret = 1;

		$data = [
			'board_id'    => $board_id,
			'parent_id'   => $parent_id,
			'user_id'     => $user_id,
			'author_name' => $author ?: ( $user_id ? ( wp_get_current_user()->display_name ) : '' ),
			'subject'     => Sanitizer::text( $_POST['subject'] ?? '' ),
			'content'   => $allow_html
				? Sanitizer::html( $_POST['content'] ?? '' )
				: Sanitizer::textarea( $_POST['content'] ?? '' ),
			'password'  => $password,
			'is_secret' => $is_secret,
			'is_notice' => ( is_super_admin() || current_user_can( 'manage_options' ) ) && ! empty( $_POST['is_notice'] ) ? 1 : 0,
			'status'    => 'publish',
		];

		if ( $post_id > 0 ) {
			// Update — check ownership
			$existing = PostRepository::get_by_id( $post_id );
			if ( ! $existing ) wp_send_json_error( __( '게시글을 찾을 수 없습니다.', 'sbk-board' ) );
			if ( $existing->user_id && $existing->user_id !== $user_id && ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( '수정 권한이 없습니다.', 'sbk-board' ) );
			}
			PostRepository::update( $post_id, $data );
		} else {
			$post_id = PostRepository::insert( $data );
			if ( ! $post_id ) wp_send_json_error( __( '저장에 실패했습니다.', 'sbk-board' ) );
		}

		// Handle file uploads
		if ( ! empty( $_FILES['attachments']['name'][0] ) ) {
			$this->handle_uploads( $post_id, $board_id, $settings );
		}

		// Build redirect URL back to the board page (view saved post)
		$page_url = esc_url_raw( $this->input( 'page_url' ) );
		if ( '' === $page_url ) {
			$referer  = ! empty( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
			$page_url = $referer ? (string) strtok( $referer, '?' ) : '';
		}
		if ( '' === $page_url ) {
			$page_url = home_url( '/' );
		}
		$redirect = add_query_arg(
			[
				'sbk_view' => $post_id,
				'sbk_t'    => time(),
			],
			$page_url
		);

		wp_send_json_success( [ 'post_id' => $post_id, 'redirect' => $redirect ] );
	}

	/** Delete a post */
	public function handle_delete(): void {
		$this->verify_nonce();
		$post_id = $this->int( 'post_id' );
		$post    = PostRepository::get_by_id( $post_id );
		if ( ! $post ) wp_send_json_error( __( '게시글을 찾을 수 없습니다.', 'sbk-board' ) );

		$user_id = get_current_user_id();
		if ( $post->user_id && (int) $post->user_id !== $user_id && ! current_user_can( 'manage_options' ) ) {
			// Guest: check password
			$pw = Sanitizer::text( $_POST['password'] ?? '' );
			if ( ! $post->password || ! wp_check_password( $pw, $post->password ) ) {
				wp_send_json_error( __( '비밀번호가 틀렸습니다.', 'sbk-board' ) );
			}
		}

		PostRepository::delete( $post_id );
		wp_send_json_success();
	}

	/** Get post view HTML (for AJAX navigation) */
	public function handle_view(): void {
		$this->verify_nonce();
		$post_id  = $this->int( 'post_id' );
		$board_id = $this->int( 'board_id' );
		$post     = PostRepository::get_by_id( $post_id );
		if ( ! $post ) wp_send_json_error( __( '게시글을 찾을 수 없습니다.', 'sbk-board' ) );

		PostRepository::increment_view( $post_id );

		$board    = BoardRepository::get_by_id( $board_id );
		$settings = BoardRepository::get_settings( $board_id );
		$skin     = $settings['skin'] ?? 'basic';
		$files    = FileRepository::get_by_post( $post_id );

		$html = SkinLoader::render( $skin, 'view', compact( 'post', 'board', 'settings', 'files' ) );
		wp_send_json_success( [ 'html' => $html ] );
	}

	private function handle_uploads( int $post_id, int $board_id, array $settings ): void {
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		// 커스텀 업로드 폴더: wp-content/sbk-board-uploads/YYYY/MM/
		$upload_base_dir = WP_CONTENT_DIR . '/sbk-board-uploads';
		$upload_base_url = content_url( 'sbk-board-uploads' );
		$subdir          = '/' . gmdate( 'Y' ) . '/' . gmdate( 'm' );
		$upload_dir      = $upload_base_dir . $subdir;
		$upload_url      = $upload_base_url . $subdir;

		// 디렉토리 생성 (없으면)
		if ( ! is_dir( $upload_dir ) ) {
			wp_mkdir_p( $upload_dir );
		}

		// .htaccess 및 index.php 보안 파일 생성 (최초 1회)
		$htaccess = $upload_base_dir . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, "Options -Indexes\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
		$index = $upload_base_dir . '/index.php';
		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}

		$max_count = (int) ( $settings['max_files'] ?? 5 );
		$max_size  = (int) ( $settings['upload_max_size'] ?? 5 ) * 1024 * 1024; // MB → bytes
		$current   = count( FileRepository::get_by_post( $post_id ) );

		$files = $_FILES['attachments'];
		foreach ( $files['name'] as $i => $name ) {
			if ( ! $name || $current >= $max_count ) break;
			if ( (int) $files['error'][ $i ] !== UPLOAD_ERR_OK ) continue;
			if ( (int) $files['size'][ $i ] > $max_size ) continue;

			$safe_name = wp_unique_filename( $upload_dir, sanitize_file_name( $name ) );
			$dest_path = $upload_dir . '/' . $safe_name;

			if ( move_uploaded_file( $files['tmp_name'][ $i ], $dest_path ) ) {
				FileRepository::insert( [
					'post_id'   => $post_id,
					'file_name' => $safe_name,
					'file_url'  => $upload_url . '/' . $safe_name,
					'file_size' => $files['size'][ $i ],
				] );
				$current++;
			}
		}
	}
}
