<?php
namespace SBKBoard\Core;
class Nonce {
	public static function verify( string $action = 'sbkboard_nonce' ): bool {
		$nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';
		return (bool) wp_verify_nonce( $nonce, $action );
	}
	public static function die_if_invalid( string $action = 'sbkboard_nonce' ): void {
		if ( ! self::verify( $action ) ) {
			wp_send_json_error( [ 'message' => __( '보안 토큰이 유효하지 않습니다.', 'sbk-board' ) ], 403 );
		}
	}
}
