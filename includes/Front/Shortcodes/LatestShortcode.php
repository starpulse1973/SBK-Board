<?php
namespace SBKBoard\Front\Shortcodes;

use SBKBoard\DB\BoardRepository;
use SBKBoard\DB\PostRepository;
use SBKBoard\Skin\SkinLoader;

/**
 * Supported:
 * - [sbk_latest board_id="1" rpp="5" url="" skin=""]
 * - [sbk_latest id="1" count="5"] (legacy)
 * - [sbk_recent ...] (alias)
 */
class LatestShortcode {
	public function init(): void {
		add_shortcode( 'sbk_latest', [ $this, 'render' ] );
		add_shortcode( 'sbk_recent', [ $this, 'render' ] );
	}

	public function render( $atts = [], $content = '', string $tag = 'sbk_latest' ): string {
		$atts = shortcode_atts(
			[
				'board_id' => 0,
				'id'       => 0,
				'slug'     => '',
				'rpp'      => 5,
				'count'    => 5,
				'columns'  => 0,
				'item_min' => 0,
				'title_max_length' => 0,
				'date_format'      => '',
				'url'      => '',
				'page_url' => '',
				'skin'     => '',
			],
			(array) $atts,
			$tag
		);

		$board_id = (int) $atts['board_id'];
		if ( $board_id <= 0 ) {
			$board_id = (int) $atts['id'];
		}

		$board = null;
		if ( $board_id > 0 ) {
			$board = BoardRepository::get_by_id( $board_id );
		} elseif ( ! empty( $atts['slug'] ) ) {
			$board = BoardRepository::get_by_slug( sanitize_key( (string) $atts['slug'] ) );
		}

		if ( ! $board ) {
			return '';
		}

		$settings = BoardRepository::get_settings( (int) $board->id );
		$skin     = sanitize_key( (string) $atts['skin'] );
		if ( '' === $skin ) {
			$skin = (string) ( $settings['skin'] ?? 'basic' );
		}

		$rpp = (int) $atts['rpp'];
		if ( $rpp <= 0 ) {
			$rpp = (int) $atts['count'];
		}
		$rpp = max( 1, min( 50, $rpp ) );
		$columns = (int) $atts['columns'];
		if ( $columns <= 0 ) {
			$columns = min( 6, $rpp );
		}
		$columns = max( 1, min( 6, $columns ) );
		$item_min = max( 0, (int) $atts['item_min'] );
		$title_max_length = max( 0, (int) $atts['title_max_length'] );
		$date_format      = sanitize_text_field( (string) ( $atts['date_format'] ?? '' ) );
		$settings['latest_columns'] = $columns;
		$settings['latest_count']   = $rpp;
		if ( $item_min > 0 ) {
			$settings['latest_item_min'] = max( 80, min( 600, $item_min ) ) . 'px';
		}
		if ( $title_max_length > 0 ) {
			$settings['title_max_length'] = max( 10, min( 200, $title_max_length ) );
		}
		if ( '' !== $date_format ) {
			if ( strlen( $date_format ) > 40 ) {
				$date_format = substr( $date_format, 0, 40 );
			}
			$settings['date_format'] = $date_format;
		}

		$url = esc_url_raw( (string) $atts['url'] );
		if ( '' === $url ) {
			$url = esc_url_raw( (string) $atts['page_url'] );
		}
		if ( '' === $url ) {
			$url = get_permalink() ?: home_url( '/' );
		}

		$list  = PostRepository::get_list( (int) $board->id, [ 'per_page' => $rpp, 'paged' => 1, 'with_content' => ( 'gallery' === $skin ) ] );
		$items = (array) ( $list['items'] ?? [] );

		wp_enqueue_style( 'sbkboard-board' );

		$html = SkinLoader::render(
			$skin,
			'latest',
			[
				'board'    => $board,
				'settings' => $settings,
				'items'    => $items,
				'url'      => $url,
			]
		);

		if ( '' === trim( $html ) ) {
			return $this->render_fallback( $items, $url, $settings );
		}

		return $html;
	}

	private function render_fallback( array $items, string $url, array $settings ): string {
		$max_len     = max( 10, min( 200, (int) ( $settings['title_max_length'] ?? 80 ) ) );
		$date_format = (string) ( $settings['date_format'] ?? 'Y.m.d' );
		$css_vars    = \SBKBoard\Core\Style::build_css_vars( $settings );

		ob_start();
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
						$link = add_query_arg( 'sbk_view', (int) $post->id, $url );
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
		<?php
		return (string) ob_get_clean();
	}
}
