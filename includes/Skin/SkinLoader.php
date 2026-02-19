<?php
namespace SBKBoard\Skin;

class SkinLoader {
	private static array $skins = [];

	public static function get_registered(): array {
		if ( empty( self::$skins ) ) self::discover();
		return self::$skins;
	}

	private static function discover(): void {
		$base = SBKBOARD_DIR . 'skins/';
		if ( ! is_dir( $base ) ) return;
		foreach ( (array) glob( $base . '*/skin.json' ) as $json_file ) {
			$slug = basename( dirname( $json_file ) );
			$data = json_decode( (string) file_get_contents( $json_file ), true );
			if ( is_array( $data ) ) {
				self::$skins[ $slug ] = wp_parse_args( $data, [
					'name'        => $slug,
					'description' => '',
					'version'     => '1.2.2',
					'dir'         => dirname( $json_file ) . '/',
					'url'         => SBKBOARD_URL . 'skins/' . $slug . '/',
				] );
				self::$skins[ $slug ]['dir'] = dirname( $json_file ) . '/';
				self::$skins[ $slug ]['url'] = SBKBOARD_URL . 'skins/' . $slug . '/';
			}
		}
	}

	public static function get( string $slug ): array {
		$skins = self::get_registered();
		return $skins[ $slug ] ?? $skins['basic'] ?? [];
	}

	/**
	 * Render a skin template file and return the HTML.
	 *
	 * @param string $slug   Skin slug.
	 * @param string $tpl    Template name without .php extension (e.g. 'list', 'view').
	 * @param array  $vars   Variables to extract into template scope.
	 */
	public static function render( string $slug, string $tpl, array $vars = [] ): string {
		$skin = self::get( $slug );
		if ( empty( $skin['dir'] ) ) return '';
		$file = $skin['dir'] . $tpl . '.php';
		if ( ! file_exists( $file ) ) {
			// Fallback to basic skin
			$basic = self::get( 'basic' );
			$file  = ( $basic['dir'] ?? '' ) . $tpl . '.php';
		}
		if ( ! file_exists( $file ) ) return '';
		ob_start();
		extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
		include $file;
		return (string) ob_get_clean();
	}
}

