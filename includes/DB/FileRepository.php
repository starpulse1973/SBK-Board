<?php
namespace SBKBoard\DB;
class FileRepository {
	private static function table(): string { global $wpdb; return $wpdb->prefix . 'sbk_files'; }
	public static function get_by_post( int $post_id ): array { global $wpdb; return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE post_id=%d', $post_id ) ) ?: []; }
	public static function get_first_image_urls_by_posts( array $post_ids ): array {
		global $wpdb;
		$post_ids = array_values( array_unique( array_filter( array_map( 'intval', $post_ids ) ) ) );
		if ( empty( $post_ids ) ) return [];

		$placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );
		$sql = 'SELECT post_id, file_name, file_url, id FROM ' . self::table() . " WHERE post_id IN ({$placeholders}) ORDER BY post_id ASC, id ASC";
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $post_ids ) ) ?: [];

		$map      = [];
		$img_exts = [ 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp' ];
		foreach ( $rows as $row ) {
			$post_id = (int) $row->post_id;
			if ( isset( $map[ $post_id ] ) ) continue;
			$ext = strtolower( (string) pathinfo( (string) $row->file_name, PATHINFO_EXTENSION ) );
			if ( in_array( $ext, $img_exts, true ) ) {
				$map[ $post_id ] = (string) $row->file_url;
			}
		}

		return $map;
	}
	public static function get_by_id( int $id ): ?object { global $wpdb; return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id=%d', $id ) ); }
	public static function insert( array $data ): int {
		global $wpdb;
		if ( empty( $data['file_url'] ) ) $data['file_url'] = '';
		$data['created_at'] = current_time('mysql');
		$wpdb->insert( self::table(), $data );
		return (int) $wpdb->insert_id;
	}
	public static function delete( int $id ): bool { global $wpdb; return (bool) $wpdb->delete( self::table(), [ 'id' => $id ] ); }
	public static function increment_download( int $id ): void { global $wpdb; $wpdb->query( $wpdb->prepare( 'UPDATE ' . self::table() . ' SET download_count=download_count+1 WHERE id=%d', $id ) ); }
	public static function delete_by_post( int $post_id ): void { global $wpdb; $wpdb->delete( self::table(), [ 'post_id' => $post_id ] ); }
}
