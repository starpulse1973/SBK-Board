<?php
namespace SBKBoard\Core;
class Sanitizer {
	public static function text( string $value ): string { return sanitize_text_field( wp_unslash( $value ) ); }
	public static function textarea( string $value ): string { return sanitize_textarea_field( wp_unslash( $value ) ); }
	public static function html( string $value ): string { return wp_kses_post( wp_unslash( $value ) ); }
	public static function int( $value ): int { return (int) $value; }
	public static function email( string $value ): string { return sanitize_email( wp_unslash( $value ) ); }
	public static function request( string $key, string $type = 'text' ) {
		$raw = $_REQUEST[ $key ] ?? '';
		switch ( $type ) {
			case 'int':      return self::int( $raw );
			case 'html':     return self::html( $raw );
			case 'email':    return self::email( $raw );
			case 'textarea': return self::textarea( $raw );
			default:         return self::text( $raw );
		}
	}
}
