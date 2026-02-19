<?php
/**
 * Basic skin – full list wrapper (used by shortcode / Elementor widget).
 * Available vars: $board, $settings, $items, $notices, $total, $paged, $per_page, $keyword
 */
defined( 'ABSPATH' ) || exit;

$board_id  = (int) $board->id;
$can_write = ! empty( $settings['allow_guest_write'] ) || is_user_logged_in();
$page_url  = ! empty( $page_url ) ? esc_url_raw( (string) $page_url ) : ( get_permalink() ?: home_url( '/' ) );
$css_vars  = \SBKBoard\Core\Style::build_css_vars( (array) $settings );

// Pagination
$total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;
?>
<div class="sbkboard"
     data-board-id="<?php echo esc_attr( $board_id ); ?>"
     data-per-page="<?php echo esc_attr( (string) (int) $per_page ); ?>"
     data-gallery-columns="<?php echo esc_attr( (string) max( 1, min( 6, (int) ( $settings['gallery_columns'] ?? 3 ) ) ) ); ?>"
     data-page-url="<?php echo esc_url( $page_url ); ?>"
     style="<?php echo esc_attr( $css_vars ); ?>">

	<!-- Toolbar -->
	<div class="sbkboard-toolbar">
		<form class="sbkboard-search" method="get" action="<?php echo esc_url( $page_url ); ?>">
			<input type="hidden" name="sbk_board" value="<?php echo esc_attr( $board_id ); ?>">
			<input type="text" name="sbk_q" value="<?php echo esc_attr( $keyword ); ?>"
			       placeholder="<?php esc_attr_e( '검색어 입력', 'sbk-board' ); ?>">
			<button type="submit" class="sbkboard-btn sbkboard-btn-secondary sbkboard-btn-search">
				<?php esc_html_e( '검색', 'sbk-board' ); ?>
			</button>
		</form>
		<?php if ( $can_write ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'sbk_write', $board_id, $page_url ) ); ?>"
			   class="sbkboard-btn sbkboard-btn-primary sbkboard-btn-write">
				<?php esc_html_e( '글쓰기', 'sbk-board' ); ?>
			</a>
		<?php endif; ?>
	</div>

	<!-- List area (re-rendered on AJAX page change) -->
	<div class="sbkboard-list-area">
		<?php include __DIR__ . '/list-inner.php'; ?>
	</div>

</div>
