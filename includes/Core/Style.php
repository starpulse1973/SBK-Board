<?php
namespace SBKBoard\Core;

class Style {
	public static function build_css_vars( array $settings ): string {
		$vars = [];

		$vars[] = '--sbk-primary:' . self::hex_color( (string) ( $settings['primary_color'] ?? '' ), '#0073aa' );
		$vars[] = '--sbk-accent:' . self::hex_color( (string) ( $settings['accent_color'] ?? '' ), '#d63638' );
		$vars[] = '--sbk-border-width:' . self::int_px( $settings['border_width'] ?? 1, 0, 10, 1 );
		$vars[] = '--sbk-search-width:' . self::css_length( (string) ( $settings['search_width'] ?? '260px' ), '260px' );

		$fonts = isset( $settings['fonts'] ) && is_array( $settings['fonts'] ) ? $settings['fonts'] : [];
		$vars[] = '--sbk-list-head-font:' . self::int_px( $fonts['list_header'] ?? 12, 10, 40, 12 );
		$vars[] = '--sbk-list-body-font:' . self::int_px( $fonts['list_body'] ?? 14, 10, 40, 14 );
		$vars[] = '--sbk-btn-font:' . self::int_px( $fonts['button_native'] ?? 13, 10, 40, 13 );

		$list_table = isset( $settings['list_table'] ) && is_array( $settings['list_table'] ) ? $settings['list_table'] : [];
		$vars[] = '--sbk-list-row-pad-y:' . self::css_length( (string) ( $list_table['row_padding_y'] ?? '8px' ), '8px' );
		$vars[] = '--sbk-list-head-bg:' . self::hex_color( (string) ( $list_table['header_bg'] ?? '' ), '#f6f7f7' );
		$vars[] = '--sbk-list-head-color:' . self::hex_color( (string) ( $list_table['header_text'] ?? '' ), '#1d2327' );
		$vars[] = '--sbk-list-divider:' . self::hex_color( (string) ( $list_table['divider_color'] ?? '' ), '#e5e5e5' );
		$vars[] = '--sbk-list-divider-w:' . self::int_px( $list_table['divider_width'] ?? 1, 0, 10, 1 );
		$vars[] = '--sbk-list-divider-style:' . self::line_style( (string) ( $list_table['divider_style'] ?? 'solid' ) );
		$vars[] = '--sbk-list-hover-bg:' . self::hex_color( (string) ( $list_table['hover_bg'] ?? '' ), '#f9f9f9' );

		$view_table = isset( $settings['view_table'] ) && is_array( $settings['view_table'] ) ? $settings['view_table'] : [];
		$vars[] = '--sbk-view-td-bg:' . self::view_bg( (string) ( $view_table['td_bg'] ?? 'transparent' ) );
		$vars[] = '--sbk-view-divider:' . self::hex_color( (string) ( $view_table['divider_color'] ?? '' ), '#dddddd' );
		$vars[] = '--sbk-view-divider-w:' . self::int_px( $view_table['divider_width'] ?? 1, 0, 10, 1 );
		$vars[] = '--sbk-view-divider-style:' . self::line_style( (string) ( $view_table['divider_style'] ?? 'solid' ) );
		$vars[] = '--sbk-view-title-size:' . self::int_px( $view_table['title_size'] ?? 18, 10, 60, 18 );
		$vars[] = '--sbk-view-title-weight:' . self::font_weight( $view_table['title_weight'] ?? 700, 700 );
		$vars[] = '--sbk-view-title-color:' . self::hex_color( (string) ( $view_table['title_color'] ?? '' ), '#1d2327' );
		$vars[] = '--sbk-view-body-size:' . self::int_px( $view_table['body_size'] ?? 14, 10, 60, 14 );
		$vars[] = '--sbk-view-body-weight:' . self::font_weight( $view_table['body_weight'] ?? 400, 400 );
		$vars[] = '--sbk-view-body-color:' . self::hex_color( (string) ( $view_table['body_color'] ?? '' ), '#333333' );
		$vars[] = '--sbk-view-body-line-height:' . self::line_height( (string) ( $view_table['body_line_height'] ?? '1.8' ), '1.8' );

		return implode( ';', $vars );
	}

	private static function hex_color( string $value, string $default ): string {
		$value = trim( $value );
		if ( preg_match( '/^[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $value ) ) {
			$value = '#' . $value;
		}
		$color = sanitize_hex_color( $value );
		if ( ! $color ) {
			$color = sanitize_hex_color( $default );
		}
		return $color ? $color : $default;
	}

	private static function int_px( $value, int $min, int $max, int $default ): string {
		$int = (int) $value;
		if ( $int < $min || $int > $max ) {
			$int = $default;
		}
		return $int . 'px';
	}

	private static function css_length( string $value, string $default ): string {
		$value = preg_replace( '/\s+/', '', $value );
		if ( ! is_string( $value ) || '' === $value ) {
			return $default;
		}
		if ( preg_match( '/^\d+(?:\.\d+)?(px|rem|em|%)$/', $value ) ) {
			return $value;
		}
		if ( preg_match( '/^\d+(?:\.\d+)?$/', $value ) ) {
			return $value . 'px';
		}
		return $default;
	}

	private static function line_style( string $value ): string {
		return in_array( $value, [ 'solid', 'dashed', 'dotted' ], true ) ? $value : 'solid';
	}

	private static function font_weight( $value, int $default ): string {
		$weight = (int) $value;
		if ( $weight < 100 || $weight > 900 ) {
			$weight = $default;
		}
		$weight = (int) ( round( $weight / 100 ) * 100 );
		return (string) $weight;
	}

	private static function line_height( string $value, string $default ): string {
		$value = trim( $value );
		if ( '' === $value ) {
			return $default;
		}
		if ( preg_match( '/^\d+(?:\.\d+)?$/', $value ) ) {
			$num = (float) $value;
			if ( $num >= 0.8 && $num <= 4 ) {
				return rtrim( rtrim( (string) $num, '0' ), '.' );
			}
			return $default;
		}
		if ( preg_match( '/^\d+(?:\.\d+)?(px|rem|em|%)$/', $value ) ) {
			return $value;
		}
		return $default;
	}

	private static function view_bg( string $value ): string {
		$value = trim( $value );
		if ( 'transparent' === strtolower( $value ) ) {
			return 'transparent';
		}
		return self::hex_color( $value, 'transparent' );
	}
}