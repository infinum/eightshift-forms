<?php

/**
 * Goodbits Settings class.
 *
 * @package EightshiftForms\Integrations\Goodbits
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Goodbits;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGoodbits class.
 */
class SettingsGoodbits implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_goodbits';

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_goodbits';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_goodbits';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_goodbits';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'goodbits';

	/**
	 * Goodbits Use key.
	 */
	public const SETTINGS_GOODBITS_USE_KEY = 'goodbits-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_GOODBITS_API_KEY_KEY = 'goodbits-api-key';

	/**
	 * List ID Key.
	 */
	public const SETTINGS_GOODBITS_LIST_KEY = 'goodbits-list';

	/**
	 * Integration fields Key.
	 */
	public const SETTINGS_GOODBITS_INTEGRATION_FIELDS_KEY = 'goodbits-integration-fields';

	/**
	 * Instance variable for Goodbits data.
	 *
	 * @var ClientInterface
	 */
	protected $goodbitsClient;

	/**
	 * Instance variable for Goodbits form data.
	 *
	 * @var MapperInterface
	 */
	protected $goodbits;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $goodbitsClient Inject Goodbits which holds Goodbits connect data.
	 * @param MapperInterface $goodbits Inject Goodbits which holds Goodbits form data.
	 */
	public function __construct(
		ClientInterface $goodbitsClient,
		MapperInterface $goodbits
	) {
		$this->goodbitsClient = $goodbitsClient;
		$this->goodbits = $goodbits;
	}
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
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

		$list = $this->getSettingsValue(SettingsGoodbits::SETTINGS_GOODBITS_LIST_KEY, $formId);

		if (empty($list)) {
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
		$isUsed = (bool) $this->isCheckboxOptionChecked(self::SETTINGS_GOODBITS_USE_KEY, self::SETTINGS_GOODBITS_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyGoodbits()) ? Variables::getApiKeyGoodbits() : $this->getOptionValue(self::SETTINGS_GOODBITS_API_KEY_KEY);

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
			'label' => __('Goodbits', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg"><g fill-rule="nonzero" fill="none"><circle fill="#5086FF" cx="15" cy="15" r="15"/><path d="M15 26.026C8.92 26.026 3.974 21.1 3.974 15c0-2.94 1.152-5.742 3.238-7.808.636-.596 1.649-.576 2.225.08.576.615.576 1.55 0 2.145-3.08 3.08-3.08 8.066-.02 11.166a7.818 7.818 0 0 0 2.523 1.689 7.967 7.967 0 0 0 6.12 0c4.013-1.69 5.88-6.298 4.192-10.312-1.232-2.94-4.093-4.808-7.272-4.808a1.619 1.619 0 0 1-1.649-1.51 1.619 1.619 0 0 1 1.51-1.649h.12c6.079-.08 11.085 4.788 11.185 10.868.08 6.08-4.788 11.086-10.868 11.185-.08-.02-.159-.02-.278-.02Z" fill="#FFF"/><path d="M14.94 16.649a1.56 1.56 0 0 1-1.57-1.57V5.623a1.619 1.619 0 0 1 1.51-1.65c.855-.04 1.59.656 1.65 1.51v9.577c-.02.874-.715 1.589-1.59 1.589Z" fill="#FFF"/></g></svg>',
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
		if (!$this->isSettingsGlobalValid()) {
			return [
				[
					'component' => 'highlighted-content',
					'highlightedContentTitle' => __('We are sorry but', 'eightshift-forms'),
					// translators: %s will be replaced with the global settings url.
					'highlightedContentSubtitle' => sprintf(__('in order to use Goodbits integration please navigate to <a href="%s">global settings</a> and provide the missing configuration data.', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(self::SETTINGS_TYPE_KEY)),
				],
			];
		}

		$items = $this->goodbitsClient->getItems();

		if (!$items) {
			return [
				[
					'component' => 'highlighted-content',
					'highlightedContentTitle' => __('We are sorry but', 'eightshift-forms'),
					'highlightedContentSubtitle' => __('we couldn\'t get the data from the Goodbits. Please check if your API key is valid.', 'eightshift-forms'),
				],
			];
		}

		$itemOptions = array_map(
			function ($option) use ($formId) {
				return [
					'component' => 'select-option',
					'selectOptionLabel' => $option['title'] ?? '',
					'selectOptionValue' => $option['id'] ?? '',
					'selectOptionIsSelected' => $this->isCheckedSettings($option['id'], self::SETTINGS_GOODBITS_LIST_KEY, $formId),
				];
			},
			$items
		);

		array_unshift(
			$itemOptions,
			[
				'component' => 'select-option',
				'selectOptionLabel' => '',
				'selectOptionValue' => '',
			]
		);

		$selectedItem = $this->getSettingsValue(self::SETTINGS_GOODBITS_LIST_KEY, $formId);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Goodbits settings', 'eightshift-forms'),
				'introSubtitle' => __('Configure your Goodbits settings in one place.', 'eightshift-forms'),
			],
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName(self::SETTINGS_GOODBITS_LIST_KEY),
				'selectId' => $this->getSettingsName(self::SETTINGS_GOODBITS_LIST_KEY),
				'selectFieldLabel' => __('List', 'eightshift-forms'),
				'selectFieldHelp' => __('Select list for subscription.', 'eightshift-forms'),
				'selectOptions' => $itemOptions,
				'selectIsRequired' => true,
				'selectValue' => $selectedItem,
				'selectSingleSubmit' => true,
			],
		];

		// If the user has selected the list.
		if ($selectedItem) {
			$output = array_merge(
				$output,
				[
					[
						'component' => 'divider',
					],
					[
						'component' => 'intro',
						'introTitle' => __('Form View Details', 'eightshift-forms'),
						'introTitleSize' => 'medium',
						'introSubtitle' => __('Configure your Goodbits form frontend view in one place.', 'eightshift-forms'),
					],
					[
						'component' => 'group',
						'groupId' => $this->getSettingsName(self::SETTINGS_GOODBITS_INTEGRATION_FIELDS_KEY),
						'groupContent' => $this->getIntegrationFieldsDetails(
							self::SETTINGS_GOODBITS_INTEGRATION_FIELDS_KEY,
							self::SETTINGS_TYPE_KEY,
							$this->goodbits->getFormFields($formId),
							$formId
						),
					]
				]
			);
		}

		return $output;
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$isUsed = (bool) $this->isCheckboxOptionChecked(self::SETTINGS_GOODBITS_USE_KEY, self::SETTINGS_GOODBITS_USE_KEY);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Goodbits settings', 'eightshift-forms'),
				'introSubtitle' => __('
					Configure your Goodbits settings in one place. <br />
					To get a Goodbits API key you must login to your <a target="_blank" href="https://app.Goodbits.com/integrations/api/">Goodbits Account</a> and copy the provided API Key.', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introTitle' => __('How to get an API key?', 'eightshift-forms'),
				'introTitleSize' => 'medium',
				'introSubtitle' => __('
					1. Login to your Goodbits Account. <br />
					2. Go to <a target="_blank" href="https://app.Goodbits.com/integrations/api/">Developer API</a>. <br />
					3. Copy the API key to the provided field or use global constant.
				', 'eightshift-forms'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => __('Check options to use', 'eightshift-forms'),
				'checkboxesFieldHelp' => __('Select integrations you want to use in your form.', 'eightshift-forms'),
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_GOODBITS_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_GOODBITS_USE_KEY),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Use Goodbits', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GOODBITS_USE_KEY, self::SETTINGS_GOODBITS_USE_KEY),
						'checkboxValue' => self::SETTINGS_GOODBITS_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];

		if ($isUsed) {
			$apiKey = Variables::getApiKeyGoodbits();

			$output = array_merge(
				$output,
				[
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_GOODBITS_API_KEY_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_GOODBITS_API_KEY_KEY),
						'inputFieldLabel' => __('API Key', 'eightshift-forms'),
						'inputFieldHelp' => __('You can provide API key using global variable also.', 'eightshift-forms'),
						'inputType' => 'password',
						'inputIsRequired' => true,
						'inputValue' => !empty($apiKey) ? $apiKey : $this->getOptionValue(self::SETTINGS_GOODBITS_API_KEY_KEY),
						'inputIsDisabled' => !empty($apiKey),
					],
				]
			);
		}

		return $output;
	}
}
