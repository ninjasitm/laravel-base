---
applyTo: "**/*"
description: "Project-specific coding patterns and shared utilities for laravel-base."
---

# Patterns and conventions

## State management

- State modules are not applicable (backend package; no client-side store).
- Use Laravel request lifecycle + Eloquent model state.
- Persist state transitions through repositories/models and expose via API/resources.

## API patterns

- API services live in `Api/src/Http/` and supporting contracts/helpers in `Api/src/`.
- Keep controllers thin; delegate business logic to repositories/helpers/traits in `Content/src/`.

## Component patterns

- Classes follow layered package modules (`Http`, `Models`, `Repositories`, `Traits`, `Providers`).
- Package behavior should be composed through traits/contracts to stay reusable in host apps.

## Utilities

- Shared utilities live in `Helpers/src/`.
- Put cross-cutting helpers in `Nitm\Helpers\*` classes and avoid duplicate utility logic.
