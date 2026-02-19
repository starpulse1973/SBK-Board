<?php
namespace SBKBoard\Core;
class Activator {
	public static function activate(): void {
		\SBKBoard\DB\Schema::create_tables();
		if ( ! get_option( 'sbkboard_settings' ) ) {
			add_option( 'sbkboard_settings', [
				'upload_max_size'  => 5,
				'allowed_ext'      => 'jpg,jpeg,png,gif,pdf,zip,doc,docx',
				'posts_per_page'   => 15,
				'enable_comments'  => true,
				'enable_votes'     => true,
				'word_filter_mode' => 'block',
				'word_filter_list' => '',
				'skin_default'     => 'basic',
			] );
		}
		flush_rewrite_rules();
	}
}
