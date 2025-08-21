<?php

/**
 * Talentlyft Settings class.
 *
 * @package EightshiftForms\Integrations\Talentlyft
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Talentlyft;

use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Integrations\AbstractSettingsIntegrations;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Settings\SettingInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsTalentlyft class.
 */
class SettingsTalentlyft extends AbstractSettingsIntegrations implements SettingGlobalInterface, SettingInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_talentlyft';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_talentlyft';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_talentlyft';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'talentlyft';

	/**
	 * Talentlyft Use key.
	 */
	public const SETTINGS_TALENTLYFT_USE_KEY = 'talentlyft-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_TALENTLYFT_API_KEY_KEY = 'talentlyft-api-key';

	/**
	 * File upload limit Key.
	 */
	public const SETTINGS_TALENTLYFT_FILE_UPLOAD_LIMIT_KEY = 'talentlyft-file-upload-limit';

	/**
	 * List type key.
	 */
	public const SETTINGS_TALENTLYFT_LIST_TYPE_KEY = 'talentlyft-list-type';

	/**
	 * File upload limit default. Defined in MB.
	 */
	public const SETTINGS_TALENTLYFT_FILE_UPLOAD_LIMIT_DEFAULT = 5;

	/**
	 * Skip integration.
	 */
	public const SETTINGS_TALENTLYFT_SKIP_INTEGRATION_KEY = 'talentlyft-skip-integration';

	/**
	 * Consent privacy key.
	 */
	public const SETTINGS_TALENTLYFT_CONSENT_PRIVACY_KEY = 'talentlyft-consent-privacy';
	public const SETTINGS_TALENTLYFT_CONSENT_STORAGE_KEY = 'talentlyft-consent-storage';
	public const SETTINGS_TALENTLYFT_CONSENT_SHARE_KEY = 'talentlyft-consent-share';

	/**
	 * Talentlyft use lead key.
	 */
	public const SETTINGS_TALENTLYFT_USE_FLAGS_KEY = 'talentlyft-use-lead';
	public const SETTINGS_TALENTLYFT_USE_FLAGS_APPLIED_KEY = 'applied';
	public const SETTINGS_TALENTLYFT_USE_FLAGS_PROSPECT_KEY = 'prospect';

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
		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_TALENTLYFT_USE_KEY, self::SETTINGS_TALENTLYFT_USE_KEY);
		$apiKey = (bool) SettingsHelpers::getOptionWithConstant(Variables::getApiKeyTalentlyft(), self::SETTINGS_TALENTLYFT_API_KEY_KEY);

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
		// Bailout if feature is not active.
		if (!$this->isSettingsGlobalValid()) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Lead', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_TALENTLYFT_USE_FLAGS_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Is Applied', 'eightshift-forms'),
										'checkboxHelp' => \__('Candidates are considered as applied and receiving the "thank you for applying" email.', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_TALENTLYFT_USE_FLAGS_APPLIED_KEY, self::SETTINGS_TALENTLYFT_USE_FLAGS_KEY, $formId),
										'checkboxValue' => self::SETTINGS_TALENTLYFT_USE_FLAGS_APPLIED_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Is Prospect', 'eightshift-forms'),
										'checkboxHelp' => \__('Candidates are considered as sourced and not receiving the "thank you for applying" email.', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_TALENTLYFT_USE_FLAGS_PROSPECT_KEY, self::SETTINGS_TALENTLYFT_USE_FLAGS_KEY, $formId),
										'checkboxValue' => self::SETTINGS_TALENTLYFT_USE_FLAGS_PROSPECT_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									],
								],
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
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_TALENTLYFT_USE_KEY, self::SETTINGS_TALENTLYFT_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$deactivateIntegration = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_TALENTLYFT_SKIP_INTEGRATION_KEY, self::SETTINGS_TALENTLYFT_SKIP_INTEGRATION_KEY);
		$selectedListType = \array_flip(\array_filter(\explode(Config::DELIMITER, SettingsHelpers::getOptionValue(self::SETTINGS_TALENTLYFT_LIST_TYPE_KEY))));

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
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_TALENTLYFT_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_TALENTLYFT_SKIP_INTEGRATION_KEY,
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
									Variables::getApiKeyTalentlyft(),
									self::SETTINGS_TALENTLYFT_API_KEY_KEY,
									'ES_API_KEY_TALENTLYFT',
									\__('API key', 'eightshift-forms'),
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
								'inputName' => SettingsHelpers::getOptionName(self::SETTINGS_TALENTLYFT_FILE_UPLOAD_LIMIT_KEY),
								'inputFieldLabel' => \__('Max upload file size', 'eightshift-forms'),
								// translators: %d will be replaced with the default file upload limit.
								'inputFieldHelp' => \sprintf(\__('Up to %dMB.', 'eightshift-forms'), self::SETTINGS_TALENTLYFT_FILE_UPLOAD_LIMIT_DEFAULT),
								'inputType' => 'number',
								'inputIsNumber' => true,
								'inputFieldAfterContent' => 'MB',
								'inputFieldInlineBeforeAfterContent' => true,
								'inputPlaceholder' => self::SETTINGS_TALENTLYFT_FILE_UPLOAD_LIMIT_DEFAULT,
								'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_TALENTLYFT_FILE_UPLOAD_LIMIT_KEY),
								'inputMin' => 1,
								'inputMax' => 5,
								'inputStep' => 1,
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'select',
								'selectIsMultiple' => true,
								'selectName' => SettingsHelpers::getSettingName(self::SETTINGS_TALENTLYFT_LIST_TYPE_KEY),
								'selectFieldLabel' => \__('Additional statuses list', 'eightshift-forms'),
								'selectFieldHelp' => \__('Get additional jobs from other statuses. Published is always included. Use with caution!', 'eightshift-forms'),
								'selectContent' => [
									[
										'component' => 'select-option',
										'selectOptionLabel' => \__('Internal', 'eightshift-forms'),
										'selectOptionValue' => 'internal',
										'selectOptionIsSelected' => isset($selectedListType['internal']),
									],
								],
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Compliance', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'textarea',
								'textareaName' => SettingsHelpers::getOptionName(self::SETTINGS_TALENTLYFT_CONSENT_PRIVACY_KEY),
								'textareaFieldLabel' => \__('Privacy Consent', 'eightshift-forms'),
								'textareaValue' => SettingsHelpers::getOptionValue(self::SETTINGS_TALENTLYFT_CONSENT_PRIVACY_KEY),
							],
							[
								'component' => 'textarea',
								'textareaName' => SettingsHelpers::getOptionName(self::SETTINGS_TALENTLYFT_CONSENT_STORAGE_KEY),
								'textareaFieldLabel' => \__('Storage Consent', 'eightshift-forms'),
								'textareaValue' => SettingsHelpers::getOptionValue(self::SETTINGS_TALENTLYFT_CONSENT_STORAGE_KEY),
								// translators: %s will be replaced with the available variables.
								'textareaFieldHelp' => \sprintf(\__('Available variables: %s', 'eightshift-forms'), '<code>{period} {company}</code>'),
							],
							[
								'component' => 'textarea',
								'textareaName' => SettingsHelpers::getOptionName(self::SETTINGS_TALENTLYFT_CONSENT_SHARE_KEY),
								'textareaFieldLabel' => \__('Share Consent', 'eightshift-forms'),
								'textareaValue' => SettingsHelpers::getOptionValue(self::SETTINGS_TALENTLYFT_CONSENT_SHARE_KEY),
								// translators: %s will be replaced with the available variables.
								'textareaFieldHelp' => \sprintf(\__('Available variables: %s', 'eightshift-forms'), '<code>{companies}</code>'),
							],
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsTalentlyft::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									// translators: %s will be replaced with the link.
									\sprintf(\__('Log in to your <a target="_blank" rel="noopener noreferrer" href="%s">Talentlyft Account</a>.', 'eightshift-forms'), 'https://app.talentlyft.com/'),
									// translators: %s will be replaced with the link.
									\sprintf(\__('Go to <a target="_blank" rel="noopener noreferrer" href="%s">Integrations Settings</a>.', 'eightshift-forms'), 'https://app.talentlyft.com/infinum/settings/integrations'),
									\__('Click on <strong>Settings under the TalentLyft card</strong>.', 'eightshift-forms'),
									\__('Generate a new <strong>API token</strong>.', 'eightshift-forms'),
									\__('Copy the API key into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
