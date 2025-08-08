# Stitch Wizard – Overview

> JSON-driven, **server-rendered** multi-step forms – progressive-enhanced.

Stitch Wizard is a Laravel package that lets you describe a complete wizard flow in a single JSON (or PHP array) schema and instantly obtain:

* Blade pages with modern Tailwind UI
* Robust Laravel validation on each step
* Optional AJAX navigation & history management
* Playwright-ready routes for E2E testing

All without hand-coding controllers, views or JS for every field.



## Why Stitch Wizard?

Building multi-step forms is usually painful:

* ❌  Re-implementing step navigation logic  
* ❌  Copy-pasting validation across pages  
* ❌  Front-end / back-end drift when rules change  
* ❌  Poor accessibility & progressive enhancement  

Stitch Wizard solves these by **driving the entire flow from a single schema** that both the server and front-end understand.



## Key Features

| Capability | Details |
|------------|---------|
| **Steps & Sub-steps** | Unlimited depth defined in JSON with labels, icons, grouping. |
| **Per-field Validation** | Leverages native Laravel validation rules parsed from the schema. |
| **Conditional Visibility Engine** | Simple operators (`=`, `!=`, `>`, `<`, `in`, `exists`, `truthy`, `falsy`) allow dynamic fields & steps. |
| **Session State Store** | Progress persists across refreshes; swappable contract paves way for **database store** in Sprint 1. |
| **File Uploads** | Any file field is stored via Laravel’s filesystem, reference saved in wizard state. |
| **Deep-linking** | `GET /wizard/{key}/step/{slug}` lets QA jump directly to a step. |
| **AJAX-enhanced Navigation** | Alpine.js swaps DOM & manages history; falls back to full-page POST on `<noscript>`. |
| **Blade UI** | Tailwind-styled templates with accessible markup and a responsive progress bar. |
| **Testing Hooks** | Deterministic route keys & data-attributes make Playwright tests trivial. |



## Canonical Example – Real Estate Listing (Thailand)

The **Real Estate wizard** is the reference implementation used throughout this documentation.

Highlights:

1. **6 parent steps** (`Basics`, `Location`, `Details`, `Features`, `Media`, `Contact`).
2. Thailand-specific fields: *Chanote* land title, *Rai-Ngan-Wah* area units, BTS/MRT proximity.
3. Conditional pricing logic:  
   *If `listing_type = rent` → show `rent_price` else `sale_price`.*
4. Upload fields for photos & floor plans.
5. Optional final `Contact` step skipped when user selects “Broker listing”.

The entire JSON is reproduced in [`docs/examples/real-estate.md`](examples/real-estate.md) with annotated screenshots.

> The legacy **demo wizard** remains only for baseline regression tests and tutorial snippets.



## High-Level Architecture

* **Service Provider** – Registers config, views, routes, state-store binding.  
* **Wizard Controller** – Central entry for `show`, `postStep`, `finalize`.  
* **JSON Schema Validator** – Translates field rules → Laravel validator.  
* **Visibility Engine** – Evaluates display conditions on each render.  
* **State Stores** –  
  * `SessionWizardStateStore` (default)  
  * `DatabaseWizardStateStore` (🎯 planned in Sprint 1)  
* **Alpine.js Module** – Optional progressive enhancement layer.  

For deeper diagrams and contract interfaces see [`docs/architecture.md`](architecture.md).  
The exact JSON schema format is documented in [`docs/schema.md`](schema.md).



## Next Milestones

| Sprint | Goal | Outcome |
|--------|------|---------|
| **0** (now) | Documentation & project positioning | PR #1 – this doc set |
| **1** | Database-backed state store | Resumable wizards across devices |
| **2** | Reusable UI kit & theme hooks | Design-system friendly components |
| **3** | Drag-and-drop form builder (optional) | Non-devs author wizards |

---

Happy stitching!  
Questions or ideas? Open a discussion or create a feature branch following the [contributing guide](contributing.md).
