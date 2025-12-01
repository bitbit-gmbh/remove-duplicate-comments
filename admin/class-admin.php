<?php
/**
 * Admin class for Remove Duplicate Comments plugin
 *
 * This class handles the admin interface registration and asset enqueuing
 * for the Remove Duplicate Comments plugin.
 *
 * @package Remove_Duplicate_Comments
 * @subpackage Admin
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RDC_Admin class
 *
 * This class registers the admin menu item under Tools, enqueues admin assets,
 * and renders the admin page for the Remove Duplicate Comments plugin.
 *
 * @since 1.0.0
 */
class RDC_Admin {

	/**
	 * Constructor
	 *
	 * Initializes the admin class and registers WordPress hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 *
	 * Registers WordPress action hooks for admin functionality.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
	}

	/**
	 * Register admin menu
	 *
	 * Registers the plugin page under Tools menu with capability check.
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu() {
		add_management_page(
			__( 'Remove Duplicate Comments', 'remove-duplicate-comments' ),
			__( 'Remove Duplicate Comments', 'remove-duplicate-comments' ),
			'manage_options',
			'remove-duplicate-comments',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Enqueue admin styles
	 *
	 * Enqueues CSS only on the plugin's admin page.
	 *
	 * @param string $hook_suffix WordPress admin page hook suffix.
	 * @since 1.0.0
	 */
	public function enqueue_admin_styles( $hook_suffix ) {
		if ( 'tools_page_remove-duplicate-comments' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'rdc-admin',
			RDC_PLUGIN_URL . '/admin/css/admin.css',
			array(),
			RDC_VERSION,
			'all'
		);
	}

	/**
	 * Render admin page
	 *
	 * Renders the admin page by including the template file.
	 *
	 * @since 1.0.0
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'remove-duplicate-comments' ) );
		}

		require_once RDC_PLUGIN_DIR . '/admin/admin-page.php';
	}
}