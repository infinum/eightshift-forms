<?php

/**
 * Airtable Settings class.
 *
 * @package EightshiftForms\Integrations\Airtable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Airtable;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsAirtable class.
 */
class SettingsAirtable implements SettingInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_airtable';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_airtable';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_airtable';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'airtable';

	/**
	 * Airtable Use key.
	 */
	public const SETTINGS_AIRTABLE_USE_KEY = 'airtable-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_AIRTABLE_API_KEY_KEY = 'airtable-api-key';

	/**
	 * List ID Key.
	 */
	public const SETTINGS_AIRTABLE_LIST_KEY = 'airtable-list';

	/**
	 * Field ID Key.
	 */
	public const SETTINGS_AIRTABLE_FIELD_KEY = 'airtable-field';

	/**
	 * Integration fields Key.
	 */
	public const SETTINGS_AIRTABLE_INTEGRATION_FIELDS_KEY = 'airtable-integration-fields';

	/**
	 * Conditional tags key.
	 */
	public const SETTINGS_AIRTABLE_CONDITIONAL_TAGS_KEY = 'airtable-conditional-tags';

	/**
	 * Instance variable for airtable data.
	 *
	 * @var ClientInterface
	 */
	protected $airtableClient;

	/**
	 * Instance variable for Airtable form data.
	 *
	 * @var MapperInterface
	 */
	protected $airtable;

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $airtableClient Inject Airtable which holds Airtable connect data.
	 * @param MapperInterface $airtable Inject Airtable which holds Airtable form data.
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(
		ClientInterface $airtableClient,
		MapperInterface $airtable,
		SettingsFallbackDataInterface $settingsFallback
	) {
		$this->airtableClient = $airtableClient;
		$this->airtable = $airtable;
		$this->settingsFallback = $settingsFallback;
	}
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsValid']);
	}

	/**
	 * Determine if settings are valid.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return boolean
	 */
	public function isSettingsValid(string $formId): bool
	{
		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		$list = $this->getSettingsValue(SettingsAirtable::SETTINGS_AIRTABLE_LIST_KEY, $formId);
		$field = $this->getSettingsValue(SettingsAirtable::SETTINGS_AIRTABLE_FIELD_KEY, $formId);

		if (empty($list) || empty($field)) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_AIRTABLE_USE_KEY, self::SETTINGS_AIRTABLE_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyAirtable()) ? Variables::getApiKeyAirtable() : $this->getOptionValue(self::SETTINGS_AIRTABLE_API_KEY_KEY);

		if (!$isUsed || empty($apiKey)) {
			return false;
		}

		return true;
	}

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		$type = self::SETTINGS_TYPE_KEY;

		// Bailout if global config is not valid.
		if (!$this->isSettingsGlobalValid()) {
			return $this->getNoValidGlobalConfigOutput($type);
		}

		// Get forms from the API.
		$items = $this->airtableClient->getItems(false);

		// Bailout if integration can't fetch data.
		if (!$items) {
			return $this->getNoIntegrationFetchDataOutput($type);
		}

		// Find selected form id.
		$selectedFormId = $this->getSettingsValue(self::SETTINGS_AIRTABLE_LIST_KEY, $formId);

		$output = [];

		// If the user has selected the form id populate additional config.
		if ($selectedFormId) {
			$item = $this->airtableClient->getItem($selectedFormId);

			$formFields = $this->airtable->getFormFields($formId);

			if ($item) {
				$selectedFormFieldId = $this->getSettingsValue(self::SETTINGS_AIRTABLE_FIELD_KEY, $formId);

				// Output additonal tabs for config.
				$output = [
					...$this->getOutputFormSelectionAdditional(
						$formId,
						$item['items'],
						$selectedFormFieldId,
						self::SETTINGS_AIRTABLE_FIELD_KEY
					),
					$formFields ? [
						'component' => 'tabs',
						'tabsContent' => [
							$this->getOutputIntegrationFields(
								$formId,
								$formFields,
								$type,
								self::SETTINGS_AIRTABLE_INTEGRATION_FIELDS_KEY,
							),
							$this->getOutputConditionalTags(
								$formId,
								$formFields,
								self::SETTINGS_AIRTABLE_CONDITIONAL_TAGS_KEY
							),
						],
					] : [],
				];
			}
		}

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			...$this->getOutputFormSelection(
				$formId,
				$items,
				$selectedFormId,
				self::SETTINGS_TYPE_KEY,
				self::SETTINGS_AIRTABLE_LIST_KEY
			),
			...$output,
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		// Bailout if feature is not active.
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_AIRTABLE_USE_KEY, self::SETTINGS_AIRTABLE_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$apiKey = Variables::getApiKeyAirtable();

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('API', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_AIRTABLE_API_KEY_KEY),
								'inputId' => $this->getSettingsName(self::SETTINGS_AIRTABLE_API_KEY_KEY),
								'inputFieldLabel' => \__('API key', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'password',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_AIRTABLE_API_KEY_KEY),
								'inputIsDisabled' => !empty($apiKey),
							],
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsAirtable::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Airtable Account.', 'eightshift-forms'),
									// translators: %s will be replaced with the link.
									\sprintf(\__('Go to the <a target="_blank" rel="noopener noreferrer" href="%s">Developers Hub</a>.', 'eightshift-forms'), 'https://airtable.com/create/tokens/new'),
									\__('Create a new Personal access token with the scopes <strong>"data.records:write"</strong> and <strong>"schema.bases:read"</strong>.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
