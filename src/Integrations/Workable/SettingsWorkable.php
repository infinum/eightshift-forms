<?php

/**
 * Workable Settings class.
 *
 * @package EightshiftForms\Integrations\Workable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Workable;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsWorkable class.
 */
class SettingsWorkable implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Use general helper trait.
	 */
	use FiltersOuputMock;

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_workable';

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
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_WORKABLE_USE_KEY, self::SETTINGS_WORKABLE_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyWorkable()) ? Variables::getApiKeyWorkable() : $this->getOptionValue(self::SETTINGS_WORKABLE_API_KEY_KEY);
		$subdomain = !empty(Variables::getSubdomainWorkable()) ? Variables::getSubdomainWorkable() : $this->getOptionValue(self::SETTINGS_WORKABLE_SUBDOMAIN_KEY);

		if (!$isUsed || empty($apiKey) || empty($subdomain)) {
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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_WORKABLE_USE_KEY, self::SETTINGS_WORKABLE_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$apiKey = Variables::getApiKeyWorkable();
		$subdomain = Variables::getSubdomainWorkable();

		$successRedirectUrl = $this->getSuccessRedirectUrlFilterValue(self::SETTINGS_TYPE_KEY, '');
		$deactivateIntegration = $this->isCheckboxOptionChecked(self::SETTINGS_WORKABLE_SKIP_INTEGRATION_KEY, self::SETTINGS_WORKABLE_SKIP_INTEGRATION_KEY);

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => $this->getSettingsName(self::SETTINGS_WORKABLE_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => $this->settingDataDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => $this->settingDataDeactivatedIntegration('checkboxHelp'),
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
									'introSubtitle' => $this->settingDataDeactivatedIntegration('introSubtitle'),
									'introIsHighlighted' => true,
									'introIsHighlightedImportant' => true,
								],
							] : [
								[
									'component' => 'input',
									'inputName' => $this->getSettingsName(self::SETTINGS_WORKABLE_API_KEY_KEY),
									'inputFieldLabel' => \__('API key', 'eightshift-forms'),
									'inputFieldHelp' => $this->getGlobalVariableOutput('ES_API_KEY_WORKABLE', !empty($apiKey)),
									'inputType' => 'password',
									'inputIsRequired' => true,
									'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_WORKABLE_API_KEY_KEY),
									'inputIsDisabled' => !empty($apiKey),
								],
								[
									'component' => 'input',
									'inputName' => $this->getSettingsName(self::SETTINGS_WORKABLE_SUBDOMAIN_KEY),
									'inputFieldLabel' => \__('Subdomain', 'eightshift-forms'),
									'inputFieldHelp' => $this->getGlobalVariableOutput('ES_SUBDOMAIN_WORKABLE', !empty($subdomain)),
									'inputType' => 'text',
									'inputIsRequired' => true,
									'inputValue' => !empty($subdomain) ? $subdomain : $this->getOptionValue(self::SETTINGS_WORKABLE_SUBDOMAIN_KEY),
									'inputIsDisabled' => !empty($subdomain),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								$this->settingTestAliConnection(self::SETTINGS_TYPE_KEY),
							]),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_TYPE_KEY . '-' . SettingsGeneral::SETTINGS_GLOBAL_REDIRECT_SUCCESS_KEY),
								'inputFieldLabel' => \__('After submit redirect URL', 'eightshift-forms'),
								// translators: %s will be replaced with forms field name and filter output copy.
								'inputFieldHelp' => \sprintf(\__('
									If URL is provided, after a successful submission the user is redirected to the provided URL and the success message will <strong>not</strong> show.
									<br />
									%s', 'eightshift-forms'), $successRedirectUrl['settingsGlobal']),
								'inputType' => 'url',
								'inputIsUrl' => true,
								'inputIsDisabled' => $successRedirectUrl['filterUsedGlobal'],
								'inputValue' => $successRedirectUrl['dataGlobal'],
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_KEY),
								'inputFieldLabel' => \__('Max upload file size', 'eightshift-forms'),
								'inputFieldHelp' => \__('Up to 25MB.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputIsNumber' => true,
								'inputFieldAfterContent' => 'MB',
								'inputFieldInlineBeforeAfterContent' => true,
								'inputPlaceholder' => self::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_DEFAULT,
								'inputValue' => $this->getOptionValue(self::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_KEY),
								'inputMin' => 1,
								'inputMax' => 25,
								'inputStep' => 1,
							],
						],
					],
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
