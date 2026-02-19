<?php
namespace SBKBoard\Front\Ajax;

use SBKBoard\DB\BoardRepository;
use SBKBoard\DB\PostRepository;
use SBKBoard\Skin\SkinLoader;

class ListAjax extends BaseAjax {
	public function register(): void {
		$this->add( 'sbkboard_list', 'handle' );
	}

	public function handle(): void {
		$this->verify_nonce();
		$board_id = $this->int( 'board_id' );
		$paged    = max( 1, $this->int( 'page' ) );
		$keyword  = $this->input( 'keyword' );
		$rpp      = max( 0, $this->int( 'rpp' ) );
		$gallery_columns = max( 0, $this->int( 'gallery_columns' ) );
		$page_url = esc_url_raw( $this->input( 'page_url' ) );
		$board    = BoardRepository::get_by_id( $board_id );
		if ( ! $board ) wp_send_json_error( __( '게시판을 찾을 수 없습니다.', 'sbk-board' ) );

		$settings = BoardRepository::get_settings( $board_id );
		$per_page = (int) ( $settings['per_page'] ?? 15 );
		if ( $rpp > 0 ) {
			$per_page = max( 1, min( 50, $rpp ) );
		}
		if ( $gallery_columns > 0 ) {
			$settings['gallery_columns'] = max( 1, min( 6, $gallery_columns ) );
		}
		$skin     = $settings['skin'] ?? 'basic';

		$data = PostRepository::get_list( $board_id, [
			'per_page' => $per_page,
			'paged'    => $paged,
			'search'   => $keyword,
			'with_content' => ( 'gallery' === $skin ),
		] );

		$html = SkinLoader::render( $skin, 'list-inner', [
			'board'    => $board,
			'settings' => $settings,
			'items'    => $data['items'],
			'notices'  => $data['notices'],
			'total'    => $data['total'],
			'paged'    => $paged,
			'per_page' => $per_page,
			'keyword'  => $keyword,
			'page_url' => $page_url,
		] );

		wp_send_json_success( [ 'html' => $html ] );
	}
}
