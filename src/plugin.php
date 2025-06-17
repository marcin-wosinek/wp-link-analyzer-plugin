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
class Rocket_Wpc_Plugin_Class {

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
				array(),
				'1.0.0',
				array( 'in_footer' => true )
			);
		}

			wp_localize_script(
				'link-analyzer',
				'link_analyzer_obj',
				array(
					'addDataUrl' => get_rest_url( null, '/link-analyzer/v1/add-data' ),
				)
			);
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
