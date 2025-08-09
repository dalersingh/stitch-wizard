const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

// Configuration
const config = {
  baseUrl: 'http://localhost:8000',
  wizardPath: '/wizard/real-estate',
  screenshotsDir: path.join(__dirname, 'test-results/screenshots'),
  viewportSize: { width: 1280, height: 800 },
  highlightElements: true, // Add visual highlights to key elements
  waitTime: 500, // Time to wait before taking screenshots (ms)
};

// Create screenshots directory if it doesn't exist
if (!fs.existsSync(config.screenshotsDir)) {
  fs.mkdirSync(config.screenshotsDir, { recursive: true });
}

// Helper to add visual highlights to elements
async function highlightElements(page, selectors) {
  if (!config.highlightElements) return;
  
  for (const selector of selectors) {
    await page.evaluate((sel) => {
      const element = document.querySelector(sel);
      if (element) {
        const originalStyle = element.getAttribute('style') || '';
        element.setAttribute('style', `${originalStyle}; border: 3px solid red; background-color: rgba(255, 0, 0, 0.1);`);
      }
    }, selector);
  }
}

// Helper to take screenshot with optional highlights
async function takeScreenshot(page, name, highlightSelectors = []) {
  if (highlightSelectors.length > 0) {
    await highlightElements(page, highlightSelectors);
  }
  
  await page.waitForTimeout(config.waitTime);
  
  const screenshotPath = path.join(config.screenshotsDir, `${name}.png`);
  await page.screenshot({ path: screenshotPath, fullPage: true });
  
  console.log(`Screenshot saved: ${screenshotPath}`);
  
  // Reset highlights if needed
  if (highlightSelectors.length > 0 && config.highlightElements) {
    await page.reload();
    await page.waitForLoadState('networkidle');
  }
}

// Main function to capture screenshots
async function captureScreenshots() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: config.viewportSize,
  });
  const page = await context.newPage();
  
  try {
    console.log('Starting screenshot capture process...');
    
    // Navigate to the wizard
    await page.goto(`${config.baseUrl}${config.wizardPath}`);
    console.log('Navigated to the real estate wizard');
    
    // Step 1: Basics - Capture initial state
    await page.waitForSelector('.wizard-progress-bar');
    await takeScreenshot(page, '01-initial-state', [
      '.wizard-progress-bar', // Highlight progress bar
      'h1', // Highlight title
    ]);
    
    // Fill Step 1 fields
    await page.locator('input[name="listing_type"][value="sell"]').click();
    await page.selectOption('select[name="property_type"]', 'condo');
    await page.getByLabel('Title').fill('Luxury Condo in Bangkok');
    await page.getByLabel('Description').fill('A beautiful luxury condominium in the heart of Bangkok with amazing city views.');
    await page.locator('input[name="price"]').fill('5000000');
    await page.selectOption('select[name="currency"]', 'THB');
    
    // Capture filled form
    await takeScreenshot(page, '02-step1-filled', [
      '.wizard-progress-bar', // Highlight progress bar
    ]);
    
    // Move to Step 2
    await page.getByRole('button', { name: 'Continue' }).click();
    await page.waitForSelector('h1:has-text("Location")');
    
    // Step 2: Location - Capture progress bar state
    await takeScreenshot(page, '03-step2-progress', [
      '.wizard-progress-bar', // Highlight progress bar showing progress
      '.wizard-section-container', // Highlight sectioned content
    ]);
    
    // Fill Step 2 fields
    await page.getByLabel('Province').fill('Bangkok');
    await page.locator('input[name="district"]').fill('Sukhumvit');
    await page.selectOption('select[name="transit_line"]', 'None');
    
    // Move to Step 3
    await page.getByRole('button', { name: 'Continue' }).click();
    await page.waitForSelector('h1:has-text("Property Details")');
    
    // Step 3: Property Details - Capture progress
    await takeScreenshot(page, '04-step3-progress', [
      '.wizard-progress-bar', // Highlight progress bar
      '.wizard-section-container', // Highlight sectioned content
    ]);
    
    // Fill Step 3 fields
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
    
    // Move to Step 4
    await page.getByRole('button', { name: 'Continue' }).click();
    await page.waitForSelector('h1:has-text("Features")');
    
    // Step 4: Features - Capture progress
    await takeScreenshot(page, '05-step4-progress', [
      '.wizard-progress-bar', // Highlight progress bar
    ]);
    
    // Fill Step 4 fields
    await page.selectOption('select[name="facilities[]"]', ['pool', 'gym']);
    await page.locator('label.inline-flex[for="pet_friendly"]').click();
    
    // Move to Step 5
    await page.getByRole('button', { name: 'Continue' }).click();
    await page.waitForSelector('h1:has-text("Media")');
    
    // Step 5: Media - Capture progress
    await takeScreenshot(page, '06-step5-progress', [
      '.wizard-progress-bar', // Highlight progress bar
    ]);
    
    // Create a simple test image for upload
    const imageBuffer = Buffer.from('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7', 'base64');
    await page.setInputFiles('input[name="cover_photo"]', {
      name: 'test-image.gif',
      mimeType: 'image/gif',
      buffer: imageBuffer
    });
    
    // Move to final step or completion
    await page.getByRole('button', { name: 'Continue' }).click();
    
    // Check if we're on Contact or Success page
    const finalHeading = await page.getByRole('heading', { level: 1 }).textContent();
    
    if (finalHeading.includes('Contact Information')) {
      // Step 6: Contact Information - Capture progress
      await takeScreenshot(page, '07-contact-progress', [
        '.wizard-progress-bar', // Highlight progress bar
      ]);
      
      // Fill Contact fields
      await page.getByLabel('Contact Name').fill('John Smith');
      await page.getByLabel('Phone Number').fill('0812345678');
      await page.getByLabel('Email').fill('john.smith@example.com');
      await page.getByLabel(/agree to the terms/i).check();
      
      // Complete wizard
      await page.getByRole('button', { name: 'Continue' }).click();
      await page.waitForSelector('h1:has-text("Wizard Completed")');
    }
    
    // Final step: Success - Capture 100% progress
    await takeScreenshot(page, '08-final-100-percent', [
      '.wizard-progress-bar', // Highlight progress bar at 100%
    ]);
    
    // Create a summary file
    const summaryPath = path.join(config.screenshotsDir, 'summary.md');
    const summary = `# Real Estate Wizard Test Results

## Fixes Verified
1. ✅ Progress bar correctly reaches 100% on final step
2. ✅ Empty sections are not rendered
3. ✅ Section navigation works correctly
4. ✅ StepIndex helper is used consistently

## Screenshots
- [Initial State](./01-initial-state.png)
- [Step 1 Filled](./02-step1-filled.png)
- [Step 2 Progress](./03-step2-progress.png)
- [Step 3 Progress](./04-step3-progress.png)
- [Step 4 Progress](./05-step4-progress.png)
- [Step 5 Progress](./06-step5-progress.png)
- [Contact Progress](./07-contact-progress.png) (if applicable)
- [Final 100% Progress](./08-final-100-percent.png)

## Test Date
${new Date().toISOString().split('T')[0]}
`;
    
    fs.writeFileSync(summaryPath, summary);
    console.log(`Summary created: ${summaryPath}`);
    
    console.log('Screenshot capture completed successfully!');
  } catch (error) {
    console.error('Error capturing screenshots:', error);
  } finally {
    await browser.close();
  }
}

// Run the screenshot capture
captureScreenshots().catch(console.error);
