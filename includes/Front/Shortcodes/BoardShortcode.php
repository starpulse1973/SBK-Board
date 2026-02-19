<?php
namespace SBKBoard\Front\Shortcodes;

use SBKBoard\DB\BoardRepository;
use SBKBoard\DB\PostRepository;
use SBKBoard\DB\FileRepository;
use SBKBoard\Skin\SkinLoader;

/**
 * [sbk_board id="1"] or [sbk_board slug="free"]
 */
class BoardShortcode {
	public function init(): void {
		add_shortcode( 'sbk_board', [ $this, 'render' ] );
	}

	public function render( array $atts ): string {
		$atts = shortcode_atts( [
			'id'       => 0,
			'slug'     => '',
			'rpp'      => 0,
			'per_page' => 0,
			'gallery_columns' => 0,
			'url'      => '',
			'page_url' => '',
			'title_max_length' => 0,
			'date_format'      => '',
			'search_width'     => '',
		], $atts, 'sbk_board' );

		if ( $atts['slug'] ) {
			$board = BoardRepository::get_by_slug( sanitize_key( $atts['slug'] ) );
		} else {
			$board = BoardRepository::get_by_id( (int) $atts['id'] );
		}

		if ( ! $board ) {
			return '<p class="sbkboard-msg sbkboard-msg-error">' . esc_html__( '게시판을 찾을 수 없습니다.', 'sbk-board' ) . '</p>';
		}

		$settings  = BoardRepository::get_settings( $board->id );
		$skin_slug = $settings['skin'] ?? 'basic';
		$rpp       = (int) $atts['rpp'];
		if ( $rpp <= 0 ) {
			$rpp = (int) $atts['per_page'];
		}
		if ( $rpp > 0 ) {
			$settings['per_page'] = max( 1, min( 50, $rpp ) );
		}

		$gallery_columns = (int) $atts['gallery_columns'];
		if ( $gallery_columns > 0 ) {
			$settings['gallery_columns'] = max( 1, min( 6, $gallery_columns ) );
		}

		$title_max_length = (int) $atts['title_max_length'];
		if ( $title_max_length > 0 ) {
			$settings['title_max_length'] = max( 10, min( 200, $title_max_length ) );
		}

		$date_format = sanitize_text_field( (string) ( $atts['date_format'] ?? '' ) );
		if ( '' !== $date_format ) {
			if ( strlen( $date_format ) > 40 ) {
				$date_format = substr( $date_format, 0, 40 );
			}
			$settings['date_format'] = $date_format;
		}

		$search_width = sanitize_text_field( (string) ( $atts['search_width'] ?? '' ) );
		$search_width = preg_replace( '/\s+/', '', $search_width );
		if ( is_string( $search_width ) && '' !== $search_width ) {
			if ( preg_match( '/^\d+(?:\.\d+)?$/', $search_width ) ) {
				$search_width .= 'px';
			}
			if ( preg_match( '/^\d+(?:\.\d+)?(px|%|em|rem)$/', $search_width ) ) {
				$settings['search_width'] = $search_width;
			}
		}

		$page_url = esc_url_raw( (string) ( $atts['page_url'] ?? '' ) );
		if ( '' === $page_url ) {
			$page_url = esc_url_raw( (string) ( $atts['url'] ?? '' ) );
		}
		if ( '' === $page_url ) {
			$page_url = get_permalink() ?: home_url( '/' );
		}

		wp_enqueue_style( 'sbkboard-board' );
		wp_enqueue_script( 'sbkboard-board' );

		// Route to the correct view
		$view_id  = isset( $_GET['sbk_view'] )  ? (int) $_GET['sbk_view']  : 0;
		$write_id = isset( $_GET['sbk_write'] ) ? (int) $_GET['sbk_write'] : 0;
		$edit_id  = isset( $_GET['sbk_edit'] )  ? (int) $_GET['sbk_edit']  : 0;

		if ( $view_id ) {
			return $this->render_view( $view_id, $board, $settings, $skin_slug, $page_url );
		}

		if ( $write_id === (int) $board->id || isset( $_GET['sbk_write'] ) ) {
			return $this->render_write( null, $board, $settings, $skin_slug, $page_url );
		}

		if ( $edit_id ) {
			return $this->render_write( $edit_id, $board, $settings, $skin_slug, $page_url );
		}

		return $this->render_list( $board, $settings, $skin_slug, $page_url );
	}

	private function render_list( object $board, array $settings, string $skin_slug, string $page_url ): string {
		$paged    = max( 1, (int) ( $_GET['sbk_page'] ?? 1 ) );
		$keyword  = sanitize_text_field( wp_unslash( $_GET['sbk_q'] ?? '' ) );
		$per_page = (int) ( $settings['per_page'] ?? 15 );

		$list_data = PostRepository::get_list( $board->id, [
			'per_page' => $per_page,
			'paged'    => $paged,
			'search'   => $keyword,
			'with_content' => ( 'gallery' === $skin_slug ),
		] );

		return SkinLoader::render( $skin_slug, 'list', [
			'board'    => $board,
			'settings' => $settings,
			'items'    => $list_data['items'],
			'notices'  => $list_data['notices'],
			'total'    => $list_data['total'],
			'paged'    => $paged,
			'per_page' => $per_page,
			'keyword'  => $keyword,
			'page_url' => $page_url,
		] );
	}

	private function render_view( int $post_id, object $board, array $settings, string $skin_slug, string $page_url ): string {
		$post = PostRepository::get_by_id( $post_id );
		if ( ! $post || (int) $post->board_id !== (int) $board->id ) {
			return '<p class="sbkboard-msg sbkboard-msg-error">' . esc_html__( '게시글을 찾을 수 없습니다.', 'sbk-board' ) . '</p>';
		}

		// Secret post check
		if ( $post->is_secret ) {
			$user_id  = get_current_user_id();
			$is_owner = $user_id && $user_id === (int) $post->user_id;
			$is_admin = current_user_can( 'manage_options' );
			if ( ! $is_owner && ! $is_admin ) {
				return SkinLoader::render( $skin_slug, 'password', compact( 'post', 'board', 'settings', 'page_url' ) );
			}
		}

		PostRepository::increment_view( $post_id );
		$files = FileRepository::get_by_post( $post_id );
		return SkinLoader::render( $skin_slug, 'view', compact( 'post', 'board', 'settings', 'files', 'page_url' ) );
	}

	private function render_write( ?int $post_id, object $board, array $settings, string $skin_slug, string $page_url ): string {
		$user_id   = get_current_user_id();
		$can_write = ! empty( $settings['allow_guest_write'] ) || $user_id;
		if ( ! $can_write ) {
			return '<p class="sbkboard-msg sbkboard-msg-error">' . esc_html__( '로그인이 필요합니다.', 'sbk-board' ) . '</p>';
		}

		$post  = null;
		$files = [];
		if ( $post_id ) {
			$post = PostRepository::get_by_id( $post_id );
			if ( ! $post ) {
				return '<p class="sbkboard-msg sbkboard-msg-error">' . esc_html__( '게시글을 찾을 수 없습니다.', 'sbk-board' ) . '</p>';
			}
			$files = FileRepository::get_by_post( $post_id );
		}

		return SkinLoader::render( $skin_slug, 'write', compact( 'board', 'settings', 'post', 'files', 'page_url' ) );
	}
}
