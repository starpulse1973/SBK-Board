<?php
namespace SBKBoard\Admin\Pages;

use SBKBoard\DB\BoardRepository;
use SBKBoard\Skin\SkinLoader;

class BoardEditPage {
	private function sanitize_tab( string $tab ): string {
		$allowed = [ 'basic', 'skin', 'design', 'editor', 'uploads', 'backup' ];
		return in_array( $tab, $allowed, true ) ? $tab : 'basic';
	}

	private function make_unique_slug( string $slug, int $current_id = 0 ): string {
		$base      = '' !== $slug ? $slug : 'board';
		$candidate = $base;
		$index     = 2;
		while ( true ) {
			$existing = BoardRepository::get_by_slug( $candidate );
			if ( ! $existing || (int) $existing->id === $current_id ) {
				return $candidate;
			}
			$candidate = $base . '-' . $index;
			$index++;
		}
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( "\u{AD8C}\u{D55C}\u{C774} \u{C5C6}\u{C2B5}\u{B2C8}\u{B2E4}.", 'sbk-board' ) );
		}

		$board_id = isset( $_GET['board_id'] ) ? absint( $_GET['board_id'] ) : absint( $_GET['id'] ?? 0 );
		$board    = $board_id > 0 ? BoardRepository::get_by_id( $board_id ) : null;
		if ( $board_id > 0 && ! $board ) {
			echo '<div class="wrap sbkboard-admin"><p>' . esc_html__( "\u{AC8C}\u{C2DC}\u{D310} \u{C815}\u{BCF4}\u{B97C} \u{CC3E}\u{C744} \u{C218} \u{C5C6}\u{C2B5}\u{B2C8}\u{B2E4}.", 'sbk-board' ) . '</p></div>';
			return;
		}

		$settings = $board ? BoardRepository::get_settings( (int) $board->id ) : BoardRepository::default_settings();
		$title    = $board ? __( "\u{AC8C}\u{C2DC}\u{D310} \u{C218}\u{C815}", 'sbk-board' ) : __( "\u{AC8C}\u{C2DC}\u{D310} \u{CD94}\u{AC00}", 'sbk-board' );
		$skins    = SkinLoader::get_registered();

		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'basic';
		$active_tab = $this->sanitize_tab( $active_tab );
		$base_url   = add_query_arg(
			[
				'page'     => 'sbkboard-board-edit',
				'board_id' => $board_id > 0 ? $board_id : null,
			],
			admin_url( 'admin.php' )
		);

		$restore_msg   = '';
		$restore_error = '';
		if ( 'backup' === $active_tab && isset( $_POST['sbkboard_restore'] ) && $board ) {
			check_admin_referer( 'sbkboard_restore_' . $board_id );
			[ $ok, $msg ] = $this->do_restore( (int) $board->id );
			if ( $ok ) {
				$restore_msg = $msg;
			} else {
				$restore_error = $msg;
			}
		}
		?>
		<div class="wrap sbkboard-admin">
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( "\u{AC8C}\u{C2DC}\u{D310} \u{C124}\u{C815}\u{C774} \u{C800}\u{C7A5}\u{B418}\u{C5C8}\u{C2B5}\u{B2C8}\u{B2E4}.", 'sbk-board' ); ?></p></div>
			<?php endif; ?>
			<?php if ( $restore_msg ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $restore_msg ); ?></p></div>
			<?php endif; ?>
			<?php if ( $restore_error ) : ?>
				<div class="notice notice-error is-dismissible"><p><?php echo esc_html( $restore_error ); ?></p></div>
			<?php endif; ?>

			<?php if ( $board ) : ?>
				<div class="sbkboard-shortcodes">
					<p><strong><?php echo esc_html__( "\u{B2E8}\u{CD95}\u{CF54}\u{B4DC}", 'sbk-board' ); ?></strong></p>
					<p><code>[sbk_board id="<?php echo esc_html( (string) $board->id ); ?>"]</code></p>
					<p><code>[sbk_board slug="<?php echo esc_html( (string) $board->slug ); ?>"]</code></p>
					<p><code>[sbk_latest board_id="<?php echo esc_html( (string) $board->id ); ?>" rpp="5"]</code></p>
				</div>
			<?php endif; ?>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'basic', $base_url ) ); ?>" class="nav-tab <?php echo 'basic' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="basic"><?php echo esc_html__( "\u{AE30}\u{BCF8}", 'sbk-board' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'skin', $base_url ) ); ?>" class="nav-tab <?php echo 'skin' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="skin"><?php echo esc_html__( "\u{C2A4}\u{D0A8}", 'sbk-board' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'design', $base_url ) ); ?>" class="nav-tab <?php echo 'design' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="design"><?php echo esc_html__( "\u{B514}\u{C790}\u{C778}", 'sbk-board' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'editor', $base_url ) ); ?>" class="nav-tab <?php echo 'editor' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="editor"><?php echo esc_html__( "\u{C5D0}\u{B514}\u{D130}", 'sbk-board' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'uploads', $base_url ) ); ?>" class="nav-tab <?php echo 'uploads' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="uploads"><?php echo esc_html__( "\u{CCA8}\u{BD80}\u{D30C}\u{C77C}", 'sbk-board' ); ?></a>
				<?php if ( $board ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'tab', 'backup', $base_url ) ); ?>" class="nav-tab <?php echo 'backup' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="backup"><?php echo esc_html__( "\u{BC31}\u{C5C5}/\u{BCF5}\u{C6D0}", 'sbk-board' ); ?></a>
				<?php endif; ?>
			</h2>
			<input type="hidden" id="sbkboard_active_tab" value="<?php echo esc_attr( $active_tab ); ?>">

			<?php if ( 'backup' === $active_tab && $board ) : ?>
				<?php $this->render_backup_tab( (int) $board->id, $base_url ); ?>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="sbkboard_save_board">
					<input type="hidden" name="board_id" value="<?php echo esc_attr( (string) $board_id ); ?>">
					<input type="hidden" id="sbkboard_active_tab_input" name="sbkboard_active_tab" value="<?php echo esc_attr( $active_tab ); ?>">
					<?php wp_nonce_field( 'sbkboard_save_board' ); ?>

					<div class="sbkboard-panel <?php echo 'basic' === $active_tab ? 'is-active' : ''; ?>" data-tab-panel="basic">
						<table class="form-table" role="presentation">
							<tr><th><?php echo esc_html__( "\u{AC8C}\u{C2DC}\u{D310} \u{C774}\u{B984}", 'sbk-board' ); ?></th><td><input type="text" name="name" class="regular-text" value="<?php echo esc_attr( (string) ( $board->name ?? '' ) ); ?>" required></td></tr>
							<tr><th><?php echo esc_html__( "\u{C2AC}\u{B7EC}\u{ADF8}", 'sbk-board' ); ?></th><td><input type="text" name="slug" class="regular-text" value="<?php echo esc_attr( (string) ( $board->slug ?? '' ) ); ?>"></td></tr>
							<tr>
								<th><?php echo esc_html__( "\u{C77D}\u{AE30} \u{AD8C}\u{D55C}", 'sbk-board' ); ?></th>
								<td>
									<select name="settings[read_permission]">
										<option value="everyone" <?php selected( $settings['read_permission'], 'everyone' ); ?>><?php echo esc_html__( "\u{BAA8}\u{B4E0} \u{C0AC}\u{C6A9}\u{C790}", 'sbk-board' ); ?></option>
										<option value="logged_in" <?php selected( $settings['read_permission'], 'logged_in' ); ?>><?php echo esc_html__( "\u{B85C}\u{ADF8}\u{C778} \u{C0AC}\u{C6A9}\u{C790}", 'sbk-board' ); ?></option>
										<option value="admin" <?php selected( $settings['read_permission'], 'admin' ); ?>><?php echo esc_html__( "\u{AD00}\u{B9AC}\u{C790}", 'sbk-board' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th><?php echo esc_html__( "\u{C4F0}\u{AE30} \u{AD8C}\u{D55C}", 'sbk-board' ); ?></th>
								<td>
									<select name="settings[write_permission]">
										<option value="everyone" <?php selected( $settings['write_permission'], 'everyone' ); ?>><?php echo esc_html__( "\u{BAA8}\u{B4E0} \u{C0AC}\u{C6A9}\u{C790}", 'sbk-board' ); ?></option>
										<option value="logged_in" <?php selected( $settings['write_permission'], 'logged_in' ); ?>><?php echo esc_html__( "\u{B85C}\u{ADF8}\u{C778} \u{C0AC}\u{C6A9}\u{C790}", 'sbk-board' ); ?></option>
										<option value="admin" <?php selected( $settings['write_permission'], 'admin' ); ?>><?php echo esc_html__( "\u{AD00}\u{B9AC}\u{C790}", 'sbk-board' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th><?php echo esc_html__( "\u{AE30}\u{B2A5} \u{C635}\u{C158}", 'sbk-board' ); ?></th>
								<td>
									<label><input type="checkbox" name="settings[allow_guest_write]" value="1" <?php checked( ! empty( $settings['allow_guest_write'] ) ); ?>> <?php echo esc_html__( "\u{BE44}\u{D68C}\u{C6D0} \u{AE00}\u{C4F0}\u{AE30} \u{D5C8}\u{C6A9}", 'sbk-board' ); ?></label><br>
									<label><input type="checkbox" name="settings[allow_comments]" value="1" <?php checked( ! empty( $settings['allow_comments'] ) ); ?>> <?php echo esc_html__( "\u{B313}\u{AE00} \u{D5C8}\u{C6A9}", 'sbk-board' ); ?></label><br>
									<label><input type="checkbox" name="settings[show_votes]" value="1" <?php checked( ! empty( $settings['show_votes'] ) ); ?>> <?php echo esc_html__( "\u{CD94}\u{CC9C}/\u{BE44}\u{CD94}\u{CC9C} \u{D45C}\u{C2DC}", 'sbk-board' ); ?></label><br>
									<label><input type="checkbox" name="settings[allow_downvote]" value="1" <?php checked( ! empty( $settings['allow_downvote'] ) ); ?>> <?php echo esc_html__( "\u{BE44}\u{CD94}\u{CC9C} \u{D5C8}\u{C6A9}", 'sbk-board' ); ?></label><br>
									<label><input type="checkbox" name="settings[allow_secret]" value="1" <?php checked( ! empty( $settings['allow_secret'] ) ); ?>> <?php echo esc_html__( "\u{BE44}\u{BC00}\u{AE00} \u{D5C8}\u{C6A9}", 'sbk-board' ); ?></label><br>
									<label><input type="checkbox" name="settings[enable_notice]" value="1" <?php checked( ! empty( $settings['enable_notice'] ) ); ?>> <?php echo esc_html__( "\u{ACF5}\u{C9C0}\u{AE00} \u{D5C8}\u{C6A9}(\u{AD00}\u{B9AC}\u{C790})", 'sbk-board' ); ?></label><br>
									<label><input type="checkbox" name="settings[show_author]" value="1" <?php checked( ! empty( $settings['show_author'] ) ); ?>> <?php echo esc_html__( "\u{C791}\u{C131}\u{C790} \u{D45C}\u{C2DC}", 'sbk-board' ); ?></label><br>
									<label><input type="checkbox" name="settings[show_views]" value="1" <?php checked( ! empty( $settings['show_views'] ) ); ?>> <?php echo esc_html__( "\u{C870}\u{D68C}\u{C218} \u{D45C}\u{C2DC}", 'sbk-board' ); ?></label>
								</td>
							</tr>
						</table>
					</div>

					<div class="sbkboard-panel <?php echo 'skin' === $active_tab ? 'is-active' : ''; ?>" data-tab-panel="skin">
						<table class="form-table" role="presentation">
							<tr>
								<th><?php echo esc_html__( "\u{AC8C}\u{C2DC}\u{D310} \u{C2A4}\u{D0A8}", 'sbk-board' ); ?></th>
								<td>
									<select id="sbkboard_skin_select" name="settings[skin]">
										<?php foreach ( $skins as $skin_slug => $meta ) : ?>
											<?php $skin_name = is_array( $meta ) ? ( $meta['name'] ?? $skin_slug ) : $skin_slug; ?>
											<option value="<?php echo esc_attr( (string) $skin_slug ); ?>" <?php selected( (string) $settings['skin'], (string) $skin_slug ); ?>><?php echo esc_html( (string) $skin_name ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
						</table>
					</div>

					<div class="sbkboard-panel <?php echo 'design' === $active_tab ? 'is-active' : ''; ?>" data-tab-panel="design">
						<?php $view_table = is_array( $settings['view_table'] ?? null ) ? $settings['view_table'] : []; ?>
						<table class="form-table sbkboard-design-table" role="presentation">
							<tr class="sbkboard-group-heading"><th colspan="2"><?php echo esc_html__( "\u{AC8C}\u{C2DC}\u{BB3C} \u{BCF4}\u{AE30} \u{D398}\u{C774}\u{C9C0} \u{B514}\u{C790}\u{C778}", 'sbk-board' ); ?></th></tr>
							<tr><th><?php echo esc_html__( "\u{BCF4}\u{AE30} \u{BCF8}\u{BB38} \u{BC30}\u{ACBD}", 'sbk-board' ); ?></th><td><input type="text" name="settings[view_table][td_bg]" value="<?php echo esc_attr( (string) ( $view_table['td_bg'] ?? 'transparent' ) ); ?>" class="regular-text"></td></tr>
							<tr><th><?php echo esc_html__( "\u{BCF4}\u{AE30} \u{AD6C}\u{BD84}\u{C120} \u{C0C9}\u{C0C1}", 'sbk-board' ); ?></th><td><input type="text" name="settings[view_table][divider_color]" value="<?php echo esc_attr( (string) ( $view_table['divider_color'] ?? '#dddddd' ) ); ?>" class="regular-text"></td></tr>
							<tr>
								<th><?php echo esc_html__( "\u{BCF4}\u{AE30} \u{AD6C}\u{BD84}\u{C120} \u{C2A4}\u{D0C0}\u{C77C}", 'sbk-board' ); ?></th>
								<td>
									<select name="settings[view_table][divider_style]">
										<option value="solid" <?php selected( (string) ( $view_table['divider_style'] ?? 'solid' ), 'solid' ); ?>><?php echo esc_html__( "\u{C2E4}\u{C120}", 'sbk-board' ); ?></option>
										<option value="dashed" <?php selected( (string) ( $view_table['divider_style'] ?? '' ), 'dashed' ); ?>><?php echo esc_html__( "\u{D30C}\u{C120}", 'sbk-board' ); ?></option>
										<option value="dotted" <?php selected( (string) ( $view_table['divider_style'] ?? '' ), 'dotted' ); ?>><?php echo esc_html__( "\u{C810}\u{C120}", 'sbk-board' ); ?></option>
									</select>
								</td>
							</tr>
							<tr><th><?php echo esc_html__( "\u{C81C}\u{BAA9} \u{D14D}\u{C2A4}\u{D2B8} \u{D06C}\u{AE30}", 'sbk-board' ); ?></th><td><input type="number" name="settings[view_table][title_size]" min="10" max="60" value="<?php echo esc_attr( (string) ( $view_table['title_size'] ?? 18 ) ); ?>"> px</td></tr>
							<tr><th><?php echo esc_html__( "\u{C81C}\u{BAA9} \u{D14D}\u{C2A4}\u{D2B8} \u{AD75}\u{AE30}", 'sbk-board' ); ?></th><td><input type="number" name="settings[view_table][title_weight]" min="100" max="900" step="100" value="<?php echo esc_attr( (string) ( $view_table['title_weight'] ?? 700 ) ); ?>"></td></tr>
							<tr><th><?php echo esc_html__( "\u{C81C}\u{BAA9} \u{D14D}\u{C2A4}\u{D2B8} \u{C0C9}\u{C0C1}", 'sbk-board' ); ?></th><td><input type="text" name="settings[view_table][title_color]" value="<?php echo esc_attr( (string) ( $view_table['title_color'] ?? '#1d2327' ) ); ?>" class="regular-text"></td></tr>
							<tr><th><?php echo esc_html__( "\u{BCF8}\u{BB38} \u{D14D}\u{C2A4}\u{D2B8} \u{D06C}\u{AE30}", 'sbk-board' ); ?></th><td><input type="number" name="settings[view_table][body_size]" min="10" max="60" value="<?php echo esc_attr( (string) ( $view_table['body_size'] ?? 14 ) ); ?>"> px</td></tr>
							<tr><th><?php echo esc_html__( "\u{BCF8}\u{BB38} \u{D14D}\u{C2A4}\u{D2B8} \u{AD75}\u{AE30}", 'sbk-board' ); ?></th><td><input type="number" name="settings[view_table][body_weight]" min="100" max="900" step="100" value="<?php echo esc_attr( (string) ( $view_table['body_weight'] ?? 400 ) ); ?>"></td></tr>
							<tr><th><?php echo esc_html__( "\u{BCF8}\u{BB38} \u{D14D}\u{C2A4}\u{D2B8} \u{C0C9}\u{C0C1}", 'sbk-board' ); ?></th><td><input type="text" name="settings[view_table][body_color]" value="<?php echo esc_attr( (string) ( $view_table['body_color'] ?? '#333333' ) ); ?>" class="regular-text"></td></tr>
							<tr><th><?php echo esc_html__( "\u{BCF8}\u{BB38} \u{D14D}\u{C2A4}\u{D2B8} \u{C904} \u{B192}\u{C774}", 'sbk-board' ); ?></th><td><input type="text" name="settings[view_table][body_line_height]" value="<?php echo esc_attr( (string) ( $view_table['body_line_height'] ?? '1.8' ) ); ?>" class="regular-text"></td></tr>
						</table>
					</div>

					<div class="sbkboard-panel <?php echo 'editor' === $active_tab ? 'is-active' : ''; ?>" data-tab-panel="editor">
						<table class="form-table" role="presentation">
							<tr>
								<th><?php echo esc_html__( "\u{AC8C}\u{C2DC}\u{AE00} \u{C791}\u{C131} \u{C5D0}\u{B514}\u{D130} \u{C885}\u{B958}", 'sbk-board' ); ?></th>
								<td>
									<select id="sbkboard_editor_type_select" name="settings[editor_type]">
										<option value="textarea" <?php selected( (string) ( $settings['editor_type'] ?? 'textarea' ), 'textarea' ); ?>><?php echo esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C5D0}\u{B514}\u{D130}", 'sbk-board' ); ?></option>
										<option value="wp_editor" <?php selected( (string) ( $settings['editor_type'] ?? 'textarea' ), 'wp_editor' ); ?>><?php echo esc_html__( "\u{C6CC}\u{B4DC}\u{D504}\u{B808}\u{C2A4} \u{C5D0}\u{B514}\u{D130}", 'sbk-board' ); ?></option>
									</select>
								</td>
							</tr>
							<tr class="sbkboard-editor-html-row" <?php if ( 'textarea' !== (string) ( $settings['editor_type'] ?? 'textarea' ) ) : ?>style="display:none;"<?php endif; ?>>
								<th><?php echo esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C5D0}\u{B514}\u{D130} HTML", 'sbk-board' ); ?></th>
								<td><label><input type="checkbox" name="settings[allow_html]" value="1" <?php checked( ! empty( $settings['allow_html'] ) ); ?>> <?php echo esc_html__( "\u{D5C8}\u{C6A9}\u{B41C} HTML \u{D0DC}\u{ADF8} \u{C0AC}\u{C6A9} \u{AC00}\u{B2A5}", 'sbk-board' ); ?></label></td>
							</tr>
						</table>
					</div>

					<div class="sbkboard-panel <?php echo 'uploads' === $active_tab ? 'is-active' : ''; ?>" data-tab-panel="uploads">
						<table class="form-table" role="presentation">
							<tr><th><?php echo esc_html__( "\u{CCA8}\u{BD80}\u{D30C}\u{C77C} \u{D5C8}\u{C6A9}", 'sbk-board' ); ?></th><td><label><input type="checkbox" name="settings[allow_attach]" value="1" <?php checked( ! empty( $settings['allow_attach'] ) ); ?>> <?php echo esc_html__( "\u{D5C8}\u{C6A9}", 'sbk-board' ); ?></label></td></tr>
							<tr><th><?php echo esc_html__( "\u{CD5C}\u{B300} \u{D30C}\u{C77C} \u{C218}", 'sbk-board' ); ?></th><td><input type="number" name="settings[max_files]" min="1" max="20" value="<?php echo esc_attr( (string) $settings['max_files'] ); ?>"></td></tr>
							<tr><th><?php echo esc_html__( "\u{CD5C}\u{B300} \u{D30C}\u{C77C} \u{D06C}\u{AE30}(MB)", 'sbk-board' ); ?></th><td><input type="number" name="settings[upload_max_size]" min="1" max="200" value="<?php echo esc_attr( (string) $settings['upload_max_size'] ); ?>"></td></tr>
							<tr><th><?php echo esc_html__( "\u{D5C8}\u{C6A9} \u{D655}\u{C7A5}\u{C790}", 'sbk-board' ); ?></th><td><input type="text" name="settings[allowed_exts]" class="regular-text" value="<?php echo esc_attr( (string) $settings['allowed_exts'] ); ?>"></td></tr>
						</table>
					</div>

					<?php submit_button( __( "\u{AC8C}\u{C2DC}\u{D310} \u{C800}\u{C7A5}", 'sbk-board' ) ); ?>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	private function render_backup_tab( int $board_id, string $base_url ): void {
		$backup_url = wp_nonce_url(
			add_query_arg(
				[
					'action'   => 'sbkboard_backup_board',
					'board_id' => $board_id,
				],
				admin_url( 'admin-post.php' )
			),
			'sbkboard_backup_' . $board_id
		);
		?>
		<div class="sbkboard-panel is-active" data-tab-panel="backup">
			<h3><?php echo esc_html__( "\u{BC31}\u{C5C5} \u{D30C}\u{C77C} \u{B2E4}\u{C6B4}\u{B85C}\u{B4DC}", 'sbk-board' ); ?></h3>
			<p><?php echo esc_html__( "\u{AC8C}\u{C2DC}\u{D310} \u{B370}\u{C774}\u{D130}\u{C640} \u{C124}\u{C815}\u{C744} JSON \u{D30C}\u{C77C}\u{B85C} \u{C800}\u{C7A5}\u{D569}\u{B2C8}\u{B2E4}.", 'sbk-board' ); ?></p>
			<a href="<?php echo esc_url( $backup_url ); ?>" class="button button-primary"><?php echo esc_html__( "\u{BC31}\u{C5C5} \u{D30C}\u{C77C} \u{B2E4}\u{C6B4}\u{B85C}\u{B4DC}", 'sbk-board' ); ?></a>
			<hr style="margin:24px 0;">
			<h3><?php echo esc_html__( "\u{BC31}\u{C5C5} \u{D30C}\u{C77C} \u{BCF5}\u{C6D0}", 'sbk-board' ); ?></h3>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( add_query_arg( 'tab', 'backup', $base_url ) ); ?>">
				<?php wp_nonce_field( 'sbkboard_restore_' . $board_id ); ?>
				<input type="hidden" name="sbkboard_restore" value="1">
				<input type="hidden" name="board_id" value="<?php echo esc_attr( (string) $board_id ); ?>">
				<input type="file" name="sbkboard_backup_file" accept=".json" required>
				<?php submit_button( __( "\u{BCF5}\u{C6D0} \u{C2E4}\u{D589}", 'sbk-board' ), 'secondary' ); ?>
			</form>
		</div>
		<?php
	}

	private function do_backup( int $board_id, array $settings ): void {
		global $wpdb;

		$board = BoardRepository::get_by_id( $board_id );
		if ( ! $board ) {
			wp_die( 'Board not found' );
		}

		$posts = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}sbk_posts WHERE board_id = %d ORDER BY id ASC", $board_id ),
			ARRAY_A
		);

		$post_ids = array_column( (array) $posts, 'id' );
		$comments = [];
		$files    = [];
		if ( $post_ids ) {
			$ids_str  = implode( ',', array_map( 'intval', $post_ids ) );
			$comments = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sbk_comments WHERE post_id IN ({$ids_str}) ORDER BY id ASC", ARRAY_A ); // phpcs:ignore WordPress.DB
			$files    = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sbk_files WHERE post_id IN ({$ids_str}) ORDER BY id ASC", ARRAY_A ); // phpcs:ignore WordPress.DB
		}

		$payload = [
			'sbkboard_backup_version' => '1.0',
			'exported_at'             => gmdate( 'Y-m-d\\TH:i:s\\Z' ),
			'board'                   => (array) $board,
			'settings'                => $settings,
			'posts'                   => $posts,
			'comments'                => $comments,
			'files'                   => $files,
		];

		$filename = 'sbkboard-backup-' . sanitize_file_name( (string) $board->slug ) . '-' . gmdate( 'Ymd-His' ) . '.json';
		$json     = wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		if ( false === $json ) {
			$json = '{}';
		}

		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		nocache_headers();
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: ' . strlen( $json ) );
		header( 'Pragma: public' );
		echo $json;
		exit;
	}

	private function do_restore( int $board_id ): array {
		global $wpdb;

		if ( empty( $_FILES['sbkboard_backup_file']['tmp_name'] ) ) {
			return [ false, __( "\u{D30C}\u{C77C}\u{C744} \u{C5C5}\u{B85C}\u{B4DC}\u{D574} \u{C8FC}\u{C138}\u{C694}.", 'sbk-board' ) ];
		}

		$json = file_get_contents( $_FILES['sbkboard_backup_file']['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( false === $json ) {
			return [ false, __( "\u{C5C5}\u{B85C}\u{B4DC} \u{D30C}\u{C77C}\u{C744} \u{C77D}\u{C744} \u{C218} \u{C5C6}\u{C2B5}\u{B2C8}\u{B2E4}.", 'sbk-board' ) ];
		}

		$data = json_decode( $json, true );
		if ( ! is_array( $data ) || empty( $data['sbkboard_backup_version'] ) ) {
			return [ false, __( "\u{C62C}\u{BC14}\u{B978} \u{D615}\u{C2DD}\u{C758} \u{BC31}\u{C5C5} \u{D30C}\u{C77C}\u{C774} \u{C544}\u{B2D9}\u{B2C8}\u{B2E4}.", 'sbk-board' ) ];
		}

		if ( ! empty( $data['settings'] ) && is_array( $data['settings'] ) ) {
			BoardRepository::save_settings( $board_id, BoardRepository::normalize_settings( $data['settings'] ) );
		}

		$old_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}sbk_posts WHERE board_id = %d", $board_id ) );
		if ( $old_ids ) {
			$ids_str = implode( ',', array_map( 'intval', $old_ids ) );
			$wpdb->query( "DELETE FROM {$wpdb->prefix}sbk_comments WHERE post_id IN ({$ids_str})" ); // phpcs:ignore WordPress.DB
			$wpdb->query( "DELETE FROM {$wpdb->prefix}sbk_files WHERE post_id IN ({$ids_str})" ); // phpcs:ignore WordPress.DB
		}
		$wpdb->delete( $wpdb->prefix . 'sbk_posts', [ 'board_id' => $board_id ], [ '%d' ] );

		$id_map = [];
		foreach ( (array) ( $data['posts'] ?? [] ) as $post ) {
			$old_id = (int) $post['id'];
			unset( $post['id'] );
			$post['board_id'] = $board_id;
			$wpdb->insert( $wpdb->prefix . 'sbk_posts', $post );
			$id_map[ $old_id ] = (int) $wpdb->insert_id;
		}
		foreach ( (array) ( $data['comments'] ?? [] ) as $comment ) {
			$old_pid = (int) $comment['post_id'];
			if ( ! isset( $id_map[ $old_pid ] ) ) {
				continue;
			}
			unset( $comment['id'] );
			$comment['post_id'] = $id_map[ $old_pid ];
			$wpdb->insert( $wpdb->prefix . 'sbk_comments', $comment );
		}
		foreach ( (array) ( $data['files'] ?? [] ) as $file ) {
			$old_pid = (int) $file['post_id'];
			if ( ! isset( $id_map[ $old_pid ] ) ) {
				continue;
			}
			unset( $file['id'] );
			$file['post_id'] = $id_map[ $old_pid ];
			$wpdb->insert( $wpdb->prefix . 'sbk_files', $file );
		}

		return [ true, sprintf( __( "\u{BCF5}\u{C6D0}\u{C774} \u{C644}\u{B8CC}\u{B418}\u{C5C8}\u{C2B5}\u{B2C8}\u{B2E4}. (%d\u{AC1C} \u{AC8C}\u{C2DC}\u{AE00} \u{BCF5}\u{C6D0})", 'sbk-board' ), count( $id_map ) ) ];
	}

	public function handle_backup_download(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( "\u{AD8C}\u{D55C}\u{C774} \u{C5C6}\u{C2B5}\u{B2C8}\u{B2E4}.", 'sbk-board' ) );
		}

		$board_id = isset( $_GET['board_id'] ) ? absint( $_GET['board_id'] ) : 0;
		if ( $board_id <= 0 ) {
			wp_die( esc_html__( "\u{AC8C}\u{C2DC}\u{D310} ID\u{AC00} \u{C62C}\u{BC14}\u{B974}\u{C9C0} \u{C54A}\u{C2B5}\u{B2C8}\u{B2E4}.", 'sbk-board' ) );
		}

		check_admin_referer( 'sbkboard_backup_' . $board_id );

		$board = BoardRepository::get_by_id( $board_id );
		if ( ! $board ) {
			wp_die( esc_html__( "\u{AC8C}\u{C2DC}\u{D310} \u{C815}\u{BCF4}\u{B97C} \u{CC3E}\u{C744} \u{C218} \u{C5C6}\u{C2B5}\u{B2C8}\u{B2E4}.", 'sbk-board' ) );
		}

		$settings = BoardRepository::get_settings( $board_id );
		$this->do_backup( $board_id, $settings );
		exit;
	}

	public function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( "\u{AD8C}\u{D55C}\u{C774} \u{C5C6}\u{C2B5}\u{B2C8}\u{B2E4}.", 'sbk-board' ) );
		}

		check_admin_referer( 'sbkboard_save_board' );

		$board_id = isset( $_POST['board_id'] ) ? absint( $_POST['board_id'] ) : 0;
		$name     = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		if ( '' === $name ) {
			$name = __( "\u{C774}\u{B984} \u{C5C6}\u{B294} \u{AC8C}\u{C2DC}\u{D310}", 'sbk-board' );
		}

		$slug = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) );
		if ( '' === $slug ) {
			$slug = sanitize_title( $name );
		}
		$slug = $this->make_unique_slug( $slug, $board_id );

		$raw_settings = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : [];
		if ( $board_id > 0 ) {
			$existing_settings = BoardRepository::get_settings( $board_id );
			if ( ! array_key_exists( 'per_page', $raw_settings ) && isset( $existing_settings['per_page'] ) ) {
				$raw_settings['per_page'] = $existing_settings['per_page'];
			}
			if ( ! array_key_exists( 'gallery_columns', $raw_settings ) && isset( $existing_settings['gallery_columns'] ) ) {
				$raw_settings['gallery_columns'] = $existing_settings['gallery_columns'];
			}
		}
		$settings     = BoardRepository::normalize_settings( $raw_settings );

		if ( $board_id > 0 ) {
			BoardRepository::update( $board_id, [ 'name' => $name, 'slug' => $slug ] );
			BoardRepository::save_settings( $board_id, $settings );
		} else {
			$board_id = BoardRepository::insert( [ 'name' => $name, 'slug' => $slug, 'settings' => wp_json_encode( $settings ) ] );
		}

		$tab = isset( $_POST['sbkboard_active_tab'] ) ? sanitize_key( wp_unslash( $_POST['sbkboard_active_tab'] ) ) : 'basic';
		$tab = $this->sanitize_tab( $tab );

		wp_safe_redirect( admin_url( 'admin.php?page=sbkboard-board-edit&board_id=' . $board_id . '&updated=1&tab=' . $tab ) );
		exit;
	}
}
