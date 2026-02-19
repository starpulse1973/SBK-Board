<?php
/**
 * Gallery skin – latest posts widget.
 * Available vars: $board, $settings, $items, $url
 */
defined( 'ABSPATH' ) || exit;

use SBKBoard\DB\FileRepository;

$columns     = max( 1, min( 6, (int) ( $settings['latest_columns'] ?? ( $settings['gallery_columns'] ?? 3 ) ) ) );
$item_min    = (int) max( 120, min( 320, floor( 960 / max( 1, $columns ) ) ) );
$item_min_css = (string) ( $settings['latest_item_min'] ?? '' );
$item_min_css = preg_replace( '/\s+/', '', $item_min_css );
if ( ! is_string( $item_min_css ) || '' === $item_min_css ) {
	$item_min_css = (string) $item_min . 'px';
} elseif ( preg_match( '/^\d+(?:\.\d+)?$/', $item_min_css ) ) {
	$item_min_css .= 'px';
} elseif ( ! preg_match( '/^\d+(?:\.\d+)?(px|%|em|rem)$/', $item_min_css ) ) {
	$item_min_css = (string) $item_min . 'px';
}
$date_format = (string) ( $settings['date_format'] ?? 'Y.m.d' );
$max_len     = max( 10, min( 200, (int) ( $settings['title_max_length'] ?? 80 ) ) );
$target_url  = ! empty( $url ) ? (string) $url : ( get_permalink() ?: home_url( '/' ) );
$css_vars    = \SBKBoard\Core\Style::build_css_vars( (array) $settings );
$post_ids    = array_map(
	static function ( $p ): int {
		return (int) ( $p->id ?? 0 );
	},
	(array) $items
);
$file_thumbs = FileRepository::get_first_image_urls_by_posts( $post_ids );
?>
<div class="sbkboard-latest sbkboard-gallery-latest" style="<?php echo esc_attr( $css_vars ); ?>">
	<div class="sbkboard-gallery-grid" style="--sbk-latest-item-min: <?php echo esc_attr( $item_min_css ); ?>; grid-template-columns: repeat(auto-fit, minmax(var(--sbk-latest-item-min), 1fr));">
		<?php if ( empty( $items ) ) : ?>
			<p style="color:#aaa; padding:16px; text-align:center; grid-column:1/-1;">
				<?php esc_html_e( '등록된 게시글이 없습니다.', 'sbk-board' ); ?>
			</p>
		<?php else : ?>
			<?php foreach ( $items as $post ) :
				$title_raw = (string) ( $post->subject ?? '' );
				if ( function_exists( 'mb_strimwidth' ) ) {
					$title = mb_strimwidth( $title_raw, 0, $max_len, '...', 'UTF-8' );
				} else {
					$title = substr( $title_raw, 0, $max_len );
					if ( strlen( $title_raw ) > strlen( $title ) ) {
						$title .= '...';
					}
				}
				$link      = add_query_arg( 'sbk_view', (int) $post->id, $target_url );
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
				<a href="<?php echo esc_url( $link ); ?>">
					<div class="sbkboard-gallery-thumb">
						<?php if ( $thumb_url ) : ?>
							<img src="<?php echo esc_url( $thumb_url ); ?>"
							     alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
						<?php else : ?>
							<div style="display:flex; align-items:center; justify-content:center; height:100%; color:#bbb; font-size:32px;">&#128196;</div>
						<?php endif; ?>
					</div>
					<div class="sbkboard-gallery-info">
						<div class="title">
							<?php if ( ! empty( $post->is_secret ) ) : ?>
								<span class="sbkboard-secret-badge"><?php esc_html_e( 'Secret', 'sbk-board' ); ?></span>
							<?php endif; ?>
							<?php echo esc_html( $title ); ?>
						</div>
						<div class="meta">
							<?php echo esc_html( mysql2date( $date_format, (string) $post->created_at ) ); ?>
						</div>
					</div>
				</a>
			</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
