// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * Playwright configuration for Stitch Wizard E2E tests
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
  // Directory where tests are located
  testDir: './e2e/tests',
  
  // Pattern for test files
  testMatch: '**/*.spec.ts',
  
  // Maximum time one test can run (in milliseconds)
  timeout: 60000,
  
  // Fail the build on CI if you accidentally left test.only in the source code
  forbidOnly: !!process.env.CI,
  
  // Retry failed tests on CI
  retries: process.env.CI ? 2 : 0,
  
  // Opt out of parallel tests for more predictable screenshots
  workers: process.env.CI ? 1 : 1,
  
  // Reporter to use
  reporter: [
    ['html', { open: 'never', outputFolder: 'test-results/html-report' }],
    ['list'],
    ['json', { outputFile: 'test-results/test-results.json' }],
  ],
  
  use: {
    // Base URL to use in navigation
    // In Docker the nginx service is reachable via container hostname `nginx`
    // but we also allow overriding from the environment.
    baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://nginx',
    
    // Capture screenshot after each test
    screenshot: 'on',
    
    // Record trace for failed tests
    trace: 'retain-on-failure',
    
    // Record video for failed tests
    video: 'on-first-retry',
    
    // Viewport size
    viewport: { width: 1280, height: 720 },
    
    // Action timeout (in milliseconds)
    actionTimeout: 15000,
    
    // Navigation timeout (in milliseconds)
    navigationTimeout: 30000,
  },
  
  // Configure projects for different browsers
  projects: [
    {
      name: 'chromium',
      use: { 
        ...devices['Desktop Chrome'],
        // Slow down actions for more reliable tests and better screenshots
        launchOptions: {
          slowMo: 100,
        },
      },
    },
    
    // Project specifically for screenshots
    {
      name: 'screenshots',
      use: { 
        ...devices['Desktop Chrome'],
        launchOptions: {
          slowMo: 200,
        },
        // Save screenshots in a dedicated directory
        screenshot: { 
          mode: 'on', 
          fullPage: true,
          path: 'test-results/screenshots' 
        },
      },
      testMatch: '**/*.screenshots.ts',
    },
    
    // Uncomment these if you need additional browsers
    // {
    //   name: 'firefox',
    //   use: { ...devices['Desktop Firefox'] },
    // },
    // {
    //   name: 'webkit',
    //   use: { ...devices['Desktop Safari'] },
    // },
  ],
  
  // In Docker we rely on the nginx container so we don't start a dev server here.

  // Output directory for test artifacts – mapped as a volume in docker-compose
  outputDir: 'test-results/artifacts',
});
