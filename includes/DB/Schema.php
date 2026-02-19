<?php
namespace SBKBoard\DB;
class Schema {
	public static function create_tables(): void {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( "CREATE TABLE {$wpdb->prefix}sbk_boards (
			id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			slug       VARCHAR(100)    NOT NULL,
			name       VARCHAR(200)    NOT NULL DEFAULT '',
			settings   LONGTEXT        NOT NULL,
			created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug)
		) $charset;" );
		dbDelta( "CREATE TABLE {$wpdb->prefix}sbk_posts (
			id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			board_id     BIGINT UNSIGNED NOT NULL DEFAULT 0,
			parent_id    BIGINT UNSIGNED NOT NULL DEFAULT 0,
			sort_key     VARCHAR(255)    NOT NULL DEFAULT '',
			depth        TINYINT UNSIGNED NOT NULL DEFAULT 0,
			user_id      BIGINT UNSIGNED NOT NULL DEFAULT 0,
			author_name  VARCHAR(100)    NOT NULL DEFAULT '',
			author_email VARCHAR(100)    NOT NULL DEFAULT '',
			password     VARCHAR(255)    NOT NULL DEFAULT '',
			subject      VARCHAR(500)    NOT NULL DEFAULT '',
			content      LONGTEXT        NOT NULL,
			is_notice    TINYINT(1)      NOT NULL DEFAULT 0,
			is_secret    TINYINT(1)      NOT NULL DEFAULT 0,
			status       VARCHAR(20)     NOT NULL DEFAULT 'publish',
			view_count   BIGINT UNSIGNED NOT NULL DEFAULT 0,
			vote_up      BIGINT UNSIGNED NOT NULL DEFAULT 0,
			vote_down    BIGINT UNSIGNED NOT NULL DEFAULT 0,
			ip           VARCHAR(45)     NOT NULL DEFAULT '',
			created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY board_id (board_id),
			KEY parent_id (parent_id),
			KEY sort_key (sort_key(191)),
			KEY is_notice (is_notice),
			KEY status (status),
			KEY created_at (created_at),
			KEY board_status_notice_created (board_id, status, is_notice, created_at, id)
		) $charset;" );
		dbDelta( "CREATE TABLE {$wpdb->prefix}sbk_comments (
			id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id      BIGINT UNSIGNED NOT NULL DEFAULT 0,
			parent_id    BIGINT UNSIGNED NOT NULL DEFAULT 0,
			user_id      BIGINT UNSIGNED NOT NULL DEFAULT 0,
			author_name  VARCHAR(100)    NOT NULL DEFAULT '',
			author_email VARCHAR(100)    NOT NULL DEFAULT '',
			password     VARCHAR(255)    NOT NULL DEFAULT '',
			content      TEXT            NOT NULL,
			is_secret    TINYINT(1)      NOT NULL DEFAULT 0,
			status       VARCHAR(20)     NOT NULL DEFAULT 'publish',
			ip           VARCHAR(45)     NOT NULL DEFAULT '',
			created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY post_id (post_id),
			KEY parent_id (parent_id),
			KEY status (status)
		) $charset;" );
		dbDelta( "CREATE TABLE {$wpdb->prefix}sbk_files (
			id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id        BIGINT UNSIGNED NOT NULL DEFAULT 0,
			comment_id     BIGINT UNSIGNED NOT NULL DEFAULT 0,
			attachment_id  BIGINT UNSIGNED NOT NULL DEFAULT 0,
			file_name      VARCHAR(500)    NOT NULL DEFAULT '',
			file_url       TEXT            NOT NULL,
			file_size      BIGINT UNSIGNED NOT NULL DEFAULT 0,
			download_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
			created_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY post_id (post_id),
			KEY comment_id (comment_id)
		) $charset;" );
		dbDelta( "CREATE TABLE {$wpdb->prefix}sbk_categories (
			id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			board_id   BIGINT UNSIGNED NOT NULL DEFAULT 0,
			name       VARCHAR(200)    NOT NULL DEFAULT '',
			sort_order SMALLINT        NOT NULL DEFAULT 0,
			PRIMARY KEY (id),
			KEY board_id (board_id)
		) $charset;" );
		dbDelta( "CREATE TABLE {$wpdb->prefix}sbk_votes (
			id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id    BIGINT UNSIGNED NOT NULL DEFAULT 0,
			user_id    BIGINT UNSIGNED NOT NULL DEFAULT 0,
			ip         VARCHAR(45)     NOT NULL DEFAULT '',
			vote_type  TINYINT(1)      NOT NULL DEFAULT 1,
			created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY post_user (post_id, user_id),
			KEY post_ip (post_id, ip)
		) $charset;" );
		dbDelta( "CREATE TABLE {$wpdb->prefix}sbk_views (
			id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id   BIGINT UNSIGNED NOT NULL DEFAULT 0,
			ip        VARCHAR(45)     NOT NULL DEFAULT '',
			viewed_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY post_ip (post_id, ip)
		) $charset;" );
		dbDelta( "CREATE TABLE {$wpdb->prefix}sbk_meta (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			object_id   BIGINT UNSIGNED NOT NULL DEFAULT 0,
			object_type VARCHAR(20)     NOT NULL DEFAULT 'post',
			meta_key    VARCHAR(255)    NOT NULL DEFAULT '',
			meta_value  LONGTEXT,
			PRIMARY KEY (id),
			KEY object (object_id, object_type),
			KEY meta_key (meta_key)
		) $charset;" );
		update_option( 'sbkboard_db_version', SBKBOARD_VERSION );
	}
	public static function drop_tables(): void {
		global $wpdb;
		foreach ( [ 'sbk_boards','sbk_posts','sbk_comments','sbk_files','sbk_categories','sbk_votes','sbk_views','sbk_meta' ] as $t ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$t}" );
		}
	}
}
