<?php
/**
 * Basic skin – inner table (AJAX-replaceable).
 * Available vars: $board, $settings, $items, $notices, $total, $paged, $per_page, $keyword
 */
defined( 'ABSPATH' ) || exit;

$board_id    = (int) $board->id;
$page_url    = ! empty( $page_url ) ? esc_url_raw( (string) $page_url ) : ( get_permalink() ?: home_url( '/' ) );
$total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;
$show_views  = ! empty( $settings['show_views'] );
$show_votes  = ! empty( $settings['show_votes'] );
$show_author = ! isset( $settings['show_author'] ) || ! empty( $settings['show_author'] );
$date_format = (string) ( $settings['date_format'] ?? 'Y.m.d' );
$title_max_length = max( 10, min( 200, (int) ( $settings['title_max_length'] ?? 80 ) ) );

$num_start = $total - ( ( $paged - 1 ) * $per_page );
?>

<table class="sbkboard-list">
	<thead>
		<tr>
			<th class="col-num"><?php esc_html_e( '번호', 'sbk-board' ); ?></th>
			<th class="col-title"><?php esc_html_e( '제목', 'sbk-board' ); ?></th>
			<?php if ( $show_author ) : ?>
				<th class="col-author"><?php esc_html_e( '작성자', 'sbk-board' ); ?></th>
			<?php endif; ?>
			<th class="col-date"><?php esc_html_e( '날짜', 'sbk-board' ); ?></th>
			<?php if ( $show_views ) : ?><th class="col-views"><?php esc_html_e( '조회', 'sbk-board' ); ?></th><?php endif; ?>
			<?php if ( $show_votes ) : ?><th class="col-votes"><?php esc_html_e( '추천', 'sbk-board' ); ?></th><?php endif; ?>
		</tr>
	</thead>
	<tbody>
		<?php
		// Notices first
		foreach ( $notices as $post ) :
			$view_url = add_query_arg( [ 'sbk_view' => $post->id ], $page_url );
		?>
		<tr>
			<td class="col-num">
				<span class="sbkboard-notice-badge"><?php esc_html_e( '공지', 'sbk-board' ); ?></span>
			</td>
			<td class="col-title">
				<a href="<?php echo esc_url( $view_url ); ?>">
					<?php if ( $post->is_secret ) : ?>
						<span class="sbkboard-secret-badge"><?php esc_html_e( '비밀', 'sbk-board' ); ?></span>
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
				</a>
			</td>
			<?php if ( $show_author ) : ?><td class="col-author"><?php echo esc_html( $post->author_name ); ?></td><?php endif; ?>
			<td class="col-date"><?php echo esc_html( mysql2date( $date_format, $post->created_at ) ); ?></td>
			<?php if ( $show_views ) : ?><td class="col-views"><?php echo esc_html( number_format( $post->view_count ) ); ?></td><?php endif; ?>
			<?php if ( $show_votes ) : ?><td class="col-votes"><?php echo esc_html( ( (int)$post->vote_up - (int)$post->vote_down ) ); ?></td><?php endif; ?>
		</tr>
		<?php endforeach; ?>

		<?php
		// Regular posts
		$i = 0;
		foreach ( $items as $post ) :
			$num      = $num_start - $i++;
			$view_url = add_query_arg( [ 'sbk_view' => $post->id ], $page_url );
		?>
		<tr>
			<td class="col-num"><?php echo esc_html( $num ); ?></td>
			<td class="col-title">
				<?php if ( (int) $post->depth > 0 ) : ?>
					<span class="sbkboard-depth-indent" style="width:<?php echo (int) $post->depth * 16; ?>px; display:inline-block;"></span>
					<span class="sbkboard-reply-indicator">↳</span>
				<?php endif; ?>
				<a href="<?php echo esc_url( $view_url ); ?>">
					<?php if ( $post->is_secret ) : ?>
						<span class="sbkboard-secret-badge"><?php esc_html_e( '비밀', 'sbk-board' ); ?></span>
					<?php endif; ?>
					<?php
					$row_title_raw = (string) $post->subject;
					if ( function_exists( 'mb_strimwidth' ) ) {
						$row_title = mb_strimwidth( $row_title_raw, 0, $title_max_length, '...', 'UTF-8' );
					} else {
						$row_title = substr( $row_title_raw, 0, $title_max_length );
						if ( strlen( $row_title_raw ) > strlen( $row_title ) ) {
							$row_title .= '...';
						}
					}
					echo esc_html( $row_title );
					?>
				</a>
			</td>
			<?php if ( $show_author ) : ?><td class="col-author"><?php echo esc_html( $post->author_name ); ?></td><?php endif; ?>
			<td class="col-date"><?php echo esc_html( mysql2date( $date_format, $post->created_at ) ); ?></td>
			<?php if ( $show_views ) : ?><td class="col-views"><?php echo esc_html( number_format( $post->view_count ) ); ?></td><?php endif; ?>
			<?php if ( $show_votes ) : ?><td class="col-votes"><?php echo esc_html( ( (int)$post->vote_up - (int)$post->vote_down ) ); ?></td><?php endif; ?>
		</tr>
		<?php endforeach; ?>

		<?php if ( empty( $items ) && empty( $notices ) ) : ?>
		<tr>
			<td colspan="<?php echo 2 + (int)$show_author + 1 + (int)$show_views + (int)$show_votes; ?>"
			    style="text-align:center; padding:32px; color:#aaa;">
				<?php esc_html_e( '등록된 게시글이 없습니다.', 'sbk-board' ); ?>
			</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>

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
