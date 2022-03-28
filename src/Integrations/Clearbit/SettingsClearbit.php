<?php

/**
 * Clearbit Settings class.
 *
 * @package EightshiftForms\Integrations\Clearbit
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Clearbit;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsClearbit class.
 */
class SettingsClearbit implements SettingsClearbitDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_clearbit';

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
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
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

		$typeItems = Filters::ALL[self::SETTINGS_TYPE_KEY]['integration'];

		if (!isset($typeItems[$type])) {
			return false;
		}

		$useClearbit = $this->getSettingsValue($typeItems[$type]['use'], $formId);

		if (empty($useClearbit)) {
			return false;
		}

		$emailSet = $this->getSettingsValue($typeItems[$type]['email'], $formId);

		if (empty($emailSet)) {
			return false;
		}

		$mapSet = $this->getOptionValueGroup($typeItems[$type]['map']);

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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_CLEARBIT_USE_KEY, self::SETTINGS_CLEARBIT_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyClearbit()) ? Variables::getApiKeyClearbit() : $this->getOptionValue(self::SETTINGS_CLEARBIT_API_KEY_KEY);

		if (!$isUsed || empty($apiKey)) {
			return false;
		}

		return true;
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => __('Clearbit', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => Filters::ALL[self::SETTINGS_TYPE_KEY]['icon'],
		];
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
		return [];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_CLEARBIT_USE_KEY, self::SETTINGS_CLEARBIT_USE_KEY);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Clearbit', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introTitle' => __('How to get the API key?', 'eightshift-forms'),
				'introTitleSize' => 'small',
				// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'introSubtitle' => __('<ol>
						<li>Log in to your Clearbit Account.</li>
						<li>Then click on the <strong><a target="_blank" href="https://dashboard.clearbit.com/api">API</a></strong> in the sidebar.</li>
						<li>Copy the secret API key into the field below or use the global constant</li>
					</ol>', 'eightshift-forms'),
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => '',
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_CLEARBIT_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_CLEARBIT_USE_KEY),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Use Clearbit', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_CLEARBIT_USE_KEY, self::SETTINGS_CLEARBIT_USE_KEY),
						'checkboxValue' => self::SETTINGS_CLEARBIT_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];

		$outputApi = [];

		if ($isUsed) {
			$apiKey = Variables::getApiKeyClearbit();

			$outputApi = [
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_CLEARBIT_API_KEY_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_CLEARBIT_API_KEY_KEY),
					'inputFieldLabel' => __('API key', 'eightshift-forms'),
					'inputFieldHelp' => __('Can also be provided via a global variable.', 'eightshift-forms'),
					'inputType' => 'password',
					'inputIsRequired' => true,
					'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_CLEARBIT_API_KEY_KEY),
					'inputIsDisabled' => !empty($apiKey),
				],
			];
		}

		$isValid = self::isSettingsGlobalValid();
		$outputUsage = [];

		if ($isValid) {
			$outputUsage = [
				[
					'component' => 'divider',
				],
				[
					'component' => 'checkboxes',
					'checkboxesFieldLabel' => __('Available fields', 'eightshift-forms'),
					'checkboxesFieldHelp' => __('Select fields that you want to use in your forms.', 'eightshift-forms'),
					'checkboxesName' => $this->getSettingsName(self::SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY),
					'checkboxesId' => $this->getSettingsName(self::SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY),
					'checkboxesIsRequired' => true,
					'checkboxesContent' => array_map(
						function ($item) {
							return [
								'component' => 'checkbox',
								'checkboxLabel' => $item,
								'checkboxIsChecked' => $this->isCheckboxOptionChecked($item, self::SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY),
								'checkboxValue' => $item,
							];
						},
						$this->clearbitClient->getParams()
					),
				],
			];
		}

		return array_merge(
			$output,
			$outputApi,
			$outputUsage
		);
	}

	/**
	 * Output array settings for form.
	 *
	 * @param string $formId Form ID.
	 * @param array<int, array<string, mixed>> $formFields Items from cache data.
	 * @param array<string, string> $keys Array of keys to get data from.
	 *
	 * @return array<int, array<string, array<int|string, array<string, mixed>>|bool|string>>
	 */
	public function getOutputClearbit(string $formId, array $formFields, array $keys): array
	{
		$useKey = isset($keys['use']) ? $keys['use'] : '';
		$emailFieldKey = isset($keys['email']) ? $keys['email'] : '';

		$useClearbit = \apply_filters(SettingsClearbit::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, $formId);

		if (!$useClearbit) {
			return [];
		}

		$isUsed = $this->isCheckboxSettingsChecked($useKey, $useKey, $formId);

		$output = [
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => __('Clearbit', 'eightshift-forms'),
				'introTitleSize' => 'medium',
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => '',
				'checkboxesName' => $this->getSettingsName($useKey),
				'checkboxesId' => $this->getSettingsName($useKey),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Use Clearbit integration', 'eightshift-forms'),
						'checkboxIsChecked' => $isUsed,
						'checkboxValue' => $useKey,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];

		$outputEmail = [];

		if ($isUsed) {
			$outputEmail = [
				[
					'component' => 'select',
					'selectName' => $this->getSettingsName($emailFieldKey),
					'selectId' => $this->getSettingsName($emailFieldKey),
					'selectFieldLabel' => __('Email field', 'eightshift-forms'),
					'selectFieldHelp' => __('Select what field in HubSpot is email filed.', 'eightshift-forms'),
					'selectOptions' => array_merge(
						[
							[
								'component' => 'select-option',
								'selectOptionLabel' => '',
								'selectOptionValue' => '',
							],
						],
						array_map(
							function ($option) use ($formId, $emailFieldKey) {
								if ($option['component'] === 'input') {
									return [
										'component' => 'select-option',
										'selectOptionLabel' => $option['inputFieldLabel'] ?? '',
										'selectOptionValue' => $option['inputId'] ?? '',
										'selectOptionIsSelected' => $this->isCheckedSettings($option['inputId'], $emailFieldKey, $formId),
									];
								}
							},
							$formFields
						)
					),
					'selectIsRequired' => true,
					'selectValue' => $this->getSettingsValue($emailFieldKey, $formId),
				],
			];
		}

		return array_merge(
			$output,
			$outputEmail
		);
	}

	/**
	 * Output array settings for form.
	 *
	 * @param array<string, string> $properties Array of properties from integration.
	 * @param array<string, string> $keys Array of keys to get data from.
	 *
	 * @return array<int, array<string, array<int|string, array<string, mixed>>|bool|string>>
	 */
	public function getOutputGlobalClearbit(array $properties, array $keys): array
	{
		$mapKey = $keys['map'] ?? '';

		$isValid = $this->isSettingsGlobalValid();

		if (!$isValid) {
			return [];
		}

		$clearbitAvailableKeys = $this->getOptionCheckboxValues(SettingsClearbit::SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY);

		$clearbitMapValue = $this->getOptionValueGroup($mapKey);

		return [
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => __('Clearbit', 'eightshift-forms'),
				'introSubtitle' => __('Control which fields from Clearbit are connected to the HubSpot properties. <br/>First column is Clearbit field, and the secound column is HubSpot field.', 'eightshift-forms'),
				'introTitleSize' => 'medium',
			],
			$clearbitAvailableKeys ? [
				'component' => 'group',
				'groupId' => $this->getSettingsName($mapKey),
				'groupStyle' => 'default',
				'groupContent' => [
					[
						'component' => 'group',
						'groupSaveOneField' => true,
						'groupContent' =>  array_map(
							static function ($item) use ($clearbitMapValue, $properties) {
								$selectedValue = $clearbitMapValue[$item] ?? '';

								return [
									'component' => 'select',
									'selectName' => $item,
									'selectId' => $item,
									'selectFieldLabel' => $item,
									'selectOptions' => array_merge(
										[
											[
												'component' => 'select-option',
												'selectOptionLabel' => '',
												'selectOptionValue' => '',
											],
										],
										array_map(
											function ($option) use ($selectedValue) {
												return [
													'component' => 'select-option',
													'selectOptionLabel' => $option,
													'selectOptionValue' => $option,
													'selectOptionIsSelected' => $selectedValue === $option,
												];
											},
											$properties
										)
									),
								];
							},
							$clearbitAvailableKeys
						),
					],
				],
			] : [],
		];
	}
}
