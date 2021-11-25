<?php

/**
 * HubSpot Settings class.
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Hubspot;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsHubspot class.
 */
class SettingsHubspot implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_hubspot';

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_hubspot';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_hubspot';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_hubspot';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'hubspot';

	/**
	 * HubSpot Use key.
	 */
	public const SETTINGS_HUBSPOT_USE_KEY = 'hubspot-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_HUBSPOT_API_KEY_KEY = 'hubspot-api-key';

	/**
	 * Item ID Key.
	 */
	public const SETTINGS_HUBSPOT_ITEM_ID_KEY = 'hubspot-item-id';

	/**
	 * Integration fields Key.
	 */
	public const SETTINGS_HUBSPOT_INTEGRATION_FIELDS_KEY = 'hubspot-integration-fields';

	/**
	 * Instance variable for Hubspot data.
	 *
	 * @var ClientInterface
	 */
	protected $hubspotClient;

	/**
	 * Instance variable for HubSpot form data.
	 *
	 * @var MapperInterface
	 */
	protected $hubspot;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $hubspotClient Inject Hubspot which holds Hubspot connect data.
	 * @param MapperInterface $hubspot Inject HubSpot which holds HubSpot form data.
	 */
	public function __construct(
		ClientInterface $hubspotClient,
		MapperInterface $hubspot
	) {
		$this->hubspotClient = $hubspotClient;
		$this->hubspot = $hubspot;
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

		$itemId = $this->getSettingsValue(self::SETTINGS_HUBSPOT_ITEM_ID_KEY, $formId);

		if (empty($itemId)) {
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
		$isUsed = (bool) $this->isCheckboxOptionChecked(SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY, SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyHubspot()) ? Variables::getApiKeyHubspot() : $this->getOptionValue(SettingsHubspot::SETTINGS_HUBSPOT_API_KEY_KEY);

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
			'label' => __('HubSpot', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg"><path d="M23.02 9.914V6.356A2.743 2.743 0 0024.6 3.883V3.8a2.748 2.748 0 00-2.74-2.741h-.084a2.748 2.748 0 00-2.74 2.74v.084a2.743 2.743 0 001.581 2.473V9.92a7.774 7.774 0 00-3.696 1.627l-9.784-7.62a3.122 3.122 0 10-1.462 1.901l9.62 7.49a7.796 7.796 0 00.12 8.79l-2.928 2.927a2.514 2.514 0 00-.726-.119 2.541 2.541 0 102.541 2.542 2.506 2.506 0 00-.118-.727l2.896-2.896a7.809 7.809 0 105.933-13.922m-1.2 11.721a4.007 4.007 0 114.016-4.005 4.007 4.007 0 01-4.007 4.007" fill="#FF7A59" fill-rule="nonzero"/></svg>',
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
					'highlightedContentSubtitle' => sprintf(__('in order to use HubSpot integration please navigate to <a href="%s">global settings</a> and provide the missing configuration data.', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(self::SETTINGS_TYPE_KEY)),
				]
			];
		}

		$items = $this->hubspotClient->getItems();

		if (!$items) {
			return [
				[
					'component' => 'highlighted-content',
					'highlightedContentTitle' => __('We are sorry but', 'eightshift-forms'),
					'highlightedContentSubtitle' => __('we couldn\'t get the data from Hubspot. Please check if your API key is valid.', 'eightshift-forms'),
				],
			];
		}

		$itemOptions = array_map(
			function ($option) use ($formId) {
				$id = $option['id'] ?? '';

				return [
					'component' => 'select-option',
					'selectOptionLabel' => $option['title'] ?? '',
					'selectOptionValue' => $id,
					'selectOptionIsSelected' => $this->isCheckedSettings($id, self::SETTINGS_HUBSPOT_ITEM_ID_KEY, $formId),
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

		$selectedItem = $this->getSettingsValue(self::SETTINGS_HUBSPOT_ITEM_ID_KEY, $formId);
		$manifestForm = Components::getManifest(dirname(__DIR__, 2) . '/Blocks/components/form');

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('HubSpot settings', 'eightshift-forms'),
				'introSubtitle' => __('Configure your HubSpot settings in one place.', 'eightshift-forms'),
			],
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName(self::SETTINGS_HUBSPOT_ITEM_ID_KEY),
				'selectId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_ITEM_ID_KEY),
				'selectFieldLabel' => __('Form ID', 'eightshift-forms'),
				// translators: %1$s will be replaced with js selector, %2$s will be replaced with the cache type.
				'selectFieldHelp' => sprintf(__('Select what HubSpot form you want to show on this form. If you don\'t see your lists correctly try clearing cache on this <a href="#" class="%1$s" data-type="%2$s">link</a>.', 'eightshift-forms'), $manifestForm['componentCacheJsClass'], self::SETTINGS_TYPE_KEY),
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
						'introSubtitle' => __('Configure your Mailchimp form frontend view in one place.', 'eightshift-forms'),
					],
					[
						'component' => 'group',
						'groupId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_INTEGRATION_FIELDS_KEY),
						'groupContent' => $this->getIntegrationFieldsDetails(
							self::SETTINGS_HUBSPOT_INTEGRATION_FIELDS_KEY,
							self::SETTINGS_TYPE_KEY,
							$this->hubspot->getFormFields($formId),
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
		$isUsed = (bool) $this->isCheckboxOptionChecked(self::SETTINGS_HUBSPOT_USE_KEY, self::SETTINGS_HUBSPOT_USE_KEY);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('HubSpot settings', 'eightshift-forms'),
				'introSubtitle' => __('Configure your HubSpot settings in one place.', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introTitle' => __('How to get an API key?', 'eightshift-forms'),
				'introTitleSize' => 'medium',
				'introSubtitle' => __('
					1. Login to your HubSpot Account. <br />
				', 'eightshift-forms'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => __('Check options to use', 'eightshift-forms'),
				'checkboxesFieldHelp' => __('Select integrations you want to use in your form.', 'eightshift-forms'),
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_HUBSPOT_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_USE_KEY),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Use HubSpot', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_HUBSPOT_USE_KEY, self::SETTINGS_HUBSPOT_USE_KEY),
						'checkboxValue' => self::SETTINGS_HUBSPOT_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];

		if ($isUsed) {
			$apiKey = Variables::getApiKeyHubspot();

			$output = array_merge(
				$output,
				[
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_HUBSPOT_API_KEY_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_API_KEY_KEY),
						'inputFieldLabel' => __('API Key', 'eightshift-forms'),
						'inputFieldHelp' => __('Open your HubSpot account and provide API key. You can provide API key using global variable also.', 'eightshift-forms'),
						'inputType' => 'password',
						'inputIsRequired' => true,
						'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_HUBSPOT_API_KEY_KEY),
						'inputIsDisabled' => !empty($apiKey),
					]
				]
			);
		}

		return $output;
	}
}
