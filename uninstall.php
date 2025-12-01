<?php
/**
 * Uninstall script for Remove Duplicate Comments plugin
 *
 * Handles plugin cleanup when deleted from WordPress admin interface.
 * This file is executed automatically when the plugin is deleted.
 *
 * @package Remove_Duplicate_Comments
 * @since 1.0.0
 */

// Prevent direct access - WordPress must be executing the uninstall process
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Additional security check for WordPress environment
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cleanup operations.
 *
 * Currently, this plugin does not store any persistent data:
 * - No options in wp_options table
 * - No custom database tables
 * - No user meta or transients
 * - No cached data
 *
 * The plugin only performs on-demand duplicate comment deletion
 * without saving any configuration or settings.
 *
 * This file is structured for future extensibility if settings
 * or persistent data are added in future versions.
 */

// Example: Delete plugin options (if added in future versions)
// delete_option( 'rdc_plugin_settings' );
// delete_option( 'rdc_last_run_timestamp' );

// Example: Delete plugin transients (if added in future versions)
// delete_transient( 'rdc_processing_status' );

// Example: Drop custom database tables (if added in future versions)
// global $wpdb;
// $table_name = $wpdb->prefix . 'rdc_logs';
// $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

// Example: Delete user meta (if added in future versions)
// delete_metadata( 'user', 0, 'rdc_user_preference', '', true );

// Example: Clear scheduled cron jobs (if added in future versions)
// wp_clear_scheduled_hook( 'rdc_daily_cleanup' );

// Example: Multisite cleanup (if plugin supports network activation)
// if ( is_multisite() ) {
//     global $wpdb;
//     $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
//     
//     foreach ( $blog_ids as $blog_id ) {
//         switch_to_blog( $blog_id );
//         
//         // Perform cleanup for each site
//         delete_option( 'rdc_plugin_settings' );
//         
//         restore_current_blog();
//     }
// }

/**
 * WordPress Uninstall Best Practices:
 *
 * 1. Always check WP_UNINSTALL_PLUGIN constant for security
 * 2. Only delete data that belongs to this plugin
 * 3. Be careful with shared data (e.g., don't delete comments themselves)
 * 4. Consider multisite installations
 * 5. Use WordPress functions (delete_option, $wpdb->query) not raw SQL
 * 6. Don't use plugin-specific functions (they may not be loaded)
 * 7. Test thoroughly - uninstall can't be undone
 * 8. Consider user data privacy regulations (GDPR, etc.)
 */