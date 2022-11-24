<?php

/**
 * HubSpot Settings class.
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Hubspot;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\Clearbit\ClearbitClientInterface;
use EightshiftForms\Integrations\Clearbit\SettingsClearbitDataInterface;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingsAll;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftForms\Troubleshooting\SettingsTroubleshootingDataInterface;
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
	 * Filemanager folder Key.
	 */
	public const SETTINGS_HUBSPOT_FILEMANAGER_FOLDER_KEY = 'hubspot-filemanager-folder';

	/**
	 * File upload allowed types key.
	 */
	public const SETTINGS_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY = 'hubspot-upload-allowed-types';

	/**
	 * Global File upload allowed types key.
	 */
	public const SETTINGS_GLOBAL_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY = 'hubspot-global-upload-allowed-types';

	/**
	 * Use Clearbit Key.
	 */
	public const SETTINGS_HUBSPOT_USE_CLEARBIT_KEY = 'hubspot-use-clearbit';

	/**
	 * Use Clearbit email field Key.
	 */
	public const SETTINGS_HUBSPOT_CLEARBIT_EMAIL_FIELD_KEY = 'hubspot-clearbit-email-field';

	/**
	 * Use Clearbit map keys Key.
	 */
	public const SETTINGS_HUBSPOT_CLEARBIT_MAP_KEYS_KEY = 'hubspot-clearbit-map-keys';

	/**
	 * Instance variable for Clearbit data.
	 *
	 * @var ClearbitClientInterface
	 */
	protected $clearbitClient;

	/**
	 * Instance variable for Clearbit settings.
	 *
	 * @var SettingsClearbitDataInterface
	 */
	protected $settingsClearbit;

	/**
	 * Instance variable for Hubspot data.
	 *
	 * @var HubspotClientInterface
	 */
	protected $hubspotClient;

	/**
	 * Instance variable for HubSpot form data.
	 *
	 * @var MapperInterface
	 */
	protected $hubspot;

	/**
	 * Instance variable for Troubleshooting settings.
	 *
	 * @var SettingsTroubleshootingDataInterface
	 */
	protected $settingsTroubleshooting;

	/**
	 * Create a new instance.
	 *
	 * @param ClearbitClientInterface $clearbitClient Inject Clearbit which holds Clearbit connect data.
	 * @param SettingsClearbitDataInterface $settingsClearbit Inject Clearbit which holds Clearbit settings data.
	 * @param HubspotClientInterface $hubspotClient Inject Hubspot which holds Hubspot connect data.
	 * @param MapperInterface $hubspot Inject HubSpot which holds HubSpot form data.
	 * @param SettingsTroubleshootingDataInterface $settingsTroubleshooting Inject Troubleshooting which holds Troubleshooting settings data.
	 */
	public function __construct(
		ClearbitClientInterface $clearbitClient,
		SettingsClearbitDataInterface $settingsClearbit,
		HubspotClientInterface $hubspotClient,
		MapperInterface $hubspot,
		SettingsTroubleshootingDataInterface $settingsTroubleshooting
	) {
		$this->clearbitClient = $clearbitClient;
		$this->settingsClearbit = $settingsClearbit;
		$this->hubspotClient = $hubspotClient;
		$this->hubspot = $hubspot;
		$this->settingsTroubleshooting = $settingsTroubleshooting;
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
		$isUsed = $this->isCheckboxOptionChecked(SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY, SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY);
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
			'label' => \__('HubSpot', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => Filters::ALL[self::SETTINGS_TYPE_KEY]['icon'],
			'type' => SettingsAll::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
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
		// Bailout if global config is not valid.
		if (!$this->isSettingsGlobalValid()) {
			return [
				[
					'component' => 'highlighted-content',
					'highlightedContentTitle' => \__('Some config required', 'eightshift-forms'),
					// translators: %s will be replaced with the global settings url.
					'highlightedContentSubtitle' => \sprintf(\__('Before using HubSpot you need to configure it in  <a href="%s">global settings</a>.', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(self::SETTINGS_TYPE_KEY)),
					'highlightedContentIcon' => 'tools',
				]
			];
		}

		$items = $this->hubspotClient->getItems(false);

		// Bailout if items are missing.
		if (!$items) {
			return [
				[
					'component' => 'highlighted-content',
					'highlightedContentTitle' => \__('Something went wrong', 'eightshift-forms'),
					'highlightedContentSubtitle' => \__('Data from HubSpot couldn\'t be fetched. Check the API key.', 'eightshift-forms'),
					'highlightedContentIcon' => 'error',
				],
			];
		}

		// Find selected item.
		$selectedItem = $this->getSettingsValue(self::SETTINGS_HUBSPOT_ITEM_ID_KEY, $formId);

		// If the user has selected the list item add additional settings.
		if ($selectedItem) {
			$formFields = $this->hubspot->getFormFields($formId);

			$output = \array_merge(
				$this->getOutputFilemanager($formId),
				$this->settingsClearbit->getOutputClearbit(
					$formId,
					$formFields,
					[
						'use' => self::SETTINGS_HUBSPOT_USE_CLEARBIT_KEY,
						'email' => self::SETTINGS_HUBSPOT_CLEARBIT_EMAIL_FIELD_KEY,
					]
				),
				$this->getOutputFields($formId, $formFields)
			);
		}

		return \array_merge(
			$this->getOutputFormSelection($formId, $items, $selectedItem),
			$output ?? []
		);
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_HUBSPOT_USE_KEY, self::SETTINGS_HUBSPOT_USE_KEY);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => \__('HubSpot', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introTitle' => \__('How to get the API key?', 'eightshift-forms'),
				'introTitleSize' => 'small',
				// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'introSubtitle' => \__('<ol>
						<li>Log in to your HubSpot account</li>
						<li>Click on the settings cog icon in the top right, next to your account</li>
						<li>In the menu on the left, under <strong>Integrations</strong> click <strong>Private Apps</strong></li>
						<li>Click on <strong>Create a private app</strong></li>
						<li>Provide the app name and these scopes: <strong>forms, files, crm.objects.contacts.write, crm.schemas.custom.read</strong>.</li>
						<li>Copy the API key into the field below or use the global constant.</li>
					</ol>', 'eightshift-forms'),
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => '',
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_HUBSPOT_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_USE_KEY),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => \__('Use HubSpot', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_HUBSPOT_USE_KEY, self::SETTINGS_HUBSPOT_USE_KEY),
						'checkboxValue' => self::SETTINGS_HUBSPOT_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];

		$outputValid = [];

		if ($isUsed) {
			$apiKey = Variables::getApiKeyHubspot();

			$outputValid = \array_merge(
				[
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_HUBSPOT_API_KEY_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_API_KEY_KEY),
						'inputFieldLabel' => \__('API key', 'eightshift-forms'),
						'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
						'inputType' => 'password',
						'inputIsRequired' => true,
						'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_HUBSPOT_API_KEY_KEY),
						'inputIsDisabled' => !empty($apiKey),
					],
					[
						'component' => 'divider',
					],
					[
						'component' => 'intro',
						'introTitle' => \__('File Upload', 'eightshift-forms'),
						'introSubtitle' => \__('Define global settings for file upload fields.', 'eightshift-forms'),
						'introTitleSize' => 'small',
					],
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_GLOBAL_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_GLOBAL_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
						'inputFieldLabel' => \__('Upload allowed types', 'eightshift-forms'),
						// translators: %s will be replaced with the link.
						'inputFieldHelp' => \sprintf(\__('Limit what file types users can upload using your Hubspot forms. Each type must be written with comma separator without dashes. You can find all <a href="%s" target="_blank">mime types here</a>.', 'eightshift-forms'), 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types'),
						'inputType' => 'text',
						'inputValue' => $this->getOptionValue(self::SETTINGS_GLOBAL_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
					],
				],
				$this->settingsClearbit->getOutputGlobalClearbit(
					$this->hubspotClient->getContactProperties(),
					[
						'map' => self::SETTINGS_HUBSPOT_CLEARBIT_MAP_KEYS_KEY,
					]
				)
			);
		}

		return [
			...$output,
			...$outputValid,
			...$this->settingsTroubleshooting->getOutputGlobalTroubleshooting(SettingsHubspot::SETTINGS_TYPE_KEY),
		];
	}

	/**
	 * Output array - form selection.
	 *
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $items Items from cache data.
	 * @param string $selectedItem Selected form item.
	 *
	 * @return array<int, array<string, array<int|string, array<string, mixed>>|bool|string>>
	 */
	private function getOutputFormSelection(string $formId, array $items, string $selectedItem): array
	{
		$manifestForm = Components::getManifest(\dirname(__DIR__, 2) . '/Blocks/components/form');

		$lastUpdatedTime = $items[ClientInterface::TRANSIENT_STORED_TIME]['title'] ?? '';
		unset($items[ClientInterface::TRANSIENT_STORED_TIME]);

		return [
			[
				'component' => 'intro',
				'introTitle' => \__('HubSpot', 'eightshift-forms'),
			],
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName(self::SETTINGS_HUBSPOT_ITEM_ID_KEY),
				'selectId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_ITEM_ID_KEY),
				'selectFieldLabel' => \__('Form', 'eightshift-forms'),
				// translators: %1$s will be replaced with js selector, %2$s will be replaced with the cache type, %3$s will be replaced with latest update time.
				'selectFieldHelp' => \sprintf(\__('If a list isn\'t showing up or is missing some items, try <a href="#" class="%1$s" data-type="%2$s">clearing the cache</a>. Last updated: %3$s.', 'eightshift-forms'), $manifestForm['componentCacheJsClass'], self::SETTINGS_TYPE_KEY, $lastUpdatedTime),
				'selectOptions' => \array_merge(
					[
						[
							'component' => 'select-option',
							'selectOptionLabel' => '',
							'selectOptionValue' => '',
						]
					],
					\array_map(
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
					)
				),
				'selectIsRequired' => true,
				'selectValue' => $selectedItem,
				'selectSingleSubmit' => true,
			],
		];
	}

	/**
	 * Output array - file manager.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return array<int, array<string, string>>
	 */
	private function getOutputFilemanager(string $formId): array
	{
		return [
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => \__('File manager', 'eightshift-forms'),
				'introTitleSize' => 'medium',
			],
			[
				'component' => 'input',
				'inputName' => $this->getSettingsName(self::SETTINGS_HUBSPOT_FILEMANAGER_FOLDER_KEY),
				'inputId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_FILEMANAGER_FOLDER_KEY),
				'inputPlaceholder' => HubspotClient::HUBSPOT_FILEMANAGER_DEFAULT_FOLDER_KEY,
				'inputFieldLabel' => \__('Folder', 'eightshift-forms'),
				'inputFieldHelp' => \__('If you use file input field all files will be uploaded to the specified folder.', 'eightshift-forms'),
				'inputType' => 'text',
				'inputValue' => $this->getSettingsValue(self::SETTINGS_HUBSPOT_FILEMANAGER_FOLDER_KEY, $formId),
			],
			[
				'component' => 'input',
				'inputName' => $this->getSettingsName(self::SETTINGS_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
				'inputId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
				'inputFieldLabel' => \__('Upload allowed types', 'eightshift-forms'),
				// translators: %s will be replaced with the link.
				'inputFieldHelp' => \sprintf(\__('Limit what file types users can upload using your Hubspot forms. Each type must be written with comma separator without dashes. You can find all <a href="%s" target="_blank">mime types here</a>. This field will override global settings.', 'eightshift-forms'), 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types'),
				'inputPlaceholder' => $this->getOptionValue(self::SETTINGS_GLOBAL_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
				'inputType' => 'text',
				'inputValue' => $this->getSettingsValue(self::SETTINGS_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY, $formId),
			],
		];
	}

	/**
	 * Output array - integration fields.
	 *
	 * @param string $formId Form ID.
	 * @param array<int, array<string, mixed>> $formFields Items from cache data.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getOutputFields(string $formId, array $formFields): array
	{
		$beforeContent = '';

		$filterName = Filters::getIntegrationFilterName(self::SETTINGS_TYPE_KEY, 'adminFieldsSettings');
		if (\has_filter($filterName)) {
			$beforeContent = \apply_filters($filterName, '') ?? '';
		}

		$sortingButton = Components::render('sorting');

		$formViewDetailsIsEditableFilterName = Filters::getIntegrationFilterName(self::SETTINGS_TYPE_KEY, 'fieldsSettingsIsEditable');
		if (\has_filter($formViewDetailsIsEditableFilterName)) {
			$sortingButton = \__('This integration sorting and editing is disabled because of the active filter in your project!', 'eightshift-forms');
		}

		return [
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => \__('Form fields', 'eightshift-forms'),
				'introTitleSize' => 'medium',
				// translators: %s replaces the button or string.
				'introSubtitle' => \sprintf(\__('
					Control which fields show up on the frontend, and set up how they look and work. <br />
					To change the field order, click on the button below. To save the new order, please click on the "save settings" button at the bottom of the page. <br /><br />
					%s', 'eightshift-forms'), $sortingButton),
			],
			[
				'component' => 'group',
				'groupId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_INTEGRATION_FIELDS_KEY),
				'groupBeforeContent' => $beforeContent,
				'additionalGroupClass' => Components::getComponent('sorting')['componentCombinedClass'],
				'groupStyle' => 'integration',
				'groupContent' => $this->getIntegrationFieldsDetails(
					self::SETTINGS_HUBSPOT_INTEGRATION_FIELDS_KEY,
					self::SETTINGS_TYPE_KEY,
					$formFields,
					$formId
				),
			]
		];
	}
}
