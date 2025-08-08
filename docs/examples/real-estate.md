# Real Estate Listing Wizard – Canonical Example

The **Real Estate wizard** is the primary, fully-featured showcase used throughout Stitch Wizard’s docs, tests and UI/UX decisions.

---

## Flow at a Glance

The wizard walks the user through **6 steps**:

| # | Step            | Purpose & Notable Fields |
|---|-----------------|--------------------------|
| 1 | Basics          | `listing_type` (sale / rent) radio, `property_type`, `broker_listing` toggle |
| 2 | Location        | Address, province, **Chanote** land-title checkbox, BTS/MRT proximity |
| 3 | Details         | Bedrooms, bathrooms, **Land Area (rai/ngan/wah)**, year built |
| 4 | Features        | Amenities multiselect (pool, gym…), `chanote` visibility logic demo |
| 5 | Media           | Photo and floor-plan **file uploads** (multiple) |
| 6 | Contact (conditional) | Name, email, phone – *skipped automatically when `broker_listing` is truthy* |

---

## Key Schema Snippets

### 1. Listing Type → Price vs Rent

```jsonc
// Step: Basics
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

// Step: Details
{ "key": "sale_price", "label": "Sale Price (THB)", "type": "text",
  "rules": "nullable|numeric|min:1000",
  "when": { "all": [{ "key": "listing_type", "=", "sale" }] }
},
{ "key": "rent_price", "label": "Monthly Rent (THB)", "type": "text",
  "rules": "nullable|numeric|min:100",
  "when": { "all": [{ "key": "listing_type", "=", "rent" }] }
}
```

### 2. Thailand-Specific Land Data

```jsonc
// Step: Location
{ "key": "chanote", "label": "Chanote Land Title", "type": "checkbox" },

// Step: Details
{
  "key": "land_area",
  "label": "Land Area",
  "type": "group",                // rendered as three inputs side-by-side
  "fields": [
    { "key": "rai",  "label": "Rai",  "type": "text", "rules": "nullable|integer|min:0" },
    { "key": "ngan", "label": "Ngan", "type": "text", "rules": "nullable|integer|min:0|max:3" },
    { "key": "wah",  "label": "Wah",  "type": "text", "rules": "nullable|integer|min:0|max:99" }
  ]
}
```

### 3. Optional Contact Step

```jsonc
// Step definition
{
  "slug": "contact",
  "title": "Your Contact Info",
  "fields": [
    { "key": "contact_name",  "label": "Name",  "type": "text", "rules": "required" },
    { "key": "contact_email", "label": "Email", "type": "text", "rules": "required|email" }
  ],
  "when": { "all": [{ "key": "broker_listing", "falsy": true }] }
}
```

---

## Screenshots

When you run the Playwright suite, screenshots are saved to:

```
e2e/test-results/real-estate/
├─ step1.png
├─ step2.png
├─ step3.png
├─ step4.png
├─ step5.png
└─ success.png
```

These align with the five visible steps plus the final success screen.

---

## Try It Locally

```bash
# inside demos/demo-app
php artisan serve

# then open
http://localhost:8000/wizard/real-estate
```

Interact through all steps, upload a photo (any file works), and submit – you’ll land on the success page.

---

For the exhaustive schema reference (all keys, operators, etc.) see [`../schema.md`](../schema.md). Happy listing!
