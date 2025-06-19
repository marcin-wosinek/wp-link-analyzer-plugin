const { test, expect } = require("@playwright/test");
const { activatePlugin } = require("./activate-plugin");

// WordPress admin credentials
const WP_USERNAME = "admin";
const WP_PASSWORD = "password"; // Change this to match your WordPress setup

test.describe("Link Analyzer Admin Page", () => {
	test.beforeAll(async ({ browser }) => {
		await activatePlugin(browser);
	});

	test.beforeEach(async ({ page }) => {
		// Login to WordPress admin
		await page.goto("/wp-login.php");
		await page.fill("#user_login", WP_USERNAME);
		await page.fill("#user_pass", WP_PASSWORD);
		await page.click("#wp-submit");

		// Verify we're logged in
		await expect(page.locator("#wpadminbar")).toBeVisible();
	});

	test("should display Link Analyzer admin page", async ({ page }) => {
		// Navigate to Link Analyzer admin page
		await page.goto("/wp-admin/admin.php?page=link-analyzer-plugin");

		// Verify the page title...
		await expect(page.locator("h1")).toContainText("Link Analyzer");
		// acction butotn ...
		await expect(page.getByText("Remove old sessions")).toBeVisible();
		// screen table ...
		await expect(page.getByText("Screen height statistics")).toBeVisible();
		// chart ...
		await expect(page.getByText("Screen height distribution")).toBeVisible();
		// and link table
		await expect(page.getByText("Links above the fold")).toBeVisible();
	});

	test("should register a session when visiting homepage", async ({ page, context }) => {
		// First, visit the homepage to create a session
		// Create a new page in the same context for the frontend visit
		const frontendPage = await context.newPage();
		
		// Visit the homepage
		await frontendPage.goto("/");
		
		// Wait a moment for the session to be registered
		await frontendPage.waitForTimeout(1000);
		
		// Close the frontend page
		await frontendPage.close();
		
		// Now go to the admin page to check if the session was registered
		await page.goto("/wp-admin/admin.php?page=link-analyzer-plugin");
		
		// Find the section with sessions by its header
		const sessionsHeader = page.getByRole('heading', { name: 'Screen height statistics' });
		await sessionsHeader.waitFor({ state: 'visible' });
		
		// Find the sessions table that follows this header
		const sessionsTable = sessionsHeader.locator('xpath=./following::table').first();
		await sessionsTable.waitFor({ state: 'visible' });
		
		// Check if there is exactly one session registered
		const sessionRows = sessionsTable.locator('tbody tr');
		const count = await sessionRows.count();
		
		// Verify we have exactly one session
		expect(count).toBe(1);
		
		// Additional verification that the session data is present
		// Verify the table headers are as expected
		const tableHeaders = sessionsTable.locator('thead th');
		await expect(tableHeaders.nth(0)).toContainText('Screen height');
		await expect(tableHeaders.nth(1)).toContainText('Number of sessions');
		
		// Verify the session row has data in all cells
		const sessionCells = sessionRows.first().locator('td');
		await expect(sessionCells).toHaveCount(2);
	});
});
