<?php
/**
 *  Exception handling
 *
 * @package     Link Analyzer
 * @since       1.0
 * @author      Marcin Wosinek
 * @license     GPL-2.0-or-later
 */

namespace LINK_ANALYZER;

// Automatically added when writing the WHOOPS section below to find the whoops functions.
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

// Register WHOOPS - See WHOOPS's github + KTC tutorial.
add_action('init', __NAMESPACE__ . '\load_whoops');
/**
 * Initialize WHOOPS and registers the handler with a custom editor style
 *
 * @since 1.0.0
 *
 * @return void
 */
function load_whoops()
{
    $whoops     = new Run();
    $error_page = new PrettyPageHandler();
    $error_page->setEditor('sublime'); // Set a specific style to the code display.
    $whoops->pushHandler($error_page);
    $whoops->register();
}
