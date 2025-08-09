# Stitch Wizard

Stitch Wizard is a Laravel package for **JSON-driven, multi-step forms** with server-side rendering and progressive enhancement via Alpine.js.  
The **Real Estate wizard** serves as the canonical showcase of the API, UI/UX and E2E flow, while a lightweight **demo wizard** is retained solely for regression tests.

---

## Quick Start (local development)

1. Require the package from your Laravel app using a local path repository:

   ```bash
   composer config repositories.stitch path ../stitch-wizard
   composer require stitch-wizard/stitch-wizard:*
   ```

2. Publish the configuration (optional – defaults work out of the box):

   ```bash
   php artisan vendor:publish --tag=stitch-wizard-config
   ```

3. Routes are auto-loaded by the service provider.  
   Simply boot the app and navigate to:

   ```
   http://localhost:8000/wizard/real-estate
   ```

   You should see the first step of the Real Estate listing form.

---

## Documentation

• Overview – feature list & architecture: [`docs/overview.md`](docs/overview.md)  
• Schema reference – steps, fields, validation: [`docs/schema.md`](docs/schema.md)  
• Real Estate example – full JSON & screenshots: [`docs/examples/real-estate.md`](docs/examples/real-estate.md)  
• Testing – Pest & Playwright guides: [`docs/testing.md`](docs/testing.md)  
• Contributing – branch & PR workflow: [`docs/contributing.md`](docs/contributing.md)

Enjoy building elegant multi-step experiences with Stitch Wizard!
