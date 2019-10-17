<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

class Uploader_Process extends Background_Tool_Process {

	/**
	 * @var string
	 */
	protected $action = 'uploader';

	/**
	 * @var int
	 */
	private $license_limit = -1;

	/**
	 * @var int
	 */
	private $offloaded = 0;

	/**
	 * Process attachments chunk.
	 *
	 * @param array $attachments
	 * @param int   $blog_id
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	protected function process_attachments_chunk( $attachments, $blog_id ) {
		$processed = array();

		// With caching this may be some minutes behind, and may not include previous batches,
		// but this really doesn't matter in the grand scheme of things as it'll eventually catch up.
		$this->license_limit = $this->as3cf->get_total_allowed_media_items_to_upload();

		foreach ( $attachments as $attachment_id ) {
			// Check we are allowed to carry on offloading.
			if ( ! $this->should_upload_attachment( $attachment_id, $blog_id ) ) {
				return $processed;
			}

			if ( $this->handle_attachment( $attachment_id, $blog_id ) ) {
				$this->offloaded++;
			}

			// Whether actually offloaded or not, we've processed the item.
			$processed[] = $attachment_id;
		}

		return $processed;
	}

	/**
	 * Upload the attachment to provider.
	 *
	 * @param int $attachment_id
	 * @param int $blog_id
	 *
	 * @return bool
	 * @throws \Exception
	 */
	protected function handle_attachment( $attachment_id, $blog_id ) {
		// Skip item if attachment already on provider.
		if ( $this->as3cf->get_attachment_provider_info( $attachment_id ) ) {
			return false;
		}

		$provider_object = $this->as3cf->upload_attachment( $attachment_id, null, null, false );

		// Build error message
		if ( is_wp_error( $provider_object ) ) {
			if ( $this->count_errors() <= 100 ) {
				foreach ( $provider_object->get_error_messages() as $error_message ) {
					$error_msg = sprintf( __( 'Error offloading to bucket - %s', 'amazon-s3-and-cloudfront' ), $error_message );
					$this->record_error( $blog_id, $attachment_id, $error_msg );
				}
			}

			return false;
		}

		return true;
	}

	/**
	 * Check there is enough allowed items for the license before uploading.
	 *
	 * @param int $attachment_id
	 * @param int $blog_id
	 *
	 * @return bool
	 */
	protected function should_upload_attachment( $attachment_id, $blog_id ) {
		// No limit, or not counting towards limit.
		if ( 0 > $this->license_limit ) {
			return true;
		}

		// If media limit met, cancel the offload and give notice.
		if ( 0 >= ( $this->license_limit - $this->offloaded ) ) {
			// Be really, really sure!
			$this->as3cf->update_media_library_total();
			$this->license_limit = $this->as3cf->get_total_allowed_media_items_to_upload();

			if ( 0 === $this->license_limit ) {
				$this->cancel();

				$account_link = sprintf( '<a href="%s" target="_blank">%s</a>', $this->as3cf->get_my_account_url(), __( 'My Account', 'amazon-s3-and-cloudfront' ) );
				$notice_msg   = __( "<strong>WP Offload Media</strong> &mdash; You've reached your license limit so we've had to stop your offload. To offload the rest of your Media Library, please upgrade your license from %s and simply start the offload again. It will start from where it stopped.", 'amazon-s3-and-cloudfront' );
				$error_msg    = sprintf( $notice_msg, $account_link );

				$notice_id = $this->tool->get_tool_key() . '_license_limit';

				$this->as3cf->notices->undismiss_notice_for_all( $notice_id );

				$args = array(
					'custom_id'         => $notice_id,
					'flash'             => false,
					'only_show_to_user' => false,
				);

				$this->as3cf->notices->add_notice( $error_msg, $args );

				return false;
			} else {
				// Carry on!
				$this->offloaded = 0;
			}
		}

		return true;
	}

	/**
	 * Get blog attachments to process.
	 *
	 * @param array $blog
	 * @param int   $limit Maximum number of attachment IDs to return
	 * @param bool  $count Just return the count, negates $limit, default false
	 *
	 * @return array|int
	 */
	protected function get_blog_attachments( $blog, $limit, $count = false ) {
		global $wpdb;

		if ( $count ) {
			$sql = 'SELECT count(DISTINCT posts.ID)';
		} else {
			$sql = 'SELECT DISTINCT posts.ID';
		}

		$sql .= "
			FROM `{$blog['prefix']}posts` AS posts
			WHERE posts.post_type = 'attachment'
			AND posts.ID NOT IN (
			    SELECT postmeta.post_id
				FROM `{$blog['prefix']}postmeta` AS postmeta
				WHERE postmeta.post_id = posts.ID
				AND postmeta.meta_key = 'amazonS3_info'
			)
		";

		if ( ! empty( $blog['last_attachment_id'] ) ) {
			$sql .= " AND posts.ID < '{$blog['last_attachment_id']}'";
		}

		/**
		 * Allow users to exclude certain MIME types from attachments to upload.
		 *
		 * @param array
		 */
		$ignored_mime_types = apply_filters( 'as3cf_ignored_mime_types', array() );
		if ( is_array( $ignored_mime_types ) && ! empty( $ignored_mime_types ) ) {
			$ignored_mime_types = array_map( 'sanitize_text_field', $ignored_mime_types );
			$sql                .= ' AND posts.post_mime_type NOT IN ("' . implode( '", "', $ignored_mime_types ) . '")';
		}

		if ( $count ) {
			return $wpdb->get_var( $sql );
		} else {
			$sql .= " ORDER BY posts.ID DESC LIMIT {$limit}";

			return array_map( 'intval', $wpdb->get_col( $sql ) );
		}
	}

	/**
	 * Called when background process has been cancelled.
	 */
	protected function cancelled() {
		$this->as3cf->update_media_library_total();
	}

	/**
	 * Called when background process has been paused.
	 */
	protected function paused() {
		$this->as3cf->update_media_library_total();
	}

	/**
	 * Called when background process has been resumed.
	 */
	protected function resumed() {
		// Do nothing at the moment.
	}

	/**
	 * Called when background process has completed.
	 */
	protected function completed() {
		$this->as3cf->update_media_library_total();
	}

	/**
	 * Get complete notice message.
	 *
	 * @return string
	 */
	protected function get_complete_message() {
		return __( '<strong>WP Offload Media</strong> &mdash; Finished offloading Media Library to bucket.', 'amazon-s3-and-cloudfront' );
	}
}