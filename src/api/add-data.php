<?php
namespace LINK_ANALYZER;

class Add_Data_Controller {
  // Here initialize our namespace and resource name.
	public function __construct() {
    $this->namespace     = '/link-analyzer/v1';
	  $this->resource_name = 'add-data';
  }

	public function register_routes() {
	  register_rest_route( $this->namespace, '/' . $this->resource_name, array(
			// Here we register the readable endpoint for collections.
			array(
				'methods'   => 'POST',
				'callback'  => array( $this, 'add_data' )
			),
			// Register our schema callback.
			'schema' => array( $this, 'add_data_schema' ),
		) );

	  register_rest_route( $this->namespace, '/' . $this->resource_name, array(
			array(
				'methods'   => 'GET',
				'callback'  => array( $this, 'dummy_get' )
			),
		) );
	}

		/**
			* dummy response for testing purpose
			*
			* @param WP_REST_Request $request Current request.
			*/
		public function dummy_get( $request ) {
			return rest_ensure_response( "all works" );
		}

	// Sets up the proper HTTP status code for authorization.
	public function authorization_status_code() {
	  $status = 401;

	  if ( is_user_logged_in() ) {
	    $status = 403;
	  }

    return $status;
	}
}
