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
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsClearbit class.
 */
class SettingsClearbit implements SettingsClearbitDataInterface, ServiceInterface, UtilsSettingGlobalInterface
{
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
		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		$typeItems = \apply_filters(UtilsConfig::FILTER_SETTINGS_DATA, [])[self::SETTINGS_TYPE_KEY]['integration'];

		if (!isset($typeItems[$type])) {
			return false;
		}

		$useClearbit = UtilsSettingsHelper::getSettingValue($typeItems[$type]['use'], $formId);

		if (empty($useClearbit)) {
			return false;
		}

		$mapSet = UtilsSettingsHelper::getOptionValueGroup($typeItems[$type]['map']);

		if (empty($mapSet)) {
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
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_CLEARBIT_USE_KEY, self::SETTINGS_CLEARBIT_USE_KEY);
		$apiKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyClearbit(), self::SETTINGS_CLEARBIT_API_KEY_KEY)['value'];

		if (!$isUsed || empty($apiKey)) {
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
						],
					],
					self::isSettingsGlobalValid() ? [
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
					 ] : [],
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
	 * @param string $formId Form ID.
	 * @param string $key Key for use toggle.
	 *
	 * @return array<string, array<int, array<string, array<int, array<string, mixed>>|bool|string>>|string>
	 */
	public function getOutputClearbit(string $formId, string $key): array
	{
		$useClearbit = \apply_filters(SettingsClearbit::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, $formId);

		if (!$useClearbit) {
			return [];
		}

		$isUsed = UtilsSettingsHelper::isSettingCheckboxChecked($key, $key, $formId);

		$output = [
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => '',
				'checkboxesName' => UtilsSettingsHelper::getSettingName($key),
				'checkboxesIsRequired' => false,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => \__('Clearbit integration', 'eightshift-forms'),
						'checkboxIsChecked' => $isUsed,
						'checkboxValue' => $key,
						'checkboxSingleSubmit' => true,
						'checkboxAsToggle' => true,
						'checkboxAsToggleSize' => 'medium',
					],
				]
			],
		];

		return [
			'component' => 'tab',
			'tabLabel' => \__('Clearbit', 'eightshift-forms'),
			'tabContent' => [
				...$output,
			],
		];
	}

	/**
	 * Output array settings for form.
	 *
	 * @param array<string, string> $properties Array of properties from integration.
	 * @param array<string, string> $keys Array of keys to get data from.
	 *
	 * @return array<string, array<int, array<string, array<int, array<string, array<int, array<string, array<int|string, array<string, bool|string>>|string>>|bool|string>>|string>>|string>
	 */
	public function getOutputGlobalClearbit(array $properties, array $keys): array
	{
		$mapKey = $keys['map'] ?? '';

		$isValid = $this->isSettingsGlobalValid();

		if (!$isValid) {
			return [];
		}

		$clearbitAvailableKeys = UtilsSettingsHelper::getOptionCheckboxValues(SettingsClearbit::SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY);

		$clearbitMapValue = UtilsSettingsHelper::getOptionValueGroup($mapKey);

		return [
			'component' => 'tab',
				'tabLabel' => \__('Clearbit', 'eightshift-forms'),
				'tabContent' => [
					[
						'component' => 'intro',
						'introSubtitle' => \__('Map Clearbit fields to HubSpot properties.', 'eightshift-forms'),
					],
					[
						'component' => 'divider',
						'dividerExtraVSpacing' => true,
					],
					$clearbitAvailableKeys ? [
						'component' => 'group',
						'groupName' => UtilsSettingsHelper::getOptionName($mapKey),
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
					] : [],
				],
		];
	}
}
