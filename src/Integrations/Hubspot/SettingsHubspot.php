<?php

/**
 * HubSpot Settings class.
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Hubspot;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftForms\Integrations\AbstractSettingsIntegrations;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsHubspot class.
 */
class SettingsHubspot extends AbstractSettingsIntegrations implements UtilsSettingGlobalInterface, UtilsSettingInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_hubspot';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_hubspot';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_hubspot';

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
	 * Skip integration.
	 */
	public const SETTINGS_HUBSPOT_SKIP_INTEGRATION_KEY = 'hubspot-skip-integration';

	/**
	 * Instance variable for Hubspot data.
	 *
	 * @var HubspotClientInterface
	 */
	protected $hubspotClient;

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param HubspotClientInterface $hubspotClient Inject Hubspot which holds Hubspot connect data.
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(
		HubspotClientInterface $hubspotClient,
		SettingsFallbackDataInterface $settingsFallback
	) {
		$this->hubspotClient = $hubspotClient;
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
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY, SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY);
		$apiKey = (bool) UtilsSettingsHelper::getOptionWithConstant(Variables::getApiKeyHubspot(), self::SETTINGS_HUBSPOT_API_KEY_KEY);

		if (!$isUsed || !$apiKey) {
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
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_HUBSPOT_FILEMANAGER_FOLDER_KEY),
								'inputPlaceholder' => HubspotClient::HUBSPOT_FILEMANAGER_DEFAULT_FOLDER_KEY,
								'inputFieldLabel' => \__('File uploads folder', 'eightshift-forms'),
								'inputFieldHelp' => \__('All of the uploaded files will land inside this folder in the HubSpot file manager.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_HUBSPOT_FILEMANAGER_FOLDER_KEY, $formId),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
								'inputFieldLabel' => \__('Upload allowed file types', 'eightshift-forms'),
								'inputFieldHelp' => \sprintf(
									// Translators: %s will be replaced with the link.
									\__('Comma-separated list of <a href="%s" target="_blank">file type identifiers</a> (MIME types), e.g. <code>pdf</code>, <code>jpg</code>, <code>txt</code>.', 'eightshift-forms'),
									'https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types'
								),
								'inputPlaceholder' => UtilsSettingsHelper::getOptionValue(self::SETTINGS_GLOBAL_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
								'inputType' => 'text',
								'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY, $formId),
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
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_HUBSPOT_USE_KEY, self::SETTINGS_HUBSPOT_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$deactivateIntegration = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_HUBSPOT_SKIP_INTEGRATION_KEY, self::SETTINGS_HUBSPOT_SKIP_INTEGRATION_KEY);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('General', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_HUBSPOT_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_HUBSPOT_SKIP_INTEGRATION_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($deactivateIntegration ? [
								[
									'component' => 'intro',
									'introSubtitle' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('introSubtitle'),
									'introIsHighlighted' => true,
									'introIsHighlightedImportant' => true,
								],
							] : [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getPasswordFieldWithGlobalVariable(
									Variables::getApiKeyHubspot(),
									self::SETTINGS_HUBSPOT_API_KEY_KEY,
									'ES_API_KEY_HUBSPOT',
									\__('API key', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getTestAliConnection(self::SETTINGS_TYPE_KEY),
							]),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							...$this->getGlobalGeneralSettings(self::SETTINGS_TYPE_KEY),
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_GLOBAL_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
								'inputFieldLabel' => \__('Allowed file types', 'eightshift-forms'),
								'inputFieldHelp' => \sprintf(
									// Translators: %s will be replaced with the link.
									\__('Comma-separated list of <a href="%s" target="_blank">file type identifiers</a> (MIME types), e.g. <code>pdf</code>, <code>jpg</code>, <code>txt</code>.', 'eightshift-forms'),
									'https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types'
								),
								'inputType' => 'text',
								'inputValue' => UtilsSettingsHelper::getOptionValue(self::SETTINGS_GLOBAL_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY),
							],
						],
					],
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
									\__('Provide the app name and these scopes: <strong>forms, files, crm.objects.contacts.read, crm.objects.contacts.write, crm.schemas.custom.read</strong>.', 'eightshift-forms'),
									\__('Copy the API key into the field below or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
