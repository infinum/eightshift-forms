<?php

/**
 * Goodbits Settings class.
 *
 * @package EightshiftForms\Integrations\Goodbits
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Goodbits;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_GOODBITS_USE_KEY, self::SETTINGS_GOODBITS_USE_KEY);
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
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m6.5 13.5 2.358-7.074m.249 7.074 2.358-7.074m.25 7.074 2.358-7.074" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="10" cy="10" r="9" stroke="#29A3A3" stroke-width="1.5" fill="none"/></svg>',
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
					'highlightedContentTitle' => __('Some config required', 'eightshift-forms'),
					// translators: %s will be replaced with the global settings url.
					'highlightedContentSubtitle' => sprintf(__('Before using Goodbits you need to configure it in  <a href="%s">global settings</a>.', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(self::SETTINGS_TYPE_KEY)),
					'highlightedContentIcon' => 'tools',
				],
			];
		}

		$items = $this->goodbitsClient->getItems();

		if (!$items) {
			return [
				[
					'component' => 'highlighted-content',
					'highlightedContentTitle' => __('Something went wrong', 'eightshift-forms'),
					'highlightedContentSubtitle' => __('Data from Goodbits couldn\'t be fetched. Check the API key.', 'eightshift-forms'),
					'highlightedContentIcon' => 'error',
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
				'introTitle' => __('Goodbits', 'eightshift-forms'),
			],
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName(self::SETTINGS_GOODBITS_LIST_KEY),
				'selectId' => $this->getSettingsName(self::SETTINGS_GOODBITS_LIST_KEY),
				'selectFieldLabel' => __('List', 'eightshift-forms'),
				'selectOptions' => $itemOptions,
				'selectIsRequired' => true,
				'selectValue' => $selectedItem,
				'selectSingleSubmit' => true,
			],
		];

		// If the user has selected the list.
		if ($selectedItem) {
			$beforeContent = '';

			$filterName = Filters::getIntegrationFilterName(self::SETTINGS_TYPE_KEY, 'adminFieldsSettings');
			if (has_filter($filterName)) {
				$beforeContent = \apply_filters($filterName, '') ?? '';
			}

			$output = array_merge(
				$output,
				[
					[
						'component' => 'divider',
					],
					[
						'component' => 'intro',
						'introTitle' => __('Form fields', 'eightshift-forms'),
						'introTitleSize' => 'medium',
						'introSubtitle' => __('Control which fields show up on the frontend, set up how they look and work.', 'eightshift-forms'),
					],
					[
						'component' => 'group',
						'groupId' => $this->getSettingsName(self::SETTINGS_GOODBITS_INTEGRATION_FIELDS_KEY),
						'groupBeforeContent' => $beforeContent,
						'groupStyle' => 'integration',
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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_GOODBITS_USE_KEY, self::SETTINGS_GOODBITS_USE_KEY);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Goodbits', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introTitle' => __('How to get the API key?', 'eightshift-forms'),
				'introTitleSize' => 'medium',
				// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'introSubtitle' => __('<ol>
						<li>Log in to your Goodbits Account.</li>
						<li>Go to <strong>Settings</strong>, then click <strong><a target="_blank" href="https://app.Goodbits.com/integrations/api/">API</a></strong>.</li>
						<li>Copy the API key into the field below or use the global constant</li>
					</ol>', 'eightshift-forms'),
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => '',
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
						'component' => 'divider',
					],
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_GOODBITS_API_KEY_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_GOODBITS_API_KEY_KEY),
						'inputFieldLabel' => __('API key', 'eightshift-forms'),
						'inputFieldHelp' => __('Can also be provided via a global variable.', 'eightshift-forms'),
						'inputType' => 'password',
						'inputIsRequired' => true,
						'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_GOODBITS_API_KEY_KEY),
						'inputIsDisabled' => !empty($apiKey),
					],
				]
			);
		}

		return $output;
	}
}
