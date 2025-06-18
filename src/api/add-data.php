<?php

namespace LINK_ANALYZER;

class Add_Data_Controller {

	/**
	 * Sets the controller values
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace     = '/link-analyzer/v1';
		$this->resource_name = 'add-data';
	}

	/**
	 * Register controller routes
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->resource_name,
			array(
				// Here we register the readable endpoint for collections.
				array(
					'methods'  => 'POST',
					'callback' => array( $this, 'add_data' ),
					'args'     => array(
						'screenWidth'  => array(
							'required'          => true,
							'type'              => 'integer',
							'description'       => 'Screen width in pixels',
							'sanitize_callback' => 'absint',
							'validate_callback' => array( $this, 'validate_screen_width' ),
						),
						'screenHeight' => array(
							'required'          => true,
							'type'              => 'integer',
							'description'       => 'Screen height in pixels',
							'sanitize_callback' => 'absint',
							'validate_callback' => array( $this, 'validate_screen_height' ),
						),
						'linkData'     => array(
							'required'          => true,
							'type'              => 'array',
							'description'       => 'Array of link objects with text and href properties',
							'validate_callback' => array( $this, 'validate_link_data' ),
							'sanitize_callback' => array( $this, 'sanitize_link_data' ),
						),
					),
				),
			)
		);
	}

	/**
	 * Validates that the screen width is a positive integer.
	 *
	 * @param mixed           $value The screen width value to validate.
	 * @param WP_REST_Request $request Current request.
	 * @param string          $param The parameter name.
	 * @return bool|WP_Error True if valid, WP_Error if not.
	 */
	public function validate_screen_width( $value, $request, $param ) {
		if ( ! is_int( $value ) || $value <= 0 ) {
			return new WP_Error(
				'invalid_screen_width',
				'Screen width must be a positive integer',
				array( 'status' => 400 )
			);
		}
		return true;
	}

	/**
	 * Validates that the screen height is a positive integer.
	 *
	 * @param mixed           $value The screen height value to validate.
	 * @param WP_REST_Request $request Current request.
	 * @param string          $param The parameter name.
	 * @return bool|WP_Error True if valid, WP_Error if not.
	 */
	public function validate_screen_height( $value, $request, $param ) {
		if ( ! is_int( $value ) || $value <= 0 ) {
			return new WP_Error(
				'invalid_screen_height',
				'Screen height must be a positive integer',
				array( 'status' => 400 )
			);
		}
		return true;
	}

	/**
	 * Validates the link data structure and content.
	 *
	 * @param mixed           $value The link data to validate.
	 * @param WP_REST_Request $request Current request.
	 * @param string          $param The parameter name.
	 * @return bool|WP_Error True if valid, WP_Error if not.
	 * @throws WP_Error If the link data is invalid.
	 */
	public function validate_link_data( $value, $request, $param ) {
		// Check if it's an array.
		if ( ! is_array( $value ) ) {
			return new WP_Error(
				'invalid_link_data_type',
				'linkData must be an array',
				array( 'status' => 400 )
			);
		}

		// Check if array is empty.
		if ( empty( $value ) ) {
			return new WP_Error(
				'empty_link_data',
				'linkData array cannot be empty',
				array( 'status' => 400 )
			);
		}

		// Validate each item in the array.
		foreach ( $value as $index => $link ) {
			// Check if each item is an object (associative array in PHP).
			if ( ! is_array( $link ) || array_keys( $link ) === range( 0, count( $link ) - 1 ) ) {
				return new WP_Error(
					'invalid_link_item_type',
					sprintf( 'linkData item at index %d must be an object', $index ),
					array( 'status' => 400 )
				);
			}

			// Check required properties.
			if ( ! isset( $link['text'] ) ) {
				return new WP_Error(
					'missing_link_text',
					sprintf( 'linkData item at index %d is missing required "text" property', $index ),
					array( 'status' => 400 )
				);
			}

			if ( ! isset( $link['href'] ) ) {
				return new WP_Error(
					'missing_link_href',
					sprintf( 'linkData item at index %d is missing required "href" property', $index ),
					array( 'status' => 400 )
				);
			}

			// Validate property types.
			if ( ! is_string( $link['text'] ) ) {
				return new WP_Error(
					'invalid_link_text_type',
					sprintf( 'linkData item at index %d: "text" must be a string', $index ),
					array( 'status' => 400 )
				);
			}

			if ( ! is_string( $link['href'] ) ) {
				return new WP_Error(
					'invalid_link_href_type',
					sprintf( 'linkData item at index %d: "href" must be a string', $index ),
					array( 'status' => 400 )
				);
			}

			// Optional: Validate href format (URL).
			if ( ! filter_var( $link['href'], FILTER_VALIDATE_URL ) ) {
				return new WP_Error(
					'invalid_link_href_format',
					sprintf( 'linkData item at index %d: "href" must be a valid URL', $index ),
					array( 'status' => 400 )
				);
			}

			// Optional: Check for empty strings.
			if ( empty( trim( $link['text'] ) ) ) {
				return new WP_Error(
					'empty_link_text',
					sprintf( 'linkData item at index %d: "text" cannot be empty', $index ),
					array( 'status' => 400 )
				);
			}
		}

		return true;
	}

	/**
	 * Sanitize data provided by the API user
	 *
	 * @param array $value The link data to sanitize.
	 * @return array Sanitized link data
	 */
	public function sanitize_link_data( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $value as $link ) {
			if ( is_array( $link ) && isset( $link['text'] ) && isset( $link['href'] ) ) {
				$sanitized[] = array(
					'text' => sanitize_text_field( $link['text'] ),
					'href' => esc_url_raw( $link['href'] ),
				);
			}
		}

		return $sanitized;
	}

	/**
	 * Saves the data provided by the API call.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response Response object.
	 * @throws \Exception If there is an error during data insertion.
	 */
	public function add_data( $request ) {
		global $wpdb;

		// Get the table names.
		$tables = \LINK_ANALYZER\Link_Analyzer_Plugin_Class::get_table_names();

		// Get sanitized parameters.
		$screen_width  = absint( $request->get_param( 'screenWidth' ) );
		$screen_height = absint( $request->get_param( 'screenHeight' ) );
		$link_data     = $this->sanitize_link_data( $request->get_param( 'linkData' ) );

		// Start transaction.
		$wpdb->query( $wpdb->prepare( 'START TRANSACTION' ) );

		try {
			// 1. Insert session.
			$session_result = $wpdb->insert(
				$tables['sessions'],
				array(
					'screen_width'  => $screen_width,
					'screen_height' => $screen_height,
				),
				array(
					'%d',
					'%d',
				)
			);

			if ( false === $session_result ) {
				throw new \Exception( 'Failed to insert session data.' );
			}

			$session_id = $wpdb->insert_id;

			// 2. Process links.
			$link_order = 0;
			foreach ( $link_data as $link ) {
				// Check if link already exists.
				$existing_link    = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT id FROM `{$wpdb->prefix}linkanalyzer_links` WHERE link_text = %s AND link_href = %s",
						$link['text'],
						$link['href']
					)
				);
				$existing_link_id = $existing_link ? $existing_link->id : null;

				// If link doesn't exist, insert it.
				if ( null === $existing_link_id ) {
					$link_result = $wpdb->insert(
						$tables['links'],
						array(
							'link_text' => $link['text'],
							'link_href' => $link['href'],
						),
						array(
							'%s',
							'%s',
						)
					);

					if ( false === $link_result ) {
						throw new \Exception( 'Failed to insert link data.' );
					}

					$link_id = $wpdb->insert_id;
				} else {
					$link_id = $existing_link_id;
				}

				// 3. Create relationship between session and link
				$session_link_result = $wpdb->insert(
					$tables['session_links'],
					array(
						'session_id' => $session_id,
						'link_id'    => $link_id,
						'link_order' => $link_order++,
					),
					array(
						'%d',
						'%d',
						'%d',
					)
				);

				if ( false === $session_link_result ) {
					throw new \Exception( 'Failed to create session-link relationship.' );
				}
			}

			// Commit transaction.
			$wpdb->query( $wpdb->prepare( 'COMMIT' ) );

			// Return success response.
			return rest_ensure_response(
				array(
					'success'     => true,
					'message'     => 'Data saved successfully.',
					'session_id'  => $session_id,
					'links_count' => count( $link_data ),
				)
			);

		} catch ( \Exception $e ) {
			// Rollback transaction on error.
			$wpdb->query( $wpdb->prepare( 'ROLLBACK' ) );

			return new \WP_Error(
				'data_insertion_failed',
				$e->getMessage(),
				array( 'status' => 500 )
			);
		}
	}
}
