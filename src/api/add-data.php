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
				),
				// Register our schema callback.
				// 'schema' => array( $this, 'add_data_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->resource_name,
			array(
				array(
					'methods'  => 'GET',
					'callback' => array( $this, 'dummy_get' ),
				),
			)
		);
	}

	/**
	 * Schema for adding link data.
	 *
	 * @return array schema desription
	 */
	public function add_data_schema() {
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

	/**
	 * Dummy response for testing purpose
	 */
	public function dummy_get() {
		return rest_ensure_response( 'all works' );
	}
}
