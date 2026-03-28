---
applyTo: "**/*"
description: "Testing standards and conventions for laravel-base."
---

# Testing Standards

## Commands

```bash
# Run all tests
php vendor/bin/phpunit

# Run tests with coverage
php vendor/bin/phpunit --coverage-text

# Run E2E tests
N/A (package repo has no browser E2E suite)
```

## Test Structure

```php
public function test_repository_returns_model(): void
{
	$repository = app(ExampleRepository::class);
	$model = $repository->findOrFail(1);

	$this->assertNotNull($model);
}
```

## Test File Naming

- Unit tests: `*Test.php` in `tests/Unit/`
- Integration tests: `*Test.php` in `tests/Feature/` and `tests/APIs/`
- E2E tests: Not used in this package by default

## Test Location

- Unit tests live in `tests/Unit/`
- Integration tests in `tests/Feature/` and `tests/APIs/`
- Shared test utilities live in `Testing/src/`

## Coverage Requirements

- Minimum coverage: No enforced threshold in repo config; keep meaningful coverage for touched areas
- Critical paths must have integration or E2E coverage
- New features require tests before merging
