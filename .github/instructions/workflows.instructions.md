---
applyTo: "**/*"
description: "Developer workflows and environment settings for laravel-base."
---

# Workflows

## Development

- **Install dependencies:** `composer install`
- **Build/package checks:** `composer dump-autoload`
- **Framework discovery check:** `php artisan package:discover`

## Testing

- **Unit/Feature/API tests:** `php vendor/bin/phpunit`
- **Coverage:** `php vendor/bin/phpunit --coverage-text`
- **Parallel tests (optional):** `php vendor/bin/paratest`

## Code quality

- **Lint/Format:** `./vendor/bin/pint`
- **Static analysis (if configured):** `php vendor/bin/phpstan analyse`

## Environment variables

| Variable        | Purpose                                                | Required |
| --------------- | ------------------------------------------------------ | -------- |
| `APP_ENV`       | Runtime environment (`testing`, `local`, `production`) | Yes      |
| `DB_CONNECTION` | Database connection used by testbench/host app         | No       |
