<?php
/**
 * Admin Controller.
 *
 * @package     Link Analyzer
 * @since       1.0
 * @author      Marcin Wosinek
 * @license     GPL-2.0-or-later
 */

namespace LINK_ANALYZER;

use LINK_ANALYZER\DB_Handler;

/**
 * Admin Controller class for handling admin-related functionality.
 */
class Admin_Controller {
	/**
	 * View renderer instance.
	 *
	 * @var View_Renderer
	 */
	private $renderer;

	/**
	 * The page slug for the admin menu.
	 *
	 * @var string
	 */
	const MENU_SLUG = 'link-analyzer-plugin';

	/**
	 * The capability required to access the admin page.
	 *
	 * @var string
	 */
	const REQUIRED_CAPABILITY = 'manage_options';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->renderer = new View_Renderer();
	}

	/**
	 * Initialize the admin functionality.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'rest_api_init', array( $this, 'register_admin_endpoints' ) );
	}

	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		add_menu_page(
			'Link analyzer plugin',
			'Link analyzer',
			self::REQUIRED_CAPABILITY,
			self::MENU_SLUG,
			array( $this, 'render_admin_page' ),
			'dashicons-chart-line',
			30
		);
	}

	/**
	 * Render the admin page.
	 *
	 * Checks user capabilities and renders the admin page view.
	 */
	public function render_admin_page() {
		// Check user capabilities.
		if ( ! current_user_can( self::REQUIRED_CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'link-analyzer' ) );
		}

		// Get data for the view.
		$data = $this->prepare_view_data();

		// Render the view.
		$this->renderer->render( 'admin-page', $data );
	}

	/**
	 * Prepare data for the admin view.
	 *
	 * @return array The data for the view.
	 */
	private function prepare_view_data() {
		// Use the existing DB_Handler class.

		return array(
			'db_version'     => get_option( 'link_analyzer_db_version' ),
			'screen_heights' => DB_Handler::get_screen_height_stats(),
			'links'          => DB_Handler::get_link_data(),
		);
	}

	/**
	 * Register admin-only REST API endpoints.
	 *
	 * @return void
	 */
	public function register_admin_endpoints() {
		register_rest_route(
			'link-analyzer/v1',
			'/admin/remove-old-sessions',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'remove_old_sessions' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
			)
		);

		register_rest_route(
			'link-analyzer/v1',
			'/admin/clear-data',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'clear_analytics_data' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
			)
		);
	}

	/**
	 * Check if the current user has admin permissions.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return bool|WP_Error True if the user has permission, WP_Error otherwise.
	 */
	public function admin_permissions_check( $request ) {
		if ( ! current_user_can( self::REQUIRED_CAPABILITY ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'You do not have permissions to access this endpoint.', 'link-analyzer' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}

	/**
	 * Remove old sessions endpoint callback.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response object.
	 */
	public function remove_old_sessions( $request ) {
		// Add a nonce check for additional security.
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'Invalid nonce.', 'link-analyzer' ),
				array( 'status' => 403 )
			);
		}

		// Remove old sessions using DB_Handler with default value (7 days).
		$deleted = DB_Handler::remove_old_sessions();

		if ( false === $deleted ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'message' => esc_html__( 'Failed to remove old sessions.', 'link-analyzer' ),
				)
			);
		}

		$message = sprintf(
		// translators: %d: Number of deleted sessions.
			esc_html__( 'Successfully removed %d old sessions.', 'link-analyzer' ),
			$deleted
		);

		return rest_ensure_response(
			array(
				'success'       => true,
				'message'       => $message,
				'deleted_count' => $deleted,
			)
		);
	}

	/**
	 * Clear analytics data endpoint callback.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response object.
	 */
	public function clear_analytics_data( $request ) {
		// Add a nonce check for additional security.
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'Invalid nonce.', 'link-analyzer' ),
				array( 'status' => 403 )
			);
		}

		// Clear data using DB_Handler.
		$result = DB_Handler::clear_all_data();

		if ( is_wp_error( $result ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'message' => $result->get_error_message(),
				)
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => esc_html__( 'Analytics data has been cleared successfully.', 'link-analyzer' ),
			)
		);
	}
}
