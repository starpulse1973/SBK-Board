<?php
/**
 * Plugin Name: SBK Board
 * Plugin URI:  https://socialbridge.co.kr
 * Description: 사회적기술기업 소셜브릿지에서 제공하는 무료 워드프레스 게시판 플러그인으로 Elementor 위젯 전용 한국형 계층식 게시판입니다.
 * Version:     1.2.2
 * Author:      Social Bridge Dev. Team
 * Author URI:  https://socialbridge.co.kr
 * Text Domain: sbk-board
 * Domain Path: /languages
 * Requires at least: 6.9
 * Requires PHP: 8.1
 * License:     GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SBKBOARD_VERSION',   '1.2.2' );
define( 'SBKBOARD_FILE',      __FILE__ );
define( 'SBKBOARD_DIR',       plugin_dir_path( __FILE__ ) );
define( 'SBKBOARD_URL',       plugin_dir_url( __FILE__ ) );
define( 'SBKBOARD_BASENAME',  plugin_basename( __FILE__ ) );

spl_autoload_register( function ( $class ) {
	$prefix = 'SBKBoard\\';
	$base   = SBKBOARD_DIR . 'includes/';
	if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) return;
	$relative = substr( $class, strlen( $prefix ) );
	$file     = $base . str_replace( '\\', '/', $relative ) . '.php';
	if ( file_exists( $file ) ) require $file;
} );

register_activation_hook( __FILE__, [ 'SBKBoard\\Core\\Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'SBKBoard\\Core\\Deactivator', 'deactivate' ] );

add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'sbk-board', false, dirname( SBKBOARD_BASENAME ) . '/languages' );
	SBKBoard\Core\Plugin::get_instance()->run();
} );

