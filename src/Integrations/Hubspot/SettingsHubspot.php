<?php

/**
 * HubSpot Settings class.
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Hubspot;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\Clearbit\ClearbitClientInterface;
use EightshiftForms\Integrations\Clearbit\SettingsClearbitDataInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsHubspot class.
 */
class SettingsHubspot implements SettingInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

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
	 * Conditional tags key.
	 */
	public const SETTINGS_HUBSPOT_CONDITIONAL_TAGS_KEY = 'hubspot-conditional-tags';

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
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param ClearbitClientInterface $clearbitClient Inject Clearbit which holds Clearbit connect data.
	 * @param SettingsClearbitDataInterface $settingsClearbit Inject Clearbit which holds Clearbit settings data.
	 * @param HubspotClientInterface $hubspotClient Inject Hubspot which holds Hubspot connect data.
	 * @param MapperInterface $hubspot Inject HubSpot which holds HubSpot form data.
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(
		ClearbitClientInterface $clearbitClient,
		SettingsClearbitDataInterface $settingsClearbit,
		HubspotClientInterface $hubspotClient,
		MapperInterface $hubspot,
		SettingsFallbackDataInterface $settingsFallback
	) {
		$this->clearbitClient = $clearbitClient;
		$this->settingsClearbit = $settingsClearbit;
		$this->hubspotClient = $hubspotClient;
		$this->hubspot = $hubspot;
		$this->settingsFallback = $settingsFallback;
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
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		$type = self::SETTINGS_TYPE_KEY;

		// Bailout if global config is not valid.
		if (!$this->isSettingsGlobalValid()) {
			return $this->getNoValidGlobalConfigOutput($type);
		}

		// Get forms from the API.
		$items = $this->hubspotClient->getItems(false);

		// Bailout if integration can't fetch data.
		if (!$items) {
			return $this->getNoIntegrationFetchDataOutput($type);
		}

		// Find selected form id.
		$selectedFormId = $this->getSettingsValue(self::SETTINGS_HUBSPOT_ITEM_ID_KEY, $formId);

		$output = [];

		// If the user has selected the list.
		if ($selectedFormId) {
			$formFields = $this->hubspot->getFormFields($formId);

			// Output additonal tabs for config.
			$output = [
				'component' => 'tabs',
				'tabsContent' => [
					$this->getOutputIntegrationFields(
						$formId,
						$formFields,
						$type,
						self::SETTINGS_HUBSPOT_INTEGRATION_FIELDS_KEY,
					),
					$this->getOutputConditionalTags(
						$formId,
						$formFields,
						self::SETTINGS_HUBSPOT_CONDITIONAL_TAGS_KEY
					),
					$this->getOutputFilemanager($formId),
					$this->settingsClearbit->getOutputClearbit(
						$formId,
						$formFields,
						[
							'use' => self::SETTINGS_HUBSPOT_USE_CLEARBIT_KEY,
							'email' => self::SETTINGS_HUBSPOT_CLEARBIT_EMAIL_FIELD_KEY,
						]
					),
				],
			];
		}

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			...$this->getOutputFormSelection(
				$formId,
				$items,
				$selectedFormId,
				self::SETTINGS_TYPE_KEY,
				self::SETTINGS_HUBSPOT_ITEM_ID_KEY
			),
			$output,
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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_HUBSPOT_USE_KEY, self::SETTINGS_HUBSPOT_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$apiKey = Variables::getApiKeyHubspot();

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('API', 'eightshift-forms'),
						'tabContent' => [
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
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_GLOBAL_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
								'inputId' => $this->getSettingsName(self::SETTINGS_GLOBAL_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
								'inputFieldLabel' => \__('Upload allowed types', 'eightshift-forms'),
								// translators: %s will be replaced with the link.
								'inputFieldHelp' => \sprintf(\__('Limit what file types users can upload using your Hubspot forms. Each type must be written with a comma separator without dashes. You can find all <a href="%s" target="_blank">mime types here</a>.', 'eightshift-forms'), 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types'),
								'inputType' => 'text',
								'inputValue' => $this->getOptionValue(self::SETTINGS_GLOBAL_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
							],
						],
					],
					$this->settingsClearbit->getOutputGlobalClearbit(
						$this->hubspotClient->getContactProperties(),
						[
							'map' => self::SETTINGS_HUBSPOT_CLEARBIT_MAP_KEYS_KEY,
						]
					),
					$this->settingsFallback->getOutputGlobalFallback(SettingsHubspot::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your HubSpot account.', 'eightshift-forms'),
									\__('Click on the settings cog icon in the top right, next to your account.', 'eightshift-forms'),
									\__('In the menu on the left, under <strong>Integrations</strong> click <strong>Private Apps</strong>.', 'eightshift-forms'),
									\__('Click on <strong>Create a private app</strong>.', 'eightshift-forms'),
									\__('Provide the app name and these scopes: <strong>forms, files, crm.objects.contacts.write, crm.schemas.custom.read</strong>.', 'eightshift-forms'),
									\__('Copy the API key into the field below or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Output array - file manager.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return array<string, array<int, array<string, string>>|string>
	 */
	private function getOutputFilemanager(string $formId): array
	{
		return [
			'component' => 'tab',
			'tabLabel' => \__('Options', 'eightshift-forms'),
			'tabContent' => [
				[
					'component' => 'intro',
					'introSubtitle' => \__('In these settings, you can configure additional HubSpot specific options.', 'eightshift-forms'),
				],
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_HUBSPOT_FILEMANAGER_FOLDER_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_FILEMANAGER_FOLDER_KEY),
					'inputPlaceholder' => HubspotClient::HUBSPOT_FILEMANAGER_DEFAULT_FOLDER_KEY,
					'inputFieldLabel' => \__('Folder', 'eightshift-forms'),
					'inputFieldHelp' => \__('If you use file input field all files will be uploaded to the specified folder in your HubSpot file manager.', 'eightshift-forms'),
					'inputType' => 'text',
					'inputValue' => $this->getSettingsValue(self::SETTINGS_HUBSPOT_FILEMANAGER_FOLDER_KEY, $formId),
				],
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
					'inputFieldLabel' => \__('Upload allowed file types', 'eightshift-forms'),
					// translators: %s will be replaced with the link.
					'inputFieldHelp' => \sprintf(\__('Limit what file types users can upload using your Hubspot forms. Each type must be written with a comma separator without dashes. You can find all <a href="%s" target="_blank">mime types here</a>. This field will override global settings.', 'eightshift-forms'), 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types'),
					'inputPlaceholder' => $this->getOptionValue(self::SETTINGS_GLOBAL_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
					'inputType' => 'text',
					'inputValue' => $this->getSettingsValue(self::SETTINGS_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY, $formId),
				],
			]
		];
	}
}
