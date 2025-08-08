import { test, expect } from '@playwright/test';

test('demo wizard completes successfully', async ({ page }, testInfo) => {
  await page.goto('/wizard/demo');
  await expect(page.getByRole('heading', { level: 1 })).toHaveText(/Demo|Basic|Wizard/i);
  await page.getByLabel('Full name').fill('John Doe');
  await page.getByLabel('Email').fill('john@example.com');
  await page.waitForTimeout(200);
  await page.screenshot({ path: testInfo.outputPath('step1.png') });
  await page.getByRole('button', { name: 'Continue' }).click();
  await expect(page.locator('label', { hasText: 'Employment status' })).toBeVisible();
  await page.selectOption('select[name="status"]', 'employed');
  // submit the status selection to trigger visibility logic
  await page.getByRole('button', { name: 'Continue' }).click();

  // annual income becomes visible after re-render
  await expect(page.getByLabel('Annual income')).toBeVisible();
  await page.getByLabel('Annual income').fill('50000');
  await page.waitForTimeout(200);
  await page.screenshot({ path: testInfo.outputPath('step2.png') });
  await page.getByRole('button', { name: 'Continue' }).click();
  await expect(page.getByRole('heading', { level: 1 })).toHaveText(/Wizard Completed/i);
  await page.waitForTimeout(200);
  await page.screenshot({ path: testInfo.outputPath('success.png') });
});