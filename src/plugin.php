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

require 'admin/view.php';
require 'api/add-data.php';

/**
 * Main plugin class. It manages initialization, install, and activations.
 */
class Link_Analyzer_Plugin_Class {

	public const DB_VERSION = '1.0';

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
	 * Get table names with WordPress prefix
	 *
	 * @return array
	 */
	private static function get_table_names() {
		global $wpdb;

		return array(
			'sessions'      => $wpdb->prefix . 'linkanalyzer_sessions',
			'links'         => $wpdb->prefix . 'linkanalyzer_links',
			'session_links' => $wpdb->prefix . 'linkanalyzer_session_links',
		);
	}

	/**
	 * Create database tables
	 *
	 * @return void
	 */
	private static function create_tables() {
		global $wpdb;

		$tables          = self::get_table_names();
		$charset_collate = $wpdb->get_charset_collate();

		// Sessions table
		$sql_sessions = "CREATE TABLE {$tables['sessions']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			screen_width int(11) NOT NULL,
			screen_height int(11) NOT NULL,
			created_at timestamp DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_created_at (created_at),
			KEY idx_screen_dimensions (screen_width, screen_height)
		) $charset_collate;";

		// Links table
		$sql_links = "CREATE TABLE {$tables['links']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			link_text varchar(500) NOT NULL,
			link_href varchar(2048) NOT NULL,
			created_at timestamp DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY unique_link (link_text(191), link_href(191)),
			KEY idx_href (link_href(255)),
			KEY idx_text (link_text(100))
		) $charset_collate;";

		// Session links junction table
		$sql_session_links = "CREATE TABLE {$tables['session_links']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			session_id bigint(20) unsigned NOT NULL,
			link_id bigint(20) unsigned NOT NULL,
			link_order int(11) NOT NULL,
			PRIMARY KEY (id),
			KEY idx_session_id (session_id),
			KEY idx_link_id (link_id),
			KEY idx_session_order (session_id, link_order),
			UNIQUE KEY unique_session_link_order (session_id, link_order),
			CONSTRAINT fk_session_links_session_id
				FOREIGN KEY (session_id) REFERENCES {$tables['sessions']} (id)
				ON DELETE CASCADE,
			CONSTRAINT fk_session_links_link_id
				FOREIGN KEY (link_id) REFERENCES {$tables['links']} (id)
				ON DELETE CASCADE
		) $charset_collate;";

		// Use WordPress dbDelta for table creation
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql_sessions );
		dbDelta( $sql_links );
		dbDelta( $sql_session_links );
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

				// Create database tables
				self::create_tables();

		add_option( 'link_analyzer_db_version', self::DB_VERSION );
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
		$tables = self::get_table_names();

		// Drop tables in reverse order due to foreign key constraints
		$wpdb->query( "DROP TABLE IF EXISTS {$tables['session_links']}" );
		$wpdb->query( "DROP TABLE IF EXISTS {$tables['links']}" );
		$wpdb->query( "DROP TABLE IF EXISTS {$tables['sessions']}" );

		// Remove database version option
		delete_option( 'link_analyzer_db_version' );

		// Clear any cached data
		wp_cache_flush();
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
	 * Add script
	 *
	 * @return void
	 */
	public static function wpc_script_enqueue() {
		if ( is_home() ) {
			wp_enqueue_script(
				'link-analyzer',
				plugins_url( '/script.js', __FILE__ ),
				array( 'wp-api-fetch' ),
				'1.0.0',
				array( 'in_footer' => true )
			);
		}
	}

	/**
	 * Add admin menu
	 *
	 * @return void
	 */
	public static function wpc_admin_menu() {
		add_menu_page( 'Link analyzer plugin', 'Link analyzer', 'manage_options', 'link-analyzer-plugin', __NAMESPACE__ . '\admin_page_view' );
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
}
