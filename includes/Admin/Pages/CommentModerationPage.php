<?php
namespace SBKBoard\Admin\Pages;

use SBKBoard\DB\CommentRepository;

class CommentModerationPage {
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['_wpnonce_bulk'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_bulk'] ) ), 'sbkboard_comments_bulk' ) ) {
			$action = sanitize_text_field( wp_unslash( $_POST['bulk_action'] ?? '' ) );
			$ids    = array_map( 'intval', (array) ( $_POST['comment_ids'] ?? [] ) );
			if ( 'trash' === $action && $ids ) {
				foreach ( $ids as $cid ) {
					CommentRepository::delete( $cid );
				}
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '선택한 댓글을 삭제했습니다.', 'sbk-board' ) . '</p></div>';
			}
		}

		$paged    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
		$per_page = 20;

		global $wpdb;
		$table       = $wpdb->prefix . 'sbk_comments';
		$post_table  = $wpdb->prefix . 'sbk_posts';
		$where       = "WHERE c.status != 'trash'";
		$total       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} c {$where}" );
		$offset      = ( $paged - 1 ) * $per_page;
		$comments    = $wpdb->get_results( "SELECT c.*, p.subject as post_subject FROM {$table} c LEFT JOIN {$post_table} p ON c.post_id=p.id {$where} ORDER BY c.id DESC LIMIT {$per_page} OFFSET {$offset}" ) ?: [];
		$total_pages = (int) ceil( $total / $per_page );

		echo '<div class="wrap sbkboard-wrap">';
		echo '<h1>' . esc_html__( '댓글 관리', 'sbk-board' ) . '</h1>';

		echo '<form method="post">';
		wp_nonce_field( 'sbkboard_comments_bulk', '_wpnonce_bulk' );
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<td class="manage-column column-cb check-column"><input type="checkbox"></td>';
		echo '<th>' . esc_html__( '번호', 'sbk-board' ) . '</th>';
		echo '<th>' . esc_html__( '댓글 내용', 'sbk-board' ) . '</th>';
		echo '<th>' . esc_html__( '게시글', 'sbk-board' ) . '</th>';
		echo '<th>' . esc_html__( '작성자', 'sbk-board' ) . '</th>';
		echo '<th>' . esc_html__( '작성일', 'sbk-board' ) . '</th>';
		echo '</tr></thead><tbody>';

		if ( empty( $comments ) ) {
			echo '<tr><td colspan="6" style="text-align:center;padding:20px;">' . esc_html__( '댓글이 없습니다.', 'sbk-board' ) . '</td></tr>';
		}
		foreach ( $comments as $comment ) {
			echo '<tr>';
			echo '<th scope="row" class="check-column"><input type="checkbox" name="comment_ids[]" value="' . esc_attr( (string) $comment->id ) . '"></th>';
			echo '<td>' . esc_html( (string) $comment->id ) . '</td>';
			echo '<td>' . esc_html( wp_trim_words( (string) $comment->content, 15 ) ) . '</td>';
			echo '<td>' . esc_html( (string) ( $comment->post_subject ?? '' ) ) . '</td>';
			echo '<td>' . esc_html( (string) $comment->author_name ) . '</td>';
			echo '<td>' . esc_html( mysql2date( 'Y.m.d H:i', (string) $comment->created_at ) ) . '</td>';
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
				$url = add_query_arg( [ 'page' => 'sbkboard-comments', 'paged' => $page ], admin_url( 'admin.php' ) );
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
