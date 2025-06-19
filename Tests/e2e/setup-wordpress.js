/**
 * WordPress setup script for Playwright tests
 * 
 * This script helps set up WordPress for testing by:
 * 1. Ensuring WordPress is installed
 * 2. Creating a test user if needed
 * 3. Activating the Link Analyzer plugin
 */
const { chromium } = require('@playwright/test');
const { execSync } = require('child_process');

// Configuration
const config = {
  baseUrl: process.env.BASE_URL || 'http://localhost:8080',
  adminUser: 'admin',
  adminPassword: 'password',
  adminEmail: 'admin@example.com',
  siteName: 'Link Analyzer Test Site'
};

async function setupWordPress() {
  console.log('Setting up WordPress for testing...');
  
  try {
    // Check if WordPress is running using Docker
    execSync('docker compose ps wordpress | grep Up', { stdio: 'inherit' });
    console.log('WordPress container is running');
  } catch (error) {
    console.log('Starting WordPress container...');
    execSync('docker compose up -d wordpress db', { stdio: 'inherit' });
    
    // Wait for WordPress to be ready
    console.log('Waiting for WordPress to be ready...');
    await new Promise(resolve => setTimeout(resolve, 10000));
  }
  
  // Use WP-CLI to check if WordPress is installed
  try {
    const isInstalled = execSync(
      `docker compose run --rm wpcli wp core is-installed`,
      { stdio: 'pipe' }
    ).toString().trim();
    
    console.log('WordPress installation status:', isInstalled);
  } catch (error) {
    console.log('WordPress not installed. Installing now...');
    
    // Install WordPress
    execSync(
      `docker compose run --rm wpcli wp core install --url=${config.baseUrl} --title="${config.siteName}" --admin_user=${config.adminUser} --admin_password=${config.adminPassword} --admin_email=${config.adminEmail} --skip-email`,
      { stdio: 'inherit' }
    );
    
    console.log('WordPress installed successfully');
  }
  
  // Activate the Link Analyzer plugin
  try {
    execSync(
      `docker compose run --rm wpcli wp plugin activate wp-link-analyzer`,
      { stdio: 'inherit' }
    );
    console.log('Link Analyzer plugin activated');
  } catch (error) {
    console.error('Failed to activate Link Analyzer plugin:', error.message);
  }
}

// Run the setup if this script is executed directly
if (require.main === module) {
  (async () => {
    try {
      await setupWordPress();
      console.log('WordPress setup completed successfully');
    } catch (error) {
      console.error('WordPress setup failed:', error);
      process.exit(1);
    }
  })();
}

module.exports = { setupWordPress };
