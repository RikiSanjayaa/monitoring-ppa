# AGENTS.md
Guidance for coding agents operating in this repository.
Follow direct user instructions first, then this guide.

## 1) Project Context
- Stack: Laravel 12, Filament 3, PHP 8.2+
- Domain: monitoring kasus lintas satker (Ditres PPA PPO)
- Language in code/domain: Indonesian terms (`Kasus`, `Satker`, `Perkara`, etc.)
- Testing: PHPUnit through `php artisan test`
- Formatter/lint: Laravel Pint

## 2) Additional Rule Files
Checked paths:
- `.cursor/rules/` -> not present
- `.cursorrules` -> not present
- `.github/copilot-instructions.md` -> not present
If these files appear later, treat them as repository-specific overrides.

## 3) Runtime Notes
- The active app flow is Laravel + Filament; no active Vite workflow is required.
- Prefer PHP/Artisan commands for setup, run, and verification.
- Do not introduce Node/Vite steps unless explicitly requested.

## 4) Setup and Run Commands
Run from repository root.
- Install deps: `composer install`
- Create env: `cp .env.example .env`
- App key: `php artisan key:generate`
- Migrate DB: `php artisan migrate`
- Fresh local reset: `php artisan migrate:fresh --seed`
- Convenience setup: `composer run setup`
- Serve app: `php artisan serve`
- Serve alias: `composer run dev`
- Clear config cache: `php artisan config:clear`

## 5) Build/Lint/Test Commands
Primary commands:
- Lint/format PHP: `./vendor/bin/pint`
- Lint check only: `./vendor/bin/pint --test`
- Full tests: `composer test`
- Full tests direct: `php artisan test`

Single-test workflows (important):
- Single file: `php artisan test tests/Feature/ExampleTest.php`
- Single method: `php artisan test tests/Feature/ExampleTest.php --filter=test_the_application_returns_a_successful_response`
- By class/name pattern: `php artisan test --filter=ExampleTest`
- Raw PHPUnit single method: `./vendor/bin/phpunit tests/Feature/ExampleTest.php --filter=test_the_application_returns_a_successful_response`

Testing environment notes:
- `phpunit.xml` uses in-memory SQLite (`DB_DATABASE=:memory:`)
- Prefer `php artisan test` for normal work; use raw PHPUnit only when needed

## 6) Code Style Guidelines

### Formatting
- Follow PSR-12 and Laravel Pint defaults.
- Use 4 spaces, UTF-8, LF endings, trailing newline.
- Keep methods focused and readable.
- Avoid unnecessary comments.

### Imports and Namespace
- Namespace appears immediately after `<?php`.
- Use one `use` import per line.
- Remove unused imports.
- Prefer imported class names over inline fully-qualified names.
- Alias imports only when there is a conflict or clarity issue.

### Types and Signatures
- Use explicit parameter and return types.
- Use typed properties where possible.
- Prefer enums for constrained values (`UserRole`, `DokumenStatus`).
- Add PHPDoc for complex arrays/collections/generics.
- Keep docblocks synchronized with behavior.

### Naming Conventions
- Classes/enums: `PascalCase`.
- Methods/variables: `camelCase`.
- Database columns: `snake_case`.
- Keep existing Indonesian domain naming consistent.
- Predicate helpers should read clearly (`isAdmin`, `isAtasan`).

### Eloquent, Relations, and Queries
- Start query chains with `Model::query()`.
- Reuse model relations/scopes over duplicated logic.
- Eager load relations for Filament tables/exports to avoid N+1.
- Respect global scopes and role constraints.
- Bypass scopes only with explicit intent and clear reason.

### Filament Patterns
- Keep resource definitions declarative and grouped by sections.
- Keep labels/defaults explicit for nullable display fields.
- Keep table filters/columns intentional and consistent.
- Ensure resource visibility and actions match policies.

### Validation and Data Handling
- Validate/normalize external input (forms, imports).
- Use guard clauses for missing required values.
- Keep parsing logic in small helper methods.
- Preserve established import behavior unless requirements change.

### Error Handling
- Catch exceptions only when there is a defined fallback.
- Keep try/catch blocks narrow.
- Do not silently swallow unexpected errors.
- Do not expose sensitive internals in user-facing outputs.

### Authorization and Security
- Enforce policy checks and role/satker boundaries consistently.
- Update policy behavior when adding new actions.
- Avoid shortcuts that bypass access control paths.

### Migrations, Seeders, and Tests
- Keep migrations deterministic and reversible.
- Add constraints/indexes intentionally.
- Prefer idempotent seeding (`updateOrCreate`) when practical.
- Put app behavior tests in `tests/Feature`.
- Put pure logic tests in `tests/Unit`.
- Add regression tests for bug fixes and permission changes.

## 7) Agent Execution Workflow
When implementing changes:
1. Read nearby files and mirror existing conventions.
2. Make minimal, focused edits.
3. Run formatter: `./vendor/bin/pint`.
4. Run targeted tests first (single file/method).
5. Run broader tests when scope is wider.
6. Report what changed and how it was verified.

## 8) Quick Reference
- Setup all: `composer run setup`
- Serve app: `php artisan serve`
- Dev alias: `composer run dev`
- Format fix: `./vendor/bin/pint`
- Format check: `./vendor/bin/pint --test`
- All tests: `composer test`
- One file: `php artisan test tests/Feature/ExampleTest.php`
- One method: `php artisan test tests/Feature/ExampleTest.php --filter=test_the_application_returns_a_successful_response`
