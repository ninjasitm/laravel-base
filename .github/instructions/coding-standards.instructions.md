---
applyTo: "**/*"
description: "Coding standards, patterns, and conventions for laravel-base."
---

# Coding Standards

## Primary Pattern: Service provider + trait-driven composition

```php
class NitmContentServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		$this->mergeConfigFrom(__DIR__.'/../config/nitm-content.php', 'nitm-content');
	}
}
```

**Rules:**

- Use strict, explicit return types and parameter types where supported.
- Keep package code framework-idiomatic (service providers, contracts, traits, repositories).
- Prefer reusable helpers/traits over duplicating controller/model logic.

## Data Access Pattern

```php
$model = $this->query()->where('id', $id)->firstOrFail();
return $model;
```

## API / Route Pattern

```php
Route::middleware(['auth:sanctum'])->group(function () {
	Route::get('/resource/{id}', [ResourceController::class, 'show']);
});
```

## Naming Conventions

- Files: `StudlyCase.php` (PSR-4 class/file mapping)
- Components: `StudlyCase` classes grouped by domain (`Models`, `Repositories`, `Http`, `Traits`)
- Functions: `camelCase` methods with verb-first naming
- Variables: `camelCase` local variables and properties

## Code Quality

- Follow Laravel and PHP best practices
- Run linting before committing: `./vendor/bin/pint`
- Format code consistently: `./vendor/bin/pint`
