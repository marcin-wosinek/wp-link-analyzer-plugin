<?php
/**
 * Implements the Unit test set for the DB_Handler class.
 *
 * @package     Link Analyzer
 * @since       1.0
 * @author      Marcin Wosinek
 * @license     GPL-2.0-or-later
 */

namespace LINK_ANALYZER;

require_once dirname(dirname(__DIR__)) . '/src/db.php';

use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Unit test set for the DB_Handler class.
 */
class DB_Handler_Test extends TestCase {
    /**
     * Set up test environment.
     */
    protected function setUp(): void {
        parent::setUp();
        
        // Mock global $wpdb
        global $wpdb;
        $wpdb = new \stdClass();
        $wpdb->prefix = 'wp_';
    }

    /**
     * Test get_table_names method.
     */
    public function testGetTableNames() {
        global $wpdb;
        
        $tables = DB_Handler::get_table_names();
        
        $this->assertIsArray($tables);
        $this->assertArrayHasKey('sessions', $tables);
        $this->assertArrayHasKey('links', $tables);
        $this->assertArrayHasKey('session_links', $tables);
        $this->assertEquals('wp_linkanalyzer_sessions', $tables['sessions']);
        $this->assertEquals('wp_linkanalyzer_links', $tables['links']);
        $this->assertEquals('wp_linkanalyzer_session_links', $tables['session_links']);
    }
}
