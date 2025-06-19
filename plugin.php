<?php
/**
 * Plugin Template
 *
 * @package     TO FILL
 * @author      Mathieu Lamiot
 * @copyright   TO FILL
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Link Analyzer
 * Version:           1.0
 * Description:       Analyze the links at the homepage.
 * Author:            Marcin Wosinek
 * Plugin URI:        https://github.com/marcin-wosinek/wp-link-analyzer-plugin
 * Requires at least: 6.8
 * Requires PHP:      8.1
 * Author URI:        https://how-to.dev/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       link-analyzer
 */

namespace LINK_ANALYZER;

define( 'LINK_ANALYZER_PLUGIN', __FILE__ ); // Filename of the plugin, including the file.

if ( ! defined( 'ABSPATH' ) ) { // If WordPress is not loaded.
	exit( 'WordPress not loaded. Can not load the plugin' );
}

// Load the dependencies installed through composer.
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/plugin.php';
require_once __DIR__ . '/src/support/exceptions.php';

// Plugin initialization.
/**
 * Creates the plugin object on plugins_loaded hook
 *
 * @return void
 */
function wpc_crawler_plugin_init() {
	$wpc_crawler_plugin = new Link_Analyzer_Plugin_Class();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\wpc_crawler_plugin_init' );

register_activation_hook( __FILE__, __NAMESPACE__ . '\Link_Analyzer_Plugin_Class::wpc_activate' );
register_uninstall_hook( __FILE__, __NAMESPACE__ . '\Link_Analyzer_Plugin_Class::wpc_uninstall' );

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\Link_Analyzer_Plugin_Class::wpc_script_enqueue' );

// Register both plugin and admin REST API endpoints.
add_action(
	'rest_api_init',
	function () {
		// Register main plugin endpoints.
		Link_Analyzer_Plugin_Class::wpc_rest_api_init();

		// Register admin endpoints.
		$admin_controller = new Admin_Controller();
		$admin_controller->register_admin_endpoints();
	}
);

// Initialize Admin Controller if in admin area.
if ( is_admin() ) {
	add_action(
		'init',
		function () {
			$admin_controller = new Admin_Controller();
			$admin_controller->init();
		}
	);
}
add_action( 'link_analyzer_cron_hook', __NAMESPACE__ . '\Link_Analyzer_Plugin_Class::wpc_cron_cleanup' );
