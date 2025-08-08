# Architecture

A high-level map of how Stitch Wizard turns a **JSON schema → multi-step form**.

---

## Core Components

| Layer | Path | Responsibility |
|-------|------|----------------|
| **Service Provider** | `src/StitchWizardServiceProvider.php` | Registers config, binds contracts, loads routes & views. |
| **Routes** | `routes/wizard.php` | `/wizard/{key}` (GET) & step / finalize POST handlers. |
| **Controller** | `src/Http/Controllers/WizardController.php` | Orchestrates request flow: show / postStep / finalize. |
| **Views** | `resources/views/wizard/*.blade.php` | Tailwind UI for step & success pages. |
| **JSON Schema Validator** | `src/Validation/BasicJsonSchemaValidator.php` | Converts field rules → Laravel validator. |
| **Visibility Engine** | `src/Visibility/SimpleVisibilityEngine.php` | Evaluates `when` clauses (`=`, `!=`, `>`, `<`, `in` …). |
| **State Store** | `src/Stores\Contracts\WizardStateStore.php` | Persists wizard data (see implementations below). |
| **Alpine Module** | `resources/js/alpine/index.js` | Progressive enhancement: AJAX nav + history. |

---

## Contracts / Interfaces

```php
interface WizardStateStore {
    public function get(string $wizardKey): array;
    public function put(string $wizardKey, array $state): void;
    public function clear(string $wizardKey): void;
}

interface VisibilityEngine {
    public function visible(array $condition, array $state): bool;
}

interface JsonSchemaValidator {
    public function validate(array $schemaStep, array $input): array; // returns validated data
}
```

Implementations:

* `SessionWizardStateStore` – default, session-backed.  
* `DatabaseWizardStateStore` – **planned** (drop-in via container binding).

Binding swap (example):

```php
// in a service provider or tests
$this->app->bind(WizardStateStore::class, DatabaseWizardStateStore::class);
```

---

## Request Lifecycle

1. **GET `/wizard/{key}`**  
   ‑ Controller resolves wizard config, state, renders first/target step.

2. **User submits form → POST `/wizard/{key}/step/{slug}`**  
   ‑ Extract fields for that step.  
   ‑ Pass to JsonSchemaValidator → returns `$data`.

3. **Visibility Engine** recalculates which next step is allowed.

4. **State Store** persists merged state (`put`).

5. **Redirect / AJAX 200** to next step URL.

6. **Finalize**  
   ‑ POST/GET `/wizard/{key}/finalize` → retrieves full state, triggers `WizardFinished` event (app-level).  
   ‑ Clears store, shows `success.blade.php`.

---

## Data-Flow Diagram (text)

*Browser* → **Routes** → **WizardController**  
• loads **Wizard Config** (`config/stitch-wizard.php`)  
• fetches **State** via `WizardStateStore`  
• on POST:  
&nbsp;&nbsp;↳ **JsonSchemaValidator** (field rules)  
&nbsp;&nbsp;↳ **VisibilityEngine** (conditions)  
&nbsp;&nbsp;↳ saves to **StateStore**  
• chooses template in **Views**  
→ rendered HTML sent back  
↳ optional **Alpine.js** intercepts links/forms for AJAX

---

## Extensibility Cheatsheet

* **New Field Type** – add Blade partial + frontend input handling; reference via `"type": "rating"` in schema.  
* **Custom Visibility Operator** – extend `SimpleVisibilityEngine` or bind your own engine.  
* **Alternative State Store** – implement `WizardStateStore`, bind in container.  
* **Global UI Theme** – publish views (`php artisan vendor:publish --tag=stitch-wizard-views`) and tweak Tailwind classes.  
* **Events / Hooks** – listen for `WizardFinished` (or add new events in the controller).

---

Keep components small, stateless, and swappable – the JSON schema remains the single source of truth.
