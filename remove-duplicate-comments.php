<?php
/**
 * Plugin Name: Remove Duplicate Comments
 * Description: Findet doppelte Kommentare basierend auf identischem Inhalt und verschiebt ältere Kopien in den Papierkorb. Anfängerfreundlich mit Live-Fortschrittsanzeige.
 * Version: 1.1.0
 * Author: bitbit GmbH
 * Author URI: https://www.bitbit.de
 * License: Apache-2.0
 * License URI: https://www.apache.org/licenses/LICENSE-2.0
 * Requires at least: 6.7
 * Requires PHP: 8.1
 * Tested up to: 6.7
 * Text Domain: remove-duplicate-comments
 * Domain Path: /languages
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'RDC_VERSION', '1.1.0' );
define( 'RDC_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'RDC_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Main plugin singleton class for Remove Duplicate Comments.
 *
 * Handles plugin initialization, text domain loading, and hook registration.
 *
 * @since 1.0.0
 */
class Remove_Duplicate_Comments {

	/**
	 * Plugin instance.
	 *
	 * @since 1.0.0
	 * @var Remove_Duplicate_Comments|null
	 */
	private static $instance = null;

	/**
	 * Private constructor to prevent direct instantiation.
	 *
	 * Initializes plugin hooks.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Get plugin instance (singleton pattern).
	 *
	 * @since 1.0.0
	 * @return Remove_Duplicate_Comments Plugin instance.
	 */
	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize plugin functionality.
	 *
	 * Called on plugins_loaded hook.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// Load plugin text domain for internationalization
		load_plugin_textdomain(
			'remove-duplicate-comments',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		// Load AJAX handler class
		require_once RDC_PLUGIN_DIR . '/includes/class-ajax-handler.php';

		// Initialize AJAX handler for admin functionality
		if ( is_admin() ) {
			new RDC_Ajax_Handler();

			// Load admin class
			require_once RDC_PLUGIN_DIR . '/admin/class-admin.php';

			// Initialize admin interface
			new RDC_Admin();
		}

		// Plugin initialization will be implemented in subsequent phases
	}

	/**
	 * Plugin activation hook callback.
	 *
	 * Checks system requirements and performs activation tasks.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		// Check WordPress version compatibility
		if ( version_compare( get_bloginfo( 'version' ), '6.7', '<' ) ) {
			wp_die(
				esc_html__( 'This plugin requires WordPress 6.7 or higher.', 'remove-duplicate-comments' )
			);
		}

		// Check PHP version compatibility
		if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
			wp_die(
				esc_html__( 'This plugin requires PHP 8.1 or higher.', 'remove-duplicate-comments' )
			);
		}

		// Activation tasks can be added here in future phases
	}

	/**
	 * Plugin deactivation hook callback.
	 *
	 * Reserved for cleanup tasks during plugin deactivation.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Deactivation cleanup tasks will be implemented if needed
	}
}

// Register activation hook
register_activation_hook( __FILE__, array( 'Remove_Duplicate_Comments', 'activate' ) );

// Register deactivation hook
register_deactivation_hook( __FILE__, array( 'Remove_Duplicate_Comments', 'deactivate' ) );

// Initialize plugin
Remove_Duplicate_Comments::get_instance();