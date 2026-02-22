---
applyTo: "**/*"
description: "Deployment configuration and commands for laravel-base."
---

# Deployment

## Target Platform

- **Platform:** GitLab CI/CD (package release pipeline)
- **Environment:** GitLab CI `master` release/tag workflow

## Deploy Commands

```bash
# Deploy via GitLab CI tag pipeline
git push origin master

# Build for production
composer install && php vendor/bin/phpunit
```

## Environment Variables

| Variable            | Description                           | Required |
| ------------------- | ------------------------------------- | -------- |
| `GITLAB_USER_NAME`  | Git author name used by tag bump job  | Yes      |
| `GITLAB_USER_EMAIL` | Git author email used by tag bump job | Yes      |

## CI/CD

```yaml
# .gitlab-ci.yml
stages:
	- build-and-test
	- publish
```

## Pre-Deployment Checklist

- [ ] All tests pass
- [ ] Linting passes
- [ ] Build completes without errors
- [ ] Environment variables configured
- [ ] Database migrations applied (if applicable)
