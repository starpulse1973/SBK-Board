<?php
namespace SBKBoard\Admin\Pages;

class SettingsPage {
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if (
			isset( $_POST['sbkboard_save_settings'], $_POST['_wpnonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'sbkboard_settings' )
		) {
			$settings = [
				'upload_max_size'  => (int) ( $_POST['upload_max_size'] ?? 5 ),
				'allowed_ext'      => sanitize_text_field( wp_unslash( $_POST['allowed_ext'] ?? 'jpg,jpeg,png,gif,pdf,zip,doc,docx' ) ),
				'posts_per_page'   => (int) ( $_POST['posts_per_page'] ?? 15 ),
				'enable_comments'  => isset( $_POST['enable_comments'] ) ? 1 : 0,
				'enable_votes'     => isset( $_POST['enable_votes'] ) ? 1 : 0,
				'word_filter_mode' => sanitize_text_field( wp_unslash( $_POST['word_filter_mode'] ?? 'block' ) ),
				'word_filter_list' => sanitize_textarea_field( wp_unslash( $_POST['word_filter_list'] ?? '' ) ),
				'skin_default'     => sanitize_text_field( wp_unslash( $_POST['skin_default'] ?? 'basic' ) ),
			];
			update_option( 'sbkboard_settings', $settings );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '설정을 저장했습니다.', 'sbk-board' ) . '</p></div>';
		}

		$s = (array) get_option( 'sbkboard_settings', [] );
		$s = wp_parse_args(
			$s,
			[
				'upload_max_size'  => 5,
				'allowed_ext'      => 'jpg,jpeg,png,gif,pdf,zip,doc,docx',
				'posts_per_page'   => 15,
				'enable_comments'  => 1,
				'enable_votes'     => 1,
				'word_filter_mode' => 'block',
				'word_filter_list' => '',
				'skin_default'     => 'basic',
			]
		);

		echo '<div class="wrap sbkboard-wrap">';
		echo '<h1>' . esc_html__( 'SBK Board 설정', 'sbk-board' ) . '</h1>';
		echo '<form method="post">';
		wp_nonce_field( 'sbkboard_settings' );
		echo '<table class="form-table"><tbody>';

		echo '<tr><th>' . esc_html__( '기본 스킨', 'sbk-board' ) . '</th><td>';
		$skins = \SBKBoard\Skin\SkinLoader::get_registered();
		echo '<select name="skin_default">';
		foreach ( $skins as $key => $info ) {
			$label = is_array( $info ) ? ( $info['name'] ?? $key ) : $key;
			echo '<option value="' . esc_attr( (string) $key ) . '"' . selected( $s['skin_default'], $key, false ) . '>' . esc_html( (string) $label ) . '</option>';
		}
		echo '</select></td></tr>';

		echo '<tr><th>' . esc_html__( '페이지당 글 수(기본)', 'sbk-board' ) . '</th><td>';
		echo '<input type="number" name="posts_per_page" value="' . esc_attr( (string) $s['posts_per_page'] ) . '" min="5" max="100"></td></tr>';

		echo '<tr><th>' . esc_html__( '파일 최대 크기 (MB)', 'sbk-board' ) . '</th><td>';
		echo '<input type="number" name="upload_max_size" value="' . esc_attr( (string) $s['upload_max_size'] ) . '" min="1" max="100"></td></tr>';

		echo '<tr><th>' . esc_html__( '허용 확장자', 'sbk-board' ) . '</th><td>';
		echo '<input type="text" name="allowed_ext" class="regular-text" value="' . esc_attr( (string) $s['allowed_ext'] ) . '">';
		echo '<p class="description">' . esc_html__( '콤마로 구분. 예: jpg,jpeg,png,gif,pdf', 'sbk-board' ) . '</p></td></tr>';

		echo '<tr><th>' . esc_html__( '기능', 'sbk-board' ) . '</th><td>';
		echo '<label><input type="checkbox" name="enable_comments" value="1"' . checked( $s['enable_comments'], 1, false ) . '> ' . esc_html__( '댓글 허용 (기본)', 'sbk-board' ) . '</label><br>';
		echo '<label><input type="checkbox" name="enable_votes" value="1"' . checked( $s['enable_votes'], 1, false ) . '> ' . esc_html__( '추천 허용 (기본)', 'sbk-board' ) . '</label></td></tr>';

		echo '<tr><th>' . esc_html__( '금칙어 필터 모드', 'sbk-board' ) . '</th><td>';
		echo '<select name="word_filter_mode">';
		echo '<option value="block"' . selected( $s['word_filter_mode'], 'block', false ) . '>' . esc_html__( '차단', 'sbk-board' ) . '</option>';
		echo '<option value="replace"' . selected( $s['word_filter_mode'], 'replace', false ) . '>' . esc_html__( '***로 치환', 'sbk-board' ) . '</option>';
		echo '<option value="off"' . selected( $s['word_filter_mode'], 'off', false ) . '>' . esc_html__( '비활성', 'sbk-board' ) . '</option>';
		echo '</select></td></tr>';

		echo '<tr><th>' . esc_html__( '금칙어 목록', 'sbk-board' ) . '</th><td>';
		echo '<textarea name="word_filter_list" rows="5" class="large-text">' . esc_textarea( (string) $s['word_filter_list'] ) . '</textarea>';
		echo '<p class="description">' . esc_html__( '줄바꿈으로 구분', 'sbk-board' ) . '</p></td></tr>';

		echo '</tbody></table>';
		submit_button( __( '설정 저장', 'sbk-board' ), 'primary', 'sbkboard_save_settings' );
		echo '</form></div>';
	}
}
