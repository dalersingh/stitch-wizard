import { defineConfig } from '@playwright/test';
import path from 'path';

// cross-platform repository root path for Docker volume mounting
const repoPath = process.cwd().replace(/\\/g, '/');
const defaultCmd = `docker run --rm -p 8000:8000 -v ${repoPath}:/app -w /app php:8.3-cli php -S 0.0.0.0:8000 -t /app/demos/demo-app/public`;
const serverCommand = process.env.PHP_SERVER_CMD || defaultCmd;

const baseConfig: any = {
  // test files live next to this config file
  testDir: 'tests',
  use: { baseURL: process.env.BASE_URL || 'http://localhost:8000' },
  reporter: [['list'], ['html', { outputFolder: 'playwright-report' }]],
  timeout: 30000,
  expect: { timeout: 5000 },
  outputDir: 'test-results'
};

// Only let Playwright manage a web server when not explicitly skipped
if (serverCommand !== 'skip') {
  baseConfig.webServer = {
    command: serverCommand,
    url: process.env.BASE_URL || 'http://localhost:8000',
    reuseExistingServer: true,
    timeout: 120000
  };
}

export default defineConfig(baseConfig);
