# Pardot Integration — JIRA-style Refactor Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor the Pardot integration from an auto-builder (MapperInterface) to a JIRA-style manual integration where the user builds the form by hand and maps fields to Pardot in per-form settings.

**Architecture:** Switch `integrationType` from `INTEGRATION_TYPE_DEFAULT` to `INTEGRATION_TYPE_NO_BUILDER`, delete `Pardot.php` (mapper), add per-form settings to `SettingsPardot` (handler dropdown + field mapping tab), and update `PardotClient::postApplication` to read the handler ID and mapping from form meta rather than block attributes / `FD_ITEM_ID`.

**Tech Stack:** PHP 8.4, WordPress hooks/meta, Eightshift Libs DI, React (Gutenberg blocks), Eightshift Frontend Libs.

## Global Constraints

- No automated tests — verify manually at each task boundary, matching the existing integration convention.
- Indentation: tabs throughout all PHP and JS files.
- Follow the JIRA integration (`src/Integrations/Jira/`) as the exact reference pattern for any uncertainty.
- No new abstractions or helper methods beyond what the task requires.
- Spec: `docs/superpowers/specs/2026-06-17-pardot-integration-design.md`

---

## File Map

| File                                                            | Action                                                                                                               |
| --------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| `src/Integrations/Pardot/Pardot.php`                            | **Delete**                                                                                                           |
| `src/Integrations/Pardot/SettingsPardot.php`                    | **Modify** — add `SettingInterface`, constants, `isSettingsValid`, `getSettingsData`, inject `PardotClientInterface` |
| `src/Integrations/Pardot/PardotClient.php`                      | **Modify** — rewrite `postApplication` and `prepareParams` to use settings + mapping                                 |
| `src/Rest/Routes/Integrations/Pardot/FormSubmitPardotRoute.php` | **Modify** — remove `FD_ITEM_ID` from mandatory params, use per-form validity filter                                 |
| `src/Hooks/FiltersSettingsBuilder.php`                          | **Modify** — remove `Pardot` use statement + `fields` key, add `settings` key, change `integrationType`              |
| `src/Blocks/custom/pardot/components/pardot-options.js`         | **Modify** — swap to `IntegrationsInternalOptions`                                                                   |
| `src/Blocks/custom/pardot/components/pardot-editor.js`          | **Modify** — swap to `FormEditor` + `InnerBlocks`                                                                    |
| `src/Blocks/custom/pardot/pardot-block.js`                      | **Modify** — remove `itemIdKey` wiring                                                                               |
| `src/Blocks/custom/pardot/manifest.json`                        | **Modify** — remove `pardotIntegrationId` attribute                                                                  |

---

## Task 1: Strip mapper from the block layer

**Files:**

- Modify: `src/Blocks/custom/pardot/components/pardot-options.js`
- Modify: `src/Blocks/custom/pardot/components/pardot-editor.js`
- Modify: `src/Blocks/custom/pardot/pardot-block.js`
- Modify: `src/Blocks/custom/pardot/manifest.json`

**Interfaces:**

- Produces: block renders with `IntegrationsInternalOptions` (standard settings panel) and `FormEditor` + `InnerBlocks` (editor area); no `pardotIntegrationId` block attribute.

- [ ] **Step 1: Update `pardot-options.js`**

Replace the entire file content with:

```js
import React from 'react';
import { select } from '@wordpress/data';
import { STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { IntegrationsInternalOptions } from './../../../components/integrations/components/integrations-internal-options';

export const PardotOptions = ({ attributes, setAttributes, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('pardot');

	const { title } = manifest;

	return (
		<IntegrationsInternalOptions
			title={title}
			clientId={clientId}
			attributes={attributes}
			setAttributes={setAttributes}
		/>
	);
};
```

- [ ] **Step 2: Update `pardot-editor.js`**

Replace the entire file content with:

```js
import React from 'react';
import { select } from '@wordpress/data';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, BlockInserter, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { FormEditor, additionalBlocksNoIntegration } from '../../../components/form/components/form-editor';

export const PardotEditor = ({ attributes, setAttributes, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('pardot');

	const { blockClass } = attributes;

	return (
		<div className={blockClass}>
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent: (
						<InnerBlocks
							allowedBlocks={additionalBlocksNoIntegration}
							renderAppender={() => <BlockInserter clientId={clientId} />}
						/>
					),
				})}
			/>
		</div>
	);
};
```

- [ ] **Step 3: Update `pardot-block.js`**

Replace the entire file content with:

```js
import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { PardotEditor } from './components/pardot-editor';
import { PardotOptions } from './components/pardot-options';

export const Pardot = (props) => {
	return (
		<>
			<InspectorControls>
				<PardotOptions {...props} />
			</InspectorControls>
			<PardotEditor {...props} />
		</>
	);
};
```

- [ ] **Step 4: Update `manifest.json` — remove `pardotIntegrationId` attribute**

Replace the entire file content with:

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
	"attributes": {}
}
```

- [ ] **Step 5: Build JS and verify block loads**

```bash
bun run build
```

Open the block editor, insert a Pardot form block. Verify: the sidebar shows standard block controls (no handler dropdown), the editor area shows an inner blocks inserter (not a pre-built locked form). No JS console errors.

- [ ] **Step 6: Commit**

```bash
git add src/Blocks/custom/pardot/
git commit -m "refactor(pardot): remove mapper block UI, switch to FormEditor + InnerBlocks"
```

---

## Task 2: Delete the mapper class and update FiltersSettingsBuilder

**Files:**

- Delete: `src/Integrations/Pardot/Pardot.php`
- Modify: `src/Hooks/FiltersSettingsBuilder.php`

**Interfaces:**

- Produces: `FiltersSettingsBuilder` registers Pardot as `INTEGRATION_TYPE_NO_BUILDER` with a `settings` key pointing to `SettingsPardot::FILTER_SETTINGS_NAME` (the filter is registered in Task 3; returning `[]` until then is fine).

- [ ] **Step 1: Delete `Pardot.php`**

```bash
rip src/Integrations/Pardot/Pardot.php
```

- [ ] **Step 2: Remove the `Pardot` use statement from `FiltersSettingsBuilder.php`**

In `src/Hooks/FiltersSettingsBuilder.php`, remove this line:

```php
use EightshiftForms\Integrations\Pardot\Pardot;
```

- [ ] **Step 3: Update the Pardot entry in `FiltersSettingsBuilder.php`**

Find this block (exact match):

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

Replace with:

```php
			SettingsPardot::SETTINGS_TYPE_KEY => [
				'settingsGlobal' => SettingsPardot::FILTER_SETTINGS_GLOBAL_NAME,
				'settings' => SettingsPardot::FILTER_SETTINGS_NAME,
				'type' => Config::SETTINGS_INTERNAL_TYPE_INTEGRATION,
				'integrationType' => Config::INTEGRATION_TYPE_NO_BUILDER,
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

- [ ] **Step 4: Verify PHP parses cleanly**

```bash
php -l src/Hooks/FiltersSettingsBuilder.php
```

Expected: `No syntax errors detected`

- [ ] **Step 5: Verify WordPress loads without fatal errors**

Open WP admin. Confirm no fatal error / white screen. The Pardot global settings page still loads (no form-level tab yet — that is Task 3).

- [ ] **Step 6: Commit**

```bash
git add src/Integrations/Pardot/Pardot.php src/Hooks/FiltersSettingsBuilder.php
git commit -m "refactor(pardot): remove MapperInterface, switch to INTEGRATION_TYPE_NO_BUILDER"
```

---

## Task 3: Add per-form settings to SettingsPardot

**Files:**

- Modify: `src/Integrations/Pardot/SettingsPardot.php`

**Interfaces:**

- Consumes: `PardotClientInterface::getItems(): array<string, ['id'=>string,'title'=>string,'submitUrl'=>string]>`; `PardotClientInterface::getItem(string $handlerId): array<string, ['id'=>string,'title'=>string,'dataFormat'=>string,'isRequired'=>bool]>`
- Produces:
  - `SettingsPardot::FILTER_SETTINGS_NAME = 'es_forms_settings_pardot'`
  - `SettingsPardot::FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_pardot'`
  - `SettingsPardot::SETTINGS_PARDOT_ITEM_ID_KEY = 'pardot-item-id'`
  - `SettingsPardot::SETTINGS_PARDOT_PARAMS_MAP_KEY = 'pardot-params-map'`
  - `isSettingsValid(bool $output, string $formId): bool`
  - `getSettingsData(string $formId): array`

- [ ] **Step 1: Add missing `use` imports**

In `src/Integrations/Pardot/SettingsPardot.php`, add these lines after the existing `use` block:

```php
use EightshiftForms\Settings\SettingInterface;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Config\Config;
use EightshiftForms\Integrations\Pardot\PardotClientInterface;
```

- [ ] **Step 2: Add `SettingInterface` to the class declaration**

Change:

```php
class SettingsPardot extends AbstractSettingsIntegrations implements SettingGlobalInterface, ServiceInterface
```

to:

```php
class SettingsPardot extends AbstractSettingsIntegrations implements SettingGlobalInterface, SettingInterface, ServiceInterface
```

- [ ] **Step 3: Add new constants after `SETTINGS_PARDOT_OAUTH_ALLOW_KEY`**

```php
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_pardot';

	/**
	 * Filter settings is valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_pardot';

	/**
	 * Selected form handler ID key.
	 */
	public const SETTINGS_PARDOT_ITEM_ID_KEY = 'pardot-item-id';

	/**
	 * Field mapping key.
	 */
	public const SETTINGS_PARDOT_PARAMS_MAP_KEY = 'pardot-params-map';
```

- [ ] **Step 4: Add `$pardotClient` instance variable after `$oauthPardot`**

```php
	/**
	 * Instance variable for Pardot client.
	 *
	 * @var PardotClientInterface
	 */
	protected $pardotClient;
```

- [ ] **Step 5: Update the constructor to inject `PardotClientInterface`**

Replace the current constructor with:

```php
	/**
	 * Create a new instance.
	 *
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback methods.
	 * @param OauthInterface $oauthPardot Inject Oauth methods.
	 * @param PardotClientInterface $pardotClient Inject Pardot client.
	 */
	public function __construct(
		SettingsFallbackDataInterface $settingsFallback,
		OauthInterface $oauthPardot,
		PardotClientInterface $pardotClient,
	) {
		$this->settingsFallback = $settingsFallback;
		$this->oauthPardot = $oauthPardot;
		$this->pardotClient = $pardotClient;
	}
```

- [ ] **Step 6: Register the two new filters in `register()`**

Add at the end of the `register()` method body:

```php
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsValid'], 10, 2);
```

- [ ] **Step 7: Add `isSettingsValid()` after `isSettingsGlobalValid()`**

```php
	/**
	 * Determine if settings are valid.
	 *
	 * @param bool $output Output.
	 * @param string $formId Form ID.
	 *
	 * @return boolean
	 */
	public function isSettingsValid(bool $output, string $formId): bool
	{
		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		$selectedHandler = SettingsHelpers::getSettingValue(self::SETTINGS_PARDOT_ITEM_ID_KEY, $formId);

		if (!$selectedHandler) {
			return false;
		}

		return true;
	}
```

- [ ] **Step 8: Add `getSettingsData()` after `isSettingsValid()`**

```php
	/**
	 * Get Form settings data array.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		if (!$this->isSettingsGlobalValid()) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$formDetails = GeneralHelpers::getFormDetails($formId);

		$selectedHandler = SettingsHelpers::getSettingValue(self::SETTINGS_PARDOT_ITEM_ID_KEY, $formId);
		$mapParams = SettingsHelpers::getSettingValueGroup(self::SETTINGS_PARDOT_PARAMS_MAP_KEY, $formId);

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Settings', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'select',
								'selectName' => SettingsHelpers::getSettingName(self::SETTINGS_PARDOT_ITEM_ID_KEY),
								'selectFieldLabel' => \__('Form handler', 'eightshift-forms'),
								'selectSingleSubmit' => true,
								'selectPlaceholder' => \__('Select form handler', 'eightshift-forms'),
								'selectContent' => \array_map(
									static function ($option) use ($selectedHandler) {
										return [
											'component' => 'select-option',
											'selectOptionLabel' => $option['title'],
											'selectOptionValue' => $option['id'],
											'selectOptionIsSelected' => $selectedHandler === $option['id'],
										];
									},
									$this->pardotClient->getItems()
								),
							],
						],
					],
					$selectedHandler ? [
						'component' => 'tab',
						'tabLabel' => \__('Field mapping', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('For each Pardot field, enter the name of the corresponding form field.', 'eightshift-forms'),
								'introHelp' => SettingsOutputHelpers::getPartialFieldTags(SettingsOutputHelpers::getPartialFormFieldNames($formDetails[Config::FD_FIELD_NAMES])),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'field',
								'fieldLabel' => '<b>' . \__('Pardot field', 'eightshift-forms') . '</b>',
								'fieldContent' => '<b>' . \__('Form field name', 'eightshift-forms') . '</b>',
								'fieldBeforeContent' => '&emsp;',
								'fieldIsFiftyFiftyHorizontal' => true,
							],
							[
								'component' => 'group',
								'groupName' => SettingsHelpers::getSettingName(self::SETTINGS_PARDOT_PARAMS_MAP_KEY),
								'groupSaveOneField' => true,
								'groupStyle' => 'default-listing',
								'groupContent' => \array_map(
									function ($field) use ($mapParams) {
										$id = $field['id'] ?? '';

										return [
											'component' => 'input',
											'inputName' => $id,
											'inputFieldLabel' => $field['title'],
											'inputValue' => $mapParams[$id] ?? '',
											'inputFieldIsFiftyFiftyHorizontal' => true,
											'inputFieldBeforeContent' => '&rarr;',
										];
									},
									\array_values($this->pardotClient->getItem($selectedHandler))
								),
							],
						],
					] : [],
				],
			],
		];
	}
```

- [ ] **Step 9: Verify PHP parses cleanly**

```bash
php -l src/Integrations/Pardot/SettingsPardot.php
```

Expected: `No syntax errors detected`

- [ ] **Step 10: Verify per-form settings appear in WP admin**

Open a form's settings page → Pardot tab. Confirm:

- "Settings" sub-tab shows a "Form handler" dropdown populated from the Pardot API.
- Selecting a handler and saving reveals a "Field mapping" tab listing all Pardot fields with text inputs.
- Available form field names are shown as hint text.

- [ ] **Step 11: Commit**

```bash
git add src/Integrations/Pardot/SettingsPardot.php
git commit -m "feat(pardot): add per-form settings — handler selection and field mapping"
```

---

## Task 4: Update submit logic — read handler + mapping from settings

**Files:**

- Modify: `src/Integrations/Pardot/PardotClient.php`
- Modify: `src/Rest/Routes/Integrations/Pardot/FormSubmitPardotRoute.php`

**Interfaces:**

- Consumes:
  - `SettingsPardot::SETTINGS_PARDOT_ITEM_ID_KEY` (Task 3)
  - `SettingsPardot::SETTINGS_PARDOT_PARAMS_MAP_KEY` (Task 3)
  - `SettingsPardot::FILTER_SETTINGS_IS_VALID_NAME` (Task 3)
  - `SettingsHelpers::getSettingValue(string $key, string $formId): string`
  - `SettingsHelpers::getSettingValueGroup(string $key, string $formId): array<string, string>`
- Produces: `postApplication()` POSTs `pardotFieldName = resolvedFormFieldValue` pairs built from the mapping saved in form meta.

- [ ] **Step 1: Update `postApplication()` in `PardotClient.php`**

Replace the entire `postApplication()` method with:

```php
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

		$itemId = SettingsHelpers::getSettingValue(SettingsPardot::SETTINGS_PARDOT_ITEM_ID_KEY, $formId);
		$mapParams = SettingsHelpers::getSettingValueGroup(SettingsPardot::SETTINGS_PARDOT_PARAMS_MAP_KEY, $formId);

		// Filter override post request.
		$filterName = HooksHelpers::getFilterName(['integrations', SettingsPardot::SETTINGS_TYPE_KEY, 'overridePostRequest']);
		if (\has_filter($filterName)) {
			$filterValue = \apply_filters($filterName, [], $itemId, $params, $files, $formId) ?? [];

			if ($filterValue) {
				return $filterValue;
			}
		}

		$handler = $this->getItems()[$itemId] ?? [];
		$url = $handler['submitUrl'] ?? '';

		if (!$url) {
			$details = ApiHelpers::getIntegrationApiResponseDetails(
				SettingsPardot::SETTINGS_TYPE_KEY,
				new WP_Error('missing_url', 'Submit URL not found for this form handler.'),
				'',
				[],
				[],
				$itemId,
				$formId
			);
			$details[Config::IARD_MSG] = SettingsFallback::SETTINGS_FALLBACK_FLAG_PARDOT_MISSING_CONFIG;

			return ApiHelpers::getIntegrationErrorInternalOutput($details);
		}

		$body = $this->prepareParams($params, $mapParams);

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
			$params,
			$files,
			$itemId,
			$formId
		);

		$code = $details[Config::IARD_CODE];

		// Form handlers respond with 302 on success.
		if ($code >= 200 && $code < 400) {
			return ApiHelpers::getIntegrationSuccessInternalOutput($details);
		}

		$details[Config::IARD_MSG] = $this->getErrorMsg($details[Config::IARD_BODY]);

		return ApiHelpers::getIntegrationErrorInternalOutput($details);
	}
```

- [ ] **Step 2: Update `prepareParams()` in `PardotClient.php`**

Replace the existing private `prepareParams()` method with:

```php
	/**
	 * Prepare params for form-encoded POST using the field mapping from settings.
	 *
	 * @param array<string, mixed> $params Form params.
	 * @param array<string, string> $mapParams Mapping of pardot field name => form field name.
	 *
	 * @return string
	 */
	private function prepareParams(array $params, array $mapParams): string
	{
		$params = GeneralHelpers::removeUnnecessaryParamFields($params);

		// Index submitted form params by field name for fast lookup.
		$paramsByName = [];
		foreach ($params as $param) {
			$name = $param['name'] ?? '';

			if (!$name) {
				continue;
			}

			$value = $param['value'] ?? '';

			if (\is_array($value)) {
				$value = \implode(',', $value);
			}

			if (\is_string($value)) {
				$value = \wp_strip_all_tags($value);
			}

			$paramsByName[$name] = $value;
		}

		// Build payload: pardot field name => resolved form field value.
		$output = [];
		foreach ($mapParams as $pardotField => $formFieldName) {
			if (!$pardotField || !$formFieldName) {
				continue;
			}

			$output[$pardotField] = $paramsByName[$formFieldName] ?? '';
		}

		return \http_build_query($output);
	}
```

- [ ] **Step 3: Verify PHP parses cleanly**

```bash
php -l src/Integrations/Pardot/PardotClient.php
```

Expected: `No syntax errors detected`

- [ ] **Step 4: Update `getMandatoryParams()` in `FormSubmitPardotRoute.php`**

Replace the current `getMandatoryParams()` with:

```php
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
			Config::FD_PARAMS => 'array',
		];
	}
```

- [ ] **Step 5: Update the validity check in `submitAction()` in `FormSubmitPardotRoute.php`**

Find this block inside `submitAction()`:

```php
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
```

Replace with:

```php
		$formId = $formDetails[Config::FD_FORM_ID];

		if (!\apply_filters(SettingsPardot::FILTER_SETTINGS_IS_VALID_NAME, false, $formId)) {
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
```

- [ ] **Step 6: Verify PHP parses cleanly**

```bash
php -l src/Rest/Routes/Integrations/Pardot/FormSubmitPardotRoute.php
```

Expected: `No syntax errors detected`

- [ ] **Step 7: End-to-end submit test**

1. Open a form with a Pardot block in the block editor.
2. Manually add a text input field named `email` and another named `first-name`.
3. In form settings → Pardot → Settings: select a form handler, save.
4. In form settings → Pardot → Field mapping: for the Pardot `Email` field enter `email`; for `First Name` enter `first-name`; save.
5. Submit the form in the browser with real values.
6. Verify the prospect is created/updated in Pardot with the correct field values.
7. Clear the handler setting, submit again — verify a proper error response (not a 500).

- [ ] **Step 8: Commit**

```bash
git add src/Integrations/Pardot/PardotClient.php src/Rest/Routes/Integrations/Pardot/FormSubmitPardotRoute.php
git commit -m "feat(pardot): read handler + field mapping from per-form settings on submit"
```
