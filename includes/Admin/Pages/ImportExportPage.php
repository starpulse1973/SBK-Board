<?php
namespace SBKBoard\Admin\Pages;

use SBKBoard\DB\BoardRepository;

class ImportExportPage {
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap sbkboard-wrap">';
		echo '<h1>' . esc_html__( '게시판 백업/복원', 'sbk-board' ) . '</h1>';

		echo '<h2>' . esc_html__( '백업 내보내기', 'sbk-board' ) . '</h2>';
		echo '<p>' . esc_html__( '게시판 설정을 JSON 파일로 내보냅니다.', 'sbk-board' ) . '</p>';

		if ( isset( $_GET['sbkboard_export'] ) && check_admin_referer( 'sbkboard_export' ) ) {
			$boards = BoardRepository::get_all();
			$export = [];

			foreach ( $boards as $board ) {
				$export[] = [
					'slug'     => $board->slug,
					'name'     => $board->name,
					'settings' => BoardRepository::get_settings( (int) $board->id ),
				];
			}

			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="sbkboard-export-' . gmdate( 'Ymd-His' ) . '.json"' );
			echo wp_json_encode( $export, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			exit;
		}

		$export_url = wp_nonce_url( admin_url( 'admin.php?page=sbkboard-import-export&sbkboard_export=1' ), 'sbkboard_export' );
		echo '<a href="' . esc_url( $export_url ) . '" class="button">' . esc_html__( '게시판 설정 내보내기', 'sbk-board' ) . '</a>';

		echo '<hr>';

		echo '<h2>' . esc_html__( '복원 가져오기', 'sbk-board' ) . '</h2>';
		echo '<p>' . esc_html__( '내보낸 JSON 파일에서 게시판 설정을 복원합니다.', 'sbk-board' ) . '</p>';

		if (
			isset( $_POST['sbkboard_import'], $_POST['_wpnonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'sbkboard_import' )
			&& ! empty( $_FILES['import_file']['tmp_name'] )
		) {
			$tmp_file     = sanitize_text_field( wp_unslash( $_FILES['import_file']['tmp_name'] ) );
			$json_content = file_get_contents( $tmp_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$data         = json_decode( (string) $json_content, true );
			$imported     = 0;

			if ( is_array( $data ) ) {
				foreach ( $data as $item ) {
					$slug = sanitize_key( $item['slug'] ?? '' );
					$name = sanitize_text_field( $item['name'] ?? '' );
					if ( ! $slug || ! $name ) {
						continue;
					}

					$existing = BoardRepository::get_by_slug( $slug );
					if ( $existing ) {
						BoardRepository::update( (int) $existing->id, [ 'name' => $name ] );
						if ( ! empty( $item['settings'] ) ) {
							BoardRepository::save_settings( (int) $existing->id, (array) $item['settings'] );
						}
					} else {
						$id = BoardRepository::insert(
							[
								'slug'     => $slug,
								'name'     => $name,
								'settings' => '{}',
							]
						);
						if ( $id && ! empty( $item['settings'] ) ) {
							BoardRepository::save_settings( (int) $id, (array) $item['settings'] );
						}
					}
					$imported++;
				}

				echo '<div class="notice notice-success is-dismissible"><p>'
					. sprintf( esc_html__( '%d개 게시판을 가져왔습니다.', 'sbk-board' ), $imported )
					. '</p></div>';
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( '올바른 JSON 파일이 아닙니다.', 'sbk-board' ) . '</p></div>';
			}
		}

		echo '<form method="post" enctype="multipart/form-data">';
		wp_nonce_field( 'sbkboard_import' );
		echo '<input type="file" name="import_file" accept=".json"> ';
		submit_button( __( '복원 가져오기', 'sbk-board' ), 'secondary', 'sbkboard_import', false );
		echo '</form>';

		echo '</div>';
	}
}
