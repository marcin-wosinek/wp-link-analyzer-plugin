/**
 * E2E test helpers for WordPress
 *
 * This file contains helper functions for common WordPress testing tasks
 */

// WordPress admin credentials
const WP_USERNAME = "admin";
const WP_PASSWORD = "password"; // Change this to match your WordPress setup

/**
 * Login to WordPress admin
 *
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @returns {Promise<void>}
 */
async function loginAsAdmin(page) {
	// Navigate to login page
	await page.goto("/wp-login.php");

	// Fill in credentials
	await page.fill("#user_login", WP_USERNAME);
	await page.fill("#user_pass", WP_PASSWORD);

	// Submit the form
	await page.click("#wp-submit");

	// Wait for successful login - admin bar should be visible
	await page.locator("#wpadminbar").waitFor({ state: "visible", timeout: 5000 });

	// Additional check - verify we're on the dashboard or admin page
	const currentUrl = page.url();
	if (!currentUrl.includes("/wp-admin")) {
		throw new Error(`Login failed. Current URL: ${currentUrl}`);
	}

	console.log("Successfully logged in as admin");
}

/**
 * Activate the Link Analyzer plugin through the WordPress web interface
 *
 * @param {import('@playwright/test').Browser} browser - Playwright browser object
 * @returns {Promise<void>}
 */
async function activatePlugin(browser) {
	console.log("Ensuring Link Analyzer plugin is activated via web interface...");
	try {
		// Create a new browser context
		const context = await browser.newContext();
		const page = await context.newPage();

		// Login to WordPress admin
		await loginAsAdmin(page);

		// Navigate to plugins page
		await page.goto("/wp-admin/plugins.php");

		// Check if the plugin is already active
		const pluginRow = page.locator('tr[data-slug="link-analyzer"]');
		await pluginRow.waitFor({ state: "visible" });

		// Check if the plugin is not active and activate it if needed
		const isActive = await pluginRow.locator(".active").isVisible();
		if (!isActive) {
			// Click the activate link
			await pluginRow.locator('a:text-is("Activate")').click();
			// Wait for activation to complete
			await page.waitForURL("**/plugins.php**");
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

module.exports = {
	loginAsAdmin,
	activatePlugin,
	WP_USERNAME,
	WP_PASSWORD,
};
