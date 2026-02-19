<?php
namespace SBKBoard\Front\Ajax;

use SBKBoard\Core\Nonce;

abstract class BaseAjax {
	abstract public function register(): void;

	protected function add( string $action, string $method, bool $nopriv = true ): void {
		add_action( 'wp_ajax_' . $action,        [ $this, $method ] );
		if ( $nopriv ) {
			add_action( 'wp_ajax_nopriv_' . $action, [ $this, $method ] );
		}
	}

	protected function verify_nonce(): void {
		if ( ! headers_sent() ) {
			nocache_headers();
			header( 'X-SBKBoard-Ajax: no-cache' );
		}
		Nonce::die_if_invalid();
	}

	protected function input( string $key, string $type = 'text' ): string {
		$value = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		if ( 'int' === $type ) return (string)(int) $value;
		if ( 'textarea' === $type ) return sanitize_textarea_field( (string) $value );
		return sanitize_text_field( (string) $value );
	}

	protected function int( string $key ): int {
		return (int) ( $_POST[ $key ] ?? 0 );
	}
}
