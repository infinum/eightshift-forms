<?php

/**
 * Pipedrive Settings class.
 *
 * @package EightshiftForms\Integrations\Pipedrive
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Pipedrive;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsPipedrive class.
 */
class SettingsPipedrive implements ServiceInterface, SettingGlobalInterface, SettingInterface
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
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_pipedrive';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_pipedrive';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_pipedrive';

	/**
	 * Filter settings integration use key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_pipedrive';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'pipedrive';

	/**
	 * Pipedrive Use key.
	 */
	public const SETTINGS_PIPEDRIVE_USE_KEY = 'pipedrive-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_PIPEDRIVE_API_KEY_KEY = 'pipedrive-api-key';

	/**
	 * Pipedrive person-name key.
	 */
	public const SETTINGS_PIPEDRIVE_PERSON_NAME_KEY = 'pipedrive-person-name';

	/**
	 * Pipedrive issue type key.
	 */
	public const SETTINGS_PIPEDRIVE_ISSUE_TYPE_KEY = 'pipedrive-issue-type';

	/**
	 * Pipedrive title key.
	 */
	public const SETTINGS_PIPEDRIVE_TITLE_KEY = 'pipedrive-title';

	/**
	 * Pipedrive description key.
	 */
	public const SETTINGS_PIPEDRIVE_DESC_KEY = 'pipedrive-desc';

	/**
	 * Pipedrive params map key.
	 */
	public const SETTINGS_PIPEDRIVE_PARAMS_MAP_KEY = 'pipedrive-params-map';

	/**
	 * Pipedrive params manual map key.
	 */
	public const SETTINGS_PIPEDRIVE_PARAMS_MANUAL_MAP_KEY = 'pipedrive-params-manual-map';

	/**
	 * Skip integration.
	 */
	public const SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY = 'pipedrive-skip-integration';

	/**
	 * Instance variable for Pipedrive data.
	 *
	 * @var PipedriveClientInterface
	 */
	protected $pipedriveClient;

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param PipedriveClientInterface $pipedriveClient Inject Pipedrive which holds Pipedrive connect data.
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(
		PipedriveClientInterface $pipedriveClient,
		SettingsFallbackDataInterface $settingsFallback
	) {
		$this->pipedriveClient = $pipedriveClient;
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
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings are valid.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Integration type.
	 *
	 * @return boolean
	 */
	public function isSettingsValid(string $formId): bool
	{
		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		$personName = $this->getSettingValue(self::SETTINGS_PIPEDRIVE_PERSON_NAME_KEY, $formId);

		if (!$personName) {
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
			return $this->getSettingOutputNoActiveFeature();
		}

		$formDetails = Helper::getFormDetailsById($formId);

		error_log( print_r( ( $formDetails ), true ) );

		$personName = $this->getSettingValue(self::SETTINGS_PIPEDRIVE_PERSON_NAME_KEY, $formId);

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Settings', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'select',
								'selectName' => $this->getSettingName(self::SETTINGS_PIPEDRIVE_PERSON_NAME_KEY),
								'selectFieldLabel' => \__('Person name', 'eightshift-forms'),
								'selectSingleSubmit' => true,
								'selectPlaceholder' => \__('Select person name field', 'eightshift-forms'),
								'selectContent' => \array_map(
									static function ($option) use ($personName) {
										return [
											'component' => 'select-option',
											'selectOptionLabel' => $option,
											'selectOptionValue' => $option,
											'selectOptionIsSelected' => $personName === $option,
										];
									},
									$formDetails['fieldNames'] ?? []
								),
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = $this->isOptionCheckboxChecked(self::SETTINGS_PIPEDRIVE_USE_KEY, self::SETTINGS_PIPEDRIVE_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyPipedrive()) ? Variables::getApiKeyPipedrive() : $this->getOptionValue(self::SETTINGS_PIPEDRIVE_API_KEY_KEY);

		if (!$isUsed || empty($apiKey)) {
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
		if (!$this->isOptionCheckboxChecked(self::SETTINGS_PIPEDRIVE_USE_KEY, self::SETTINGS_PIPEDRIVE_USE_KEY)) {
			return $this->getSettingOutputNoActiveFeature();
		}

		$apiKey = Variables::getApiKeyPipedrive();

		$successRedirectUrl = $this->getSuccessRedirectUrlFilterValue(self::SETTINGS_TYPE_KEY, '');
		$deactivateIntegration = $this->isOptionCheckboxChecked(self::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY, self::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY);

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
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => $this->getOptionName(self::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => $this->settingDataDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => $this->settingDataDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY,
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
									$this->getOptionName(self::SETTINGS_PIPEDRIVE_API_KEY_KEY),
									\__('API key', 'eightshift-forms'),
									!empty($apiKey) ? $apiKey : $this->getOptionValue(self::SETTINGS_PIPEDRIVE_API_KEY_KEY),
									'ES_API_KEY_PIPEDRIVE',
									!empty($apiKey)
								),
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
								'inputName' => $this->getOptionName(self::SETTINGS_TYPE_KEY . '-' . SettingsGeneral::SETTINGS_GLOBAL_REDIRECT_SUCCESS_KEY),
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
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsPipedrive::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Pipedrive Account.', 'eightshift-forms'),
									\__('Click on your profile picture in the top right corner.', 'eightshift-forms'),
									\__('Click on Personal preferences and open API tab.', 'eightshift-forms'),
									\__('Copy the secret API token into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
