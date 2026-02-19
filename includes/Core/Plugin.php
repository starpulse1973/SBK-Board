<?php
namespace SBKBoard\Core;
class Plugin {
	private static ?self $instance = null;
	public static function get_instance(): self {
		if ( null === self::$instance ) self::$instance = new self();
		return self::$instance;
	}
	private function __construct() {}
	public function run(): void {
		$this->maybe_upgrade_schema();
		if ( is_admin() ) ( new \SBKBoard\Admin\Menu() )->init();
		( new \SBKBoard\Core\Assets() )->init();
		( new \SBKBoard\Core\CacheCompat() )->init();
		( new \SBKBoard\Front\Shortcodes\BoardShortcode() )->init();
		( new \SBKBoard\Front\Shortcodes\LatestShortcode() )->init();
		$this->register_ajax();
		if ( did_action( 'elementor/loaded' ) ) {
			\SBKBoard\Elementor\ElementorIntegration::init();
		} else {
			add_action( 'elementor/loaded', [ \SBKBoard\Elementor\ElementorIntegration::class, 'init' ] );
		}
		do_action( 'sbkboard_loaded' );
	}
	private function register_ajax(): void {
		$handlers = [
			\SBKBoard\Front\Ajax\ListAjax::class,
			\SBKBoard\Front\Ajax\PostAjax::class,
			\SBKBoard\Front\Ajax\CommentAjax::class,
			\SBKBoard\Front\Ajax\FileAjax::class,
			\SBKBoard\Front\Ajax\VoteAjax::class,
			\SBKBoard\Front\Ajax\PasswordAjax::class,
		];
		foreach ( $handlers as $class ) ( new $class() )->register();
	}
	private function maybe_upgrade_schema(): void {
		$db_version = (string) get_option( 'sbkboard_db_version', '0' );
		if ( version_compare( $db_version, SBKBOARD_VERSION, '<' ) ) {
			\SBKBoard\DB\Schema::create_tables();
		}
	}
}
