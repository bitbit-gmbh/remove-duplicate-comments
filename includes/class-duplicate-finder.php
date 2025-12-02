<?php
/**
 * Duplicate Comments Finder Class
 *
 * Handles finding duplicate comments based on identical content and moving
 * older copies to the trash for safe review.
 * Implements batch processing for performance and provides comprehensive error handling.
 *
 * @package Remove_Duplicate_Comments
 * @since 1.0.0
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RDC_Duplicate_Finder class.
 *
 * Finds duplicate comments based on identical comment content WITHIN THE SAME POST
 * and moves older entries to the trash.
 * Uses batch processing to handle large datasets efficiently and provides
 * detailed progress tracking for AJAX operations.
 *
 * IMPORTANT: Duplicates are only identified when they occur on the same post.
 *
 * @since 1.0.0
 */
class RDC_Duplicate_Finder {

	/**
	 * Total number of comments processed.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private $processed_count;

	/**
	 * Total number of duplicate comments moved to trash.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private $deleted_count;

	/**
	 * Constructor.
	 *
	 * Initializes count properties to zero.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->processed_count = 0;
		$this->deleted_count   = 0;
	}

	/**
	 * Find and remove duplicate comments in batches.
	 *
	 * Main entry point for batch processing of duplicate comments.
	 * Processes comments by status and removes duplicates while keeping
	 * the newest comment in each duplicate group.
	 *
	 * IMPORTANT: Duplicates are only found and trashed when they occur
	 * within the same post (comment_post_ID).
	 *
	 * @since 1.0.0
	 * @param array $statuses   Array of comment status strings ('1', '0', 'spam', 'trash').
	 * @param int   $batch_size Number of duplicate groups to process per batch.
	 * @return array {
	 *     Processing results.
	 *
	 *     @type int    $processed Total comments processed in this batch.
	 *     @type int    $trashed   Total duplicate comments moved to trash in this batch.
	 *     @type int    $deleted   Back-compat alias for $trashed.
	 *     @type bool   $completed Whether the batch processing is complete.
	 *     @type string $error     Error message if any, null on success.
	 * }
	 */
	public function find_and_remove_duplicates( $statuses, $batch_size = 100 ) {
		// Define allowed comment statuses
		$allowed_statuses = array( '1', '0', 'spam', 'trash' );

		// Validate statuses parameter
		if ( empty( $statuses ) || ! is_array( $statuses ) ) {
			return array(
				'processed' => 0,
				'trashed'   => 0,
				'deleted'   => 0,
				'completed' => true,
				'error'     => __( 'Invalid comment statuses provided.', 'remove-duplicate-comments' ),
			);
		}

		// Filter statuses to only allowed values
		$filtered_statuses = array_intersect( $statuses, $allowed_statuses );
		if ( empty( $filtered_statuses ) ) {
			return array(
				'processed' => 0,
				'trashed'   => 0,
				'deleted'   => 0,
				'completed' => true,
				'error'     => __( 'No valid comment statuses provided.', 'remove-duplicate-comments' ),
			);
		}

		// Use filtered statuses for processing
		$statuses = $filtered_statuses;

		// Sanitize batch size - ensure positive integer, cap at 100
		$batch_size = absint( $batch_size );
		if ( 100 < $batch_size ) {
			$batch_size = 100;
		}
		if ( 1 > $batch_size ) {
			$batch_size = 1;
		}

		// Get duplicate groups from database
		$duplicate_groups = $this->get_duplicate_groups( $statuses, $batch_size );

		// Check for database errors
		if ( is_wp_error( $duplicate_groups ) ) {
			return array(
				'processed' => 0,
				'trashed'   => 0,
				'deleted'   => 0,
				'completed' => true,
				'error'     => $duplicate_groups->get_error_message(),
			);
		}

		// Process each duplicate group
		$batch_processed = 0;
		$batch_trashed   = 0;

		foreach ( $duplicate_groups as $duplicate_group ) {
			$trashed_in_group = $this->delete_duplicates( $duplicate_group, $statuses );
			$batch_trashed   += $trashed_in_group;
			$batch_processed += intval( $duplicate_group['duplicate_count'] );
		}

		// Determine if batch processing is completed
		$is_completed = count( $duplicate_groups ) < $batch_size;

		return array(
			'processed' => $batch_processed,
			'trashed'   => $batch_trashed,
			'deleted'   => $batch_trashed,
			'completed' => $is_completed,
			'error'     => null,
		);
	}

	/**
	 * Get duplicate comment groups from database.
	 *
	 * Queries the WordPress comments table to find groups of comments
	 * with identical content WITHIN THE SAME POST. Uses MD5 hash for
	 * efficient grouping and prepared statements for security.
	 *
	 * Performance: Groups by comment_post_ID first to ensure duplicates
	 * are only detected within the same post.
	 *
	 * @since 1.0.0
	 * @param array $statuses   Array of comment status strings to filter by.
	 * @param int   $batch_size Maximum number of duplicate groups to return.
	 * @return array|WP_Error Array of duplicate groups or WP_Error on database failure.
	 */
	private function get_duplicate_groups( $statuses, $batch_size ) {
		global $wpdb;

		// Build status placeholders for IN clause
		$status_placeholders = array_fill( 0, count( $statuses ), '%s' );
		$status_placeholders = implode( ',', $status_placeholders );

		// Construct SQL query with MD5 hash for efficient grouping
		// CRITICAL: Group by comment_post_ID to only find duplicates WITHIN the same post
		$query = "
			SELECT
				comment_post_ID,
				comment_content,
				COUNT(*) as duplicate_count
			FROM {$wpdb->comments}
			WHERE comment_approved IN ({$status_placeholders})
			GROUP BY comment_post_ID, MD5(comment_content)
			HAVING COUNT(*) > 1
			LIMIT %d
		";

		// Prepare query with status values and batch size
		$prepared_query = $wpdb->prepare(
			$query,
			array_merge( $statuses, array( $batch_size ) )
		);

		// Execute query and get results as associative arrays
		$results = $wpdb->get_results( $prepared_query, ARRAY_A );

		// Check for database errors
		if ( ! empty( $wpdb->last_error ) ) {
			// Log detailed error internally
			error_log( 'RDC Database Error: ' . $wpdb->last_error );
			
			return new WP_Error(
				'db_error',
				__( 'Database error occurred while finding duplicate comments.', 'remove-duplicate-comments' )
			);
		}

		// Return results (empty array if no duplicates found)
		return $results ? $results : array();
	}

	/**
	 * Move duplicate comments from a group to the trash, keeping the newest.
	 *
	 * Queries all comment IDs for the duplicate content WITHIN THE SAME POST
	 * and moves all but the newest comment to the trash. This ensures duplicates
	 * are only identified when they appear on the same post.
	 * Method name retained for backwards compatibility with earlier versions.
	 *
	 * @since 1.0.0
	 * @param array $duplicate_group {
	 *     Duplicate group data from database query.
	 *
	 *     @type int    $comment_post_ID Post ID for this duplicate group.
	 *     @type string $comment_content Content of duplicate comments.
	 *     @type string $duplicate_count Number of duplicates in group.
	 * }
	 * @param array $statuses Array of comment status strings to filter by.
	 * @return int Number of comments successfully moved to trash.
	 */
	private function delete_duplicates( $duplicate_group, $statuses ) {
		global $wpdb;

		// Build status placeholders for IN clause
		$status_placeholders = array_fill( 0, count( $statuses ), '%s' );
		$status_placeholders = implode( ',', $status_placeholders );

		// Ensure WordPress comment trash functions are available
		if ( ! function_exists( 'wp_trash_comment' ) ) {
			require_once ABSPATH . 'wp-admin/includes/comment.php';
		}

		// Query all comment IDs for this specific content, statuses AND post ID
		// CRITICAL: Filter by comment_post_ID to ensure duplicates are only within the same post
		$query = "
			SELECT comment_ID
			FROM {$wpdb->comments}
			WHERE comment_post_ID = %d
			AND comment_content = %s
			AND comment_approved IN ({$status_placeholders})
			ORDER BY comment_ID DESC
		";

		// Prepare query with post ID, comment content and status values
		$prepared_query = $wpdb->prepare(
			$query,
			array_merge(
				array(
					intval( $duplicate_group['comment_post_ID'] ),
					$duplicate_group['comment_content']
				),
				$statuses
			)
		);

		// Get all comment IDs for this duplicate group
		$comment_ids = $wpdb->get_col( $prepared_query );

		// Check for database errors
		if ( ! empty( $wpdb->last_error ) ) {
			error_log( 'RDC Database Error in delete_duplicates: ' . $wpdb->last_error );
			return 0;
		}

		// Ensure we have comments
		if ( empty( $comment_ids ) || count( $comment_ids ) < 2 ) {
			return 0;
		}

		// Convert to integers for security
		$comment_ids = array_map( 'intval', $comment_ids );

		// Keep the first ID (newest comment) and trash the rest
		array_shift( $comment_ids );

		// Trash remaining duplicates - no need to verify content again since WHERE clause already filtered
		$trashed_count = 0;
		foreach ( $comment_ids as $comment_id ) {
			$comment_status = wp_get_comment_status( $comment_id );

			// Skip if the comment is already in the trash
			if ( 'trash' === $comment_status ) {
				continue;
			}

			$trash_result = wp_trash_comment( $comment_id );

			if ( false !== $trash_result ) {
				$trashed_count++;
				$this->deleted_count++;
			}
		}

		// Update processed count with total comments in group
		$this->processed_count += intval( $duplicate_group['duplicate_count'] );

		return $trashed_count;
	}

	/**
	 * Get total number of comments processed.
	 *
	 * @since 1.0.0
	 * @return int Total processed count.
	 */
	public function get_processed_count() {
		return $this->processed_count;
	}

	/**
	 * Get total number of duplicate comments moved to trash.
	 *
	 * @since 1.0.0
	 * @return int Total moved-to-trash count.
	 */
	public function get_deleted_count() {
		return $this->deleted_count;
	}

	/**
	 * Get total number of duplicate comments moved to trash.
	 *
	 * Provided as a semantic alias for get_deleted_count().
	 *
	 * @since 1.1.1
	 * @return int Total moved-to-trash count.
	 */
	public function get_trashed_count() {
		return $this->deleted_count;
	}

	/**
	 * Reset processed and deleted counts to zero.
	 *
	 * Useful for processing multiple batches with the same instance.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function reset_counts() {
		$this->processed_count = 0;
		$this->deleted_count   = 0;
	}
}