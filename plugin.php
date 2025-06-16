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

namespace ROCKET_WP_CRAWLER;

define( 'ROCKET_CRWL_PLUGIN_FILENAME', __FILE__ ); // Filename of the plugin, including the file.

if ( ! defined( 'ABSPATH' ) ) { // If WordPress is not loaded.
	exit( 'WordPress not loaded. Can not load the plugin' );
}

// Load the dependencies installed through composer.
require_once __DIR__ . '/src/plugin.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/support/exceptions.php';

// Plugin initialization.
/**
 * Creates the plugin object on plugins_loaded hook
 *
 * @return void
 */
function wpc_crawler_plugin_init() {
	$wpc_crawler_plugin = new Rocket_Wpc_Plugin_Class();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\wpc_crawler_plugin_init' );

register_activation_hook( __FILE__, __NAMESPACE__ . '\Rocket_Wpc_Plugin_Class::wpc_activate' );
register_uninstall_hook( __FILE__, __NAMESPACE__ . '\Rocket_Wpc_Plugin_Class::wpc_uninstall' );

// TODO find more fitting place for the hook
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\script_enqueue' );

function script_enqueue() {
	wp_enqueue_script(
		'ajax-script',
		plugins_url( '/src/script.js', __FILE__ ),
		array( 'jquery' ),
		'1.0.0',
		array(
		   'in_footer' => true,
		)
	);
}
