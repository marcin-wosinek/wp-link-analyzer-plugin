<?php
/**
 *  Implements the Integration test set for the plugin management.
 *
 * @package     Link Analyzer
 * @since       1.0
 * @author      Marcin Wosinek
 * @license     GPL-2.0-or-later
 */

namespace LINK_ANALYZER;

require_once dirname(dirname(__DIR__)) . "/plugin.php";

use WPMedia\PHPUnit\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * Integration test set for the Webplan Updater Cron Class.
 */
class Rocket_Wpc_Plugin_Integration_Test extends TestCase
{
    /**
     * Checks the call to plugin init function on plugin_loaded.
     */
    public function testShouldLoadPlugin()
    {
        Functions\expect(__NAMESPACE__ . '\wpc_crawler_plugin_init')->once();
        do_action('plugins_loaded');
    }
}
