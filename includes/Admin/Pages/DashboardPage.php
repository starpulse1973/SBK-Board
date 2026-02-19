<?php
namespace SBKBoard\Admin\Pages;

class DashboardPage {
	public function render(): void {
		$boards = \SBKBoard\DB\BoardRepository::get_all();
		echo '<div class="wrap"><h1>' . esc_html__( 'SBK Board 대시보드', 'sbk-board' ) . '</h1>';
		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>' . esc_html__( '게시판명', 'sbk-board' ) . '</th><th>' . esc_html__( '슬러그', 'sbk-board' ) . '</th><th>' . esc_html__( '게시글 수', 'sbk-board' ) . '</th><th>' . esc_html__( '단축코드', 'sbk-board' ) . '</th></tr></thead><tbody>';
		if ( empty( $boards ) ) {
			echo '<tr><td colspan="4">' . esc_html__( '게시판이 없습니다.', 'sbk-board' ) . '</td></tr>';
		} else {
			foreach ( $boards as $board ) {
				echo '<tr><td><a href="' . esc_url( admin_url( 'admin.php?page=sbkboard-board-edit&id=' . $board->id ) ) . '">' . esc_html( (string) $board->name ) . '</a></td>';
				echo '<td>' . esc_html( (string) $board->slug ) . '</td>';
				echo '<td>' . esc_html( (string) \SBKBoard\DB\PostRepository::count_by_board( (int) $board->id ) ) . '</td>';
				echo '<td><code>[sbk_board id="' . esc_attr( (string) $board->id ) . '"]</code></td></tr>';
			}
		}
		echo '</tbody></table>';
		echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=sbkboard' ) ) . '" class="button button-primary">' . esc_html__( '게시판 목록', 'sbk-board' ) . '</a></p></div>';
	}
}
