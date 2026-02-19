<?php
namespace SBKBoard\Front\Ajax;

use SBKBoard\DB\PostRepository;
use SBKBoard\DB\VoteRepository;

class VoteAjax extends BaseAjax {
	public function register(): void {
		$this->add( 'sbkboard_vote', 'handle' );
	}

	public function handle(): void {
		$this->verify_nonce();
		$post_id = $this->int( 'post_id' );
		$type    = $this->input( 'type' ) === 'down' ? -1 : 1;
		$user_id = get_current_user_id();
		$ip      = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );

		$post = PostRepository::get_by_id( $post_id );
		if ( ! $post ) wp_send_json_error( __( '게시글을 찾을 수 없습니다.', 'sbk-board' ) );

		// Prevent duplicate vote
		$existing = VoteRepository::get_vote( $post_id, $user_id, $ip );
		if ( $existing !== 0 ) {
			wp_send_json_error( __( '이미 투표하셨습니다.', 'sbk-board' ) );
		}

		VoteRepository::add_vote( $post_id, $user_id, $ip, $type );

		// Increment the correct column
		if ( $type === 1 ) {
			$new_votes = (int) $post->vote_up + 1;
			PostRepository::update( $post_id, [ 'vote_up' => $new_votes ] );
		} else {
			$new_votes = (int) $post->vote_down + 1;
			PostRepository::update( $post_id, [ 'vote_down' => $new_votes ] );
		}
		$total = (int) $post->vote_up - (int) $post->vote_down + ( $type === 1 ? 1 : -1 );

		wp_send_json_success( [ 'total' => $total ] );
	}
}
