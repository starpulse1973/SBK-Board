<?php
namespace SBKBoard\Core;

class CacheCompat {
	public function init(): void {
		add_action( 'init', [ $this, 'maybe_disable_cache' ], 0 );
		add_action( 'send_headers', [ $this, 'send_nocache_headers' ], 0 );
	}

	public function maybe_disable_cache(): void {
		if ( ! $this->is_dynamic_board_request() ) {
			return;
		}

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		if ( ! defined( 'DONOTCACHEDB' ) ) {
			define( 'DONOTCACHEDB', true );
		}
		if ( ! defined( 'DONOTMINIFY' ) ) {
			define( 'DONOTMINIFY', true );
		}

		// LiteSpeed Cache compatibility hook.
		do_action( 'litespeed_control_set_nocache', 'sbkboard dynamic route' );
	}

	public function send_nocache_headers(): void {
		if ( ! $this->is_dynamic_board_request() || headers_sent() ) {
			return;
		}

		nocache_headers();
		header( 'X-LiteSpeed-Cache-Control: no-cache' );
		header( 'X-SBKBoard-Cache: bypass' );
	}

	private function is_dynamic_board_request(): bool {
		return isset( $_GET['sbk_view'] ) || isset( $_GET['sbk_write'] ) || isset( $_GET['sbk_edit'] );
	}
}

