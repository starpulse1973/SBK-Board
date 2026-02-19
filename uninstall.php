<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;
if ( get_option( 'sbkboard_uninstall_delete_data' ) ) {
	require_once __DIR__ . '/includes/DB/Schema.php';
	\SBKBoard\DB\Schema::drop_tables();
	delete_option( 'sbkboard_settings' );
	delete_option( 'sbkboard_db_version' );
	delete_option( 'sbkboard_uninstall_delete_data' );
}
