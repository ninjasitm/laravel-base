---
description: Execute implementation tasks from a task list or specification
---

You are helping to implement features based on a task list or specification.

## Your Task

1. **Load context**:
   - Read the specification or task list provided
   - Review `AGENTS.md` for coding patterns
   - Check relevant documentation in `docs/`

2. **Analyze implementation requirements**:
   - Identify files to create or modify
   - Understand dependencies between tasks
   - Note testing requirements

3. **Execute implementation**:
   - Follow TDD approach when appropriate
   - Implement in logical order (dependencies first)
   - Follow project coding standards
   - Add appropriate comments and documentation

4. **Validate implementation**:
   - Run linting: `./vendor/bin/pint`
   - Run tests: `php vendor/bin/phpunit`
   - Build/autoload refresh: `composer dump-autoload`

5. **Track progress**:
   - Mark completed tasks
   - Report any blockers or issues
   - Summarize changes made

## Guidelines

- Follow patterns from `AGENTS.md`
- Keep changes focused and atomic
- Write tests for new functionality
- Update documentation as needed
- Use conventional commit messages
