<?php
namespace SBKBoard\DB;
class CategoryRepository {
	private static function table(): string { global $wpdb; return $wpdb->prefix . 'sbk_categories'; }
	public static function get_by_board( int $board_id ): array { global $wpdb; return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE board_id=%d ORDER BY sort_order ASC, id ASC', $board_id ) ) ?: []; }
	public static function insert( array $data ): int { global $wpdb; $wpdb->insert( self::table(), $data ); return (int) $wpdb->insert_id; }
	public static function update( int $id, array $data ): bool { global $wpdb; return (bool) $wpdb->update( self::table(), $data, [ 'id' => $id ] ); }
	public static function delete( int $id ): bool { global $wpdb; return (bool) $wpdb->delete( self::table(), [ 'id' => $id ] ); }
}
