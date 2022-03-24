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
			'icon' => '<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"><g fill-rule="nonzero" fill="none"><path d="M10 0h7.324C18.8 0 20 1.2 20 2.676V10H10V0Z" fill="#31C2C2"/><path d="M0 10h10v10H3.19A3.191 3.191 0 0 1 0 16.81V10Z" fill="#29A3A3"/><path d="M10 10h10v7.431c0 1.42-1.15 2.57-2.57 2.57H10V10Z" fill="#E7F2FC"/><path d="M3.273 0H10v10H0V3.273A3.275 3.275 0 0 1 3.273 0Z" fill="#2CB7B7"/></g></svg>',
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
				'introTitleSize' => 'medium',
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
					'component' => 'divider',
				],
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
	 * @param array<string, string> $properties Array of properties from integration.
	 * @param array<string, string> $keys Array of keys to get data from.
	 *
	 * @return array<int, array<string, array<int|string, array<string, mixed>>|bool|string>>
	 */
	public function getOutputClearbit(string $formId, array $formFields, array $properties, array $keys): array
	{
		$useKey = isset($keys['use']) ? $keys['use'] : '';
		$emailFieldKey = isset($keys['email']) ? $keys['email'] : '';
		$mapKey = isset($keys['map']) ? $keys['map'] : '';

		$useClearbit = \apply_filters(SettingsClearbit::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, $formId);

		$outputClearbit = [];
		$outputClearbitMap = [];

		if (!$useClearbit) {
			return [];
		}

		$isClearbitUsed = $this->isCheckboxSettingsChecked($useKey, $useKey, $formId);

		$outputClearbit = [
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
						'checkboxIsChecked' => $isClearbitUsed,
						'checkboxValue' => $useKey,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];

		if ($isClearbitUsed) {
			$clearbitAvailableKeys = $this->getOptionCheckboxValues(SettingsClearbit::SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY);

			$clearbitMapValue = $this->getSettingsValueGroup($mapKey, $formId);

			$outputClearbitMap = [
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
				$clearbitAvailableKeys ? [
					'component' => 'group',
					'groupId' => $this->getSettingsName($mapKey),
					'groupStyle' => 'indent',
					'groupContent' => [
						[
							'component' => 'group',
							'groupLabel' => __('Map keys:', 'eightshift-forms'),
							'groupHelp' => __('Map HubSpot keys with Clearbit keys.', 'eightshift-forms'),
							'groupSaveOneField' => true,
							'groupContent' =>  array_map(
								function ($item) use ($clearbitMapValue, $properties) {
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

		return array_merge(
			$outputClearbit,
			$outputClearbitMap
		);
	}
}
