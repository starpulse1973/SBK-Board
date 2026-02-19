<?php
namespace SBKBoard\Admin\Pages;

use SBKBoard\DB\BoardRepository;
use SBKBoard\DB\PostRepository;

class PostModerationPage {
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['_wpnonce_bulk'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_bulk'] ) ), 'sbkboard_posts_bulk' ) ) {
			$action   = sanitize_text_field( wp_unslash( $_POST['bulk_action'] ?? '' ) );
			$post_ids = array_map( 'intval', (array) ( $_POST['post_ids'] ?? [] ) );
			if ( 'trash' === $action && $post_ids ) {
				foreach ( $post_ids as $pid ) {
					PostRepository::delete( $pid );
				}
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '선택한 게시글을 삭제했습니다.', 'sbk-board' ) . '</p></div>';
			}
		}

		$board_id    = isset( $_GET['board_id'] ) ? (int) $_GET['board_id'] : 0;
		$paged       = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
		$per_page    = 20;
		$boards      = BoardRepository::get_all();

		global $wpdb;
		$table       = $wpdb->prefix . 'sbk_posts';
		$where       = "WHERE status != 'trash'";
		if ( $board_id ) {
			$where .= $wpdb->prepare( ' AND board_id=%d', $board_id );
		}
		$total       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where}" );
		$offset      = ( $paged - 1 ) * $per_page;
		$posts       = $wpdb->get_results( "SELECT p.*, b.name as board_name FROM {$table} p LEFT JOIN {$wpdb->prefix}sbk_boards b ON p.board_id=b.id {$where} ORDER BY p.id DESC LIMIT {$per_page} OFFSET {$offset}" ) ?: [];
		$total_pages = (int) ceil( $total / $per_page );

		echo '<div class="wrap sbkboard-wrap">';
		echo '<h1>' . esc_html__( '게시글 관리', 'sbk-board' ) . '</h1>';

		echo '<form method="get" style="margin-bottom:12px;">';
		echo '<input type="hidden" name="page" value="sbkboard-posts">';
		echo '<select name="board_id" onchange="this.form.submit()">';
		echo '<option value="0">' . esc_html__( '전체 게시판', 'sbk-board' ) . '</option>';
		foreach ( $boards as $board ) {
			echo '<option value="' . esc_attr( (string) $board->id ) . '"' . selected( $board_id, $board->id, false ) . '>' . esc_html( (string) $board->name ) . '</option>';
		}
		echo '</select></form>';

		echo '<form method="post">';
		wp_nonce_field( 'sbkboard_posts_bulk', '_wpnonce_bulk' );
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<td class="manage-column column-cb check-column"><input type="checkbox"></td>';
		echo '<th>' . esc_html__( '번호', 'sbk-board' ) . '</th>';
		echo '<th>' . esc_html__( '제목', 'sbk-board' ) . '</th>';
		echo '<th>' . esc_html__( '게시판', 'sbk-board' ) . '</th>';
		echo '<th>' . esc_html__( '작성자', 'sbk-board' ) . '</th>';
		echo '<th>' . esc_html__( '작성일', 'sbk-board' ) . '</th>';
		echo '<th>' . esc_html__( '조회', 'sbk-board' ) . '</th>';
		echo '</tr></thead><tbody>';

		if ( empty( $posts ) ) {
			echo '<tr><td colspan="7" style="text-align:center;padding:20px;">' . esc_html__( '게시글이 없습니다.', 'sbk-board' ) . '</td></tr>';
		}
		foreach ( $posts as $post ) {
			echo '<tr>';
			echo '<th scope="row" class="check-column"><input type="checkbox" name="post_ids[]" value="' . esc_attr( (string) $post->id ) . '"></th>';
			echo '<td>' . esc_html( (string) $post->id ) . '</td>';
			echo '<td><strong>' . esc_html( (string) $post->subject ) . '</strong>';
			if ( $post->is_notice ) {
				echo ' <span class="dashicons dashicons-megaphone" title="' . esc_attr__( '공지', 'sbk-board' ) . '"></span>';
			}
			if ( $post->is_secret ) {
				echo ' <span class="dashicons dashicons-lock" title="' . esc_attr__( '비밀글', 'sbk-board' ) . '"></span>';
			}
			echo '</td>';
			echo '<td>' . esc_html( (string) ( $post->board_name ?? '' ) ) . '</td>';
			echo '<td>' . esc_html( (string) $post->author_name ) . '</td>';
			echo '<td>' . esc_html( mysql2date( 'Y.m.d H:i', (string) $post->created_at ) ) . '</td>';
			echo '<td>' . esc_html( number_format( (int) $post->view_count ) ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';

		echo '<div style="margin-top:8px;">';
		echo '<select name="bulk_action"><option value="">' . esc_html__( '일괄 작업 선택', 'sbk-board' ) . '</option>';
		echo '<option value="trash">' . esc_html__( '삭제', 'sbk-board' ) . '</option></select> ';
		submit_button( __( '적용', 'sbk-board' ), 'secondary', '', false );
		echo '</div></form>';

		if ( $total_pages > 1 ) {
			echo '<div class="tablenav bottom"><div class="tablenav-pages">';
			for ( $page = 1; $page <= $total_pages; $page++ ) {
				$url = add_query_arg( [ 'page' => 'sbkboard-posts', 'board_id' => $board_id, 'paged' => $page ], admin_url( 'admin.php' ) );
				if ( $page === $paged ) {
					echo '<span class="current">' . esc_html( (string) $page ) . '</span> ';
				} else {
					echo '<a href="' . esc_url( $url ) . '">' . esc_html( (string) $page ) . '</a> ';
				}
			}
			echo '</div></div>';
		}

		echo '</div>';
	}
}
