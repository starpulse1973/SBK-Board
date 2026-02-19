<?php
namespace SBKBoard\DB;
class PostRepository {
	private static function table(): string { global $wpdb; return $wpdb->prefix . 'sbk_posts'; }
	public static function get_list( int $board_id, array $args = [] ): array {
		global $wpdb;
		$args = wp_parse_args( $args, [ 'per_page' => 15, 'paged' => 1, 'search' => '', 'search_type' => 'all', 'with_content' => false ] );
		$table = self::table();
		$where = $wpdb->prepare( 'WHERE p.board_id=%d AND p.status=%s AND p.is_notice=0', $board_id, 'publish' );
		if ( $args['search'] ) {
			$like = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			if ( 'subject' === $args['search_type'] ) $where .= $wpdb->prepare( ' AND p.subject LIKE %s', $like );
			elseif ( 'content' === $args['search_type'] ) $where .= $wpdb->prepare( ' AND p.content LIKE %s', $like );
			else $where .= $wpdb->prepare( ' AND (p.subject LIKE %s OR p.content LIKE %s)', $like, $like );
		}
		$per_page = max( 1, (int) $args['per_page'] );
		$paged    = max( 1, (int) $args['paged'] );
		$offset   = ( $paged - 1 ) * $per_page;
		$columns  = ! empty( $args['with_content'] )
			? 'p.*'
			: 'p.id, p.board_id, p.parent_id, p.sort_key, p.depth, p.user_id, p.author_name, p.author_email, p.subject, p.is_notice, p.is_secret, p.status, p.view_count, p.vote_up, p.vote_down, p.created_at, p.updated_at';
		$items   = $wpdb->get_results( "SELECT {$columns} FROM {$table} p {$where} ORDER BY p.created_at DESC, p.id DESC LIMIT {$per_page} OFFSET {$offset}" ) ?: [];
		$total   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} p {$where}" );
		$notice_columns = ! empty( $args['with_content'] )
			? '*'
			: 'id, board_id, parent_id, sort_key, depth, user_id, author_name, author_email, subject, is_notice, is_secret, status, view_count, vote_up, vote_down, created_at, updated_at';
		$notices = $wpdb->get_results( $wpdb->prepare( "SELECT {$notice_columns} FROM {$table} WHERE board_id=%d AND is_notice=1 AND status=%s ORDER BY created_at DESC, id DESC", $board_id, 'publish' ) ) ?: [];
		return compact( 'items', 'total', 'notices' );
	}
	public static function get_by_id( int $id ): ?object { global $wpdb; return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id=%d', $id ) ); }
	public static function insert( array $data ): int {
		global $wpdb;
		$data['created_at'] = current_time( 'mysql' );
		$data['updated_at'] = current_time( 'mysql' );
		if ( empty( $data['content'] ) ) $data['content'] = '';
		$wpdb->insert( self::table(), $data );
		$id = (int) $wpdb->insert_id;
		if ( $id > 0 ) {
			$parent_id = (int)($data['parent_id'] ?? 0);
			if ( $parent_id ) {
				$parent   = self::get_by_id( $parent_id );
				$sort_key = ($parent ? $parent->sort_key : str_pad($parent_id,10,'0',STR_PAD_LEFT)) . '_' . str_pad($id,10,'0',STR_PAD_LEFT);
				$depth    = $parent ? (int)$parent->depth + 1 : 1;
			} else {
				$sort_key = str_pad($id,10,'0',STR_PAD_LEFT);
				$depth    = 0;
			}
			$wpdb->update( self::table(), [ 'sort_key' => $sort_key, 'depth' => $depth ], [ 'id' => $id ] );
		}
		return $id;
	}
	public static function update( int $id, array $data ): bool { global $wpdb; $data['updated_at'] = current_time('mysql'); return (bool) $wpdb->update( self::table(), $data, [ 'id' => $id ] ); }
	public static function delete( int $id ): bool { global $wpdb; return (bool) $wpdb->update( self::table(), [ 'status' => 'trash' ], [ 'id' => $id ] ); }
	public static function increment_view( int $id ): void { global $wpdb; $wpdb->query( $wpdb->prepare( 'UPDATE ' . self::table() . ' SET view_count=view_count+1 WHERE id=%d', $id ) ); }
	public static function count_by_board( int $board_id ): int { global $wpdb; return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::table() . ' WHERE board_id=%d AND status=%s', $board_id, 'publish' ) ); }
}
