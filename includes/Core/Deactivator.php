<?php
namespace SBKBoard\Core;
class Deactivator {
	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'sbkboard_daily_maintenance' );
		flush_rewrite_rules();
	}
}
