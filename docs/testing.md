# Testing Guide

This project ships with **two complementary test layers**:

| Layer | Technology | Purpose |
|-------|------------|---------|
| **Feature tests** | [Pest](https://pestphp.com) + Laravel test case | Fast PHP–level checks (HTTP 200s, validation, redirects). |
| **End-to-End tests** | [Playwright](https://playwright.dev) | Full-stack browser walks through the wizard, captures screenshots. |

The stack works out-of-the-box on any dev machine; Docker wrappers are optional and described last.

---

## 1. Feature Tests (Pest)

Location: `tests/Feature/*`

Current coverage:

* `WizardRoutesTest` – asserts every public route returns **HTTP 200**.
* Minimal happy-path submission for the *demo wizard* (kept as a lightweight sanity check).

Create a new test:

```php
// tests/Feature/RealEstateWizardTest.php
it('loads first step', function () {
    $response = $this->get('/wizard/real-estate');
    $response->assertOk()->assertSee('Basics');
});
```

Run the PHP test suite:

```bash
# root of repo
vendor/bin/pest
```

---

## 2. End-to-End Tests (Playwright)

Location: `e2e/tests/*`  
Config file: `e2e/playwright.config.ts`

### 2.1 Scenarios

* **real-estate.spec.ts** – canonical flow (6 steps + success).  
  Generates screenshots in `e2e/test-results/real-estate/`.
* **wizard.spec.ts** – legacy *demo wizard* baseline (2 steps).

Real Estate is the main target; update / extend this spec before every feature PR.  
The demo spec should remain untouched unless the core engine breaks.

### 2.2 Installing Playwright

```bash
cd e2e
npm install          # installs playwright/test + browsers
npx playwright install
```

(You may use `yarn` or `pnpm` interchangeably.)

### 2.3 Running Tests

```bash
# quick headless run
npx playwright test

# watch mode with UI
npx playwright test --ui
```

On success you will see output similar to:

```
✓  real-estate › step flow (6s)
  ├─ screenshots/
  │  ├─ step1.png
  │  └─ success.png
```

Screenshots live in `e2e/test-results/<wizard-key>/`.  
They are committed **only** when explicitly required for docs or reviews – treat them as artifacts, not source.

---

## 3. Dockerised Execution (optional)

If your environment exports the two variables below the Playwright config will spin up containers automatically:

```
export PHP_SERVER_CMD="docker compose up php"
export PLAYWRIGHT_DOCKER=1
```

* `PHP_SERVER_CMD` – shell command that starts the Laravel demo app (port `8000`).  
* `PLAYWRIGHT_DOCKER` – tells Playwright to run inside the official `mcr.microsoft.com/playwright` image.

When unset, Playwright simply assumes you are running `php artisan serve` locally.

---

## 4. CI Recommendation

1. Run **Pest** first – fastest feedback.  
2. Boot the demo app (`php artisan serve &`) and execute **Playwright** specs.  
3. Archive `e2e/test-results/**` as workflow artifacts for visual inspection.

---

That’s it! Keep tests green, keep screenshots meaningful, and remember:

> *Demo wizard* = baseline regression  
> **Real Estate wizard** = authoritative test bed
