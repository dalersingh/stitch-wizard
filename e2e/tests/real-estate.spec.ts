import { test, expect } from '@playwright/test';

test('real estate wizard completes successfully', async ({ page }, testInfo) => {
  await page.goto('/wizard/real-estate');
  await expect(page.getByRole('heading', { level: 1 })).toHaveText(/Basic Information/i);
  
  // Step 1: Basics
  await page.locator('input[name="listing_type"][value="sell"]').click();
  // Submit to trigger visibility logic for price field
  await page.getByRole('button', { name: 'Continue' }).click();
  
  // Now fill all required fields
  await page.selectOption('select[name="property_type"]', 'condo');
  await page.getByLabel('Title').fill('Luxury Condo in Bangkok');
  await page.getByLabel('Description').fill('A beautiful luxury condominium in the heart of Bangkok with amazing city views. Perfect for professionals.');
  await page.locator('input[name="price"]').fill('5000000');
  await page.selectOption('select[name="currency"]', 'THB');
  
  await page.waitForTimeout(200);
  await page.screenshot({ path: testInfo.outputPath('step1.png') });
  await page.getByRole('button', { name: 'Continue' }).click();
  
  // Step 2: Location
  await expect(page.getByRole('heading', { level: 1 })).toHaveText(/Location/i);
  await page.getByLabel('Province').fill('Bangkok');
  await page.locator('input[name="district"]').fill('Sukhumvit');
  await page.selectOption('select[name="transit_line"]', 'None');
  
  await page.waitForTimeout(200);
  await page.screenshot({ path: testInfo.outputPath('step2.png') });
  await page.getByRole('button', { name: 'Continue' }).click();
  
  // Step 3: Property Details
  await expect(page.getByRole('heading', { level: 1 })).toHaveText(/Property Details/i);
  await page.getByLabel('Usable Area (sqm)').fill('85');
  await page.getByLabel('Bedrooms').fill('2');
  await page.getByLabel('Bathrooms').fill('1');
  await page.locator('input[name="floor"]').fill('10');
  await page.locator('input[name="total_floors"]').fill('30');
  await page.getByLabel('Land Size (Rai)').fill('0');
  await page.getByLabel('Land Size (Ngan)').fill('0');
  await page.getByLabel('Land Size (Wah)').fill('0');
  await page.selectOption('select[name="furnishing"]', 'none');
  await page.selectOption('select[name="ownership"]', 'freehold');
  await page.selectOption('select[name="title_deed"]', 'chanote');
  await page.getByLabel('Year Built').fill('2010');
  await page.selectOption('select[name="facing"]', 'north');
  
  await page.waitForTimeout(200);
  await page.screenshot({ path: testInfo.outputPath('step3.png') });
  await page.getByRole('button', { name: 'Continue' }).click();
  
  // Step 4: Features
  await expect(page.getByRole('heading', { level: 1 })).toHaveText(/Features/i);
  
  // Select a couple of facilities (multiselect)
  await page.selectOption('select[name="facilities[]"]', ['pool', 'gym']);
  
  // Toggle pet friendly
  await page.locator('label.inline-flex[for="pet_friendly"]').click();
  
  await page.waitForTimeout(200);
  await page.screenshot({ path: testInfo.outputPath('step4.png') });
  await page.getByRole('button', { name: 'Continue' }).click();
  
  // Step 5: Media
  await expect(page.getByRole('heading', { level: 1 })).toHaveText(/Media/i);
  
  // Create a small file buffer for upload
  const imageBuffer = Buffer.from('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7', 'base64');
  await page.setInputFiles('input[name="cover_photo"]', {
    name: 'test-image.gif',
    mimeType: 'image/gif',
    buffer: imageBuffer
  });
  
  await page.waitForTimeout(200);
  await page.screenshot({ path: testInfo.outputPath('step5.png') });
  await page.getByRole('button', { name: 'Continue' }).click();
  
  // Step 6: Contact Information
  await expect(page.getByRole('heading', { level: 1 })).toHaveText(/Contact Information/i);
  await page.getByLabel('Contact Name').fill('John Smith');
  await page.getByLabel('Phone Number').fill('0812345678');
  await page.getByLabel('Email').fill('john.smith@example.com');
  await page.getByLabel(/agree to the terms/).check();
  
  await page.waitForTimeout(200);
  await page.screenshot({ path: testInfo.outputPath('step6.png') });
  await page.getByRole('button', { name: 'Continue' }).click();
  
  // Success page
  await expect(page.getByRole('heading', { level: 1 })).toHaveText(/Wizard Completed/i);
  await page.waitForTimeout(200);
  await page.screenshot({ path: testInfo.outputPath('success.png') });
});
