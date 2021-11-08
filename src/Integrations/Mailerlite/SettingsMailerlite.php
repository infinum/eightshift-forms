<?php

/**
 * Mailerlite Settings class.
 *
 * @package EightshiftForms\Integrations\Mailerlite
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailerlite;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMailerlite class.
 */
class SettingsMailerlite implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_mailerlite';

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_mailerlite';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_mailerlite';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_mailerlite';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'mailerlite';

	/**
	 * Mailerlite Use key.
	 */
	public const SETTINGS_MAILERLITE_USE_KEY = 'mailerlite-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_MAILERLITE_API_KEY_KEY = 'mailerlite-api-key';

	/**
	 * List ID Key.
	 */
	public const SETTINGS_MAILERLITE_LIST_KEY = 'mailerlite-list';

	/**
	 * Integration Breakpoints Key.
	 */
	public const SETTINGS_MAILERLITE_INTEGRATION_BREAKPOINTS_KEY = 'mailerlite-integration-breakpoints';

		/**
	 * Instance variable for Mailerlite data.
	 *
	 * @var ClientInterface
	 */
	protected $mailerliteClient;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $mailerliteClient Inject Mailerlite which holds Mailerlite connect data.
	 */
	public function __construct(ClientInterface $mailerliteClient) {
		$this->mailerliteClient = $mailerliteClient;
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
	 * Determin if settings are valid.
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

		$list = $this->getSettingsValue(SettingsMailerlite::SETTINGS_MAILERLITE_LIST_KEY, $formId);

		if (empty($list)) {
			return false;
		}

		return true;
	}

	/**
	 * Determin if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = (bool) $this->isCheckboxOptionChecked(self::SETTINGS_MAILERLITE_USE_KEY, self::SETTINGS_MAILERLITE_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyMailerlite()) ? Variables::getApiKeyMailerlite() : $this->getOptionValue(self::SETTINGS_MAILERLITE_API_KEY_KEY);

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
			'label' => __('Mailerlite', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg"><path d="M20.507 8.914c0 1.448-.613 2.73-1.616 3.732-1.114 1.114-2.73 1.393-2.73 2.34 0 1.281 2.062.891 4.04 2.87 1.309 1.308 2.117 3.035 2.117 5.04 0 3.956-3.176 7.104-7.16 7.104C11.176 30 8 26.852 8 22.9c0-2.009.808-3.736 2.117-5.045 1.978-1.978 4.039-1.588 4.039-2.869 0-.947-1.616-1.226-2.73-2.34-1.003-1.003-1.615-2.284-1.615-3.788 0-2.897 2.367-5.237 5.264-5.237.557 0 1.059.084 1.477.084.752 0 1.142-.335 1.142-.864 0-.306-.14-.696-.14-1.114C17.554.78 18.362 0 19.337 0c.975 0 1.755.808 1.755 1.783 0 1.03-.808 1.504-1.42 1.727-.502.167-.892.39-.892.891 0 .947 1.727 1.866 1.727 4.513zM19.95 22.9c0-2.758-2.034-4.986-4.791-4.986-2.758 0-4.791 2.228-4.791 4.986 0 2.73 2.033 4.986 4.79 4.986 2.758 0 4.792-2.26 4.792-4.986zM18.306 8.858c0-1.755-1.42-3.203-3.147-3.203-1.727 0-3.148 1.448-3.148 3.203s1.42 3.203 3.148 3.203c1.727 0 3.147-1.448 3.147-3.203z" fill="#23A47F" fill-rule="nonzero"/></svg>',
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
					'highlightedContentSubtitle' => sprintf(__('in order to use mailerlite integration please navigate to <a href="%s">global settings</a> and provide the missing configuration data.', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(self::SETTINGS_TYPE_KEY)),
				],
			];
		}

		$items = $this->mailerliteClient->getItems();

		if (!$items) {
			return [
				[
					'component' => 'highlighted-content',
					'highlightedContentTitle' => __('We are sorry but', 'eightshift-forms'),
					'highlightedContentSubtitle' => __('we couldn\'t get the data from the Mailerlite. Please check if you API key is valid.', 'eightshift-forms'),
				],
			];
		}

		$itemOptions = array_map(
			function ($option) use ($formId) {
				return [
					'component' => 'select-option',
					'selectOptionLabel' => $option['title'] ?? '',
					'selectOptionValue' => $option['id'] ?? '',
					'selectOptionIsSelected' => $this->isCheckedSettings($option['id'], self::SETTINGS_MAILERLITE_LIST_KEY, $formId),
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

		$selectedItem = $this->getSettingsValue(self::SETTINGS_MAILERLITE_LIST_KEY, $formId);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Mailerlite settings', 'eightshift-forms'),
				'introSubtitle' => __('Configure your Mailerlite settings in one place.', 'eightshift-forms'),
			],
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName(self::SETTINGS_MAILERLITE_LIST_KEY),
				'selectId' => $this->getSettingsName(self::SETTINGS_MAILERLITE_LIST_KEY),
				'selectFieldLabel' => __('List', 'eightshift-forms'),
				'selectFieldHelp' => __('Select list for subscription.', 'eightshift-forms'),
				'selectOptions' => $itemOptions,
				'selectIsRequired' => true,
				'selectValue' => $selectedItem,
				'selectSingleSubmit' => true,
			],
		];

		return $output;
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$isUsed = (bool) $this->isCheckboxOptionChecked(self::SETTINGS_MAILERLITE_USE_KEY, self::SETTINGS_MAILERLITE_USE_KEY);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Mailerlite settings', 'eightshift-forms'),
				'introSubtitle' => __('
					Configure your Mailerlite settings in one place. <br />
					To get a Mailerlite API key you must login to your <a target="_blank" href="https://app.mailerlite.com/integrations/api/">Mailerlite Account</a> and copy the provided API Key.', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introTitle' => __('How to get an API key?', 'eightshift-forms'),
				'introTitleSize' => 'medium',
				'introSubtitle' => __('
					1. Login to your Mailerlite Account. <br />
					2. Go to <a target="_blank" href="https://app.mailerlite.com/integrations/api/">Developer API</a>. <br />
					3. Copy the API key to the provided field or use global constant.
				', 'eightshift-forms'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => __('Check options to use', 'eightshift-forms'),
				'checkboxesFieldHelp' => __('Select integrations you want to use in your form.', 'eightshift-forms'),
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_MAILERLITE_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_MAILERLITE_USE_KEY),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Use Mailerlite', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_MAILERLITE_USE_KEY, self::SETTINGS_MAILERLITE_USE_KEY),
						'checkboxValue' => self::SETTINGS_MAILERLITE_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];

		if ($isUsed) {
			$apiKey = Variables::getApiKeyMailerlite();

			$output = array_merge(
				$output,
				[
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_MAILERLITE_API_KEY_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_MAILERLITE_API_KEY_KEY),
						'inputFieldLabel' => __('API Key', 'eightshift-forms'),
						'inputFieldHelp' => __('You can provide API key using global variable also.', 'eightshift-forms'),
						'inputType' => 'password',
						'inputIsRequired' => true,
						'inputValue' => !empty($apiKey) ? $apiKey : $this->getOptionValue(self::SETTINGS_MAILERLITE_API_KEY_KEY),
						'inputIsDisabled' => !empty($apiKey),
					],
				]
			);
		}

		return $output;
	}
}
