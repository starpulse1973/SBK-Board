<?php
/**
 * Gallery skin – inner grid (AJAX-replaceable).
 * Available vars: $board, $settings, $items, $notices, $total, $paged, $per_page, $keyword
 */
defined( 'ABSPATH' ) || exit;

use SBKBoard\DB\FileRepository;

$board_id    = (int) $board->id;
$page_url    = ! empty( $page_url ) ? esc_url_raw( (string) $page_url ) : ( get_permalink() ?: home_url( '/' ) );
$total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;
$all_posts   = array_merge( $notices, $items );
$show_author = ! isset( $settings['show_author'] ) || ! empty( $settings['show_author'] );
$date_format = (string) ( $settings['date_format'] ?? 'Y.m.d' );
$title_max_length = max( 10, min( 200, (int) ( $settings['title_max_length'] ?? 80 ) ) );
$columns     = max( 1, min( 6, (int) ( $settings['gallery_columns'] ?? 3 ) ) );
$post_ids    = array_map(
	static function ( $p ): int {
		return (int) ( $p->id ?? 0 );
	},
	$all_posts
);
$file_thumbs = FileRepository::get_first_image_urls_by_posts( $post_ids );
?>

<div class="sbkboard-gallery-grid" style="grid-template-columns: repeat(<?php echo esc_attr( (string) $columns ); ?>, 1fr);">
	<?php foreach ( $all_posts as $post ) :
		$view_url = add_query_arg( 'sbk_view', $post->id, $page_url );
		$thumb_url = '';
		$content   = (string) ( $post->content ?? '' );
		if ( preg_match( '/<img[^>]+src\s*=\s*([\"\'])(.*?)\1/i', $content, $match ) ) {
			$thumb_url = html_entity_decode( (string) $match[2], ENT_QUOTES, 'UTF-8' );
		} elseif ( preg_match( '/<img[^>]+src\s*=\s*([^\s>]+)/i', $content, $match ) ) {
			$thumb_url = html_entity_decode( trim( (string) $match[1], "\"'" ), ENT_QUOTES, 'UTF-8' );
		}
		if ( '' === $thumb_url ) {
			$thumb_url = (string) ( $file_thumbs[ (int) $post->id ] ?? '' );
		}
	?>
	<div class="sbkboard-gallery-item">
		<a href="<?php echo esc_url( $view_url ); ?>">
			<div class="sbkboard-gallery-thumb">
				<?php if ( $thumb_url ) : ?>
					<img src="<?php echo esc_url( $thumb_url ); ?>"
					     alt="<?php echo esc_attr( $post->subject ); ?>" loading="lazy">
				<?php else : ?>
					<div style="display:flex; align-items:center; justify-content:center; height:100%; color:#bbb; font-size:32px;">&#128196;</div>
				<?php endif; ?>
			</div>
			<div class="sbkboard-gallery-info">
				<div class="title">
					<?php if ( $post->is_notice ) : ?>
						<span class="sbkboard-notice-badge"><?php esc_html_e( '공지', 'sbk-board' ); ?></span>
					<?php endif; ?>
					<?php
					$title_raw = (string) $post->subject;
					if ( function_exists( 'mb_strimwidth' ) ) {
						$title = mb_strimwidth( $title_raw, 0, $title_max_length, '...', 'UTF-8' );
					} else {
						$title = substr( $title_raw, 0, $title_max_length );
						if ( strlen( $title_raw ) > strlen( $title ) ) {
							$title .= '...';
						}
					}
					echo esc_html( $title );
					?>
				</div>
				<div class="meta">
					<?php if ( $show_author ) : ?>
						<?php echo esc_html( $post->author_name ); ?>
						&middot;
					<?php endif; ?>
					<?php echo esc_html( mysql2date( $date_format, $post->created_at ) ); ?>
				</div>
			</div>
		</a>
	</div>
	<?php endforeach; ?>

	<?php if ( empty( $all_posts ) ) : ?>
	<p style="color:#aaa; padding:32px; text-align:center; grid-column:1/-1;">
		<?php esc_html_e( '등록된 게시글이 없습니다.', 'sbk-board' ); ?>
	</p>
	<?php endif; ?>
</div>

<!-- Pagination -->
<?php if ( $total_pages > 1 ) : ?>
<div class="sbkboard-pagination">
	<?php if ( $paged > 1 ) : ?>
		<a href="#" class="sbkboard-page-link" data-page="<?php echo esc_attr( $paged - 1 ); ?>">&laquo;</a>
	<?php endif; ?>
	<?php
	$range = 5;
	$start = max( 1, $paged - floor( $range / 2 ) );
	$end   = min( $total_pages, $start + $range - 1 );
	$start = max( 1, $end - $range + 1 );
	for ( $p = $start; $p <= $end; $p++ ) :
	?>
		<?php if ( $p === $paged ) : ?>
			<span class="current"><?php echo esc_html( $p ); ?></span>
		<?php else : ?>
			<a href="#" class="sbkboard-page-link" data-page="<?php echo esc_attr( $p ); ?>"><?php echo esc_html( $p ); ?></a>
		<?php endif; ?>
	<?php endfor; ?>
	<?php if ( $paged < $total_pages ) : ?>
		<a href="#" class="sbkboard-page-link" data-page="<?php echo esc_attr( $paged + 1 ); ?>">&raquo;</a>
	<?php endif; ?>
</div>
<?php endif; ?>
