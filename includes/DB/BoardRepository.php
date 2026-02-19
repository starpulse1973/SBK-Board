<?php
namespace SBKBoard\DB;

class BoardRepository {
	private static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'sbk_boards';
	}

	public static function get_all(): array {
		global $wpdb;
		return $wpdb->get_results( 'SELECT * FROM ' . self::table() . ' ORDER BY id ASC' ) ?: [];
	}

	public static function get_by_id( int $id ): ?object {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id=%d', $id ) );
	}

	public static function get_by_slug( string $slug ): ?object {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE slug=%s', $slug ) );
	}

	public static function insert( array $data ): int {
		global $wpdb;
		$wpdb->insert( self::table(), $data );
		return (int) $wpdb->insert_id;
	}

	public static function update( int $id, array $data ): bool {
		global $wpdb;
		return false !== $wpdb->update( self::table(), $data, [ 'id' => $id ] );
	}

	public static function delete( int $id ): bool {
		global $wpdb;
		return false !== $wpdb->delete( self::table(), [ 'id' => $id ] );
	}

	public static function default_settings(): array {
		$global = (array) get_option( 'sbkboard_settings', [] );
		$skin   = sanitize_key( (string) ( $global['skin_default'] ?? 'basic' ) );
		$skin   = $skin ?: 'basic';

		return [
			'skin'              => $skin,
			'per_page'          => max( 1, min( 100, (int) ( $global['posts_per_page'] ?? 15 ) ) ),
			'allow_guest_write' => 0,
			'read_permission'   => 'everyone',
			'write_permission'  => 'logged_in',
			'allow_comments'    => isset( $global['enable_comments'] ) ? ( ! empty( $global['enable_comments'] ) ? 1 : 0 ) : 1,
			'show_votes'        => isset( $global['enable_votes'] ) ? ( ! empty( $global['enable_votes'] ) ? 1 : 0 ) : 1,
			'allow_downvote'    => 0,
			'allow_secret'      => 1,
			'enable_notice'     => 1,
			'allow_attach'      => 1,
			'max_files'         => 5,
			'upload_max_size'   => max( 1, min( 200, (int) ( $global['upload_max_size'] ?? 5 ) ) ),
			'allowed_exts'      => self::normalize_exts( (string) ( $global['allowed_ext'] ?? 'jpg,jpeg,png,gif,pdf,zip,doc,docx' ) ),
			'allow_html'        => 0,
			'editor_type'       => 'textarea',
			'gallery_columns'   => 3,
			'show_views'        => 1,
			'show_author'       => 1,
			'title_max_length'  => 80,
			'date_format'       => 'Y.m.d',
			'search_width'      => '260px',
			'primary_color'     => '#0073aa',
			'accent_color'      => '#d63638',
			'border_width'      => 1,
			'fonts'             => [
				'list_header'   => 12,
				'list_body'     => 14,
				'button_native' => 13,
			],
			'list_table'        => [
				'row_padding_y' => '8px',
				'header_bg'     => '#f6f7f7',
				'header_text'   => '#1d2327',
				'divider_color' => '#e5e5e5',
				'divider_width' => 1,
				'divider_style' => 'solid',
				'hover_bg'      => '#f9f9f9',
			],
			'view_table'        => [
				'td_bg'            => 'transparent',
				'divider_color'    => '#dddddd',
				'divider_width'    => 1,
				'divider_style'    => 'solid',
				'title_size'       => 18,
				'title_weight'     => 700,
				'title_color'      => '#1d2327',
				'body_size'        => 14,
				'body_weight'      => 400,
				'body_color'       => '#333333',
				'body_line_height' => '1.8',
			],
		];
	}

	public static function normalize_settings( array $settings ): array {
		$defaults = self::default_settings();
		$data     = $settings;

		// Legacy key compatibility.
		if ( isset( $data['posts_per_page'] ) && ! isset( $data['per_page'] ) ) {
			$data['per_page'] = $data['posts_per_page'];
		}
		if ( isset( $data['enable_comments'] ) && ! isset( $data['allow_comments'] ) ) {
			$data['allow_comments'] = $data['enable_comments'];
		}
		if ( isset( $data['enable_votes'] ) && ! isset( $data['show_votes'] ) ) {
			$data['show_votes'] = $data['enable_votes'];
		}
		if ( isset( $data['enable_secret'] ) && ! isset( $data['allow_secret'] ) ) {
			$data['allow_secret'] = $data['enable_secret'];
		}
		if ( isset( $data['enable_file'] ) && ! isset( $data['allow_attach'] ) ) {
			$data['allow_attach'] = $data['enable_file'];
		}
		if ( isset( $data['allowed_ext'] ) && ! isset( $data['allowed_exts'] ) ) {
			$data['allowed_exts'] = $data['allowed_ext'];
		}
		if ( isset( $data['enable_write'] ) && ! isset( $data['allow_guest_write'] ) ) {
			$is_everyone = ( isset( $data['write_permission'] ) && 'everyone' === (string) $data['write_permission'] );
			$data['allow_guest_write'] = ( ! empty( $data['enable_write'] ) && $is_everyone ) ? 1 : 0;
		}
		if ( isset( $data['colors'] ) && is_array( $data['colors'] ) ) {
			if ( isset( $data['colors']['primary'] ) && ! isset( $data['primary_color'] ) ) {
				$data['primary_color'] = $data['colors']['primary'];
			}
			if ( isset( $data['colors']['accent'] ) && ! isset( $data['accent_color'] ) ) {
				$data['accent_color'] = $data['colors']['accent'];
			}
		}

		$normalized = $defaults;

		$skin = sanitize_key( (string) ( $data['skin'] ?? $defaults['skin'] ) );
		$normalized['skin'] = $skin ?: $defaults['skin'];

		$normalized['per_page'] = max( 1, min( 100, (int) ( $data['per_page'] ?? $defaults['per_page'] ) ) );

		$read_permission = sanitize_key( (string) ( $data['read_permission'] ?? $defaults['read_permission'] ) );
		if ( ! in_array( $read_permission, [ 'everyone', 'logged_in', 'admin' ], true ) ) {
			$read_permission = $defaults['read_permission'];
		}
		$normalized['read_permission'] = $read_permission;

		$write_permission = sanitize_key( (string) ( $data['write_permission'] ?? $defaults['write_permission'] ) );
		if ( ! in_array( $write_permission, [ 'everyone', 'logged_in', 'admin' ], true ) ) {
			$write_permission = $defaults['write_permission'];
		}
		$normalized['write_permission'] = $write_permission;

		$normalized['allow_guest_write'] = ! empty( $data['allow_guest_write'] ) ? 1 : 0;
		$normalized['allow_comments']    = ! empty( $data['allow_comments'] ) ? 1 : 0;
		$normalized['show_votes']        = ! empty( $data['show_votes'] ) ? 1 : 0;
		$normalized['allow_downvote']    = ! empty( $data['allow_downvote'] ) ? 1 : 0;
		$normalized['allow_secret']      = ! empty( $data['allow_secret'] ) ? 1 : 0;
		$normalized['enable_notice']     = ! empty( $data['enable_notice'] ) ? 1 : 0;
		$normalized['allow_attach']      = ! empty( $data['allow_attach'] ) ? 1 : 0;
		$normalized['allow_html']        = ! empty( $data['allow_html'] ) ? 1 : 0;
		$editor_type = sanitize_key( (string) ( $data['editor_type'] ?? 'textarea' ) );
		$normalized['editor_type']      = in_array( $editor_type, [ 'textarea', 'wp_editor' ], true ) ? $editor_type : 'textarea';
		$normalized['gallery_columns']  = max( 1, min( 6, (int) ( $data['gallery_columns'] ?? 3 ) ) );
		$normalized['show_views']       = ! empty( $data['show_views'] ) ? 1 : 0;
		$normalized['show_author']      = ! empty( $data['show_author'] ) ? 1 : 0;

		$normalized['max_files']       = max( 1, min( 20, (int) ( $data['max_files'] ?? $defaults['max_files'] ) ) );
		$normalized['upload_max_size'] = max( 1, min( 200, (int) ( $data['upload_max_size'] ?? $defaults['upload_max_size'] ) ) );
		$normalized['allowed_exts']    = self::normalize_exts( (string) ( $data['allowed_exts'] ?? $defaults['allowed_exts'] ) );

		$normalized['title_max_length'] = max( 10, min( 200, (int) ( $data['title_max_length'] ?? $defaults['title_max_length'] ) ) );

		$date_format = sanitize_text_field( (string) ( $data['date_format'] ?? $defaults['date_format'] ) );
		if ( '' === $date_format ) {
			$date_format = $defaults['date_format'];
		}
		if ( strlen( $date_format ) > 40 ) {
			$date_format = substr( $date_format, 0, 40 );
		}
		$normalized['date_format'] = $date_format;

		$search_width = sanitize_text_field( (string) ( $data['search_width'] ?? $defaults['search_width'] ) );
		$search_width = preg_replace( '/\s+/', '', $search_width );
		if ( ! preg_match( '/^\d+(px|%)$/', $search_width ) ) {
			$search_width = $defaults['search_width'];
		}
		$normalized['search_width'] = $search_width;

		$normalized['primary_color'] = self::sanitize_hex( (string) ( $data['primary_color'] ?? $defaults['primary_color'] ), $defaults['primary_color'] );
		$normalized['accent_color']  = self::sanitize_hex( (string) ( $data['accent_color'] ?? $defaults['accent_color'] ), $defaults['accent_color'] );
		$normalized['border_width']  = max( 0, min( 10, (int) ( $data['border_width'] ?? $defaults['border_width'] ) ) );

		$fonts = isset( $data['fonts'] ) && is_array( $data['fonts'] ) ? $data['fonts'] : [];
		$normalized['fonts'] = [
			'list_header'   => max( 10, min( 40, (int) ( $fonts['list_header'] ?? $defaults['fonts']['list_header'] ) ) ),
			'list_body'     => max( 10, min( 40, (int) ( $fonts['list_body'] ?? $defaults['fonts']['list_body'] ) ) ),
			'button_native' => max( 10, min( 40, (int) ( $fonts['button_native'] ?? $defaults['fonts']['button_native'] ) ) ),
		];

		$list_table = isset( $data['list_table'] ) && is_array( $data['list_table'] ) ? $data['list_table'] : [];
		$list_divider_style = (string) ( $list_table['divider_style'] ?? 'solid' );
		if ( ! in_array( $list_divider_style, [ 'solid', 'dashed', 'dotted' ], true ) ) {
			$list_divider_style = 'solid';
		}
		$normalized['list_table'] = [
			'row_padding_y' => self::sanitize_css_length( (string) ( $list_table['row_padding_y'] ?? $defaults['list_table']['row_padding_y'] ), $defaults['list_table']['row_padding_y'] ),
			'header_bg'     => self::sanitize_hex( (string) ( $list_table['header_bg'] ?? $defaults['list_table']['header_bg'] ), $defaults['list_table']['header_bg'] ),
			'header_text'   => self::sanitize_hex( (string) ( $list_table['header_text'] ?? $defaults['list_table']['header_text'] ), $defaults['list_table']['header_text'] ),
			'divider_color' => self::sanitize_hex( (string) ( $list_table['divider_color'] ?? $defaults['list_table']['divider_color'] ), $defaults['list_table']['divider_color'] ),
			'divider_width' => max( 0, min( 10, (int) ( $list_table['divider_width'] ?? $defaults['list_table']['divider_width'] ) ) ),
			'divider_style' => $list_divider_style,
			'hover_bg'      => self::sanitize_hex( (string) ( $list_table['hover_bg'] ?? $defaults['list_table']['hover_bg'] ), $defaults['list_table']['hover_bg'] ),
		];

		$view_table = isset( $data['view_table'] ) && is_array( $data['view_table'] ) ? $data['view_table'] : [];
		$view_divider_style = (string) ( $view_table['divider_style'] ?? 'solid' );
		if ( ! in_array( $view_divider_style, [ 'solid', 'dashed', 'dotted' ], true ) ) {
			$view_divider_style = 'solid';
		}
		$view_bg = (string) ( $view_table['td_bg'] ?? $defaults['view_table']['td_bg'] );
		$view_bg = trim( $view_bg );
		if ( '' === $view_bg || 'transparent' === strtolower( $view_bg ) ) {
			$view_bg = 'transparent';
		} else {
			$view_bg = self::sanitize_hex( $view_bg, $defaults['view_table']['td_bg'] );
		}
		$title_weight = max( 100, min( 900, (int) ( $view_table['title_weight'] ?? $defaults['view_table']['title_weight'] ) ) );
		$title_weight = (int) ( round( $title_weight / 100 ) * 100 );
		$body_weight  = max( 100, min( 900, (int) ( $view_table['body_weight'] ?? $defaults['view_table']['body_weight'] ) ) );
		$body_weight  = (int) ( round( $body_weight / 100 ) * 100 );
		$normalized['view_table'] = [
			'td_bg'            => $view_bg,
			'divider_color'    => self::sanitize_hex( (string) ( $view_table['divider_color'] ?? $defaults['view_table']['divider_color'] ), $defaults['view_table']['divider_color'] ),
			'divider_width'    => max( 0, min( 10, (int) ( $view_table['divider_width'] ?? $defaults['view_table']['divider_width'] ) ) ),
			'divider_style'    => $view_divider_style,
			'title_size'       => max( 10, min( 60, (int) ( $view_table['title_size'] ?? $defaults['view_table']['title_size'] ) ) ),
			'title_weight'     => $title_weight,
			'title_color'      => self::sanitize_hex( (string) ( $view_table['title_color'] ?? $defaults['view_table']['title_color'] ), $defaults['view_table']['title_color'] ),
			'body_size'        => max( 10, min( 60, (int) ( $view_table['body_size'] ?? $defaults['view_table']['body_size'] ) ) ),
			'body_weight'      => $body_weight,
			'body_color'       => self::sanitize_hex( (string) ( $view_table['body_color'] ?? $defaults['view_table']['body_color'] ), $defaults['view_table']['body_color'] ),
			'body_line_height' => self::sanitize_line_height( (string) ( $view_table['body_line_height'] ?? $defaults['view_table']['body_line_height'] ), $defaults['view_table']['body_line_height'] ),
		];

		return $normalized;
	}

	public static function get_settings( int $id ): array {
		$board = self::get_by_id( $id );
		if ( ! $board ) {
			return self::default_settings();
		}

		$decoded = json_decode( (string) $board->settings, true );
		if ( ! is_array( $decoded ) ) {
			$decoded = [];
		}

		return self::normalize_settings( $decoded );
	}

	public static function save_settings( int $id, array $settings ): bool {
		$normalized = self::normalize_settings( $settings );
		return self::update( $id, [ 'settings' => wp_json_encode( $normalized ) ] );
	}

	private static function normalize_exts( string $exts ): string {
		$parts = preg_split( '/\s*,\s*/', strtolower( $exts ), -1, PREG_SPLIT_NO_EMPTY );
		if ( ! is_array( $parts ) ) {
			return 'jpg,jpeg,png,gif,pdf,zip';
		}

		$clean = [];
		foreach ( $parts as $part ) {
			$ext = preg_replace( '/[^a-z0-9]/', '', (string) $part );
			if ( '' !== $ext ) {
				$clean[] = $ext;
			}
		}

		$clean = array_values( array_unique( $clean ) );
		if ( empty( $clean ) ) {
			return 'jpg,jpeg,png,gif,pdf,zip';
		}

		return implode( ',', $clean );
	}

	private static function sanitize_hex( string $value, string $default ): string {
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

	private static function sanitize_css_length( string $value, string $default ): string {
		$value = preg_replace( '/\s+/', '', $value );
		if ( '' === $value ) {
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

	private static function sanitize_line_height( string $value, string $default ): string {
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
}