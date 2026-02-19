<?php
/**
 * Basic skin latest list.
 * Available vars: $board, $settings, $items, $url
 */
defined( 'ABSPATH' ) || exit;

$settings    = (array) ( $settings ?? [] );
$items       = (array) ( $items ?? [] );
$target_url  = ! empty( $url ) ? (string) $url : ( get_permalink() ?: home_url( '/' ) );
$max_len     = max( 10, min( 200, (int) ( $settings['title_max_length'] ?? 80 ) ) );
$date_format = (string) ( $settings['date_format'] ?? 'Y.m.d' );
$css_vars    = \SBKBoard\Core\Style::build_css_vars( $settings );
?>
<div class="sbkboard-latest" style="<?php echo esc_attr( $css_vars ); ?>">
	<ul class="sbkboard-latest-list">
		<?php if ( empty( $items ) ) : ?>
			<li class="sbkboard-latest-empty"><?php echo esc_html__( 'No posts.', 'sbk-board' ); ?></li>
		<?php else : ?>
			<?php foreach ( $items as $post ) : ?>
				<?php
				$title_raw = (string) ( $post->subject ?? '' );
				if ( function_exists( 'mb_strimwidth' ) ) {
					$title = mb_strimwidth( $title_raw, 0, $max_len, '...', 'UTF-8' );
				} else {
					$title = substr( $title_raw, 0, $max_len );
					if ( strlen( $title_raw ) > strlen( $title ) ) {
						$title .= '...';
					}
				}
				$link = add_query_arg( 'sbk_view', (int) $post->id, $target_url );
				?>
				<li class="sbkboard-latest-item">
					<a class="sbkboard-latest-link" href="<?php echo esc_url( $link ); ?>">
						<?php if ( ! empty( $post->is_secret ) ) : ?>
							<span class="sbkboard-secret-badge"><?php echo esc_html__( 'Secret', 'sbk-board' ); ?></span>
						<?php endif; ?>
						<?php echo esc_html( $title ); ?>
					</a>
					<span class="sbkboard-latest-meta"><?php echo esc_html( mysql2date( $date_format, (string) $post->created_at ) ); ?></span>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>
</div>
