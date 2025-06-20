/**
 * Global setup for Playwright tests
 *
 * This file is referenced in playwright.config.js and runs once before all tests
 */
const { setupWordPress } = require("./setup-wordpress");

async function globalSetup() {
	await setupWordPress();
}

module.exports = globalSetup;
