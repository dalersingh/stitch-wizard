# Contributing Guide

Follow these guidelines to keep the codebase clean, reviewable, and in sync with our lightweight SDLC.

---

## 1 – Branches

| Purpose | Pattern | Example |
|---------|---------|---------|
| Feature / user story | `droid/feat-*` | `droid/feat-db-state-store` |
| Bug fix / hot-patch   | `droid/fix-*`  | `droid/fix-contact-step` |
| Chore / docs / tooling| `droid/chore-*`| `droid/chore-ci-cache` |

* **Base branch:** `develop`  
* **Stable release:** `main` (deployment tags come from here).  
* **Historical:** `feature/bootstrap` (frozen, never target).

---

## 2 – SDLC Flow

```
User Story → Acceptance Criteria (= test cases) → Implementation
          → QA (local Pest + Playwright) → PR → Review → Merge
```

All AC **must** be automated tests in the PR.

---

## 3 – Pull Requests

1. **Small scope.** One user story / fix max.  
2. Title starts with Conventional Commit prefix (`feat:`, `fix:`, `chore:` …).  
3. Description links the user story ID and lists acceptance criteria.  
4. Include updated / new tests and green CI.  
5. Assign **GitHub Copilot** as reviewer (or `@copilot-pull-request-reviewer`).  
6. After addressing feedback, **request re-review** from the same reviewer.  
7. Merge **only after Copilot review is approved** (squash & merge).

---

## 4 – Commit Style

* Conventional Commits (`type(scope?): subject`)  
  * examples: `feat(wizard): add rent_price visibility`, `fix(validator): allow nullable year`.
* Keep commits atomic; no WIP noise.

---

## 5 – Code Style

* **PHP:** run `vendor/bin/pint` before commit.  
* **JS/TS:** run `prettier --write` (if `package.json` scripts exist).  
* **Zero comments** in source files – code and tests only.  
* Remove dead code / files instead of commenting out.

---

## 6 – Testing Checklist

| Layer | Command |
|-------|---------|
| Feature (Pest) | `vendor/bin/pest` |
| E2E (Playwright) | `npx playwright test` |

Tests must pass locally and in CI before PR review.

---

Happy stitching! Keep PRs lean, tests green, and docs sharp.
