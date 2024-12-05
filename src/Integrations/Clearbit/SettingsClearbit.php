<?php

/**
 * Clearbit Settings class.
 *
 * @package EightshiftForms\Integrations\Clearbit
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Clearbit;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsClearbit class.
 */
class SettingsClearbit implements ServiceInterface, UtilsSettingGlobalInterface, UtilsSettingInterface
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
	 * Clearbit Use jobs queue key.
	 */
	public const SETTINGS_CLEARBIT_USE_JOBS_QUEUE_KEY = 'clearbit-use-jobs-queue';

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
	 * Use Clearbit jobs key.
	 */
	public const SETTINGS_CLEARBIT_JOBS_KEY = 'clearbit-jobs';

	/**
	 * Instance variable for Clearbit data.
	 *
	 * @var ClearbitClientInterface
	 */
	protected $clearbitClient;

	/**
	 * Create a new instance.
	 *
	 * @param ClearbitClientInterface $clearbitClient Inject Clearbit which holds Clearbit connect data.
	 */
	public function __construct(ClearbitClientInterface $clearbitClient)
	{
		$this->clearbitClient = $clearbitClient;
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
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsValid'], 10, 2);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings are valid.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Integration type.
	 *
	 * @return boolean
	 */
	public function isSettingsValid(string $formId, string $type): bool
	{
		if (!$this->isSettingsGlobalValid($type)) {
			return false;
		}

		$use = UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_CLEARBIT_SETTINGS_USE_KEY, self::SETTINGS_CLEARBIT_SETTINGS_USE_KEY, $formId);

		if (!$use) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @param string $type Integration type.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(string $type = ''): bool
	{
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_CLEARBIT_USE_KEY, self::SETTINGS_CLEARBIT_USE_KEY);
		$apiKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyClearbit(), self::SETTINGS_CLEARBIT_API_KEY_KEY)['value'];
		$map = !empty($type) ? UtilsSettingsHelper::getOptionValueGroup(self::SETTINGS_CLEARBIT_MAP_HUBSPOT_KEYS_KEY . '-' . $type) : true;

		if (!$isUsed || !$apiKey || !$map) {
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
			return UtilsSettingsOutputHelper::getNoValidGlobalConfig(self::SETTINGS_TYPE_KEY);
		}

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_CLEARBIT_SETTINGS_USE_KEY),
								'checkboxesIsRequired' => false,
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use Clearbit integration', 'eightshift-forms'),
										'checkboxHelp' => \__('Use Clearbit integration to enrich your data on this form.', 'eightshift-forms'),
										'checkboxIsChecked' => UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_CLEARBIT_SETTINGS_USE_KEY, self::SETTINGS_CLEARBIT_SETTINGS_USE_KEY, $formId),
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
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_CLEARBIT_USE_KEY, self::SETTINGS_CLEARBIT_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('General', 'eightshift-forms'),
						'tabContent' => [
							UtilsSettingsOutputHelper::getPasswordFieldWithGlobalVariable(
								UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(
									Variables::getApiKeyClearbit(),
									self::SETTINGS_CLEARBIT_API_KEY_KEY,
									'ES_API_KEY_CLEARBIT'
								),
								\__('API key', 'eightshift-forms'),
							),
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_CLEARBIT_USE_JOBS_QUEUE_KEY),
								'checkboxesIsRequired' => false,
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use jobs queue', 'eightshift-forms'),
										'checkboxHelp' => \__('Turn on your jobs queue to process Clearbit data using CRON.', 'eightshift-forms'),
										'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_CLEARBIT_USE_JOBS_QUEUE_KEY, self::SETTINGS_CLEARBIT_USE_JOBS_QUEUE_KEY),
										'checkboxValue' => self::SETTINGS_CLEARBIT_USE_JOBS_QUEUE_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
										'checkboxAsToggleSize' => 'medium',
									],
								]
							],
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
									'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY),
									'checkboxesIsRequired' => true,
									'checkboxesContent' => \array_map(
										function ($item) {
											return [
												'component' => 'checkbox',
												'checkboxLabel' => $item,
												'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked($item, self::SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY),
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
									'textareaValue' => \wp_json_encode(UtilsSettingsHelper::getOptionValueGroup(SettingsClearbit::SETTINGS_CLEARBIT_JOBS_KEY), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE),
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
									\apply_filters(UtilsHooksHelper::getFilterName(['integrations', SettingsHubspot::SETTINGS_TYPE_KEY, 'getContactProperties']), []),
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

		$clearbitAvailableKeys = UtilsSettingsHelper::getOptionCheckboxValues(SettingsClearbit::SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY);

		$clearbitMapValue = UtilsSettingsHelper::getOptionValueGroup($key);

		if (!$clearbitAvailableKeys) {
			return [];
		}

		return [
			'component' => 'group',
			'groupName' => UtilsSettingsHelper::getOptionName($key),
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
