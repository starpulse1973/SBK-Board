<?php
namespace SBKBoard\DB;
class CommentRepository {
	private static function table(): string { global $wpdb; return $wpdb->prefix . 'sbk_comments'; }
	public static function get_by_post( int $post_id ): array { global $wpdb; return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE post_id=%d AND status=%s ORDER BY id ASC', $post_id, 'publish' ) ) ?: []; }
	public static function get_by_id( int $id ): ?object { global $wpdb; return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id=%d', $id ) ); }
	public static function insert( array $data ): int {
		global $wpdb;
		if ( empty( $data['content'] ) ) $data['content'] = '';
		$data['created_at'] = current_time('mysql');
		$wpdb->insert( self::table(), $data );
		return (int) $wpdb->insert_id;
	}
	public static function update( int $id, array $data ): bool { global $wpdb; return (bool) $wpdb->update( self::table(), $data, [ 'id' => $id ] ); }
	public static function delete( int $id ): bool { global $wpdb; return (bool) $wpdb->update( self::table(), [ 'status' => 'trash' ], [ 'id' => $id ] ); }
	public static function count_by_post( int $post_id ): int { global $wpdb; return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::table() . ' WHERE post_id=%d AND status=%s', $post_id, 'publish' ) ); }
}
