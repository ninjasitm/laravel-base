---
applyTo: "**/*"
description: "Project architecture and data flow for laravel-base."
---

# laravel-base architecture

- **Framework:** Laravel 9/10 (package)
- **Language:** PHP 8.0-8.3
- Entry point: `Content/src/NitmContentServiceProvider.php` and `Api/src/NitmApiServiceProvider.php`

## Directory structure

- `Api/src/` - API service provider, contracts, helpers, auth, and HTTP layers
- `Content/src/` - Content module (models, repositories, jobs, events, observers, providers)
- `Helpers/src/` - Shared helper classes
- `Testing/src/` - Reusable test traits and package test case classes
- `tests/` - Package unit, feature, and API tests

## Data flow

- HTTP request enters host Laravel app, package routes/controllers resolve services and repositories, Eloquent models persist through configured DB connection, and resource/response helpers serialize output.

## Authentication

- Authentication is delegated to the host Laravel application; package APIs integrate with Laravel auth guards/middleware.

## API integration

- API scaffolding and controller traits are provided via `Api/` module and stubs; consumers publish configs/stubs and compose endpoints in their host app.
