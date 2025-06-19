<?php
/**
 * Implements the Unit test set for the Admin_Controller class.
 *
 * @package     Link Analyzer
 * @since       1.0
 * @author      Marcin Wosinek
 * @license     GPL-2.0-or-later
 */

namespace LINK_ANALYZER;

// Mock the View_Renderer class before requiring the admin-controller.php file
class View_Renderer {
    public function render($template, $data = array()) {
        // Mock implementation
        return 'Mocked rendered content';
    }
}

require_once dirname(dirname(__DIR__)) . '/src/admin/admin-controller.php';

use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Actions;

/**
 * Unit test set for the Admin_Controller class.
 */
class Admin_Controller_Test extends TestCase {

    /**
     * Set up test environment.
     */
    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * Test init method adds the correct WordPress hooks.
     */
    public function testInitAddsCorrectHooks() {
        // Create an instance of the Admin_Controller class
        $admin_controller = new Admin_Controller();
        
        // Set up expectations for add_action calls
        Actions\expectAdded('admin_menu')
            ->once()
            ->with([$admin_controller, 'register_admin_menu']);
            
        Actions\expectAdded('admin_enqueue_scripts')
            ->once()
            ->with([$admin_controller, 'enqueue_admin_scripts']);
        
        // Call the init method
        $admin_controller->init();
        
        // If we get here without exceptions, the test passes
        $this->assertTrue(true);
    }
}
