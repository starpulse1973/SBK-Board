<?php
namespace SBKBoard\DB;
class VoteRepository {
	private static function table(): string { global $wpdb; return $wpdb->prefix . 'sbk_votes'; }
	public static function get_vote( int $post_id, int $user_id, string $ip ): int {
		global $wpdb;
		if ( $user_id ) $row = $wpdb->get_row( $wpdb->prepare( 'SELECT vote_type FROM ' . self::table() . ' WHERE post_id=%d AND user_id=%d', $post_id, $user_id ) );
		else $row = $wpdb->get_row( $wpdb->prepare( 'SELECT vote_type FROM ' . self::table() . ' WHERE post_id=%d AND ip=%s AND user_id=0', $post_id, $ip ) );
		return $row ? (int) $row->vote_type : 0;
	}
	public static function add_vote( int $post_id, int $user_id, string $ip, int $type ): bool {
		global $wpdb;
		return (bool) $wpdb->insert( self::table(), [ 'post_id' => $post_id, 'user_id' => $user_id, 'ip' => $ip, 'vote_type' => $type, 'created_at' => current_time('mysql') ] );
	}
}
