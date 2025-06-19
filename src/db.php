<?php

namespace LINK_ANALYZER;

class DB_Handler {

	/**
	 * Database version
	 */
	const DB_VERSION = '1.0';

	/**
	 * Get table names with WordPress prefix
	 *
	 * @return array
	 */
	public static function get_table_names() {
		global $wpdb;

		return array(
			'sessions'      => $wpdb->prefix . 'linkanalyzer_sessions',
			'links'         => $wpdb->prefix . 'linkanalyzer_links',
			'session_links' => $wpdb->prefix . 'linkanalyzer_session_links',
		);
	}

	/**
	 * Remove sessions older than a specified number of days
	 *
	 * @param int $older_than_days Number of days to keep sessions for (default: 7).
	 * @return int|false Number of rows affected or false on error.
	 */
	public static function remove_old_sessions( $older_than_days = 7 ) {
		global $wpdb;

		// Calculate the cutoff date.
		$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$older_than_days} days" ) );

		// Delete sessions older than the cutoff date.
		// Note: We don't need to delete from session_links table because of the ON DELETE CASCADE constraint.
		$result = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM `{$wpdb->prefix}linkanalyzer_sessions` WHERE created_at < %s",
				$cutoff_date
			)
		);

		return $result;
	}

	/**
	 * Get screen height statistics
	 *
	 * @return array Array of screen height data with count of sessions.
	 */
	public static function get_screen_height_stats() {
		global $wpdb;

		// Get all unique screen heights with their session counts.
		$results = $wpdb->get_results(
			"SELECT screen_height, COUNT(*) as session_count
			FROM `{$wpdb->prefix}linkanalyzer_sessions`
			GROUP BY screen_height
			ORDER BY screen_height ASC",
			ARRAY_A
		);

		// Format the data.
		$formatted_data = array_map(
			function ( $row ) {
				return array(
					'screenHeight'     => (int) $row['screen_height'],
					'numberOfSessions' => (int) $row['session_count'],
				);
			},
			$results
		);

		return $formatted_data;
	}

	/**
	 * Get link data with session counts.
	 *
	 * @param int $limit Maximum number of links to return.
	 * @return array Array of link data with session counts.
	 */
	public static function get_link_data( $limit = 100 ) {
		global $wpdb;

		// Get links with their session counts.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				l.id,
				l.link_text,
				l.link_href,
				COUNT(DISTINCT sl.session_id) as session_count
				FROM `{$wpdb->prefix}linkanalyzer_links` l
				LEFT JOIN `{$wpdb->prefix}linkanalyzer_session_links` sl ON l.id = sl.link_id
				GROUP BY l.id, l.link_text, l.link_href
				ORDER BY session_count DESC
				LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		// Format the data.
		$formatted_data = array_map(
			function ( $row ) {
				return array(
					'id'           => (int) $row['id'],
					'text'         => $row['link_text'],
					'href'         => $row['link_href'],
					'sessionCount' => (int) $row['session_count'],
				);
			},
			$results
		);

		return $formatted_data;
	}

	/**
	 * Get link data for a specific session.
	 *
	 * @param int $session_id The session ID.
	 * @return array|WP_Error Link data for the session or WP_Error on failure.
	 */
	public static function get_links_for_session( $session_id ) {
		global $wpdb;

		// Validate session ID.
		if ( ! is_numeric( $session_id ) || $session_id <= 0 ) {
			return new \WP_Error(
				'invalid_session_id',
				'Invalid session ID',
				array( 'status' => 400 )
			);
		}

		// Get links for this session.
		$links = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT l.*, sl.link_order
				FROM `{$wpdb->prefix}linkanalyzer_links` l
				JOIN `{$wpdb->prefix}linkanalyzer_session_links` sl ON l.id = sl.link_id
				WHERE sl.session_id = %d
				ORDER BY sl.link_order",
				$session_id
			),
			ARRAY_A
		);

		// Format the data.
		$formatted_links = array_map(
			function ( $row ) {
				return array(
					'id'    => (int) $row['id'],
					'text'  => $row['link_text'],
					'href'  => $row['link_href'],
					'order' => (int) $row['link_order'],
				);
			},
			$links
		);

		return $formatted_links;
	}

	/**
	 * Get total number of sessions.
	 *
	 * @return int Total number of sessions.
	 */
	public static function get_total_sessions_count() {
		global $wpdb;

		// Get total count of sessions.
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}linkanalyzer_sessions`" );

		return (int) $count;
	}

	/**
	 * Clear all analytics data.
	 *
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	public static function clear_all_data() {
		global $wpdb;

		// Start a transaction.
		$wpdb->query( 'START TRANSACTION' );

		try {
			// Truncate all tables - using DELETE instead of TRUNCATE for better compatibility.
			// We delete session_links first due to foreign key constraints.
			$wpdb->query( "DELETE FROM `{$wpdb->prefix}linkanalyzer_session_links`" );
			$wpdb->query( "DELETE FROM `{$wpdb->prefix}linkanalyzer_links`" );
			$wpdb->query( "DELETE FROM `{$wpdb->prefix}linkanalyzer_sessions`" );

			// Reset auto-increment values.
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}linkanalyzer_sessions` AUTO_INCREMENT = 1" );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}linkanalyzer_links` AUTO_INCREMENT = 1" );
			$wpdb->query( "ALTER TABLE `{$wpdb->prefix}linkanalyzer_session_links` AUTO_INCREMENT = 1" );

			// Commit the transaction.
			$wpdb->query( 'COMMIT' );

			return true;
		} catch ( \Exception $e ) {
			// Rollback on error.
			$wpdb->query( 'ROLLBACK' );

			return new \WP_Error(
				'db_clear_error',
				'Failed to clear analytics data: ' . $e->getMessage(),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Create database tables
	 *
	 * @return void
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Sessions table.
		$sql_sessions = "CREATE TABLE `{$wpdb->prefix}linkanalyzer_sessions` (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			screen_width int(11) NOT NULL,
			screen_height int(11) NOT NULL,
			created_at timestamp DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_created_at (created_at),
			KEY idx_screen_dimensions (screen_width, screen_height)
		) $charset_collate;";

		// Links table.
		$sql_links = "CREATE TABLE `{$wpdb->prefix}linkanalyzer_links` (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			link_text varchar(500) NOT NULL,
			link_href varchar(2048) NOT NULL,
			created_at timestamp DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY unique_link (link_text(191), link_href(191)),
			KEY idx_href (link_href(255)),
			KEY idx_text (link_text(100))
		) $charset_collate;";

		// Session links junction table.
		$sql_session_links = "CREATE TABLE `{$wpdb->prefix}linkanalyzer_session_links` (
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
				FOREIGN KEY (session_id) REFERENCES `{$wpdb->prefix}linkanalyzer_sessions` (id)
				ON DELETE CASCADE,
			CONSTRAINT fk_session_links_link_id
				FOREIGN KEY (link_id) REFERENCES `{$wpdb->prefix}linkanalyzer_links` (id)
				ON DELETE CASCADE
		) $charset_collate;";

		// Use WordPress dbDelta for table creation.
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql_sessions );
		dbDelta( $sql_links );
		dbDelta( $sql_session_links );
	}
}
