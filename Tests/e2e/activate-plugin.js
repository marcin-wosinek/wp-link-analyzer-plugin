/**
 * Plugin activation script for Playwright tests
 * 
 * This script activates the Link Analyzer plugin through the WordPress web interface
 */
const { chromium } = require('@playwright/test');

// WordPress admin credentials
const WP_USERNAME = "admin";
const WP_PASSWORD = "password"; // Change this to match your WordPress setup

/**
 * Activates the Link Analyzer plugin through the WordPress web interface
 */
async function activatePlugin(browser) {
  console.log("Ensuring Link Analyzer plugin is activated via web interface...");
  try {
    // Create a new browser context
    const context = await browser.newContext();
    const page = await context.newPage();
    
    // Login to WordPress admin
    await page.goto("/wp-login.php");
    await page.fill("#user_login", WP_USERNAME);
    await page.fill("#user_pass", WP_PASSWORD);
    await page.click("#wp-submit");
    
    // Navigate to plugins page
    await page.goto("/wp-admin/plugins.php");
    
    // Check if the plugin is already active
    const pluginRow = page.locator('tr[data-slug="link-analyzer"]');
    await pluginRow.waitFor({ state: 'visible' });
    
    // Check if the plugin is not active and activate it if needed
    const isActive = await pluginRow.locator('.active').isVisible();
    if (!isActive) {
      // Click the activate link
      await pluginRow.locator('a:text-is("Activate")').click();
      // Wait for activation to complete
      await page.waitForURL('**/plugins.php**');
      console.log("Link Analyzer plugin activated successfully via web interface");
    } else {
      console.log("Link Analyzer plugin is already active");
    }
    
    // Close the context
    await context.close();
  } catch (error) {
    console.error("Failed to activate Link Analyzer plugin via web interface:", error);
  }
}

// Run the activation if this script is executed directly
if (require.main === module) {
  (async () => {
    try {
      const browser = await chromium.launch();
      await activatePlugin(browser);
      await browser.close();
      console.log('Plugin activation completed successfully');
    } catch (error) {
      console.error('Plugin activation failed:', error);
      process.exit(1);
    }
  })();
}

module.exports = { activatePlugin };
