<?php

/**
 * Greenhouse Settings class.
 *
 * @package EightshiftForms\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Greenhouse;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGreenhouse class.
 */
class SettingsGreenhouse implements SettingGlobalInterface, ServiceInterface
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
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_greenhouse';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'greenhouse';

	/**
	 * Greenhouse Use key.
	 */
	public const SETTINGS_GREENHOUSE_USE_KEY = 'greenhouse-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_GREENHOUSE_API_KEY_KEY = 'greenhouse-api-key';

	/**
	 * Board Token Key.
	 */
	public const SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY = 'greenhouse-board-token';

	/**
	 * File upload limit Key.
	 */
	public const SETTINGS_GREENHOUSE_FILE_UPLOAD_LIMIT_KEY = 'greenhouse-file-upload-limit';

	/**
	 * Disable default fields key.
	 */
	public const SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_KEY = 'greenhouse-disable-default-fields';
	public const SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_COVER_LETTER = 'disable-cover-letter';
	public const SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_RESUME = 'disable-resume';

	/**
	 * File upload limit default. Defined in MB.
	 */
	public const SETTINGS_GREENHOUSE_FILE_UPLOAD_LIMIT_DEFAULT = 5;

	/**
	 * Skip integration.
	 */
	public const SETTINGS_GREENHOUSE_SKIP_INTEGRATION_KEY = 'greenhouse-skip-integration';

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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_GREENHOUSE_USE_KEY, self::SETTINGS_GREENHOUSE_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyGreenhouse()) ? Variables::getApiKeyGreenhouse() : $this->getOptionValue(self::SETTINGS_GREENHOUSE_API_KEY_KEY);
		$boardToken = !empty(Variables::getBoardTokenGreenhouse()) ? Variables::getBoardTokenGreenhouse() : $this->getOptionValue(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY);

		if (!$isUsed || empty($apiKey) || empty($boardToken)) {
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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_GREENHOUSE_USE_KEY, self::SETTINGS_GREENHOUSE_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$apiKey = Variables::getApiKeyGreenhouse();
		$boardToken = Variables::getBoardTokenGreenhouse();
		$successRedirectUrl = $this->getSuccessRedirectUrlFilterValue(self::SETTINGS_TYPE_KEY, '');
		$deactivateIntegration = $this->isCheckboxOptionChecked(self::SETTINGS_GREENHOUSE_SKIP_INTEGRATION_KEY, self::SETTINGS_GREENHOUSE_SKIP_INTEGRATION_KEY);

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
								'checkboxesName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => $this->settingDataDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => $this->settingDataDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_GREENHOUSE_SKIP_INTEGRATION_KEY,
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
								$this->getSettingsPasswordFieldWithGlobalVariable(
									$this->getSettingsName(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
									\__('API key', 'eightshift-forms'),
									!empty($apiKey) ? $apiKey : $this->getOptionValue(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
									'ES_API_KEY_GREENHOUSE',
									!empty($apiKey)
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'input',
									'inputName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
									'inputFieldLabel' => \__('Job board', 'eightshift-forms'),
									'inputFieldHelp' => $this->getGlobalVariableOutput('ES_BOARD_TOKEN_GREENHOUSE', !empty($boardToken)),
									'inputType' => 'text',
									'inputIsRequired' => true,
									'inputValue' => !empty($boardToken) ? $boardToken : $this->getOptionValue(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
									'inputIsDisabled' => !empty($boardToken),
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
								'inputValue' => $successRedirectUrl['dataGlobal'],
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_FILE_UPLOAD_LIMIT_KEY),
								'inputFieldLabel' => \__('Max upload file size', 'eightshift-forms'),
								'inputFieldHelp' => \__('Up to 25MB.', 'eightshift-forms'),
								'inputFieldAfterContent' => 'MB',
								'inputFieldInlineBeforeAfterContent' => true,
								'inputType' => 'number',
								'inputIsNumber' => true,
								'inputIsRequired' => true,
								'inputPlaceholder' => '5',
								'inputValue' => $this->getOptionValue(self::SETTINGS_GREENHOUSE_FILE_UPLOAD_LIMIT_KEY) ?: self::SETTINGS_GREENHOUSE_FILE_UPLOAD_LIMIT_DEFAULT, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
								'inputMin' => 1,
								'inputMax' => 25,
								'inputStep' => 1,
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => \__('Disable default fields', 'eightshift-forms'),
								'checkboxesName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Cover letter textarea', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_COVER_LETTER, self::SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_KEY),
										'checkboxValue' => self::SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_COVER_LETTER,
										'checkboxAsToggle' => true,
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Resume textarea', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_RESUME, self::SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_KEY),
										'checkboxValue' => self::SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_RESUME,
										'checkboxAsToggle' => true,
									],
								],
							],
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsGreenhouse::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									// translators: %s will be replaced with the link.
									\sprintf(\__('Log in to your <a target="_blank" rel="noopener noreferrer" href="%s">Greenhouse Account</a>.', 'eightshift-forms'), 'https://app.greenhouse.io/'),
										// translators: %s will be replaced with the link.
									\sprintf(\__('Go to <a target="_blank" rel="noopener noreferrer" href="%s">API Credentials Settings</a>.', 'eightshift-forms'), 'https://app.greenhouse.io/configure/dev_center/credentials'),
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
									\sprintf(\__('Log in to your <a target="_blank" rel="noopener noreferrer" href="%s">Greenhouse Account</a>.', 'eightshift-forms'), 'https://app.greenhouse.io/'),
										// translators: %s will be replaced with the link.
									\sprintf(\__('Go to <a target="_blank" rel="noopener noreferrer" href="%s">Job Boards Settings</a>.', 'eightshift-forms'), 'https://app.greenhouse.io/jobboard'),
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
