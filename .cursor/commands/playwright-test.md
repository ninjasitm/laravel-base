```mdc
---
description: Run or create E2E tests using Playwright
---

# Playwright E2E Testing

Validate scenarios using Playwright for end-to-end testing.

## Usage

```

/playwright-test "User can log in and see dashboard"
/playwright-test tests/Feature/AuthTest.php

````

## Process

1. **Check for Existing Test**:
   - Look in `tests/` for matching scenario
   - If found, run the existing test

2. **Create New Test** (if needed):
   - Create test file in `tests/Feature/`
   - Follow project testing conventions
   - Include proper setup and teardown

3. **Run Test**:
   ```bash
   php vendor/bin/phpunit
   # or
   npx -y playwright test <test-file>
````

4. **Report Results**:
   - Summarize test outcomes
   - Report any failures with details
   - Suggest fixes for failing tests

## Test Template

```typescript
import { test, expect } from "@playwright/test";

test.describe("{{FEATURE_NAME}}", () => {
	test("should {{TEST_DESCRIPTION}}", async ({ page }) => {
		await page.goto("{{APP_URL}}");

		// Test steps here

		await expect(page).toHaveURL(/expected-path/);
	});
});
```

## Configuration

- Test directory: `tests/Feature/`
- Base URL: `{{APP_URL}}`
- Run command: `php vendor/bin/phpunit`

```

```
