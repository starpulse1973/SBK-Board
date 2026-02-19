<?php
namespace SBKBoard\Admin\Pages;

use SBKBoard\DB\BoardRepository;

class BoardsListPage {
	public static function handle_actions(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( ! in_array( $page, [ 'sbkboard', 'sbkboard-boards' ], true ) ) {
			return;
		}

		if ( isset( $_GET['action'], $_GET['id'], $_GET['_wpnonce'] ) && 'delete' === sanitize_key( wp_unslash( $_GET['action'] ) ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );
			if ( ! wp_verify_nonce( $nonce, 'sbkboard_delete_board' ) ) {
				wp_die( esc_html__( '잘못된 요청입니다.', 'sbk-board' ) );
			}

			BoardRepository::delete( absint( $_GET['id'] ) );
			wp_safe_redirect( admin_url( 'admin.php?page=sbkboard&deleted=1' ) );
			exit;
		}
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( '권한이 없습니다.', 'sbk-board' ) );
		}

		$boards  = BoardRepository::get_all();
		$add_url = admin_url( 'admin.php?page=sbkboard-board-edit' );
		?>
		<div class="wrap sbkboard-admin">
			<h1 class="wp-heading-inline"><?php echo esc_html__( 'SBK Board - 게시판', 'sbk-board' ); ?></h1>
			<a href="<?php echo esc_url( $add_url ); ?>" class="page-title-action"><?php echo esc_html__( '게시판 추가', 'sbk-board' ); ?></a>
			<hr class="wp-header-end">

			<?php if ( isset( $_GET['deleted'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( '게시판을 삭제했습니다.', 'sbk-board' ); ?></p></div>
			<?php endif; ?>

			<table class="widefat striped">
				<thead>
				<tr>
					<th><?php echo esc_html__( '번호', 'sbk-board' ); ?></th>
					<th><?php echo esc_html__( '게시판 이름', 'sbk-board' ); ?></th>
					<th><?php echo esc_html__( '스킨', 'sbk-board' ); ?></th>
					<th><?php echo esc_html__( '게시판 단축코드', 'sbk-board' ); ?></th>
					<th><?php echo esc_html__( '최신글 단축코드', 'sbk-board' ); ?></th>
					<th><?php echo esc_html__( '작업', 'sbk-board' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php if ( empty( $boards ) ) : ?>
					<tr>
						<td colspan="6"><?php echo esc_html__( '등록된 게시판이 없습니다.', 'sbk-board' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $boards as $board ) : ?>
						<?php
						$id         = (int) $board->id;
						$settings   = BoardRepository::get_settings( $id );
						$skin       = (string) ( $settings['skin'] ?? 'basic' );
						$edit_url   = admin_url( 'admin.php?page=sbkboard-board-edit&board_id=' . $id );
						$delete_url = wp_nonce_url(
							admin_url( 'admin.php?page=sbkboard&action=delete&id=' . $id ),
							'sbkboard_delete_board'
						);
						?>
						<tr>
							<td><?php echo esc_html( (string) $id ); ?></td>
							<td><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( (string) $board->name ); ?></a></td>
							<td><?php echo esc_html( $skin ); ?></td>
							<td><code>[sbk_board id="<?php echo esc_html( (string) $id ); ?>"]</code></td>
							<td><code>[sbk_latest board_id="<?php echo esc_html( (string) $id ); ?>" rpp="5"]</code></td>
							<td>
								<a class="button" href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html__( '수정', 'sbk-board' ); ?></a>
								<a class="button sbkboard-delete-board" href="<?php echo esc_url( $delete_url ); ?>" style="margin-left:6px;">
									<?php echo esc_html__( '삭제', 'sbk-board' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
