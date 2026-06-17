# Salesforce Pardot (Account Engagement) integration — design

**Date:** 2026-06-17
**Status:** Approved (revised — JIRA-style)
**Author:** Ivan Ružević

## Summary

Refactor the Pardot integration from an **auto-builder** (mapper-based, `INTEGRATION_TYPE_DEFAULT`)
to a **JIRA-style manual integration** (`INTEGRATION_TYPE_NO_BUILDER`). The user:

1. Manually creates form fields in the block editor.
2. In per-form settings, selects a Pardot Form Handler from a dropdown.
3. In per-form settings, maps each Pardot field to a form field name.
4. On submit, mapped values are POSTed to the handler's URL.

## Goals

- Remove the `MapperInterface` / auto-builder flow for Pardot.
- Add per-form settings: handler selection + field-to-field mapping (exactly like JIRA).
- Submit logic reads handler + mapping from settings, not from block attributes.
- Keep the global OAuth settings (Client ID/Secret, Business Unit ID, environment) unchanged.

## Non-goals

- Auto-generating form fields from the Pardot API (the approach being removed).
- Changes to OAuth flow, caching, or the Pardot API client methods.
- Backward-compatibility shims for forms using `pardotIntegrationId` block attribute.

## Architecture

### Integration type change

| Before                                        | After                                                |
| --------------------------------------------- | ---------------------------------------------------- |
| `INTEGRATION_TYPE_DEFAULT` (builder/mapper)   | `INTEGRATION_TYPE_NO_BUILDER` (JIRA-style)           |
| `'fields' => Pardot::FILTER_FORM_FIELDS_NAME` | `'settings' => SettingsPardot::FILTER_SETTINGS_NAME` |
| No per-form settings                          | Per-form settings with handler + field mapping       |
| `FD_ITEM_ID` in submit mandatory params       | `itemId` read from form settings at submit time      |
| Handler selected in block sidebar             | Handler selected in per-form settings                |

### Data flow

1. **Global setup** — unchanged: OAuth connect, credentials, business unit ID, environment.
2. **Per-form settings** — editor opens form settings → selects form handler from dropdown
   (populated via `pardotClient->getItems()`) → selects handler → field mapping tab appears with
   Pardot fields (from `pardotClient->getItem($handlerId)`) → user types form field name next to
   each Pardot field → saved as form meta.
3. **Submit** — `FormSubmitPardotRoute` calls `pardotClient->postApplication($formDetails)` →
   reads `SETTINGS_PARDOT_ITEM_ID_KEY` from form meta → reads `SETTINGS_PARDOT_PARAMS_MAP_KEY`
   mapping → for each `[pardotFieldName => formFieldName]` entry, looks up the submitted form
   param value → POSTs `pardotFieldName = value` pairs to the handler URL.

## Components

### `Integrations/Pardot/SettingsPardot.php`

**New:** implement `SettingInterface` (alongside existing `SettingGlobalInterface`).

New constants:

- `FILTER_SETTINGS_NAME = 'es_forms_settings_pardot'`
- `FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_pardot'`
- `SETTINGS_PARDOT_ITEM_ID_KEY = 'pardot-item-id'`
- `SETTINGS_PARDOT_PARAMS_MAP_KEY = 'pardot-params-map'`

New methods:

- `isSettingsValid(bool $output, string $formId): bool` — returns `false` if global invalid OR
  `SETTINGS_PARDOT_ITEM_ID_KEY` is empty for this form.
- `getSettingsData(string $formId): array` — two tabs:
  - **Settings** tab: `select` component populated from `pardotClient->getItems()`;
    `selectSingleSubmit = true`; stored as `SETTINGS_PARDOT_ITEM_ID_KEY`.
  - **Field mapping** tab (visible only when handler selected): intro with available form field
    names (`getPartialFieldTags` / `getPartialFormFieldNames`); `group` component with one `input`
    per Pardot field (label = Pardot field name, name = Pardot field `id`); stored as
    `SETTINGS_PARDOT_PARAMS_MAP_KEY`.

Constructor: inject `PardotClientInterface` (new dependency, alongside existing
`SettingsFallbackDataInterface` and `OauthInterface`).

`register()`: add `add_filter` for `FILTER_SETTINGS_NAME` and `FILTER_SETTINGS_IS_VALID_NAME`.

### `Integrations/Pardot/Pardot.php`

**Delete.** The `MapperInterface` implementation is no longer needed.

### `Integrations/Pardot/PardotClient.php` — `postApplication()`

Replace the current logic (which uses `$formDetails[Config::FD_ITEM_ID]` and passes form params
directly by name) with:

1. `$formId = $formDetails[Config::FD_FORM_ID]`
2. `$itemId = SettingsHelpers::getSettingValue(SettingsPardot::SETTINGS_PARDOT_ITEM_ID_KEY, $formId)`
3. `$mapParams = SettingsHelpers::getSettingValueGroup(SettingsPardot::SETTINGS_PARDOT_PARAMS_MAP_KEY, $formId)`
4. Index form params by field name: `$paramsByName[$param['name']] = $param['value']`
5. Build payload: for each `[$pardotField => $formField]` in `$mapParams`, set
   `$output[$pardotField] = $paramsByName[$formField] ?? ''`
6. Retrieve handler URL from `$this->getItems()[$itemId]['submitUrl']`

### `Rest/Routes/Integrations/Pardot/FormSubmitPardotRoute.php`

- Remove `Config::FD_ITEM_ID` from `getMandatoryParams()`.
- Replace the global-only validity check with the per-form filter:
  `apply_filters(SettingsPardot::FILTER_SETTINGS_IS_VALID_NAME, false, $formId)`

### `Hooks/FiltersSettingsBuilder.php`

Update the Pardot entry:

```php
SettingsPardot::SETTINGS_TYPE_KEY => [
    'settingsGlobal' => SettingsPardot::FILTER_SETTINGS_GLOBAL_NAME,
    'settings'       => SettingsPardot::FILTER_SETTINGS_NAME,          // new
    'type'           => Config::SETTINGS_INTERNAL_TYPE_INTEGRATION,
    'integrationType'=> Config::INTEGRATION_TYPE_NO_BUILDER,            // changed
    'use'            => SettingsPardot::SETTINGS_PARDOT_USE_KEY,
    'settingsForceShow' => false,
    'cache'          => [
        PardotClient::CACHE_PARDOT_FORM_HANDLERS_TRANSIENT_NAME,
        PardotClient::CACHE_PARDOT_FORM_HANDLER_FIELDS_TRANSIENT_NAME,
    ],
    'labels'         => [...],
],
```

Remove the `'fields' => Pardot::FILTER_FORM_FIELDS_NAME` key and its `use` statement.

### Block files (`Blocks/custom/pardot/`)

- **`components/pardot-options.js`**: replace `IntegrationsOptions` with
  `IntegrationsInternalOptions`; remove `itemId`, `itemIdKey` props.
- **`components/pardot-editor.js`**: remove any `IntegrationsEditor` mapper props
  (`itemIdKey`, `useInnerId`, etc.) if present; keep basic editor wrapper.
- **`manifest.json`**: remove the `pardotIntegrationId` attribute.

## Error handling

- `isSettingsValid()` guards the settings tab — if no handler is selected, the form is marked
  invalid and `FormSubmitPardotRoute` throws `BadRequestException` before hitting the API.
- `postApplication()` — if `itemId` missing or handler URL not found → existing
  `SETTINGS_FALLBACK_FLAG_PARDOT_MISSING_CONFIG` path (unchanged).
- Unmapped Pardot fields (no entry in `mapParams`) → send empty string; Pardot ignores optional
  fields, and `isRequired` fields should be caught by client-side validation.

## Testing / verification (manual)

1. Global settings (OAuth connect, test API) continue to work unchanged.
2. Per-form settings show the handler dropdown populated from the Pardot API.
3. Selecting a handler shows the field mapping tab with Pardot field names.
4. Entering form field names in the mapping and saving works.
5. Form submission POSTs the correctly mapped values to Pardot and creates/updates the prospect.
6. Submitting without a handler configured returns a proper error.
