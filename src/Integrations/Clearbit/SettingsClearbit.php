<?php

/**
 * Clearbit Settings class.
 *
 * @package EightshiftForms\Integrations\Clearbit
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Clearbit;

use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\Hubspot\HubspotClientInterface;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\SettingInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsClearbit class.
 */
class SettingsClearbit implements ServiceInterface, SettingGlobalInterface, SettingInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_clearbit';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_clearbit';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_clearbit';

	/**
	 * Filter settings integration use key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_clearbit';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'clearbit';

	/**
	 * Clearbit Use key.
	 */
	public const SETTINGS_CLEARBIT_USE_KEY = 'clearbit-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_CLEARBIT_API_KEY_KEY = 'clearbit-api-key';

	/**
	 * Clearbit available keys key.
	 */
	public const SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY = 'clearbit-available-keys';

	/**
	 * Clearbit map keys key.
	 */
	public const SETTINGS_CLEARBIT_MAP_HUBSPOT_KEYS_KEY = 'clearbit-map-keys';

	/**
	 * Use Clearbit settings key.
	 */
	public const SETTINGS_CLEARBIT_SETTINGS_USE_KEY = 'clearbit-settings-use';

	/**
	 * Use Clearbit cron key.
	 */
	public const SETTINGS_CLEARBIT_CRON_KEY = 'clearbit-cron';

	/**
	 * Instance variable for Clearbit data.
	 *
	 * @var ClearbitClientInterface
	 */
	protected $clearbitClient;

	/**
	 * Instance variable for Hubspot data.
	 *
	 * @var HubspotClientInterface
	 */
	protected $hubspotClient;

	/**
	 * Create a new instance.
	 *
	 * @param ClearbitClientInterface $clearbitClient Inject Clearbit which holds Clearbit connect data.
	 * @param HubspotClientInterface $hubspotClient Inject Hubspot which holds Hubspot connect data.
	 */
	public function __construct(
		ClearbitClientInterface $clearbitClient,
		HubspotClientInterface $hubspotClient
	) {
		$this->clearbitClient = $clearbitClient;
		$this->hubspotClient = $hubspotClient;
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
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
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

		$use = SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_CLEARBIT_SETTINGS_USE_KEY, self::SETTINGS_CLEARBIT_SETTINGS_USE_KEY, $formId);

		if (!$use) {
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
		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CLEARBIT_USE_KEY, self::SETTINGS_CLEARBIT_USE_KEY);
		$apiKey = (bool) SettingsHelpers::getOptionWithConstant(Variables::getApiKeyClearbit(), self::SETTINGS_CLEARBIT_API_KEY_KEY);

		if (!$isUsed || !$apiKey) {
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
		// Bailout if global config is not valid.
		if (!$this->isSettingsGlobalValid()) {
			return SettingsOutputHelpers::getNoValidGlobalConfig(self::SETTINGS_TYPE_KEY);
		}

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_CLEARBIT_SETTINGS_USE_KEY),
								'checkboxesIsRequired' => false,
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use Clearbit integration', 'eightshift-forms'),
										'checkboxHelp' => \__('Use Clearbit integration to enrich your data on this form.', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_CLEARBIT_SETTINGS_USE_KEY, self::SETTINGS_CLEARBIT_SETTINGS_USE_KEY, $formId),
										'checkboxValue' => self::SETTINGS_CLEARBIT_SETTINGS_USE_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
										'checkboxAsToggleSize' => 'medium',
									],
								]
							],
						],
					],
				],
			],
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
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CLEARBIT_USE_KEY, self::SETTINGS_CLEARBIT_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('General', 'eightshift-forms'),
						'tabContent' => [
							SettingsOutputHelpers::getPasswordFieldWithGlobalVariable(
								Variables::getApiKeyClearbit(),
								self::SETTINGS_CLEARBIT_API_KEY_KEY,
								'ES_API_KEY_CLEARBIT',
								\__('API key', 'eightshift-forms'),
							),
						],
					],
					...($this->isSettingsGlobalValid() ? [
						[
							'component' => 'tab',
							'tabLabel' => \__('Available fields', 'eightshift-forms'),
							'tabContent' => [
								[
									'component' => 'checkboxes',
									'checkboxesFieldHideLabel' => true,
									'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY),
									'checkboxesIsRequired' => true,
									'checkboxesContent' => \array_map(
										function ($item) {
											return [
												'component' => 'checkbox',
												'checkboxLabel' => $item,
												'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked($item, self::SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY),
												'checkboxValue' => $item,
											];
										},
										$this->clearbitClient->getParams()
									),
								],
							],
						],
						[
							'component' => 'tab',
							'tabLabel' => \__('Queue jobs', 'eightshift-forms'),
							'tabContent' => [
								[
									'component' => 'textarea',
									'textareaFieldLabel' => \__('Queue jobs', 'eightshift-forms'),
									'textareaFieldHelp' => \__('Emails in queue that are still not processed.', 'eightshift-forms'),
									'textareaIsReadOnly' => true,
									'textareaIsPreventSubmit' => true,
									'textareaName' => 'queue',
									'textareaValue' => \wp_json_encode(SettingsHelpers::getOptionValueGroup(SettingsClearbit::SETTINGS_CLEARBIT_CRON_KEY), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE),
									'textareaSize' => 'huge',
									'textareaLimitHeight' => true,
								],
							],
						],
						(\apply_filters(SettingsHubspot::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false) ? [
							'component' => 'tab',
							'tabLabel' => \__('HubSpot', 'eightshift-forms'),
							'tabContent' => [
								[
									'component' => 'intro',
									'introSubtitle' => \__('Map Clearbit fields to HubSpot properties.', 'eightshift-forms'),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								$this->getSettingsGlobalMap(
									$this->hubspotClient->getContactProperties(),
									self::SETTINGS_CLEARBIT_MAP_HUBSPOT_KEYS_KEY . '-' . SettingsHubspot::SETTINGS_TYPE_KEY
								),
							],
						] : []),
					] : []),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Clearbit Account.', 'eightshift-forms'),
									// translators: %s will be replaced with the api externa link.
									\sprintf(\__('Click on the <strong><a target="_blank" rel="noopener noreferrer" href="%s">API</a></strong> in the sidebar.', 'eightshift-forms'), 'https://dashboard.clearbit.com/api'),
									\__('Copy the secret API key into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Output array settings for form.
	 *
	 * @param array<string, string> $properties Array of properties from integration.
	 * @param string $key Key for saving the settings.
	 *
	 * @return array<string, array<int, array<string, array<int, array<string, array<int, array<string, array<int|string, array<string, bool|string>>|string>>|bool|string>>|string>>|string>
	 */
	public function getSettingsGlobalMap(array $properties, string $key): array
	{
		$isValid = $this->isSettingsGlobalValid();

		if (!$isValid) {
			return [];
		}

		$clearbitAvailableKeys = SettingsHelpers::getOptionCheckboxValues(SettingsClearbit::SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY);

		$clearbitMapValue = SettingsHelpers::getOptionValueGroup($key);

		if (!$clearbitAvailableKeys) {
			return [];
		}

		return [
			'component' => 'group',
			'groupName' => SettingsHelpers::getOptionName($key),
			'groupSaveOneField' => true,
			'groupStyle' => 'default-listing',
			'groupContent' => [
				[
					'component' => 'field',
					'fieldLabel' => '<b>' . \__('Clearbit field', 'eightshift-forms') . '</b>',
					'fieldContent' => '<b>' . \__('HubSpot property', 'eightshift-forms') . '</b>',
					'fieldBeforeContent' => '&emsp;', // "Em space" to pad it out a bit.
					'fieldIsFiftyFiftyHorizontal' => true,
				],
				...\array_map(
					static function ($item) use ($clearbitMapValue, $properties) {
						$selectedValue = $clearbitMapValue[$item] ?? '';
						return [
							'component' => 'select',
							'selectName' => $item,
							'selectFieldLabel' => '<code>' . $item . '</code>',
							'selectFieldBeforeContent' => '&rarr;',
							'selectFieldIsFiftyFiftyHorizontal' => true,
							'selectPlaceholder' => \__('Select option', 'eightshift-forms'),
							'selectContent' => \array_map(
								static function ($option) use ($selectedValue) {
									return [
										'component' => 'select-option',
										'selectOptionLabel' => $option,
										'selectOptionValue' => $option,
										'selectOptionIsSelected' => $selectedValue === $option,
									];
								},
								$properties
							),
						];
					},
					$clearbitAvailableKeys
				),
			],
		];
	}
}
