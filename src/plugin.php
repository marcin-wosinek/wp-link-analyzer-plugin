<?php
/**
 * Plugin main class
 *
 * @package     Link Analyzer
 * @since       1.0
 * @author      Marcin Wosinek
 * @license     GPL-2.0-or-later
 */

namespace LINK_ANALYZER;

// Define the plugin base directory constant if not already defined.
if ( ! defined( 'LINK_ANALYZER_PLUGIN_DIR' ) ) {
	define( 'LINK_ANALYZER_PLUGIN_DIR', plugin_dir_path( __DIR__ ) );
}

// Admin files.
require_once LINK_ANALYZER_PLUGIN_DIR . 'src/admin/admin-controller.php';
require_once LINK_ANALYZER_PLUGIN_DIR . 'src/admin/view-renderer.php';
require_once LINK_ANALYZER_PLUGIN_DIR . 'src/admin/components/screen-height-chart.php';

// API files.
require_once LINK_ANALYZER_PLUGIN_DIR . 'src/api/add-data.php';

// Core files.
require_once LINK_ANALYZER_PLUGIN_DIR . 'src/db.php';

/**
 * Main plugin class. It manages initialization, install, and activations.
 */
class Link_Analyzer_Plugin_Class {

	/**
	 * Manages plugin initialization
	 *
	 * @return void
	 */
	public function __construct() {

		// Register plugin lifecycle hooks.
		register_deactivation_hook( LINK_ANALYZER_PLUGIN, array( $this, 'wpc_deactivate' ) );
	}



	/**
	 * Handles plugin activation:
	 *
	 * @return void
	 */
	public static function wpc_activate() {
		// Security checks.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$plugin = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : '';
		check_admin_referer( "activate-plugin_{$plugin}" );

				// Create database tables.
		DB_Handler::create_tables();

		// Schedule daily cron job to run at 3:00 AM.
		if ( ! wp_next_scheduled( 'link_analyzer_cron_hook' ) ) {
			// Get the current time in the WordPress timezone.
			$timezone        = get_option( 'timezone_string' ) ? get_option( 'timezone_string' ) : 'UTC';
			$timezone_object = new \DateTimeZone( $timezone );
			$datetime        = new \DateTime( 'tomorrow 03:00:00', $timezone_object );

			// Schedule the event.
			wp_schedule_event( $datetime->getTimestamp(), 'daily', 'link_analyzer_cron_hook' );
		}

		add_option( 'link_analyzer_db_version', DB_Handler::DB_VERSION );
	}

	/**
	 * Handles plugin deactivation
	 *
	 * @return void
	 */
	public function wpc_deactivate() {
		// Security checks.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$plugin = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : '';
		check_admin_referer( "deactivate-plugin_{$plugin}" );

		global $wpdb;
		$tables = DB_Handler::get_table_names();

		// Drop tables in reverse order due to foreign key constraints.
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %s', $tables['session_links'] ) );
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %s', $tables['links'] ) );
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %s', $tables['sessions'] ) );

		// Remove database version option.
		delete_option( 'link_analyzer_db_version' );

		// Clear any cached data.
		wp_cache_flush();

		// Clear scheduled cron job.
		$timestamp = wp_next_scheduled( 'link_analyzer_cron_hook' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'link_analyzer_cron_hook' );
		}
	}

	/**
	 * Handles plugin uninstall
	 *
	 * @return void
	 */
	public static function wpc_uninstall() {

		// Security checks.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
	}

	/**
	 * Add script for the home page
	 *
	 * @return void
	 */
	public static function wpc_script_enqueue() {
		if ( is_home() ) {
			$plugin_dir = plugin_dir_path( __DIR__ );
			$plugin_url = plugin_dir_url( __DIR__ );

			wp_enqueue_script(
				'link-analyzer',
				$plugin_url . 'src/js/home-page.js',
				array( 'wp-api-fetch' ),
				filemtime( $plugin_dir . 'src/js/home-page.js' ),
				true
			);
		}
	}

	/**
	 * Register API endpoint
	 *
	 * @return void
	 */
	public static function wpc_rest_api_init() {
		$controller = new Add_Data_Controller();
		$controller->register_routes();
	}

	/**
	 * Clean up old sessions data
	 *
	 * @return void
	 */
	public static function wpc_cron_cleanup() {
		// Remove sessions older than 7 days.
		$deleted = DB_Handler::remove_old_sessions();

		// Log the cleanup action.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( 'Link Analyzer: Cleaned up %d old sessions', $deleted ) );
		}
	}
}
