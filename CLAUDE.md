# Eightshift Forms — AI Agent Guide

Public WordPress plugin maintained by Infinum. Anything not covered here, derive from the code or the `eightshift` MCP — don't guess.

## Use the eightshift MCP

For anything touching blocks, components, manifests, Tailwind config, or version upgrades use the eightshift MCP.

## Adding a new integration (canonical 11-step recipe)

Order-sensitive. Skipping a step ships a broken integration. Mirrored in `src/Integrations/Readme.md`.

1. New folder `src/Integrations/<Name>/`.
2. `<Name>.php` — maps API responses to blocks/components.
3. `<Name>Client.php` (+ `<Name>ClientInterface.php`) — all HTTP to the third-party API.
4. `Settings<Name>.php` — implements `SettingInterface` + `SettingGlobalInterface`.
5. **Register the integration in `src/Hooks/Filters.php::ALL`.** (Easy to forget. Without this, the integration is invisible to the rest of the plugin.)
6. `src/Rest/Routes/FormSubmit<Name>Route.php` — extends `AbstractIntegrationFormSubmit`.
7. New block in `src/Blocks/custom/<name>/` (use `generate_block` MCP tool).
8. **Add the new block to allowed-blocks in `src/Blocks/custom/form-selector/manifest.json`.** (Without this, editors can't insert it.)
9. Add API-key global variable in `src/Hooks/Variables.php`.
10. Add labels in `src/Labels/Labels.php`.
11. If the integration needs manual setup (like Jira), add a switch case in `Helpers/FormsHelper::getFormDetails`.

## Architectural rules (invisible from any single file)

- **Service auto-registration via DI.** Drop a class in `src/` implementing `ServiceInterface` with a `register()` method — the libs container picks it up. Don't manually wire hooks in a bootstrap file.
- **Filters before forks.** If behavior needs to vary, expose a filter via `Hooks/Filters.php::ALL` (prefix `es_forms_`) rather than branching in code.
- **Settings come in pairs.** `Setting<Thing>` (local form) + `Settings<Thing>` (global plugin). Don't merge them.
- **Helpers are domain-scoped.** Add to the existing `Helpers/<Domain>Helpers.php` that fits, or a new file with a clear domain — never a generic dump.
- **`strict_types=1` on every PHP file.** Escape outputs, sanitize inputs — this plugin handles user-submitted data so maximize safety.

## Don't edit generated files

Anything in `.gitignore` is build output. Never edit.

## Releases

- **Git Flow.** Features off `main` (`feature/<thing>`), released via `release/<N>` merged into `main`.
- **Version lives in two files.** Bump both: `eightshift-forms.php` plugin header AND `package.json`. SemVer.
- **CHANGELOG.md** ([Keep a Changelog](https://keepachangelog.com/) format) updates with the version bump, never separately.
- CI runs PHPStan + PHPCS + JS/CSS lint on every PR (PHP 8.4 only).

## Linting

- **Don't lint mid-session.** Lint at the end and ask before running.
- Run in parallel: `composer test` + `bun run lint`.
- Husky + lint-staged run these on commit automatically.

## Testing

E2E only, Playwright + WP Playground (`tests/e2e/`). No PHP unit suite — don't fabricate one.

```bash
bun run test:e2e:playground   # boot WP Playground with dataset
bun run test:e2e:ui           # interactive
```
