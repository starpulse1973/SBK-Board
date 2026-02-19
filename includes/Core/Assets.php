<?php
namespace SBKBoard\Core;
class Assets {
	public function init(): void {
		add_action( 'wp_enqueue_scripts',    [ $this, 'enqueue_frontend' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin' ] );
	}
	public function enqueue_frontend(): void {
		wp_register_style( 'sbkboard-board', SBKBOARD_URL . 'assets/board.css', [], SBKBOARD_VERSION );
		$alignment_css = <<<'CSS'
.sbkboard-post-content img {
    max-width: 100%;
    height: auto;
}
.sbkboard-post-content::after {
    content: "";
    display: block;
    clear: both;
}
.sbkboard-post-content .aligncenter,
.sbkboard-post-content img.aligncenter,
.sbkboard-post-content figure.aligncenter,
.sbkboard-post-content .wp-caption.aligncenter,
.sbkboard-post-content .wp-block-image.aligncenter {
    display: block;
    float: none;
    clear: both;
    margin-left: auto;
    margin-right: auto;
}
.sbkboard-post-content .alignleft,
.sbkboard-post-content img.alignleft,
.sbkboard-post-content figure.alignleft,
.sbkboard-post-content .wp-caption.alignleft,
.sbkboard-post-content .wp-block-image.alignleft {
    float: left;
    margin: 0.3em 1em 0.8em 0;
    max-width: calc(100% - 1em);
    box-sizing: border-box;
}
.sbkboard-post-content .alignright,
.sbkboard-post-content img.alignright,
.sbkboard-post-content figure.alignright,
.sbkboard-post-content .wp-caption.alignright,
.sbkboard-post-content .wp-block-image.alignright {
    float: right;
    margin: 0.3em 0 0.8em 1em;
    max-width: calc(100% - 1em);
    box-sizing: border-box;
}
.sbkboard-post-content .wp-block-image.aligncenter > img,
.sbkboard-post-content .wp-block-image.alignleft > img,
.sbkboard-post-content .wp-block-image.alignright > img {
    display: block;
}
CSS;
		wp_add_inline_style( 'sbkboard-board', $alignment_css );
		wp_register_script( 'sbkboard-board', SBKBOARD_URL . 'assets/board.js', [ 'jquery' ], SBKBOARD_VERSION, true );
		wp_localize_script( 'sbkboard-board', 'SBKBoard', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'sbkboard_nonce' ),
			'i18n'     => [
				'confirm_delete' => __( '정말 삭제하시겠습니까?', 'sbk-board' ),
				'error_occurred' => __( '오류가 발생했습니다.', 'sbk-board' ),
				'enter_password' => __( '비밀번호를 입력하세요.', 'sbk-board' ),
			],
		] );
	}
	public function enqueue_admin( string $hook ): void {
		if ( strpos( $hook, 'sbkboard' ) === false ) return;
		wp_enqueue_style( 'sbkboard-admin', SBKBOARD_URL . 'assets/admin.css', [], SBKBOARD_VERSION );
		wp_enqueue_script( 'sbkboard-admin', SBKBOARD_URL . 'assets/admin.js', [ 'jquery' ], SBKBOARD_VERSION, true );
		wp_localize_script( 'sbkboard-admin', 'SBKBoardAdmin', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'sbkboard_admin_nonce' ),
			'confirm_delete' => __( 'Delete this board?', 'sbk-board' ),
		] );
	}
}
