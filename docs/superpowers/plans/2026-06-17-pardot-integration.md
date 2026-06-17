# Pardot Integration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a Salesforce Pardot (Account Engagement) form integration that authenticates via Salesforce OAuth, auto-builds forms from Form Handler fields, and submits to the handler's POST URL.

**Architecture:** Builder integration (`INTEGRATION_TYPE_DEFAULT`) modeled on Airtable (auto-generates form from external source via `MapperInterface`) with Salesforce OAuth modeled on Nationbuilder. User picks a Pardot Form Handler; form fields are built from handler field definitions fetched via the API; on submit, values are POSTed form-encoded to the handler's public URL (unauthenticated).

**Tech Stack:** PHP 8.4, WordPress hooks/options, Salesforce OAuth 2.0, Pardot Account Engagement API v5, Gutenberg blocks (React JSX).

## Global Constraints

- Namespace prefix: `EightshiftForms\Integrations\Pardot`
- Settings type key: `'pardot'` (used as route slug, block name, cache key prefix)
- No automated tests — verify manually after each task
- No per-form settings class (builder forms are auto-generated, like Airtable)
- No CronJob (Pardot-side completion actions; no WP queue needed)
- No `IntegrationItemsInner` route (single-level selection, no innerId)
- All existing file edits use tabs for indentation (match the file)
- All `wp_json_encode` / `wp_remote_*` calls match existing patterns exactly
- Token expiry detected via HTTP 401 **or** `errorCode === 'INVALID_SESSION_ID'` in response body
- Prod SF auth host: `login.salesforce.com`; Sandbox: `test.salesforce.com`
- Prod Pardot data host: `pi.pardot.com`; Sandbox: `pi.demo.pardot.com`
- Pardot API data calls need two headers: `Authorization: Bearer {token}` + `Pardot-Business-Unit-Id: {id}`
- SF OAuth token body must be `application/x-www-form-urlencoded` (not JSON like Nationbuilder)
- Spec: `docs/superpowers/specs/2026-06-17-pardot-integration-design.md`

---

## File Map

**Create (15 new files):**

- `src/Integrations/Pardot/OauthPardot.php` — Salesforce OAuth token exchange/refresh
- `src/Integrations/Pardot/PardotClientInterface.php` — client contract
- `src/Integrations/Pardot/PardotClient.php` — API client (form handlers, fields, submit)
- `src/Integrations/Pardot/Pardot.php` — mapper: builds Eightshift components from handler fields
- `src/Integrations/Pardot/SettingsPardot.php` — global admin settings (OAuth connect, credentials)
- `src/Rest/Routes/Integrations/Pardot/OauthPardotRoute.php` — OAuth callback `/oauth/pardot`
- `src/Rest/Routes/Integrations/Pardot/IntegrationItemsPardotRoute.php` — handler dropdown (admin)
- `src/Rest/Routes/Integrations/Pardot/FormSubmitPardotRoute.php` — public form submit
- `src/Rest/Routes/Integrations/Pardot/TestApiPardotRoute.php` — test-api button (admin)
- `src/Blocks/custom/pardot/manifest.json`
- `src/Blocks/custom/pardot/pardot.php`
- `src/Blocks/custom/pardot/pardot-block.js`
- `src/Blocks/custom/pardot/pardot-overrides.js`
- `src/Blocks/custom/pardot/components/pardot-editor.js`
- `src/Blocks/custom/pardot/components/pardot-options.js`

**Modify (7 existing files):**

- `src/Hooks/Variables.php` — add 3 credential getters (client id/secret, business unit id)
- `src/Hooks/Filters.php` — add use statements, public filters/actions, non-translatable keys
- `src/Hooks/FiltersSettingsBuilder.php` — add Pardot builder entry
- `src/Labels/Labels.php` — add `getPardotLabels()` method and registration
- `src/Troubleshooting/SettingsFallback.php` — add Pardot error flag constants + log entries
- `src/Blocks/manifest.json` — add to `integrationsBuilder`
- `src/Blocks/custom/form-selector/manifest.json` — add Pardot template entry

---

## Task 1: Cross-cutting setup — Variables, Labels, SettingsFallback

**Files:**

- Modify: `src/Hooks/Variables.php`
- Modify: `src/Labels/Labels.php`
- Modify: `src/Troubleshooting/SettingsFallback.php`

**Interfaces:**

- Produces: `Variables::getClientIdPardot()`, `Variables::getClientSecretPardot()`, `Variables::getBusinessUnitIdPardot()` — used by OauthPardot and SettingsPardot
- Produces: `SettingsFallback::SETTINGS_FALLBACK_FLAG_PARDOT_MISSING_CONFIG` and 3 sibling constants — used by PardotClient and FormSubmitPardotRoute
- Produces: label keys `pardotMissingConfig`, `pardotSuccess`, etc. — used by routes

- [ ] **Step 1: Add credential getters to Variables.php**

  Append to end of class body, before the closing `}` of the class (after `getClientSecretNationBuilder()`):

  ```php
  	/**
  	 * Get Client ID for Pardot.
  	 *
  	 * @return string
  	 */
  	public static function getClientIdPardot(): string
  	{
  		return \defined('ES_CLIENT_ID_PARDOT') ? \ES_CLIENT_ID_PARDOT : '';
  	}

  	/**
  	 * Get Client Secret for Pardot.
  	 *
  	 * @return string
  	 */
  	public static function getClientSecretPardot(): string
  	{
  		return \defined('ES_CLIENT_SECRET_PARDOT') ? \ES_CLIENT_SECRET_PARDOT : '';
  	}

  	/**
  	 * Get Business Unit ID for Pardot.
  	 *
  	 * @return string
  	 */
  	public static function getBusinessUnitIdPardot(): string
  	{
  		return \defined('ES_BUSINESS_UNIT_ID_PARDOT') ? \ES_BUSINESS_UNIT_ID_PARDOT : '';
  	}
  ```

- [ ] **Step 2: Add Pardot fallback flag constants to SettingsFallback.php**

  After line 153 (after `SETTINGS_FALLBACK_FLAG_NATIONBUILDER_SERVER_ERROR`), add:

  ```php
  	public const SETTINGS_FALLBACK_FLAG_PARDOT_MISSING_CONFIG = 'pardotMissingConfig';
  	public const SETTINGS_FALLBACK_FLAG_PARDOT_BAD_REQUEST_ERROR = 'pardotBadRequestError';
  	public const SETTINGS_FALLBACK_FLAG_PARDOT_ERROR_SETTINGS_MISSING = 'pardotErrorSettingsMissing';
  	public const SETTINGS_FALLBACK_FLAG_PARDOT_SERVER_ERROR = 'pardotServerError';
  ```

- [ ] **Step 3: Add Pardot entries to the fallback activity-log array in SettingsFallback.php**

  After the closing Nationbuilder block (after line 705, `// Workable.` comment), add before it:

  ```php
  			// Pardot.
  			self::SETTINGS_FALLBACK_FLAG_PARDOT_MISSING_CONFIG => [
  				'label' => \__('When Pardot integration is not configured correctly, either globally or per form.', 'eightshift-forms'),
  				'isRecommended' => true,
  			],
  			self::SETTINGS_FALLBACK_FLAG_PARDOT_BAD_REQUEST_ERROR => [
  				'label' => \__('When Pardot integration returns a bad request error.', 'eightshift-forms'),
  				'isRecommended' => true,
  			],
  			self::SETTINGS_FALLBACK_FLAG_PARDOT_ERROR_SETTINGS_MISSING => [
  				'label' => \__('When Pardot integration returns a settings missing error.', 'eightshift-forms'),
  				'isRecommended' => true,
  			],
  			self::SETTINGS_FALLBACK_FLAG_PARDOT_SERVER_ERROR => [
  				'label' => \__('When Pardot integration returns a server error.', 'eightshift-forms'),
  				'isRecommended' => true,
  			],
  ```

- [ ] **Step 4: Add `pardotSuccess` to `ALL_LOCAL_LABELS` in Labels.php**

  After `'nationbuilderSuccess',` (line 41) add:

  ```php
  		'pardotSuccess',
  ```

- [ ] **Step 5: Register getPardotLabels() in getLabels() in Labels.php**

  After `'nationbuilder' => $this->getNationbuilderLabels(),` (line 75) add:

  ```php
  			'pardot' => $this->getPardotLabels(),
  ```

- [ ] **Step 6: Add getPardotLabels() method to Labels.php**

  After the closing `}` of `getNationbuilderLabels()` (after line 603), add:

  ```php

  	/**
  	 * Return labels - Pardot
  	 *
  	 * @return array<string, string>
  	 */
  	private function getPardotLabels(): array
  	{
  		return [
  			'pardotMissingConfig' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
  			'pardotErrorSettingsMissing' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
  			'pardotServerError' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
  			'pardotBadRequestError' => \__('Something is not right with the submission. Please check all the fields and try again.', 'eightshift-forms'),
  			'pardotSuccess' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
  		];
  	}
  ```

- [ ] **Step 7: Commit**

  ```bash
  git add src/Hooks/Variables.php src/Labels/Labels.php src/Troubleshooting/SettingsFallback.php
  git commit -m "feat: add Pardot cross-cutting setup (variables, labels, fallback flags)"
  ```

---

## Task 2: OauthPardot

**Files:**

- Create: `src/Integrations/Pardot/OauthPardot.php`

**Interfaces:**

- Consumes: `Variables::getClientIdPardot()`, `Variables::getClientSecretPardot()` (Task 1)
- Consumes: `SettingsPardot::SETTINGS_PARDOT_CLIENT_ID`, `SETTINGS_PARDOT_CLIENT_SECRET`, `SETTINGS_PARDOT_BUSINESS_UNIT_ID`, `SETTINGS_PARDOT_ENVIRONMENT_KEY` (defined in Task 5 — forward-reference is fine, constants used only at runtime)
- Produces: `OauthPardot::OAUTH_PARDOT_ACCESS_TOKEN_KEY = 'pardot-access-token'`
- Produces: `OauthPardot::OAUTH_PARDOT_REFRESH_TOKEN_KEY = 'pardot-refresh-token'`
- Produces: `OauthPardot::getApiUrl(string $path): string` — returns Pardot data-host URL
- Produces: `OauthPardot::hasTokenExpired(array $body): bool` — detects SF 401/INVALID_SESSION_ID
- Produces: `OauthPardot::getAccessToken(string $code): bool`
- Produces: `OauthPardot::getRefreshToken(): bool`
- Produces: `OauthPardot::getOauthAuthorizeUrl(): string`

- [ ] **Step 1: Create OauthPardot.php**

  ```php
  <?php

  /**
   * Pardot Oauth class.
   *
   * @package EightshiftForms\Integrations\Pardot
   */

  declare(strict_types=1);

  namespace EightshiftForms\Integrations\Pardot;

  use EightshiftForms\Hooks\Variables;
  use EightshiftForms\Oauth\AbstractOauth;
  use EightshiftForms\Config\Config;
  use EightshiftForms\Helpers\ApiHelpers;
  use EightshiftForms\Helpers\SettingsHelpers;

  /**
   * OauthPardot class.
   */
  class OauthPardot extends AbstractOauth
  {
  	/**
  	 * Retry count for refresh token.
  	 *
  	 * @var integer
  	 */
  	private $refreshTokenRetryCounter = 0;

  	/**
  	 * Access token key.
  	 */
  	public const OAUTH_PARDOT_ACCESS_TOKEN_KEY = 'pardot-access-token';

  	/**
  	 * Refresh token key.
  	 */
  	public const OAUTH_PARDOT_REFRESH_TOKEN_KEY = 'pardot-refresh-token';

  	/**
  	 * Get Pardot data API URL (pi.pardot.com / pi.demo.pardot.com).
  	 *
  	 * @param string $path Path.
  	 *
  	 * @return string
  	 */
  	public function getApiUrl(string $path): string
  	{
  		$host = $this->isSandbox() ? 'pi.demo.pardot.com' : 'pi.pardot.com';

  		return "https://{$host}/{$path}";
  	}

  	/**
  	 * Get Salesforce authorization URL.
  	 *
  	 * @return string
  	 */
  	public function getOauthAuthorizeUrl(): string
  	{
  		$clientId = SettingsHelpers::getOptionWithConstant(Variables::getClientIdPardot(), SettingsPardot::SETTINGS_PARDOT_CLIENT_ID);

  		return \add_query_arg(
  			[
  				'response_type' => 'code',
  				'client_id' => $clientId,
  				'redirect_uri' => $this->getRedirectUri(SettingsPardot::SETTINGS_TYPE_KEY),
  			],
  			$this->getSfAuthHost() . '/services/oauth2/authorize'
  		);
  	}

  	/**
  	 * Get access token exchange data.
  	 *
  	 * @param string $code Code.
  	 *
  	 * @return array<string, mixed>
  	 */
  	public function getOauthAccessTokenData(string $code): array
  	{
  		$clientId = SettingsHelpers::getOptionWithConstant(Variables::getClientIdPardot(), SettingsPardot::SETTINGS_PARDOT_CLIENT_ID);
  		$clientSecret = SettingsHelpers::getOptionWithConstant(Variables::getClientSecretPardot(), SettingsPardot::SETTINGS_PARDOT_SECRET);

  		return [
  			'url' => $this->getSfAuthHost() . '/services/oauth2/token',
  			'args' => \http_build_query([
  				'grant_type' => 'authorization_code',
  				'client_id' => $clientId,
  				'client_secret' => $clientSecret,
  				'redirect_uri' => $this->getRedirectUri(SettingsPardot::SETTINGS_TYPE_KEY),
  				'code' => $code,
  			]),
  			'content_type' => 'application/x-www-form-urlencoded',
  		];
  	}

  	/**
  	 * Get refresh token data.
  	 *
  	 * @return array<string, mixed>
  	 */
  	public function getOauthRefreshTokenData(): array
  	{
  		$clientId = SettingsHelpers::getOptionWithConstant(Variables::getClientIdPardot(), SettingsPardot::SETTINGS_PARDOT_CLIENT_ID);
  		$clientSecret = SettingsHelpers::getOptionWithConstant(Variables::getClientSecretPardot(), SettingsPardot::SETTINGS_PARDOT_SECRET);
  		$refreshToken = SettingsHelpers::getOptionValue(OauthPardot::OAUTH_PARDOT_REFRESH_TOKEN_KEY);

  		return [
  			'url' => $this->getSfAuthHost() . '/services/oauth2/token',
  			'args' => \http_build_query([
  				'grant_type' => 'refresh_token',
  				'client_id' => $clientId,
  				'client_secret' => $clientSecret,
  				'refresh_token' => $refreshToken,
  			]),
  			'content_type' => 'application/x-www-form-urlencoded',
  		];
  	}

  	/**
  	 * Get access token.
  	 *
  	 * @param string $code Code.
  	 *
  	 * @return boolean
  	 */
  	public function getAccessToken(string $code): bool
  	{
  		$data = $this->getOauthAccessTokenData($code);

  		return $this->getToken($data);
  	}

  	/**
  	 * Get refresh token.
  	 *
  	 * @return boolean
  	 */
  	public function getRefreshToken(): bool
  	{
  		if ($this->refreshTokenRetryCounter >= 3) {
  			return false;
  		}

  		$token = $this->getToken($this->getOauthRefreshTokenData());

  		if (!$token) {
  			$this->refreshTokenRetryCounter++;
  			return false;
  		}

  		$this->refreshTokenRetryCounter = 0;
  		return true;
  	}

  	/**
  	 * Check if token has expired (Salesforce returns 401 or INVALID_SESSION_ID).
  	 *
  	 * @param array<string, mixed> $body Body.
  	 *
  	 * @return boolean
  	 */
  	public function hasTokenExpired(array $body): bool
  	{
  		$errorCode = $body['errorCode'] ?? '';
  		if ($errorCode === 'INVALID_SESSION_ID') {
  			return true;
  		}

  		// Also check HTTP-level 401 surfaced as a code field by ApiHelpers.
  		return ($body['code'] ?? '') === '401';
  	}

  	/**
  	 * Exchange or refresh token via Salesforce.
  	 *
  	 * @param array<string, mixed> $data Data with 'url', 'args', 'content_type'.
  	 *
  	 * @return boolean
  	 */
  	private function getToken(array $data): bool
  	{
  		$response = \wp_remote_post(
  			$data['url'],
  			[
  				'headers' => [
  					'Content-Type' => $data['content_type'] ?? 'application/x-www-form-urlencoded',
  					'Accept' => 'application/json',
  				],
  				'body' => $data['args'],
  			]
  		);

  		$details = ApiHelpers::getIntegrationApiResponseDetails(
  			SettingsPardot::SETTINGS_TYPE_KEY,
  			$response,
  			$data['url'],
  		);

  		$code = $details[Config::IARD_CODE];
  		$body = $details[Config::IARD_BODY];

  		if (ApiHelpers::isSuccessResponse($code)) {
  			\update_option(SettingsHelpers::getSettingName(OauthPardot::OAUTH_PARDOT_ACCESS_TOKEN_KEY), $body['access_token']);
  			\update_option(SettingsHelpers::getSettingName(OauthPardot::OAUTH_PARDOT_REFRESH_TOKEN_KEY), $body['refresh_token']);

  			return true;
  		}

  		return false;
  	}

  	/**
  	 * Return Salesforce auth host (login.salesforce.com / test.salesforce.com).
  	 *
  	 * @return string
  	 */
  	private function getSfAuthHost(): string
  	{
  		return $this->isSandbox()
  			? 'https://test.salesforce.com'
  			: 'https://login.salesforce.com';
  	}

  	/**
  	 * Determine if sandbox mode is active.
  	 *
  	 * @return boolean
  	 */
  	private function isSandbox(): bool
  	{
  		return SettingsHelpers::getOptionValue(SettingsPardot::SETTINGS_PARDOT_ENVIRONMENT_KEY) === 'sandbox';
  	}
  }
  ```

- [ ] **Step 2: Commit**

  ```bash
  git add src/Integrations/Pardot/OauthPardot.php
  git commit -m "feat: add OauthPardot class (Salesforce OAuth for Pardot)"
  ```

---

## Task 3: PardotClientInterface + PardotClient

**Files:**

- Create: `src/Integrations/Pardot/PardotClientInterface.php`
- Create: `src/Integrations/Pardot/PardotClient.php`

**Interfaces:**

- Consumes: `OauthPardot::OAUTH_PARDOT_ACCESS_TOKEN_KEY`, `hasTokenExpired()`, `getRefreshToken()`, `getApiUrl()` (Task 2)
- Consumes: `Variables::getBusinessUnitIdPardot()`, `SettingsPardot::SETTINGS_PARDOT_BUSINESS_UNIT_ID` (Task 1 / Task 5)
- Produces: `PardotClient::getItems(bool $hideUpdateTime = true): array` — list of form handlers `['id', 'title', 'submitUrl']`
- Produces: `PardotClient::getItem(string $itemId): array` — handler fields `['id' => ['id', 'title', 'dataFormat', 'isRequired']]`
- Produces: `PardotClient::postApplication(array $formDetails): array`
- Produces: `PardotClient::getTestApi(): array`
- Produces: `PardotClient::CACHE_PARDOT_FORM_HANDLERS_TRANSIENT_NAME`
- Produces: `PardotClient::CACHE_PARDOT_FORM_HANDLER_FIELDS_TRANSIENT_NAME`

- [ ] **Step 1: Create PardotClientInterface.php**

  ```php
  <?php

  /**
   * Pardot Client interface.
   *
   * @package EightshiftForms\Integrations\Pardot
   */

  declare(strict_types=1);

  namespace EightshiftForms\Integrations\Pardot;

  use EightshiftForms\Integrations\ClientInterface;

  /**
   * PardotClientInterface interface.
   */
  interface PardotClientInterface extends ClientInterface
  {
  }
  ```

- [ ] **Step 2: Create PardotClient.php**

  ```php
  <?php

  /**
   * PardotClient integration class.
   *
   * @package EightshiftForms\Integrations\Pardot
   */

  declare(strict_types=1);

  namespace EightshiftForms\Integrations\Pardot;

  use EightshiftForms\Cache\SettingsCache;
  use EightshiftForms\Helpers\ApiHelpers;
  use EightshiftForms\Helpers\GeneralHelpers;
  use EightshiftForms\Hooks\Variables;
  use EightshiftForms\Integrations\ClientInterface;
  use EightshiftForms\Oauth\OauthInterface;
  use EightshiftForms\Helpers\SettingsHelpers;
  use EightshiftForms\Config\Config;
  use EightshiftForms\Helpers\DeveloperHelpers;
  use EightshiftForms\Helpers\HooksHelpers;
  use EightshiftForms\Troubleshooting\SettingsFallback;

  /**
   * PardotClient integration class.
   */
  class PardotClient implements PardotClientInterface
  {
  	/**
  	 * Transient cache name for form handlers.
  	 */
  	public const CACHE_PARDOT_FORM_HANDLERS_TRANSIENT_NAME = 'es_pardot_form_handlers_cache';

  	/**
  	 * Transient cache name for form handler fields.
  	 */
  	public const CACHE_PARDOT_FORM_HANDLER_FIELDS_TRANSIENT_NAME = 'es_pardot_form_handler_fields_cache';

  	/**
  	 * Pardot API version.
  	 */
  	private const API_VERSION = 'v5';

  	/**
  	 * Instance variable for Oauth.
  	 *
  	 * @var OauthInterface
  	 */
  	protected $oauthPardot;

  	/**
  	 * Create a new instance that injects classes
  	 *
  	 * @param OauthInterface $oauthPardot Inject Oauth methods.
  	 */
  	public function __construct(OauthInterface $oauthPardot)
  	{
  		$this->oauthPardot = $oauthPardot;
  	}

  	/**
  	 * Return all Pardot form handlers.
  	 *
  	 * @param bool $hideUpdateTime Determine if update time will be in the output or not.
  	 *
  	 * @return array<string, mixed>
  	 */
  	public function getItems(bool $hideUpdateTime = true): array
  	{
  		$output = \get_transient(self::CACHE_PARDOT_FORM_HANDLERS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

  		if (DeveloperHelpers::isDeveloperSkipCacheActive()) {
  			$output = [];
  		}

  		if (!$output) {
  			$items = $this->getPardotFormHandlers();

  			if ($items) {
  				foreach ($items as $item) {
  					$id = (string) ($item['id'] ?? '');

  					if (!$id) {
  						continue;
  					}

  					$embedCode = $item['embedCode'] ?? '';
  					$submitUrl = $this->parseSubmitUrl($embedCode);

  					$output[$id] = [
  						'id' => $id,
  						'title' => $item['name'] ?? '',
  						'submitUrl' => $submitUrl,
  					];
  				}

  				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
  					'id' => ClientInterface::TRANSIENT_STORED_TIME,
  					'title' => \current_datetime()->format('Y-m-d H:i:s'),
  				];

  				\set_transient(self::CACHE_PARDOT_FORM_HANDLERS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
  			}
  		}

  		if ($hideUpdateTime) {
  			unset($output[ClientInterface::TRANSIENT_STORED_TIME]);
  		}

  		return $output;
  	}

  	/**
  	 * Return form handler fields for a given handler ID.
  	 *
  	 * @param string $itemId Handler ID.
  	 *
  	 * @return array<string, mixed>
  	 */
  	public function getItem(string $itemId): array
  	{
  		$cacheKey = self::CACHE_PARDOT_FORM_HANDLER_FIELDS_TRANSIENT_NAME;
  		$output = \get_transient($cacheKey) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

  		if (DeveloperHelpers::isDeveloperSkipCacheActive()) {
  			$output = [];
  		}

  		if (!$output || empty($output[$itemId])) {
  			$fields = $this->getPardotFormHandlerFields($itemId);

  			if ($fields) {
  				foreach ($fields as $field) {
  					$fieldId = (string) ($field['id'] ?? '');
  					$prospectApiFieldId = $field['prospectApiFieldId'] ?? '';

  					if (!$fieldId) {
  						continue;
  					}

  					$output[$itemId][$fieldId] = [
  						'id' => $prospectApiFieldId ?: $fieldId,
  						'title' => $field['name'] ?? '',
  						'dataFormat' => $field['dataFormat'] ?? 'text',
  						'isRequired' => (bool) ($field['isRequired'] ?? false),
  					];
  				}

  				\set_transient($cacheKey, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
  			}
  		}

  		return $output[$itemId] ?? [];
  	}

  	/**
  	 * API request to post application to Pardot form handler.
  	 *
  	 * @param array<string, mixed> $formDetails Form details.
  	 *
  	 * @return array<string, mixed>
  	 */
  	public function postApplication(array $formDetails): array
  	{
  		$params = $formDetails[Config::FD_PARAMS];
  		$files = $formDetails[Config::FD_FILES];
  		$formId = $formDetails[Config::FD_FORM_ID];
  		$itemId = $formDetails[Config::FD_ITEM_ID];

  		// Filter override post request.
  		$filterName = HooksHelpers::getFilterName(['integrations', SettingsPardot::SETTINGS_TYPE_KEY, 'overridePostRequest']);
  		if (\has_filter($filterName)) {
  			$filterValue = \apply_filters($filterName, [], $itemId, $params, $files, $formId) ?? [];

  			if ($filterValue) {
  				return $filterValue;
  			}
  		}

  		// Get handler submit URL from cache.
  		$handler = $this->getItems()[$itemId] ?? [];
  		$url = $handler['submitUrl'] ?? '';

  		if (!$url) {
  			return ApiHelpers::getIntegrationErrorInternalOutput([
  				Config::IARD_CODE => 400,
  				Config::IARD_BODY => [],
  				Config::IARD_MSG => SettingsFallback::SETTINGS_FALLBACK_FLAG_PARDOT_MISSING_CONFIG,
  				Config::IARD_STATUS => 'error',
  				Config::IARD_VALIDATION => [],
  			]);
  		}

  		$body = $this->prepareParams($params);

  		$response = \wp_remote_post(
  			$url,
  			[
  				'headers' => [
  					'Content-Type' => 'application/x-www-form-urlencoded',
  				],
  				'body' => $body,
  				'redirection' => 0,
  			]
  		);

  		$details = ApiHelpers::getIntegrationApiResponseDetails(
  			SettingsPardot::SETTINGS_TYPE_KEY,
  			$response,
  			$url,
  			$body,
  			[],
  			$itemId,
  			$formId
  		);

  		$code = $details[Config::IARD_CODE];

  		// Form handlers respond with 302 redirect on success; wp_remote_post with redirection=0
  		// returns the redirect itself. Treat 2xx and 3xx as success.
  		if ($code >= 200 && $code < 400) {
  			return ApiHelpers::getIntegrationSuccessInternalOutput($details);
  		}

  		$details[Config::IARD_MSG] = $this->getErrorMsg($details[Config::IARD_BODY]);

  		return ApiHelpers::getIntegrationErrorInternalOutput($details);
  	}

  	/**
  	 * Test API connection.
  	 *
  	 * @return array<mixed>
  	 */
  	public function getTestApi(): array
  	{
  		$url = $this->getBaseUrl('form-handlers') . '&fields=id,name&pageSize=1';

  		$response = \wp_remote_get(
  			$url,
  			[
  				'headers' => $this->getHeaders(),
  			]
  		);

  		$details = ApiHelpers::getIntegrationApiResponseDetails(
  			SettingsPardot::SETTINGS_TYPE_KEY,
  			$response,
  			$url,
  		);

  		$body = $details[Config::IARD_BODY];

  		if ($this->oauthPardot->hasTokenExpired($body)) {
  			$refreshToken = $this->oauthPardot->getRefreshToken();

  			if ($refreshToken) {
  				return $this->getTestApi();
  			}
  		}

  		return ApiHelpers::getIntegrationApiResponseDetails(
  			SettingsPardot::SETTINGS_TYPE_KEY,
  			$response,
  			$url,
  		);
  	}

  	/**
  	 * Map service error to fallback flag.
  	 *
  	 * @param array<mixed> $body Response body.
  	 *
  	 * @return string
  	 */
  	private function getErrorMsg(array $body): string
  	{
  		$errorCode = $body['errorCode'] ?? '';

  		switch ($errorCode) {
  			case 'INVALID_SESSION_ID':
  				return SettingsFallback::SETTINGS_FALLBACK_FLAG_PARDOT_ERROR_SETTINGS_MISSING;
  			case 'SERVER_ERROR':
  				return SettingsFallback::SETTINGS_FALLBACK_FLAG_PARDOT_SERVER_ERROR;
  			case 'BAD_REQUEST':
  				return SettingsFallback::SETTINGS_FALLBACK_FLAG_PARDOT_BAD_REQUEST_ERROR;
  			default:
  				return SettingsFallback::SETTINGS_FALLBACK_FLAG_SUBMIT_INTEGRATION_ERROR_WP;
  		}
  	}

  	/**
  	 * Set headers for Pardot data-host calls.
  	 *
  	 * @return array<string, string>
  	 */
  	private function getHeaders(): array
  	{
  		$accessToken = SettingsHelpers::getOptionValue(OauthPardot::OAUTH_PARDOT_ACCESS_TOKEN_KEY);
  		$businessUnitId = SettingsHelpers::getOptionWithConstant(Variables::getBusinessUnitIdPardot(), SettingsPardot::SETTINGS_PARDOT_BUSINESS_UNIT_ID);

  		return [
  			'Content-Type' => 'application/json',
  			'Authorization' => "Bearer {$accessToken}",
  			'Pardot-Business-Unit-Id' => $businessUnitId,
  		];
  	}

  	/**
  	 * Build Pardot API base URL for an object endpoint.
  	 *
  	 * @param string $object Object name (e.g. 'form-handlers').
  	 *
  	 * @return string
  	 */
  	private function getBaseUrl(string $object): string
  	{
  		return $this->oauthPardot->getApiUrl('api/' . self::API_VERSION . '/objects/' . $object . '?');
  	}

  	/**
  	 * Fetch all form handlers from Pardot API.
  	 *
  	 * @return array<mixed>
  	 */
  	private function getPardotFormHandlers(): array
  	{
  		$url = $this->getBaseUrl('form-handlers') . 'fields=id,name,embedCode&orderBy=name&pageSize=200';

  		$response = \wp_remote_get(
  			$url,
  			[
  				'headers' => $this->getHeaders(),
  			]
  		);

  		$details = ApiHelpers::getIntegrationApiResponseDetails(
  			SettingsPardot::SETTINGS_TYPE_KEY,
  			$response,
  			$url,
  		);

  		$code = $details[Config::IARD_CODE];
  		$body = $details[Config::IARD_BODY];

  		if ($this->oauthPardot->hasTokenExpired($body)) {
  			$refreshToken = $this->oauthPardot->getRefreshToken();

  			if ($refreshToken) {
  				return $this->getPardotFormHandlers();
  			}
  		}

  		if (ApiHelpers::isSuccessResponse($code)) {
  			return $body['values'] ?? [];
  		}

  		return [];
  	}

  	/**
  	 * Fetch fields for a specific form handler.
  	 *
  	 * @param string $handlerId Handler ID.
  	 *
  	 * @return array<mixed>
  	 */
  	private function getPardotFormHandlerFields(string $handlerId): array
  	{
  		$url = $this->getBaseUrl('form-handler-fields') . 'fields=id,name,dataFormat,isRequired,prospectApiFieldId&formHandlerId=' . \urlencode($handlerId);

  		$response = \wp_remote_get(
  			$url,
  			[
  				'headers' => $this->getHeaders(),
  			]
  		);

  		$details = ApiHelpers::getIntegrationApiResponseDetails(
  			SettingsPardot::SETTINGS_TYPE_KEY,
  			$response,
  			$url,
  		);

  		$code = $details[Config::IARD_CODE];
  		$body = $details[Config::IARD_BODY];

  		if ($this->oauthPardot->hasTokenExpired($body)) {
  			$refreshToken = $this->oauthPardot->getRefreshToken();

  			if ($refreshToken) {
  				return $this->getPardotFormHandlerFields($handlerId);
  			}
  		}

  		if (ApiHelpers::isSuccessResponse($code)) {
  			return $body['values'] ?? [];
  		}

  		return [];
  	}

  	/**
  	 * Parse the form handler POST URL from its embed code.
  	 *
  	 * @param string $embedCode Embed code HTML string.
  	 *
  	 * @return string
  	 */
  	private function parseSubmitUrl(string $embedCode): string
  	{
  		if (!$embedCode) {
  			return '';
  		}

  		\preg_match('/action=["\']([^"\']+)["\']/', $embedCode, $matches);

  		return $matches[1] ?? '';
  	}

  	/**
  	 * Prepare params for form-encoded POST to handler URL.
  	 *
  	 * @param array<string, mixed> $params Form params.
  	 *
  	 * @return string URL-encoded body string.
  	 */
  	private function prepareParams(array $params): string
  	{
  		$params = GeneralHelpers::removeUnnecessaryParamFields($params);
  		$output = [];

  		foreach ($params as $param) {
  			$value = $param['value'] ?? '';
  			$name = $param['name'] ?? '';

  			if (!$name) {
  				continue;
  			}

  			if (\is_array($value)) {
  				$value = \implode(',', $value);
  			}

  			if (\is_string($value)) {
  				$value = \wp_strip_all_tags($value);
  			}

  			$output[$name] = $value;
  		}

  		return \http_build_query($output);
  	}
  }
  ```

- [ ] **Step 3: Commit**

  ```bash
  git add src/Integrations/Pardot/PardotClientInterface.php src/Integrations/Pardot/PardotClient.php
  git commit -m "feat: add PardotClient — form handlers, fields, submit via form handler URL"
  ```

---

## Task 4: Pardot mapper (Pardot.php)

**Files:**

- Create: `src/Integrations/Pardot/Pardot.php`

**Interfaces:**

- Consumes: `PardotClientInterface::getItem(string $itemId): array` (Task 3)
- Produces: `Pardot::FILTER_FORM_FIELDS_NAME = 'es_pardot_form_fields_filter'`
- Produces: `Pardot::getFormFields(string $formId, string $itemId, string $innerId): array`

- [ ] **Step 1: Create Pardot.php**

  ```php
  <?php

  /**
   * Pardot integration class.
   *
   * @package EightshiftForms\Integrations\Pardot
   */

  declare(strict_types=1);

  namespace EightshiftForms\Integrations\Pardot;

  use EightshiftForms\Form\AbstractFormBuilder;
  use EightshiftForms\Integrations\MapperInterface;
  use EightshiftForms\Helpers\HooksHelpers;
  use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

  /**
   * Pardot integration class.
   */
  class Pardot extends AbstractFormBuilder implements MapperInterface, ServiceInterface
  {
  	/**
  	 * Filter form fields.
  	 *
  	 * @var string
  	 */
  	public const FILTER_FORM_FIELDS_NAME = 'es_pardot_form_fields_filter';

  	/**
  	 * Instance variable for Pardot data.
  	 *
  	 * @var PardotClientInterface
  	 */
  	protected $pardotClient;

  	/**
  	 * Create a new instance.
  	 *
  	 * @param PardotClientInterface $pardotClient Inject Pardot client.
  	 */
  	public function __construct(PardotClientInterface $pardotClient)
  	{
  		$this->pardotClient = $pardotClient;
  	}

  	/**
  	 * Register all the hooks
  	 *
  	 * @return void
  	 */
  	public function register(): void
  	{
  		\add_filter(static::FILTER_FORM_FIELDS_NAME, [$this, 'getFormFields'], 10, 3);
  	}

  	/**
  	 * Get mapped form fields from integration.
  	 *
  	 * @param string $formId Form Id.
  	 * @param string $itemId Integration/external form ID (form handler ID).
  	 * @param string $innerId Unused for Pardot (single-level).
  	 *
  	 * @return array<string, array<int, array<string, mixed>>|string>
  	 */
  	public function getFormFields(string $formId, string $itemId, string $innerId): array
  	{
  		$output = [
  			'type' => SettingsPardot::SETTINGS_TYPE_KEY,
  			'itemId' => $itemId,
  			'innerId' => $innerId,
  			'fields' => [],
  		];

  		$fields = $this->pardotClient->getItem($itemId);

  		if (empty($fields)) {
  			return $output;
  		}

  		$mapped = $this->getFields($fields, $formId);

  		if (!$mapped) {
  			return $output;
  		}

  		$output['fields'] = $mapped;

  		return $output;
  	}

  	/**
  	 * Map Pardot handler fields to Eightshift form components.
  	 *
  	 * @param array<string, mixed> $fields Fields from PardotClient::getItem().
  	 * @param string $formId Form ID.
  	 *
  	 * @return array<int, array<string, mixed>>
  	 */
  	private function getFields(array $fields, string $formId): array
  	{
  		$output = [];

  		foreach ($fields as $field) {
  			if (empty($field)) {
  				continue;
  			}

  			$name = $field['id'] ?? '';       // prospectApiFieldId used as field name for POST
  			$label = $field['title'] ?? '';   // display name shown to editor
  			$dataFormat = \strtolower($field['dataFormat'] ?? 'text');
  			$isRequired = (bool) ($field['isRequired'] ?? false);

  			if (!$name) {
  				continue;
  			}

  			switch ($dataFormat) {
  				case 'email':
  					$output[] = [
  						'component' => 'input',
  						'inputName' => $name,
  						'inputTracking' => $name,
  						'inputFieldLabel' => $label,
  						'inputType' => 'email',
  						'inputIsEmail' => true,
  						'inputTypeCustom' => 'email',
  						'inputIsRequired' => $isRequired,
  						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
  							'inputIsEmail',
  							'inputType',
  							'inputTypeCustom',
  						]),
  					];
  					break;
  				case 'number':
  					$output[] = [
  						'component' => 'input',
  						'inputName' => $name,
  						'inputTracking' => $name,
  						'inputFieldLabel' => $label,
  						'inputType' => 'number',
  						'inputIsNumber' => true,
  						'inputTypeCustom' => 'number',
  						'inputIsRequired' => $isRequired,
  						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
  							'inputIsNumber',
  							'inputType',
  							'inputTypeCustom',
  						]),
  					];
  					break;
  				case 'phone':
  				case 'tel':
  					$output[] = [
  						'component' => 'phone',
  						'phoneName' => $name,
  						'phoneTracking' => $name,
  						'phoneFieldLabel' => $label,
  						'phoneTypeCustom' => 'phone',
  						'phoneIsNumber' => true,
  						'phoneIsRequired' => $isRequired,
  						'phoneDisabledOptions' => $this->prepareDisabledOptions('phone', [
  							'phoneTypeCustom',
  							'phoneIsNumber',
  						]),
  					];
  					break;
  				case 'date':
  					$output[] = [
  						'component' => 'date',
  						'dateName' => $name,
  						'dateTracking' => $name,
  						'dateFieldLabel' => $label,
  						'dateType' => 'date',
  						'datePreviewFormat' => 'F j, Y',
  						'dateOutputFormat' => 'Y-m-d',
  						'dateIsRequired' => $isRequired,
  						'dateDisabledOptions' => $this->prepareDisabledOptions('date', [
  							'dateType',
  							'dateOutputFormat',
  						]),
  					];
  					break;
  				case 'textarea':
  				case 'text area':
  					$output[] = [
  						'component' => 'textarea',
  						'textareaName' => $name,
  						'textareaTracking' => $name,
  						'textareaFieldLabel' => $label,
  						'textareaIsRequired' => $isRequired,
  						'textareaDisabledOptions' => $this->prepareDisabledOptions('textarea'),
  					];
  					break;
  				default:
  					// text / string / unknown — default to plain input
  					$output[] = [
  						'component' => 'input',
  						'inputName' => $name,
  						'inputTracking' => $name,
  						'inputFieldLabel' => $label,
  						'inputType' => 'text',
  						'inputIsRequired' => $isRequired,
  						'inputDisabledOptions' => $this->prepareDisabledOptions('input'),
  					];
  					break;
  			}
  		}

  		$output[] = [
  			'component' => 'submit',
  			'submitName' => 'submit',
  			'submitFieldUseError' => false,
  		];

  		// Allow filter to modify the final output.
  		$filterName = HooksHelpers::getFilterName(['integrations', SettingsPardot::SETTINGS_TYPE_KEY, 'data']);
  		if (\has_filter($filterName)) {
  			$output = \apply_filters($filterName, $output, $formId) ?? [];
  		}

  		return $output;
  	}
  }
  ```

- [ ] **Step 2: Commit**

  ```bash
  git add src/Integrations/Pardot/Pardot.php
  git commit -m "feat: add Pardot mapper — auto-builds form components from handler fields"
  ```

---

## Task 5: SettingsPardot

**Files:**

- Create: `src/Integrations/Pardot/SettingsPardot.php`

**Interfaces:**

- Consumes: `OauthPardot::OAUTH_PARDOT_ACCESS_TOKEN_KEY`, `getOauthAuthorizeUrl()` (Task 2)
- Consumes: `Variables::getClientIdPardot()`, `getClientSecretPardot()`, `getBusinessUnitIdPardot()` (Task 1)
- Produces: `SettingsPardot::SETTINGS_TYPE_KEY = 'pardot'`
- Produces: `SettingsPardot::SETTINGS_PARDOT_CLIENT_ID`, `SETTINGS_PARDOT_SECRET`, `SETTINGS_PARDOT_BUSINESS_UNIT_ID`, `SETTINGS_PARDOT_ENVIRONMENT_KEY`, `SETTINGS_PARDOT_USE_KEY`, `SETTINGS_PARDOT_SKIP_INTEGRATION_KEY`, `SETTINGS_PARDOT_OAUTH_ALLOW_KEY`
- Produces: `SettingsPardot::FILTER_SETTINGS_GLOBAL_NAME`, `FILTER_SETTINGS_GLOBAL_IS_VALID_NAME`
- Produces: `SettingsPardot::isSettingsGlobalValid(): bool`

- [ ] **Step 1: Create SettingsPardot.php**

  ```php
  <?php

  /**
   * Pardot Settings class.
   *
   * @package EightshiftForms\Integrations\Pardot
   */

  declare(strict_types=1);

  namespace EightshiftForms\Integrations\Pardot;

  use EightshiftForms\Helpers\SettingsHelpers;
  use EightshiftForms\Hooks\Variables;
  use EightshiftForms\Settings\SettingGlobalInterface;
  use EightshiftForms\Helpers\SettingsOutputHelpers;
  use EightshiftForms\Integrations\AbstractSettingsIntegrations;
  use EightshiftForms\Oauth\OauthInterface;
  use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
  use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

  /**
   * SettingsPardot class.
   */
  class SettingsPardot extends AbstractSettingsIntegrations implements SettingGlobalInterface, ServiceInterface
  {
  	/**
  	 * Filter global settings key.
  	 */
  	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_pardot';

  	/**
  	 * Filter settings global is Valid key.
  	 */
  	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_pardot';

  	/**
  	 * Settings key.
  	 */
  	public const SETTINGS_TYPE_KEY = 'pardot';

  	/**
  	 * Pardot Use key.
  	 */
  	public const SETTINGS_PARDOT_USE_KEY = 'pardot-use';

  	/**
  	 * Client ID key.
  	 */
  	public const SETTINGS_PARDOT_CLIENT_ID = 'pardot-client-id';

  	/**
  	 * Client Secret key.
  	 */
  	public const SETTINGS_PARDOT_SECRET = 'pardot-client-secret';

  	/**
  	 * Business Unit ID key.
  	 */
  	public const SETTINGS_PARDOT_BUSINESS_UNIT_ID = 'pardot-business-unit-id';

  	/**
  	 * Environment key (production|sandbox).
  	 */
  	public const SETTINGS_PARDOT_ENVIRONMENT_KEY = 'pardot-environment';

  	/**
  	 * Skip integration key.
  	 */
  	public const SETTINGS_PARDOT_SKIP_INTEGRATION_KEY = 'pardot-skip-integration';

  	/**
  	 * OAuth allow key.
  	 */
  	public const SETTINGS_PARDOT_OAUTH_ALLOW_KEY = 'pardot-oauth-allow';

  	/**
  	 * Instance variable for Fallback settings.
  	 *
  	 * @var SettingsFallbackDataInterface
  	 */
  	protected $settingsFallback;

  	/**
  	 * Instance variable for Oauth.
  	 *
  	 * @var OauthInterface
  	 */
  	protected $oauthPardot;

  	/**
  	 * Create a new instance.
  	 *
  	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback methods.
  	 * @param OauthInterface $oauthPardot Inject Oauth methods.
  	 */
  	public function __construct(
  		SettingsFallbackDataInterface $settingsFallback,
  		OauthInterface $oauthPardot,
  	) {
  		$this->settingsFallback = $settingsFallback;
  		$this->oauthPardot = $oauthPardot;
  	}

  	/**
  	 * Register all the hooks
  	 *
  	 * @return void
  	 */
  	public function register(): void
  	{
  		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
  		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
  	}

  	/**
  	 * Determine if settings global are valid.
  	 *
  	 * @return boolean
  	 */
  	public function isSettingsGlobalValid(): bool
  	{
  		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_PARDOT_USE_KEY, self::SETTINGS_PARDOT_USE_KEY);
  		$clientId = (bool) SettingsHelpers::getOptionWithConstant(Variables::getClientIdPardot(), self::SETTINGS_PARDOT_CLIENT_ID);
  		$clientSecret = (bool) SettingsHelpers::getOptionWithConstant(Variables::getClientSecretPardot(), self::SETTINGS_PARDOT_SECRET);
  		$businessUnitId = (bool) SettingsHelpers::getOptionWithConstant(Variables::getBusinessUnitIdPardot(), self::SETTINGS_PARDOT_BUSINESS_UNIT_ID);

  		if (!$isUsed || !$clientId || !$clientSecret || !$businessUnitId) {
  			return false;
  		}

  		return true;
  	}

  	/**
  	 * Get global settings array for building settings page.
  	 *
  	 * @return array<int, array<string, mixed>>
  	 */
  	public function getSettingsGlobalData(): array
  	{
  		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_PARDOT_USE_KEY, self::SETTINGS_PARDOT_USE_KEY)) {
  			return SettingsOutputHelpers::getNoActiveFeature();
  		}

  		$deactivateIntegration = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_PARDOT_SKIP_INTEGRATION_KEY, self::SETTINGS_PARDOT_SKIP_INTEGRATION_KEY);

  		return [
  			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
  			[
  				'component' => 'tabs',
  				'tabsContent' => [
  					[
  						'component' => 'tab',
  						'tabLabel' => \__('General', 'eightshift-forms'),
  						'tabContent' => [
  							[
  								'component' => 'checkboxes',
  								'checkboxesFieldLabel' => '',
  								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_PARDOT_SKIP_INTEGRATION_KEY),
  								'checkboxesContent' => [
  									[
  										'component' => 'checkbox',
  										'checkboxLabel' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxLabel'),
  										'checkboxHelp' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxHelp'),
  										'checkboxIsChecked' => $deactivateIntegration,
  										'checkboxValue' => self::SETTINGS_PARDOT_SKIP_INTEGRATION_KEY,
  										'checkboxSingleSubmit' => true,
  										'checkboxAsToggle' => true,
  									]
  								]
  							],
  							...($deactivateIntegration ? [
  								[
  									'component' => 'intro',
  									'introSubtitle' => SettingsOutputHelpers::getPartialDeactivatedIntegration('introSubtitle'),
  									'introIsHighlighted' => true,
  									'introIsHighlightedImportant' => true,
  								],
  							] : [
  								[
  									'component' => 'divider',
  									'dividerExtraVSpacing' => true,
  								],
  								SettingsOutputHelpers::getPasswordFieldWithGlobalVariable(
  									Variables::getClientIdPardot(),
  									self::SETTINGS_PARDOT_CLIENT_ID,
  									'ES_CLIENT_ID_PARDOT',
  									\__('Consumer Key (Client ID)', 'eightshift-forms'),
  								),
  								[
  									'component' => 'divider',
  									'dividerExtraVSpacing' => true,
  								],
  								SettingsOutputHelpers::getPasswordFieldWithGlobalVariable(
  									Variables::getClientSecretPardot(),
  									self::SETTINGS_PARDOT_SECRET,
  									'ES_CLIENT_SECRET_PARDOT',
  									\__('Consumer Secret (Client Secret)', 'eightshift-forms'),
  								),
  								[
  									'component' => 'divider',
  									'dividerExtraVSpacing' => true,
  								],
  								SettingsOutputHelpers::getInputFieldWithGlobalVariable(
  									Variables::getBusinessUnitIdPardot(),
  									self::SETTINGS_PARDOT_BUSINESS_UNIT_ID,
  									'ES_BUSINESS_UNIT_ID_PARDOT',
  									\__('Business Unit ID', 'eightshift-forms'),
  								),
  								[
  									'component' => 'divider',
  									'dividerExtraVSpacing' => true,
  								],
  								[
  									'component' => 'select',
  									'selectName' => SettingsHelpers::getOptionName(self::SETTINGS_PARDOT_ENVIRONMENT_KEY),
  									'selectFieldLabel' => \__('Environment', 'eightshift-forms'),
  									'selectValue' => SettingsHelpers::getOptionValue(self::SETTINGS_PARDOT_ENVIRONMENT_KEY),
  									'selectContent' => [
  										[
  											'component' => 'select-option',
  											'selectOptionLabel' => \__('Production', 'eightshift-forms'),
  											'selectOptionValue' => 'production',
  											'selectOptionIsSelected' => SettingsHelpers::getOptionValue(self::SETTINGS_PARDOT_ENVIRONMENT_KEY) !== 'sandbox',
  										],
  										[
  											'component' => 'select-option',
  											'selectOptionLabel' => \__('Sandbox', 'eightshift-forms'),
  											'selectOptionValue' => 'sandbox',
  											'selectOptionIsSelected' => SettingsHelpers::getOptionValue(self::SETTINGS_PARDOT_ENVIRONMENT_KEY) === 'sandbox',
  										],
  									],
  								],
  								[
  									'component' => 'divider',
  									'dividerExtraVSpacing' => true,
  								],
  								SettingsOutputHelpers::getOauthConnection($this->oauthPardot->getOauthAuthorizeUrl(), OauthPardot::OAUTH_PARDOT_ACCESS_TOKEN_KEY, self::SETTINGS_PARDOT_OAUTH_ALLOW_KEY),
  								[
  									'component' => 'divider',
  									'dividerExtraVSpacing' => true,
  								],
  								SettingsOutputHelpers::getTestApiConnection(self::SETTINGS_TYPE_KEY),
  							]),
  						],
  					],
  					[
  						'component' => 'tab',
  						'tabLabel' => \__('Options', 'eightshift-forms'),
  						'tabContent' => [
  							...$this->getGlobalGeneralSettings(self::SETTINGS_TYPE_KEY),
  						],
  					],
  					$this->settingsFallback->getOutputGlobalFallback(SettingsPardot::SETTINGS_TYPE_KEY),
  					[
  						'component' => 'tab',
  						'tabLabel' => \__('Help', 'eightshift-forms'),
  						'tabContent' => [
  							[
  								'component' => 'steps',
  								'stepsTitle' => \__('How to connect to Pardot?', 'eightshift-forms'),
  								'stepsContent' => [
  									\__('Log in to your Salesforce org.', 'eightshift-forms'),
  									\__('Go to <strong>Setup → Apps → App Manager</strong> and find your Connected App.', 'eightshift-forms'),
  									\__('Copy the <strong>Consumer Key</strong> and <strong>Consumer Secret</strong> into the fields above.', 'eightshift-forms'),
  									\__('Go to <strong>Marketing Setup → Business Unit Setup</strong> and copy the <strong>Business Unit ID</strong> (starts with <code>0Uv</code>).', 'eightshift-forms'),
  									// translators: %s will be replaced with the site URL.
  									\sprintf(\__('In the Connected App, set the OAuth Callback URL to <br/><code>%s/wp-json/eightshift-forms/v1/oauth/pardot</code>', 'eightshift-forms'), \get_site_url()),
  									\__('Save your settings here, then click <strong>Connect with Salesforce</strong> to authorise.', 'eightshift-forms'),
  								],
  							],
  						],
  					],
  				],
  			],
  		];
  	}
  }
  ```

- [ ] **Step 2: Commit**

  ```bash
  git add src/Integrations/Pardot/SettingsPardot.php
  git commit -m "feat: add SettingsPardot — global admin settings with OAuth connect, BUID, env toggle"
  ```

---

## Task 6: REST Routes

**Files:**

- Create: `src/Rest/Routes/Integrations/Pardot/OauthPardotRoute.php`
- Create: `src/Rest/Routes/Integrations/Pardot/IntegrationItemsPardotRoute.php`
- Create: `src/Rest/Routes/Integrations/Pardot/FormSubmitPardotRoute.php`
- Create: `src/Rest/Routes/Integrations/Pardot/TestApiPardotRoute.php`

**Interfaces:**

- Consumes: `OauthPardot::getAccessToken()`, `SettingsPardot::SETTINGS_TYPE_KEY`, `SETTINGS_PARDOT_OAUTH_ALLOW_KEY`, `FILTER_SETTINGS_GLOBAL_IS_VALID_NAME`, `SETTINGS_PARDOT_SKIP_INTEGRATION_KEY` (Tasks 2, 5)
- Consumes: `PardotClientInterface::getItems()`, `postApplication()`, `getTestApi()` (Task 3)
- Consumes: `SettingsFallback::SETTINGS_FALLBACK_FLAG_PARDOT_MISSING_CONFIG` (Task 1)

- [ ] **Step 1: Create OauthPardotRoute.php**

  ```php
  <?php

  /**
   * OAuth callback route for Pardot.
   *
   * @package EightshiftForms\Rest\Routes\Integrations\Pardot
   */

  declare(strict_types=1);

  namespace EightshiftForms\Rest\Routes\Integrations\Pardot;

  use EightshiftForms\Integrations\Pardot\SettingsPardot;
  use EightshiftForms\Oauth\OauthInterface;
  use EightshiftForms\Rest\Routes\AbstractOauth;

  /**
   * Class OauthPardotRoute
   */
  class OauthPardotRoute extends AbstractOauth
  {
  	/**
  	 * Route slug.
  	 */
  	public const ROUTE_SLUG = 'pardot';

  	/**
  	 * Instance variable for Oauth.
  	 *
  	 * @var OauthInterface
  	 */
  	protected $oauthPardot;

  	/**
  	 * Create a new instance that injects classes
  	 *
  	 * @param OauthInterface $oauthPardot Inject Oauth methods.
  	 */
  	public function __construct(OauthInterface $oauthPardot)
  	{
  		$this->oauthPardot = $oauthPardot;
  	}

  	/**
  	 * Get the base url of the route
  	 *
  	 * @return string The base URL for route you are adding.
  	 */
  	protected function getRouteName(): string
  	{
  		return '/' . AbstractOauth::ROUTE_PREFIX_OAUTH_API . '/' . self::ROUTE_SLUG;
  	}

  	/**
  	 * Get the oauth type.
  	 *
  	 * @return string
  	 */
  	protected function getOauthType(): string
  	{
  		return SettingsPardot::SETTINGS_TYPE_KEY;
  	}

  	/**
  	 * Get the oauth allow key.
  	 *
  	 * @return string
  	 */
  	protected function getOauthAllowKey(): string
  	{
  		return SettingsPardot::SETTINGS_PARDOT_OAUTH_ALLOW_KEY;
  	}

  	/**
  	 * Check if the route is admin protected.
  	 *
  	 * @return boolean
  	 */
  	protected function isRouteAdminProtected(): bool
  	{
  		return false;
  	}

  	/**
  	 * Get mandatory params.
  	 *
  	 * @param array<string, mixed> $params Params passed from the request.
  	 *
  	 * @return array<string, string>
  	 */
  	protected function getMandatoryParams(array $params): array
  	{
  		return [];
  	}

  	/**
  	 * Implement submit action.
  	 *
  	 * @param string $code The code.
  	 *
  	 * @return mixed
  	 */
  	protected function submitAction(string $code)
  	{
  		$response = $this->oauthPardot->getAccessToken($code);

  		if ($response) {
  			$this->redirect(
  				\esc_html__('OAuth connection successful', 'eightshift-forms'),
  			);
  		}

  		$this->redirect(
  			\esc_html__('OAuth connection failed', 'eightshift-forms'),
  		);
  	}
  }
  ```

- [ ] **Step 2: Create IntegrationItemsPardotRoute.php**

  ```php
  <?php

  /**
   * Integration items route for Pardot (form handlers dropdown).
   *
   * @package EightshiftForms\Rest\Routes\Integrations\Pardot
   */

  declare(strict_types=1);

  namespace EightshiftForms\Rest\Routes\Integrations\Pardot;

  use EightshiftForms\Integrations\Pardot\PardotClientInterface;
  use EightshiftForms\Integrations\Pardot\SettingsPardot;
  use EightshiftForms\Config\Config;
  use EightshiftForms\Exception\BadRequestException;
  use EightshiftForms\Helpers\UtilsHelper;
  use EightshiftForms\Labels\LabelsInterface;
  use EightshiftForms\Rest\Routes\AbstractBaseRoute;
  use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
  use EightshiftForms\Security\SecurityInterface;
  use EightshiftForms\Validation\ValidatorInterface;

  /**
   * Class IntegrationItemsPardotRoute
   */
  class IntegrationItemsPardotRoute extends AbstractSimpleFormSubmit
  {
  	/**
  	 * Instance variable for Pardot data.
  	 *
  	 * @var PardotClientInterface
  	 */
  	protected $pardotClient;

  	/**
  	 * Route slug.
  	 */
  	public const ROUTE_SLUG = SettingsPardot::SETTINGS_TYPE_KEY;

  	/**
  	 * Get the base url of the route
  	 *
  	 * @return string The base URL for route you are adding.
  	 */
  	protected function getRouteName(): string
  	{
  		return '/' . Config::ROUTE_PREFIX_INTEGRATION_ITEMS . '/' . self::ROUTE_SLUG;
  	}

  	/**
  	 * Create a new instance that injects classes
  	 *
  	 * @param SecurityInterface $security Inject security methods.
  	 * @param ValidatorInterface $validator Inject validator methods.
  	 * @param LabelsInterface $labels Inject labels methods.
  	 * @param PardotClientInterface $pardotClient Inject Pardot client.
  	 */
  	public function __construct(
  		SecurityInterface $security,
  		ValidatorInterface $validator,
  		LabelsInterface $labels,
  		PardotClientInterface $pardotClient
  	) {
  		$this->security = $security;
  		$this->validator = $validator;
  		$this->labels = $labels;
  		$this->pardotClient = $pardotClient;
  	}

  	/**
  	 * Returns allowed methods for this route.
  	 *
  	 * @return string
  	 */
  	protected function getMethods(): string
  	{
  		return static::READABLE;
  	}

  	/**
  	 * Check if the route is admin protected.
  	 *
  	 * @return boolean
  	 */
  	protected function isRouteAdminProtected(): bool
  	{
  		return true;
  	}

  	/**
  	 * Get mandatory params.
  	 *
  	 * @param array<string, mixed> $params Params passed from the request.
  	 *
  	 * @return array<string, string>
  	 */
  	protected function getMandatoryParams(array $params): array
  	{
  		return [];
  	}

  	/**
  	 * Implement submit action.
  	 *
  	 * @param array<string, mixed> $params Prepared params.
  	 *
  	 * @throws BadRequestException If Pardot is not configured.
  	 *
  	 * @return array<string, mixed>
  	 */
  	protected function submitAction(array $params): array
  	{
  		if (!\apply_filters(SettingsPardot::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
  			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
  			throw new BadRequestException(
  				$this->getLabels()->getLabel('globalNotConfigured'),
  				[
  					AbstractBaseRoute::R_DEBUG_KEY => 'integrationItemsGlobalNotConfigured',
  				]
  			);
  			// phpcs:enable
  		}

  		$items = $this->pardotClient->getItems();

  		if (!$items) {
  			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
  			throw new BadRequestException(
  				$this->getLabels()->getLabel('integrationItemsMissing'),
  				[
  					AbstractBaseRoute::R_DEBUG => $items,
  					AbstractBaseRoute::R_DEBUG_KEY => 'integrationItemsMissingItems',
  				]
  			);
  			// phpcs:enable
  		}

  		$items = \array_filter(\array_values(\array_map(
  			static function ($item) {
  				$id = $item['id'] ?? '';

  				if ($id) {
  					return [
  						'label' => $item['title'] ?? \__('No title', 'eightshift-forms'),
  						'value' => $id,
  					];
  				}
  			},
  			$items
  		)));

  		return [
  			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('integrationItemsSuccess'),
  			AbstractBaseRoute::R_DEBUG => [
  				AbstractBaseRoute::R_DEBUG => $items,
  				AbstractBaseRoute::R_DEBUG_KEY => 'integrationItemsSuccess',
  			],
  			AbstractBaseRoute::R_DATA => [
  				UtilsHelper::getStateResponseOutputKey('editorIntegrationItems') => $items,
  			],
  		];
  	}
  }
  ```

- [ ] **Step 3: Create FormSubmitPardotRoute.php**

  ```php
  <?php

  /**
   * Form submit route for Pardot.
   *
   * @package EightshiftForms\Rest\Routes\Integrations\Pardot
   */

  declare(strict_types=1);

  namespace EightshiftForms\Rest\Routes\Integrations\Pardot;

  use EightshiftForms\Captcha\CaptchaInterface;
  use EightshiftForms\Enrichment\EnrichmentInterface;
  use EightshiftForms\Integrations\Pardot\PardotClientInterface;
  use EightshiftForms\Integrations\Pardot\SettingsPardot;
  use EightshiftForms\Labels\LabelsInterface;
  use EightshiftForms\Integrations\Mailer\MailerInterface;
  use EightshiftForms\Rest\Routes\AbstractIntegrationFormSubmit;
  use EightshiftForms\Security\SecurityInterface;
  use EightshiftForms\Validation\ValidatorInterface;
  use EightshiftForms\Config\Config;
  use EightshiftForms\Exception\BadRequestException;
  use EightshiftForms\Exception\DisabledIntegrationException;
  use EightshiftForms\Helpers\SettingsHelpers;
  use EightshiftForms\Rest\Routes\AbstractBaseRoute;
  use EightshiftForms\Troubleshooting\SettingsFallback;

  /**
   * Class FormSubmitPardotRoute
   */
  class FormSubmitPardotRoute extends AbstractIntegrationFormSubmit
  {
  	/**
  	 * Route slug.
  	 */
  	public const ROUTE_SLUG = SettingsPardot::SETTINGS_TYPE_KEY;

  	/**
  	 * Instance variable for Pardot data.
  	 *
  	 * @var PardotClientInterface
  	 */
  	protected $pardotClient;

  	/**
  	 * Create a new instance that injects classes
  	 *
  	 * @param SecurityInterface $security Inject security methods.
  	 * @param ValidatorInterface $validator Inject validator methods.
  	 * @param LabelsInterface $labels Inject labels methods.
  	 * @param CaptchaInterface $captcha Inject captcha methods.
  	 * @param MailerInterface $mailer Inject mailerInterface methods.
  	 * @param EnrichmentInterface $enrichment Inject enrichment methods.
  	 * @param PardotClientInterface $pardotClient Inject pardotClient methods.
  	 */
  	public function __construct(
  		SecurityInterface $security,
  		ValidatorInterface $validator,
  		LabelsInterface $labels,
  		CaptchaInterface $captcha,
  		MailerInterface $mailer,
  		EnrichmentInterface $enrichment,
  		PardotClientInterface $pardotClient
  	) {
  		$this->security = $security;
  		$this->validator = $validator;
  		$this->labels = $labels;
  		$this->captcha = $captcha;
  		$this->mailer = $mailer;
  		$this->enrichment = $enrichment;
  		$this->pardotClient = $pardotClient;
  	}

  	/**
  	 * Get the base url of the route
  	 *
  	 * @return string The base URL for route you are adding.
  	 */
  	protected function getRouteName(): string
  	{
  		return '/' . Config::ROUTE_PREFIX_FORM_SUBMIT . '/' . self::ROUTE_SLUG;
  	}

  	/**
  	 * Check if the route is admin protected.
  	 *
  	 * @return boolean
  	 */
  	protected function isRouteAdminProtected(): bool
  	{
  		return false;
  	}

  	/**
  	 * Get mandatory params.
  	 *
  	 * @param array<string, mixed> $params Params passed from the request.
  	 *
  	 * @return array<string, string>
  	 */
  	protected function getMandatoryParams(array $params): array
  	{
  		return [
  			Config::FD_FORM_ID => 'string',
  			Config::FD_POST_ID => 'string',
  			Config::FD_ITEM_ID => 'string',
  			Config::FD_PARAMS => 'array',
  		];
  	}

  	/**
  	 * Implement submit action.
  	 *
  	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
  	 *
  	 * @throws BadRequestException If Pardot is missing config.
  	 * @throws DisabledIntegrationException If Pardot is disabled.
  	 *
  	 * @return mixed
  	 */
  	protected function submitAction(array $formDetails)
  	{
  		if (SettingsHelpers::isOptionCheckboxChecked(SettingsPardot::SETTINGS_PARDOT_SKIP_INTEGRATION_KEY, SettingsPardot::SETTINGS_PARDOT_SKIP_INTEGRATION_KEY)) {
  			$integrationSuccessResponse = $this->getIntegrationResponseSuccessOutput($formDetails);

  			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
  			throw new DisabledIntegrationException(
  				$integrationSuccessResponse[AbstractBaseRoute::R_MSG],
  				$integrationSuccessResponse[AbstractBaseRoute::R_DEBUG],
  				$integrationSuccessResponse[AbstractBaseRoute::R_DATA]
  			);
  			// phpcs:enable
  		}

  		if (!\apply_filters(SettingsPardot::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
  			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
  			throw new BadRequestException(
  				$this->getLabels()->getLabel('pardotMissingConfig'),
  				[
  					AbstractBaseRoute::R_DEBUG => $formDetails,
  					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_PARDOT_MISSING_CONFIG,
  				],
  			);
  			// phpcs:enable
  		}

  		$response = $this->pardotClient->postApplication($formDetails);

  		$formDetails[Config::FD_RESPONSE_OUTPUT_DATA] = $response;

  		return $this->getIntegrationCommonSubmitAction($formDetails);
  	}
  }
  ```

- [ ] **Step 4: Create TestApiPardotRoute.php**

  ```php
  <?php

  /**
   * Test API route for Pardot.
   *
   * @package EightshiftForms\Rest\Routes\Integrations\Pardot
   */

  declare(strict_types=1);

  namespace EightshiftForms\Rest\Routes\Integrations\Pardot;

  use EightshiftForms\Config\Config;
  use EightshiftForms\Exception\BadRequestException;
  use EightshiftForms\Integrations\Pardot\PardotClientInterface;
  use EightshiftForms\Integrations\Pardot\SettingsPardot;
  use EightshiftForms\Labels\LabelsInterface;
  use EightshiftForms\Rest\Routes\AbstractBaseRoute;
  use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
  use EightshiftForms\Security\SecurityInterface;
  use EightshiftForms\Validation\ValidatorInterface;
  use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;

  /**
   * Class TestApiPardotRoute
   */
  class TestApiPardotRoute extends AbstractSimpleFormSubmit
  {
  	/**
  	 * Route slug.
  	 */
  	public const ROUTE_SLUG = SettingsPardot::SETTINGS_TYPE_KEY;

  	/**
  	 * Instance variable for Pardot data.
  	 *
  	 * @var PardotClientInterface
  	 */
  	protected $pardotClient;

  	/**
  	 * Create a new instance that injects classes
  	 *
  	 * @param SecurityInterface $security Inject security methods.
  	 * @param ValidatorInterface $validator Inject validator methods.
  	 * @param LabelsInterface $labels Inject labels methods.
  	 * @param PardotClientInterface $pardotClient Inject Pardot client.
  	 */
  	public function __construct(
  		SecurityInterface $security,
  		ValidatorInterface $validator,
  		LabelsInterface $labels,
  		PardotClientInterface $pardotClient
  	) {
  		$this->security = $security;
  		$this->validator = $validator;
  		$this->labels = $labels;
  		$this->pardotClient = $pardotClient;
  	}

  	/**
  	 * Get the base url of the route
  	 *
  	 * @return string The base URL for route you are adding.
  	 */
  	protected function getRouteName(): string
  	{
  		return '/' . Config::ROUTE_PREFIX_TEST_API . '/' . self::ROUTE_SLUG;
  	}

  	/**
  	 * Get mandatory params.
  	 *
  	 * @param array<string, mixed> $params Params passed from the request.
  	 *
  	 * @return array<string, string>
  	 */
  	protected function getMandatoryParams(array $params): array
  	{
  		return [
  			'type' => 'string',
  		];
  	}

  	/**
  	 * Check if the route is admin protected.
  	 *
  	 * @return boolean
  	 */
  	protected function isRouteAdminProtected(): bool
  	{
  		return true;
  	}

  	/**
  	 * Implement submit action.
  	 *
  	 * @param array<string, mixed> $params Prepared params.
  	 *
  	 * @throws BadRequestException If Pardot is not configured.
  	 *
  	 * @return array<string, mixed>
  	 */
  	protected function submitAction(array $params): array
  	{
  		$output = $this->pardotClient->getTestApi();

  		if ($output[Config::IARD_STATUS] === AbstractRoute::STATUS_ERROR) {
  			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
  			throw new BadRequestException(
  				$this->getLabels()->getLabel('testApiError'),
  				[
  					AbstractBaseRoute::R_DEBUG => $output,
  					AbstractBaseRoute::R_DEBUG_KEY => 'testApiError',
  				]
  			);
  			// phpcs:enable
  		}

  		return [
  			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('testApiSuccess'),
  			AbstractBaseRoute::R_DEBUG => [
  				AbstractBaseRoute::R_DEBUG => $output,
  				AbstractBaseRoute::R_DEBUG_KEY => 'testApiSuccess',
  			],
  		];
  	}
  }
  ```

- [ ] **Step 5: Commit**

  ```bash
  git add src/Rest/Routes/Integrations/Pardot/
  git commit -m "feat: add Pardot REST routes (OAuth callback, items, form submit, test API)"
  ```

---

## Task 7: Hook wiring — Filters.php + FiltersSettingsBuilder.php

**Files:**

- Modify: `src/Hooks/Filters.php`
- Modify: `src/Hooks/FiltersSettingsBuilder.php`

**Interfaces:**

- Consumes: All Pardot class constants from Tasks 1–5

- [ ] **Step 1: Add use statements to Filters.php**

  After the existing Nationbuilder use lines (lines 39–40):

  ```php
  use EightshiftForms\Integrations\Pardot\OauthPardot;
  use EightshiftForms\Integrations\Pardot\SettingsPardot;
  ```

- [ ] **Step 2: Add Pardot public filters to Filters.php**

  In `getPublicFilters()`, after the `SettingsNationbuilder::SETTINGS_TYPE_KEY` block (after line 320):

  ```php
  			SettingsPardot::SETTINGS_TYPE_KEY => [
  				'overridePostRequest',
  				'prePostParams',
  				'beforeSuccessResponse',
  				'afterCustomResultOutputProcess',
  			],
  ```

- [ ] **Step 3: Add Pardot public actions to Filters.php**

  In `getPublicActions()`, after the `SettingsNationbuilder::SETTINGS_TYPE_KEY` block (after line 414):

  ```php
  			SettingsPardot::SETTINGS_TYPE_KEY => [
  				'submitSuccess',
  			],
  ```

- [ ] **Step 4: Add Pardot non-translatable keys to Filters.php**

  In `getSettingsNonTranslatableNames()`, after the Nationbuilder block (after line 507):

  ```php
  			SettingsPardot::SETTINGS_PARDOT_USE_KEY,
  			SettingsPardot::SETTINGS_PARDOT_CLIENT_ID,
  			SettingsPardot::SETTINGS_PARDOT_SECRET,
  			SettingsPardot::SETTINGS_PARDOT_BUSINESS_UNIT_ID,
  			OauthPardot::OAUTH_PARDOT_ACCESS_TOKEN_KEY,
  			OauthPardot::OAUTH_PARDOT_REFRESH_TOKEN_KEY,
  ```

- [ ] **Step 5: Add use statements to FiltersSettingsBuilder.php**

  After the existing Nationbuilder use lines (lines 62–63):

  ```php
  use EightshiftForms\Integrations\Pardot\Pardot;
  use EightshiftForms\Integrations\Pardot\PardotClient;
  use EightshiftForms\Integrations\Pardot\SettingsPardot;
  ```

- [ ] **Step 6: Add Pardot builder entry to FiltersSettingsBuilder.php**

  After the Nationbuilder entry (after the closing `],` of the nationbuilder block, before `// MISCELLANEOUS`):

  ```php
  			SettingsPardot::SETTINGS_TYPE_KEY => [
  				'settingsGlobal' => SettingsPardot::FILTER_SETTINGS_GLOBAL_NAME,
  				'fields' => Pardot::FILTER_FORM_FIELDS_NAME,
  				'type' => Config::SETTINGS_INTERNAL_TYPE_INTEGRATION,
  				'integrationType' => Config::INTEGRATION_TYPE_DEFAULT,
  				'use' => SettingsPardot::SETTINGS_PARDOT_USE_KEY,
  				'settingsForceShow' => false,
  				'cache' => [
  					PardotClient::CACHE_PARDOT_FORM_HANDLERS_TRANSIENT_NAME,
  					PardotClient::CACHE_PARDOT_FORM_HANDLER_FIELDS_TRANSIENT_NAME,
  				],
  				'labels' => [
  					'title' => \__('Pardot', 'eightshift-forms'),
  					'desc' => \__('Pardot (Account Engagement) integration settings.', 'eightshift-forms'),
  					'detail' => \__('Salesforce B2B marketing automation platform for creating, deploying, and managing online marketing campaigns.', 'eightshift-forms'),
  					'externalLink' => 'https://www.salesforce.com/marketing/b2b-automation/',
  				],
  			],
  ```

- [ ] **Step 7: Commit**

  ```bash
  git add src/Hooks/Filters.php src/Hooks/FiltersSettingsBuilder.php
  git commit -m "feat: wire Pardot integration into Filters and FiltersSettingsBuilder"
  ```

---

## Task 8: Gutenberg block files

**Files:**

- Create: `src/Blocks/custom/pardot/manifest.json`
- Create: `src/Blocks/custom/pardot/pardot.php`
- Create: `src/Blocks/custom/pardot/pardot-block.js`
- Create: `src/Blocks/custom/pardot/pardot-overrides.js`
- Create: `src/Blocks/custom/pardot/components/pardot-editor.js`
- Create: `src/Blocks/custom/pardot/components/pardot-options.js`

- [ ] **Step 1: Create manifest.json**

  ```json
  {
  	"$schema": "https://raw.githubusercontent.com/infinum/eightshift-frontend-libs/develop/schemas/block.json",
  	"blockName": "pardot",
  	"title": "Pardot form",
  	"description": "Pardot (Account Engagement) form block",
  	"category": "eightshift-forms",
  	"icon": {
  		"src": "esf-form-pardot"
  	},
  	"keywords": ["pardot", "salesforce"],
  	"components": {
  		"form": "form",
  		"step": "step"
  	},
  	"hasInnerBlocks": true,
  	"supports": {
  		"inserter": false
  	},
  	"attributes": {
  		"pardotIntegrationId": {
  			"type": "string"
  		}
  	}
  }
  ```

- [ ] **Step 2: Create pardot.php**

  ```php
  <?php

  /**
   * Template for the Pardot Block view.
   *
   * @package EightshiftForms
   */

  use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

  echo Helpers::render(
  	'form',
  	Helpers::props('form', $attributes, [
  		'formContent' => $renderContent,
  	])
  );
  ```

- [ ] **Step 3: Create pardot-block.js**

  ```js
  import React from 'react';
  import { InspectorControls } from '@wordpress/block-editor';
  import { PardotEditor } from './components/pardot-editor';
  import { PardotOptions } from './components/pardot-options';

  export const Pardot = (props) => {
  	const itemIdKey = 'pardotIntegrationId';

  	return (
  		<>
  			<InspectorControls>
  				<PardotOptions
  					{...props}
  					clientId={props.clientId}
  					itemIdKey={itemIdKey}
  				/>
  			</InspectorControls>
  			<PardotEditor
  				{...props}
  				itemIdKey={itemIdKey}
  			/>
  		</>
  	);
  };
  ```

- [ ] **Step 4: Create pardot-overrides.js**

  ```js
  import manifest from './manifest.json';
  import { getUtilsIcons } from '../../components/form/assets/state-init';
  import globalSettings from './../../manifest.json';

  export const overrides = {
  	...manifest,
  	icon: {
  		src: getUtilsIcons('pardot') ?? manifest.icon.src,
  	},
  	parent: globalSettings.allowedBlocksList.formsCpt,
  };
  ```

- [ ] **Step 5: Create components/pardot-editor.js**

  ```js
  import React from 'react';
  import { select } from '@wordpress/data';
  import { STORE_NAME, checkAttr } from '@eightshift/frontend-libs/scripts';
  import { IntegrationsEditor } from './../../../components/integrations/components/integrations-editor';

  export const PardotEditor = ({ attributes, setAttributes, itemIdKey, clientId }) => {
  	const manifest = select(STORE_NAME).getBlock('pardot');

  	const { blockClass } = attributes;

  	return (
  		<div className={blockClass}>
  			<IntegrationsEditor
  				clientId={clientId}
  				itemId={checkAttr(itemIdKey, attributes, manifest)}
  				innerId={''}
  				useInnerId={false}
  				attributes={attributes}
  				setAttributes={setAttributes}
  			/>
  		</div>
  	);
  };
  ```

- [ ] **Step 6: Create components/pardot-options.js**

  ```js
  import React from 'react';
  import { select } from '@wordpress/data';
  import { STORE_NAME, checkAttr } from '@eightshift/frontend-libs/scripts';
  import { IntegrationsOptions } from './../../../components/integrations/components/integrations-options';

  export const PardotOptions = ({ attributes, setAttributes, clientId, itemIdKey }) => {
  	const manifest = select(STORE_NAME).getBlock('pardot');

  	const { title, blockName } = manifest;

  	return (
  		<IntegrationsOptions
  			title={title}
  			block={blockName}
  			attributes={attributes}
  			setAttributes={setAttributes}
  			clientId={clientId}
  			itemId={checkAttr(itemIdKey, attributes, manifest)}
  			itemIdKey={itemIdKey}
  			innerId={''}
  			innerIdKey={''}
  		/>
  	);
  };
  ```

- [ ] **Step 7: Commit**

  ```bash
  git add src/Blocks/custom/pardot/
  git commit -m "feat: add Pardot Gutenberg block (manifest, PHP template, React components)"
  ```

---

## Task 9: Block registration — Blocks/manifest.json + form-selector

**Files:**

- Modify: `src/Blocks/manifest.json`
- Modify: `src/Blocks/custom/form-selector/manifest.json`

- [ ] **Step 1: Add Pardot to integrationsBuilder in Blocks/manifest.json**

  In `src/Blocks/manifest.json`, add `"eightshift-forms/pardot"` to the `integrationsBuilder` array (after the last entry, before the closing `]`).

- [ ] **Step 2: Add Pardot template to form-selector manifest**

  In `src/Blocks/custom/form-selector/manifest.json`, add a Pardot entry to the forms array alongside the Airtable entry. Airtable's entry looks like:

  ```json
  {
  	"label": "Airtable",
  	"slug": "airtable",
  	"blockName": "eightshift-forms/airtable"
  }
  ```

  Add after it (or in alphabetical order):

  ```json
  {
  	"label": "Pardot",
  	"slug": "pardot",
  	"blockName": "eightshift-forms/pardot"
  }
  ```

  No `innerBlocks` — fields are auto-generated by the builder.

- [ ] **Step 3: Commit**

  ```bash
  git add src/Blocks/manifest.json src/Blocks/custom/form-selector/manifest.json
  git commit -m "feat: register Pardot block in global manifest and form-selector templates"
  ```

---

## Manual Verification Checklist

After all tasks are complete:

1. **PHP loads without errors** — activate the plugin, check PHP error log for any class-not-found or syntax errors.
2. **Settings page appears** — go to Forms → Settings, verify "Pardot" integration appears in the integration list.
3. **Enter credentials** — enter Consumer Key, Consumer Secret, Business Unit ID; select Production; save.
4. **OAuth connect** — click "Connect with Salesforce"; authorize in Salesforce; confirm redirect back with success message.
5. **Test API** — click "Test API connection"; confirm success (requires at least one Form Handler in Pardot).
6. **Block in editor** — insert a Pardot form block; verify the handler dropdown populates with your Form Handlers.
7. **Field auto-build** — select a handler; verify the form fields are auto-generated matching the handler's fields (names, types, required).
8. **Live submit** — fill in the form on the frontend and submit; verify the prospect is created/updated in Pardot and completion actions ran (list/campaign as configured on the handler).
9. **Sandbox toggle** — switch to Sandbox; verify the OAuth connect button changes to `test.salesforce.com`.
10. **Skip integration toggle** — enable "Skip integration"; submit a form; verify it returns success without hitting Pardot.
11. **Cache clear** — clear integration cache from settings; re-open handler dropdown; verify it re-fetches from API.
