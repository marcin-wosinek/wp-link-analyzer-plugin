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
	 * Saves the provided by the call.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function add_data( $request ) {
		$json_params = $request->get_json_params();

		return rest_ensure_response( $json_params );
	}
}
