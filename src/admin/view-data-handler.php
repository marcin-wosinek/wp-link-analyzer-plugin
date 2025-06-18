<?php

namespace LINK_ANALYZER;

class View_Data_Handler {

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
}
