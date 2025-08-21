<?php

/**
 * Workable Settings class.
 *
 * @package EightshiftForms\Integrations\Workable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Workable;

use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Integrations\AbstractSettingsIntegrations;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsWorkable class.
 */
class SettingsWorkable extends AbstractSettingsIntegrations implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_workable';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_workable';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'workable';

	/**
	 * Workable Use key.
	 */
	public const SETTINGS_WORKABLE_USE_KEY = 'workable-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_WORKABLE_API_KEY_KEY = 'workable-api-key';

	/**
	 * Subdomain Key.
	 */
	public const SETTINGS_WORKABLE_SUBDOMAIN_KEY = 'workable-subdomain';

	/**
	 * File upload limit Key.
	 */
	public const SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_KEY = 'workable-file-upload-limit';

	/**
	 * File upload limit default. Defined in MB.
	 */
	public const SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_DEFAULT = 5;

	/**
	 * Skip integration.
	 */
	public const SETTINGS_WORKABLE_SKIP_INTEGRATION_KEY = 'workable-skip-integration';

	/**
	 * Geolocation tags.
	 */
	public const SETTINGS_WORKABLE_GEOLOCATION_TAGS_KEY = 'workable-geolocation-tags';

	/**
	 * List type key.
	 */
	public const SETTINGS_WORKABLE_LIST_TYPE_KEY = 'workable-list-type';

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(SettingsFallbackDataInterface $settingsFallback)
	{
		$this->settingsFallback = $settingsFallback;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
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
		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_WORKABLE_USE_KEY, self::SETTINGS_WORKABLE_USE_KEY);
		$apiKey = (bool) SettingsHelpers::getOptionWithConstant(Variables::getApiKeyWorkable(), self::SETTINGS_WORKABLE_API_KEY_KEY);
		$subdomain = (bool) SettingsHelpers::getOptionWithConstant(Variables::getSubdomainWorkable(), self::SETTINGS_WORKABLE_SUBDOMAIN_KEY);

		if (!$isUsed || !$apiKey || !$subdomain) {
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
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_WORKABLE_USE_KEY, self::SETTINGS_WORKABLE_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$deactivateIntegration = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_WORKABLE_SKIP_INTEGRATION_KEY, self::SETTINGS_WORKABLE_SKIP_INTEGRATION_KEY);

		$isGeolocationEnabled = SettingsHelpers::isOptionCheckboxChecked(SettingsGeolocation::SETTINGS_GEOLOCATION_USE_KEY, SettingsGeolocation::SETTINGS_GEOLOCATION_USE_KEY);

		$selectedListType = \array_flip(\array_filter(\explode(Config::DELIMITER, SettingsHelpers::getOptionValue(self::SETTINGS_WORKABLE_LIST_TYPE_KEY))));

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_WORKABLE_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_WORKABLE_SKIP_INTEGRATION_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($deactivateIntegration ? [
								[
									'component' => 'intro',
									'introSubtitle' => SettingsOutputHelpers::getPartialDeactivatedIntegration('introSubtitle'),
									'introIsHighlighted' => true,
									'introIsHighlightedImportant' => true,
								],
							] : [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								SettingsOutputHelpers::getPasswordFieldWithGlobalVariable(
									Variables::getApiKeyWorkable(),
									self::SETTINGS_WORKABLE_API_KEY_KEY,
									'ES_API_KEY_WORKABLE',
									\__('API key', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								SettingsOutputHelpers::getInputFieldWithGlobalVariable(
									Variables::getSubdomainWorkable(),
									self::SETTINGS_WORKABLE_SUBDOMAIN_KEY,
									'ES_SUBDOMAIN_WORKABLE',
									\__('Subdomain', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								SettingsOutputHelpers::getTestApiConnection(self::SETTINGS_TYPE_KEY),
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
								'inputName' => SettingsHelpers::getOptionName(self::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_KEY),
								'inputFieldLabel' => \__('Max upload file size', 'eightshift-forms'),
								'inputFieldHelp' => \__('Up to 25MB.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputIsNumber' => true,
								'inputFieldAfterContent' => 'MB',
								'inputFieldInlineBeforeAfterContent' => true,
								'inputPlaceholder' => self::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_DEFAULT,
								'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_KEY),
								'inputMin' => 1,
								'inputMax' => 25,
								'inputStep' => 1,
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'select',
								'selectIsMultiple' => true,
								'selectName' => SettingsHelpers::getSettingName(self::SETTINGS_WORKABLE_LIST_TYPE_KEY),
								'selectFieldLabel' => \__('Additional statuses list', 'eightshift-forms'),
								'selectFieldHelp' => \__('Get additional jobs from other statuses. Published is always included. Use with caution!', 'eightshift-forms'),
								'selectContent' => [
									[
										'component' => 'select-option',
										'selectOptionLabel' => \__('Draft', 'eightshift-forms'),
										'selectOptionValue' => 'draft',
										'selectOptionIsSelected' => isset($selectedListType['draft']),
									],
									[
										'component' => 'select-option',
										'selectOptionLabel' => \__('Closed', 'eightshift-forms'),
										'selectOptionValue' => 'closed',
										'selectOptionIsSelected' => isset($selectedListType['closed']),
									],
									[
										'component' => 'select-option',
										'selectOptionLabel' => \__('Archived', 'eightshift-forms'),
										'selectOptionValue' => 'archived',
										'selectOptionIsSelected' => isset($selectedListType['archived']),
									],
								],
							],
						],
					],
					...($isGeolocationEnabled ? [
						[
							'component' => 'tab',
							'tabLabel' => \__('Geolocation Tags', 'eightshift-forms'),
							'tabContent' => [
								[
									'component' => 'intro',
									'introSubtitle' => \__('Make sure you have added the tags in your Workable account.', 'eightshift-forms'),
									'introIsHighlighted' => true,
								],
								[
									'component' => 'textarea',
									'textareaName' => SettingsHelpers::getOptionName(self::SETTINGS_WORKABLE_GEOLOCATION_TAGS_KEY),
									'textareaIsMonospace' => true,
									'textareaSaveAsJson' => true,
									'textareaFieldLabel' => \__('Geolocation tags', 'eightshift-forms'),
									'textareaFieldHelp' => GeneralHelpers::minifyString(\__("
										Enter one tag per line, in the following format:<br />
										<code>country-code : tag-value</code><br /><br />
										Example:
										<ul>
											<li>US : disabled</li>
											<li>DE : disabled, test</li>
										</ul>", 'eightshift-forms')),
									'textareaValue' => SettingsHelpers::getOptionValueAsJson(self::SETTINGS_WORKABLE_GEOLOCATION_TAGS_KEY, 2),
								],
							],
						]
					] : []),
					$this->settingsFallback->getOutputGlobalFallback(SettingsWorkable::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									// translators: %s will be replaced with the link.
									\sprintf(\__('Log in to your <a target="_blank" rel="noopener noreferrer" href="%s">Workable Account</a>.', 'eightshift-forms'), 'https://app.workable.io/'),
									// translators: %s will be replaced with the link.
									\sprintf(\__('Go to <a target="_blank" rel="noopener noreferrer" href="%s">API Credentials Settings</a>.', 'eightshift-forms'), 'https://app.workable.io/configure/dev_center/credentials'),
									\__('Click on <strong>Create New API Key</strong>.', 'eightshift-forms'),
									\__('Select <strong>Job Board</strong> as your API Type.', 'eightshift-forms'),
									\__('Copy the API key into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the Job Board name?', 'eightshift-forms'),
								'stepsContent' => [
									// translators: %s will be replaced with the link.
									\sprintf(\__('Log in to your <a target="_blank" rel="noopener noreferrer" href="%s">Workable Account</a>.', 'eightshift-forms'), 'https://app.workable.io/'),
									// translators: %s will be replaced with the link.
									\sprintf(\__('Go to <a target="_blank" rel="noopener noreferrer" href="%s">Job Boards Settings</a>.', 'eightshift-forms'), 'https://app.workable.io/jobboard'),
									\__('Copy the <strong>Board Name</strong> you want to use.', 'eightshift-forms'),
									\__('Make the name all lowercase.', 'eightshift-forms'),
									\__('Copy the Board Name into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
