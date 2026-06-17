# Salesforce Pardot (Account Engagement) integration — design

**Date:** 2026-06-17
**Status:** Approved (design)
**Author:** Ivan Ružević

## Summary

Add a new **Pardot / Salesforce Account Engagement** integration to Eightshift Forms. It is a
**builder integration** (`Config::INTEGRATION_TYPE_DEFAULT`, modeled on Airtable/HubSpot) that
authenticates with **Salesforce OAuth** (modeled on Nationbuilder). In the block editor the user
picks a Pardot **Form Handler**; Eightshift auto-generates the form fields from that handler's
fields. On submit, field values are POSTed to the handler's public URL, which upserts the prospect
and runs Pardot-side completion actions (list/campaign assignment, etc.).

## Goals

- Connect to Pardot via Salesforce OAuth (Connected App consumer key/secret already created).
- Let editors select a Form Handler and auto-build the form from its fields (no manual mapping).
- Submit form data to Pardot, upserting the prospect.
- Support both production and sandbox environments.

## Non-goals

- Native Pardot **Forms** (hosted/iframe — cannot accept external POSTs). We use **Form Handlers**.
- Manual field-to-prospect mapping (the rejected Nationbuilder-style alternative).
- Pardot-side completion actions (list/campaign assignment) configured from WP — these are configured
  in the Pardot Form Handler itself.
- Automated tests — verified manually, matching the existing integration codebase convention.
- A WP-Cron queue (Nationbuilder needs one for list/tag jobs; Pardot does not).

## Approaches considered

1. **Form Handler builder + OAuth (chosen).** OAuth reads handlers + fields; form auto-generated;
   submit = POST to handler URL. Matches "use the form-fields API endpoint, build automatically."
2. **Prospects API + manual mapping (rejected).** OAuth + manual form, map each field to a prospect
   field via `/objects/custom-fields`, POST to `/objects/prospects`. Gives structured API errors and
   WP-side list/campaign control, but requires manual form building — the thing the user moved away
   from.

## Architecture

### Three distinct hosts (key Pardot quirk)

| Purpose                         | Production             | Sandbox               | Path                                                   |
| ------------------------------- | ---------------------- | --------------------- | ------------------------------------------------------ |
| **Auth** (OAuth)                | `login.salesforce.com` | `test.salesforce.com` | `/services/oauth2/authorize`, `/services/oauth2/token` |
| **Data** (read handlers/fields) | `pi.pardot.com`        | `pi.demo.pardot.com`  | `/api/v5/objects/...`                                  |
| **Submit** (post form data)     | handler's own URL      | handler's own URL     | parsed from `embedCode` `<form action>`                |

Data-host calls require two headers: `Authorization: Bearer {access_token}` and
`Pardot-Business-Unit-Id: {businessUnitId}`.

### Deltas vs the Nationbuilder OAuth pattern

1. Salesforce token endpoint requires an **`application/x-www-form-urlencoded`** body
   (Nationbuilder sends JSON).
2. Two extra global settings: **Business Unit ID** and an **environment (prod/sandbox)** toggle.
3. Token-expiry is detected via Salesforce **HTTP 401 / `INVALID_SESSION_ID`** (not `token_expired`).
4. Tokens are used **only for read calls** (handlers/fields/test). The actual form submission is an
   unauthenticated POST to the handler URL.

### Single-level selection (simpler than Airtable)

Form Handlers are flat (a handler owns its fields directly), so the block needs only one selector:
`pardotIntegrationId` (the handler ID). There is **no `innerId`** and **no `IntegrationItemsInner`
route**. The editor passes `useInnerId={false}` (models HubSpot, not Airtable).

## Data flow

1. **Connect.** Global settings → OAuth connect button (`getOauthConnection`) → Salesforce authorize
   → callback route `/wp-json/eightshift-forms/v1/oauth/pardot` → exchange `code` for tokens →
   store access + refresh tokens via `update_option`.
2. **Build.** Editor block → `IntegrationItems` route → `PardotClient::getItems()` →
   `GET /api/v5/objects/form-handlers` → handler dropdown. On select, mapper
   `Pardot::getFormFields($formId, $itemId, '')` → `PardotClient::getItem($handlerId)` →
   `GET /api/v5/objects/form-handler-fields?formHandlerId={id}` → Eightshift component array.
3. **Submit.** Public `/form-submit/pardot` route → `PardotClient::postApplication($formDetails)` →
   resolve the handler's POST URL (from cached handler `embedCode`) → POST url-encoded
   `name => value` pairs → treat 2xx/3xx as success.

## Components

### `Integrations/Pardot/`

- **`OauthPardot.php`** — `extends AbstractOauth implements OauthInterface`.
  - Constants: `OAUTH_PARDOT_ACCESS_TOKEN_KEY = 'pardot-access-token'`,
    `OAUTH_PARDOT_REFRESH_TOKEN_KEY = 'pardot-refresh-token'`.
  - `getOauthAuthorizeUrl()` → SF auth host `/services/oauth2/authorize` with
    `response_type=code`, `client_id`, `redirect_uri`.
  - `getOauthAccessTokenData($code)` / `getOauthRefreshTokenData()` → SF auth host
    `/services/oauth2/token`, **form-encoded** body (`grant_type`, `client_id`, `client_secret`,
    `redirect_uri`/`refresh_token`, `code`).
  - `getAccessToken`, `getRefreshToken` (retry counter, max 3), `hasTokenExpired($body)` →
    checks SF 401 / `INVALID_SESSION_ID`.
  - `getApiUrl($path)` → Pardot **data** host (env-aware). Private helper for the SF **auth** host.
  - Auth host + data host both switch on the environment setting.
- **`PardotClientInterface.php`** — `extends ClientInterface` (`getItems`, `getItem`,
  `postApplication`, `getTestApi`). Add Pardot-specific helpers if needed.
- **`PardotClient.php`** — `implements PardotClientInterface`. Injects `OauthInterface`.
  - `getItems()` → cached list of form handlers (`id`, `name`, plus the parsed POST URL from
    `embedCode`). Transient `es_pardot_form_handlers_cache`.
  - `getItem($handlerId)` → cached handler fields (`name`, `dataFormat`, `isRequired`,
    `errorMessage`). Transient `es_pardot_form_handler_fields_cache`.
  - `postApplication($formDetails)` → POST url-encoded values to the handler URL (unauthenticated).
  - `getTestApi()` → `GET /api/v5/objects/form-handlers?limit=1` with auth headers; refresh-on-401.
  - Read calls: on `hasTokenExpired`, refresh once and retry.
- **`Pardot.php`** (mapper) — `extends AbstractFormBuilder implements MapperInterface, ServiceInterface`.
  - `FILTER_FORM_FIELDS_NAME = 'es_pardot_form_fields_filter'`.
  - `register()` adds the filter (priority 10, 3 args) + dynamic block output filter.
  - `getFormFields($formId, $itemId, $innerId)` → `{ type, itemId, innerId, fields }`.
  - `getFields()` switch on handler `dataFormat`:
    - `email` → `input` type `email` (`inputIsEmail`, locked options).
    - `number` → `input` type `number`.
    - `phone`/`tel` → `input` type `tel`.
    - `date` → `input` type `date` (or date component).
    - default/text/string → `input` type `text`.
    - `isRequired` → required; field `name` → `inputName`/`inputTracking`.
  - **Limitation:** form handlers do not expose select choices, so auto-built fields are inputs.
- **`SettingsPardot.php`** — `extends AbstractSettingsIntegrations implements SettingGlobalInterface,
ServiceInterface`. Global settings only (no per-form `SettingInterface`, like Airtable).
  - Constants: `SETTINGS_TYPE_KEY = 'pardot'`, `FILTER_SETTINGS_GLOBAL_NAME`,
    `FILTER_SETTINGS_GLOBAL_IS_VALID_NAME`, `SETTINGS_PARDOT_USE_KEY`,
    `SETTINGS_PARDOT_CLIENT_ID`, `SETTINGS_PARDOT_CLIENT_SECRET`,
    `SETTINGS_PARDOT_BUSINESS_UNIT_ID`, `SETTINGS_PARDOT_ENVIRONMENT_KEY` (prod|sandbox),
    `SETTINGS_PARDOT_SKIP_INTEGRATION_KEY`, `SETTINGS_PARDOT_OAUTH_ALLOW_KEY`.
  - `isSettingsGlobalValid()` → used + clientId + clientSecret + businessUnitId all present.
  - `getSettingsGlobalData()` tabs: General (deactivate toggle, Client ID, Client Secret, Business
    Unit ID, environment select, OAuth connect button, test-api button), Options
    (`getGlobalGeneralSettings`), Fallback, Help (setup steps incl. the callback URL).

### `Rest/Routes/Integrations/Pardot/`

- **`OauthPardotRoute.php`** — `extends Rest\Routes\AbstractOauth`. `ROUTE_SLUG = 'pardot'`,
  `getOauthType()` → `'pardot'`, `getOauthAllowKey()` → `SETTINGS_PARDOT_OAUTH_ALLOW_KEY`,
  `submitAction($code)` → `getAccessToken` + redirect with success/fail message.
- **`IntegrationItemsPardotRoute.php`** — `extends AbstractSimpleFormSubmit`, admin-protected,
  `/integration-items/pardot` → `getItems()` → dropdown.
- **`FormSubmitPardotRoute.php`** — `extends AbstractIntegrationFormSubmit`, public,
  `/form-submit/pardot`. Mandatory params: `FD_FORM_ID`, `FD_POST_ID`, `FD_ITEM_ID`, `FD_PARAMS`.
  Checks skip-integration + global-valid, calls `postApplication`, then
  `getIntegrationCommonSubmitAction`.
- **`TestApiPardotRoute.php`** — `extends AbstractSimpleFormSubmit`, `/test-api/pardot` →
  `getTestApi()`.

> No `IntegrationItemsInnerPardotRoute` (single-level selection).

### `Blocks/custom/pardot/`

Builder block modeled on Airtable but single-level:

- `manifest.json` — `blockName: "pardot"`, `supports.inserter: false`, `hasInnerBlocks: true`,
  attribute `pardotIntegrationId` (string). No inner id attribute.
- `pardot.php` — renders the `form` component.
- `pardot-block.js` — `InspectorControls` + editor; `itemIdKey = 'pardotIntegrationId'`.
- `pardot-overrides.js` — icon + `parent: formsCpt`.
- `components/pardot-editor.js` — `IntegrationsEditor` with `useInnerId={false}`.
- `components/pardot-options.js` — `IntegrationsOptions`.

### Edited files

- `Hooks/Variables.php` — `getClientIdPardot()`, `getClientSecretPardot()`,
  `getBusinessUnitIdPardot()` (constants `ES_CLIENT_ID_PARDOT`, `ES_CLIENT_SECRET_PARDOT`,
  `ES_BUSINESS_UNIT_ID_PARDOT`).
- `Hooks/Filters.php` — `use` statements; add public filters/actions entry under `pardot`; add
  Client ID/Secret/Business Unit ID + access/refresh token keys to the non-translatable names list.
- `Hooks/FiltersSettingsBuilder.php` — builder entry: `integrationType => INTEGRATION_TYPE_DEFAULT`,
  `fields => Pardot::FILTER_FORM_FIELDS_NAME`, `cache => [form-handlers, form-handler-fields]`,
  labels, `externalLink => https://www.salesforce.com/marketing/b2b-automation/`.
- `Labels/Labels.php` — `getPardotLabels()` (missing config, settings missing, server error, bad
  request, success) + register in `getLabels()` + add success to `ALL_LOCAL_LABELS`.
- `Troubleshooting/SettingsFallback.php` — `SETTINGS_FALLBACK_FLAG_PARDOT_*` constants
  (missing config, bad request, settings missing, server error) + activity-log entries.
- `Blocks/manifest.json` — add `"eightshift-forms/pardot"` to `integrationsBuilder`.
- `Blocks/custom/form-selector/manifest.json` — add Pardot entry (no `innerBlocks`).

## Error handling

- **Read calls** (handlers/fields/test): on token expiry (401/`INVALID_SESSION_ID`) refresh once and
  retry; otherwise map to fallback flags — missing config, unauthorized/settings missing, or server
  error.
- **Submit**: handler POST returns a redirect/HTML page, not structured JSON, so there is no
  reliable per-field error mapping. Treat 2xx/3xx as success; any other response → generic submit
  fallback. Up-front `isRequired` validation (from handler fields) prevents most bad submissions.

## Testing / verification (manual)

1. Enter Client ID/Secret/Business Unit ID, pick environment; OAuth connect succeeds and stores tokens.
2. Test-API button returns success.
3. Block editor handler dropdown populates from the API.
4. Selecting a handler auto-builds the matching fields (email required, etc.).
5. Live submit creates/updates the prospect in Pardot and runs the handler's completion actions.
6. Token-expiry path: after expiry, a read call refreshes the token and succeeds.

## Open items

- Confirm the exact `dataFormat` enum values returned by `form-handler-fields` during implementation
  and finalize the type→component switch.
- Confirm the POST URL extraction from `embedCode` (parse `<form action>`); fall back to constructing
  the handler URL if a direct field is available.
