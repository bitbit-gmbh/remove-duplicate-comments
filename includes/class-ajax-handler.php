<?php
/**
 * AJAX Handler Class
 *
 * Handles AJAX requests for duplicate comment processing.
 * Provides secure endpoints for batch processing with proper validation,
 * nonce checking, and capability verification.
 *
 * @package Remove_Duplicate_Comments
 * @since 1.0.0
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RDC_Ajax_Handler class.
 *
 * Registers AJAX actions, validates security (nonce, capability), and processes
 * duplicate comments via AJAX. Handles both successful processing and error
 * scenarios with appropriate JSON responses.
 *
 * @since 1.0.0
 */
class RDC_Ajax_Handler {

	/**
	 * AJAX action name for processing duplicates.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $ajax_action;

	/**
	 * Nonce action name for security validation.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $nonce_action;

	/**
	 * Constructor.
	 *
	 * Initializes AJAX handler and registers WordPress hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->ajax_action  = 'rdc_process_duplicates';
		$this->nonce_action = 'rdc_duplicate_comments_nonce';

		$this->init_hooks();
	}

	/**
	 * Register WordPress action hooks.
	 *
	 * Sets up AJAX endpoints and admin script enqueuing.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		// Register AJAX action for logged-in users only
		add_action( 'wp_ajax_' . $this->ajax_action, array( $this, 'process_duplicates_ajax' ) );

		// Register admin script enqueue hook
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Enqueue JavaScript for AJAX handling on the plugin's admin page.
	 *
	 * Only loads scripts on our specific admin page to improve performance.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix WordPress admin page hook suffix.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		// Only load scripts on our plugin's admin page
		if ( 'tools_page_remove-duplicate-comments' !== $hook_suffix ) {
			return;
		}

		// Register and enqueue JavaScript file
		wp_enqueue_script(
			'rdc-ajax-handler',
			RDC_PLUGIN_URL . '/admin/js/ajax-handler.js',
			array( 'jquery', 'wp-i18n' ),
			RDC_VERSION,
			true
		);

		// Create nonce for AJAX security
		$nonce = wp_create_nonce( $this->nonce_action );

		// Prepare data for JavaScript
		$localized_data = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => $nonce,
			'action'  => $this->ajax_action,
		);

		// Pass data to JavaScript
		wp_localize_script( 'rdc-ajax-handler', 'rdcAjax', $localized_data );

		// Register script translations
		wp_set_script_translations( 'rdc-ajax-handler', 'remove-duplicate-comments', RDC_PLUGIN_DIR . '/languages' );
	}

	/**
	 * AJAX callback for processing duplicate comments.
	 *
	 * Handles the AJAX request, validates security, processes duplicates,
	 * and returns JSON response with results or errors.
	 *
	 * @since 1.0.0
	 */
	public function process_duplicates_ajax() {
		try {
			// Validate nonce - WordPress dies automatically on failure
			check_ajax_referer( $this->nonce_action, 'nonce' );

			// Check user capability
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'remove-duplicate-comments' ) ) );
			}

			// Get and validate statuses from POST data
			$statuses = isset( $_POST['statuses'] ) ? (array) $_POST['statuses'] : array();

			if ( empty( $statuses ) ) {
				wp_send_json_error( array( 'message' => __( 'No comment statuses selected.', 'remove-duplicate-comments' ) ) );
			}

			// Sanitize status values
			$statuses = array_map( 'sanitize_text_field', $statuses );

			// Define allowed statuses and filter input
			$allowed_statuses = array( '1', '0', 'spam', 'trash' );
			$statuses = array_intersect( $statuses, $allowed_statuses );

			if ( empty( $statuses ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid comment statuses provided.', 'remove-duplicate-comments' ) ) );
			}

			// Ensure duplicate finder class is loaded
			if ( ! class_exists( 'RDC_Duplicate_Finder' ) ) {
				require_once RDC_PLUGIN_DIR . '/includes/class-duplicate-finder.php';
			}

			// Process duplicates
			$finder = new RDC_Duplicate_Finder();
			$result = $finder->find_and_remove_duplicates( $statuses, 100 );

			// Handle processing results
			if ( ! empty( $result['error'] ) ) {
				wp_send_json_error( array( 'message' => $result['error'] ) );
			}

			// Construct success message with pluralization
			$processed_text = sprintf(
				_n( 'Processed %d comment', 'Processed %d comments', $result['processed'], 'remove-duplicate-comments' ),
				$result['processed']
			);
			$trashed_text = sprintf(
				_n( 'moved %d duplicate to the trash', 'moved %d duplicates to the trash', $result['trashed'], 'remove-duplicate-comments' ),
				$result['trashed']
			);
			$message = $processed_text . ', ' . $trashed_text . '.';

			// Send success response
			wp_send_json_success( array(
				'processed' => $result['processed'],
				'trashed'   => $result['trashed'],
				'deleted'   => $result['trashed'], // Backwards compatibility for older front-end code.
				'completed' => $result['completed'],
				'message'   => $message,
			) );

		} catch ( Exception $e ) {
			// Log detailed error for debugging
			error_log( 'RDC AJAX Error: ' . $e->getMessage() );

			// Send generic error to user
			wp_send_json_error( array( 'message' => __( 'An unexpected error occurred.', 'remove-duplicate-comments' ) ) );
		}
	}

	/**
	 * Get AJAX action name.
	 *
	 * @since 1.0.0
	 * @return string AJAX action name.
	 */
	public function get_ajax_action() {
		return $this->ajax_action;
	}

	/**
	 * Get nonce action name.
	 *
	 * @since 1.0.0
	 * @return string Nonce action name.
	 */
	public function get_nonce_action() {
		return $this->nonce_action;
	}
}