import { defineConfig, devices } from '@playwright/test';
import * as dotenv from 'dotenv';
import * as path from 'path';

/**
 * Read environment variables from file.
 * Supports multiple .env files for different PrestaShop versions
 * Priority: .env.local > .env.ps[version] > .env
 */
const envFiles = [
  '.env.local',     // Local overrides (gitignored)
  '.env.ps178',     // PrestaShop 1.7.8
  '.env.ps8',       // PrestaShop 8.x
  '.env.ps9',       // PrestaShop 9.x
  '.env',           // Default
];

// Load the first env file that exists
for (const envFile of envFiles) {
  const envPath = path.resolve(__dirname, envFile);
  try {
    const result = dotenv.config({ path: envPath });
    if (!result.error) {
      console.log(`✓ Loaded environment from: ${envFile}`);
      break;
    }
  } catch (error) {
    // File doesn't exist, try next one
  }
}

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
  testDir: './e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: process.env.CI ? 'github' : 'html',
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: process.env.CI_ENVIRONMENT_URL || 'http://localhost:8080',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: process.env.CI ? 'retain-on-failure' : 'off',
  },
  /* Configure projects for major browsers */
  projects: [
   /* {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },*/
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
  ],
});
