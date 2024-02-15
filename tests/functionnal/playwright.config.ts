import { defineConfig, devices } from '@playwright/test';

/**
 * Read environment variables from file.
 * https://github.com/motdotla/dotenv
 */
require('dotenv').config();

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
  testDir: './e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: 'html',
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL:  process.env.CI_ENVIRONMENT_URL, // Will need to be replaced by a CI or a local url to test
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
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
