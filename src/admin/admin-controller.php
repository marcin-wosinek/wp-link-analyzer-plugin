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
}
