<?php
namespace SBKBoard\Admin;

class Menu {
	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_menus' ] );
		add_action( 'admin_init', [ 'SBKBoard\Admin\Pages\BoardsListPage', 'handle_actions' ] );
		add_action(
			'admin_init',
			function (): void {
				$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
				if ( 'sbkboard-boards' === $page ) {
					wp_safe_redirect( admin_url( 'admin.php?page=sbkboard' ) );
					exit;
				}
			}
		);
		add_action( 'admin_post_sbkboard_save_board', [ new Pages\BoardEditPage(), 'handle_save' ] );
		add_action( 'admin_post_sbkboard_backup_board', [ new Pages\BoardEditPage(), 'handle_backup_download' ] );
	}

	public function register_menus(): void {
		add_menu_page(
			__( 'SBK Board', 'sbk-board' ),
			__( 'SBK Board', 'sbk-board' ),
			'manage_options',
			'sbkboard',
			[ new Pages\BoardsListPage(), 'render' ],
			'dashicons-format-chat',
			58
		);

		add_submenu_page(
			'sbkboard',
			__( '게시판 추가/수정', 'sbk-board' ),
			__( '게시판 추가/수정', 'sbk-board' ),
			'manage_options',
			'sbkboard-board-edit',
			[ new Pages\BoardEditPage(), 'render' ]
		);

		add_submenu_page(
			'sbkboard',
			__( '게시글 관리', 'sbk-board' ),
			__( '게시글 관리', 'sbk-board' ),
			'manage_options',
			'sbkboard-posts',
			[ new Pages\PostModerationPage(), 'render' ]
		);

		add_submenu_page(
			'sbkboard',
			__( '댓글 관리', 'sbk-board' ),
			__( '댓글 관리', 'sbk-board' ),
			'manage_options',
			'sbkboard-comments',
			[ new Pages\CommentModerationPage(), 'render' ]
		);

		add_submenu_page(
			'sbkboard',
			__( '게시판 백업/복원', 'sbk-board' ),
			__( '게시판 백업/복원', 'sbk-board' ),
			'manage_options',
			'sbkboard-import-export',
			[ new Pages\ImportExportPage(), 'render' ]
		);

		add_submenu_page(
			'sbkboard',
			__( '설정', 'sbk-board' ),
			__( '설정', 'sbk-board' ),
			'manage_options',
			'sbkboard-settings',
			[ new Pages\SettingsPage(), 'render' ]
		);
	}
}
