# Schema Reference

This document defines the JSON (or PHP array) shape that powers a Stitch Wizard form.

*All keys are **snake_case** by convention but the engine is case-insensitive.*

---

## 1. Top-level Wizard Object

| Key          | Type           | Description |
|--------------|----------------|-------------|
| `key`        | `string`       | Unique identifier used in routes, e.g. `real-estate`. |
| `title`      | `string`       | Human-readable title displayed on the progress bar. |
| `steps`      | `array<Step>`  | Ordered list of step objects. |
| `finalize`   | `array|null`   | (optional) Payload for post-completion handling – email hooks, events etc. |

```jsonc
{
  "key": "real-estate",
  "title": "Create Listing",
  "steps": [ /* see below */ ],
  "finalize": {
    "dispatch_event": "ListingSubmitted"
  }
}
```

---

## 2. Step Object

| Key        | Type                 | Required | Description |
|------------|----------------------|----------|-------------|
| `slug`     | `string`             | ✓        | URL segment for the step (`/step/{slug}`). |
| `title`    | `string`             | ✓        | Visible heading. |
| `fields`   | `array<Field>`       | ✓        | Inputs rendered for this step. |
| `next`     | `string | array`     | –        | Override default “next step” (conditional or direct). |

`next` is rarely needed; the engine automatically moves to the first step whose
visibility evaluates to **true**.

```jsonc
{
  "slug": "pricing",
  "title": "Price / Rent",
  "fields": [ /* … */ ],
  "next": "media"              // jump over Contact when Broker selected
}
```

---

## 3. Field Object

| Key          | Type                         | Required | Description |
|--------------|------------------------------|----------|-------------|
| `key`        | `string`                     | ✓        | State key (also input name). |
| `label`      | `string`                     | ✓        | Field label. |
| `type`       | enum *(see below)*           | ✓        | Input type. |
| `rules`      | `string | array<string>`     | –        | Laravel validation rules. |
| `options`    | `array<value,label>`         | –        | For `select`/`radio`/`multiselect`. |
| `placeholder`| `string`                     | –        | For text-like inputs. |
| `help`       | `string`                     | –        | Helper text under the field. |
| `default`    | `mixed`                      | –        | Default value when no state yet. |
| `multiple`   | `bool`                       | –        | Only for `file` inputs. |
| `accept`     | `string`                     | –        | Mime filter for files (`image/*`). |
| `when`       | `Condition | array<Condition>`| –        | Visibility conditions (see §5). |

Supported `type` values:

```
text | textarea | select | multiselect | radio | checkbox | toggle
date | time | file
```

Example field:

```jsonc
{
  "key": "sale_price",
  "label": "Sale Price (THB)",
  "type": "text",
  "rules": ["required", "numeric", "min:1000"],
  "placeholder": "e.g. 4 500 000",
  "when": { "all": [{ "key": "listing_type", "=", "sale" }] }
}
```

---

## 4. Validation

Stitch Wizard defers to **Laravel’s validator**.  
Provide rules as:

* Pipe string: `"required|numeric|min:1000"`
* Array of rule strings: `["required", "date"]`

Custom rule objects (`Rule::in([...])`) can be added by listening to
`WizardValidating` event and mutating the data before it reaches the validator.

---

## 5. Visibility Conditions (`when`)

A condition is evaluated against the **current wizard state**.

### 5.1 Operators

| Symbol | Meaning                                     |
|--------|---------------------------------------------|
| `=`    | equals                                      |
| `!=`   | not equals                                  |
| `>` `<`| numeric comparison (works on dates, times)  |
| `in`   | value in array                              |
| `exists` | key present in state                      |
| `truthy` / `falsy` | boolean cast check              |

### 5.2 Combinators

* `all` – **AND** (every sub-condition must be true)  
* `any` – **OR**  (at least one sub-condition true)

If `when` is omitted → field/step is **always visible**.

```jsonc
{
  "when": {
    "any": [
      { "key": "listing_type", "=", "sale" },
      { "key": "listing_type", "=", "rent" }
    ]
  }
}
```

---

## 6. Real Estate Snippets

### 6.1 Listing Type → Price Fields

```jsonc
{
  "key": "listing_type",
  "label": "Listing Type",
  "type": "radio",
  "options": [
    { "value": "sale", "label": "For Sale" },
    { "value": "rent", "label": "For Rent" }
  ],
  "rules": "required"
},

{ "key": "sale_price", "label": "Sale Price", "type": "text",
  "rules": "nullable|numeric|min:1000",
  "when": { "all": [{ "key": "listing_type", "=", "sale" }] }
},

{ "key": "rent_price", "label": "Monthly Rent", "type": "text",
  "rules": "nullable|numeric|min:100",
  "when": { "all": [{ "key": "listing_type", "=", "rent" }] }
}
```

### 6.2 Optional Contact Step

```jsonc
{
  "slug": "contact",
  "title": "Your Contact Info",
  "fields": [
    { "key": "contact_name", "label": "Name", "type": "text", "rules": "required" },
    { "key": "contact_email", "label": "Email", "type": "text", "rules": "required|email" }
  ],
  "when": { "all": [{ "key": "broker_listing", "falsy": true }] }
}
```

If the user checks **“Broker listing”** earlier, `broker_listing` is truthy,
making the entire step invisible and automatically skipping to **Finalize**.

---

## 7. Putting It Together

Minimal wizard skeleton:

```jsonc
{
  "key": "wizard-key",
  "title": "Wizard Title",
  "steps": [
    {
      "slug": "intro",
      "title": "Intro",
      "fields": [
        { "key": "name", "label": "Your Name", "type": "text", "rules": "required" }
      ]
    }
  ]
}
```

Save this in `config/stitch-wizard.php` (or DB) and visit  
`/wizard/wizard-key` – that’s all you need to start stitching!
