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
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGreenhouse class.
 */
class SettingsGreenhouse implements SettingInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_greenhouse';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_greenhouse';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_greenhouse';

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
	 * Job ID Key.
	 */
	public const SETTINGS_GREENHOUSE_JOB_ID_KEY = 'greenhouse-job-id';

	/**
	 * Integration fields Key.
	 */
	public const SETTINGS_GREENHOUSE_INTEGRATION_FIELDS_KEY = 'greenhouse-integration-fields';

	/**
	 * File upload limit Key.
	 */
	public const SETTINGS_GREENHOUSE_FILE_UPLOAD_LIMIT_KEY = 'greenhouse-file-upload-limit';

	/**
	 * File upload limit default. Defined in MB.
	 */
	public const SETTINGS_GREENHOUSE_FILE_UPLOAD_LIMIT_DEFAULT = 5;

	/**
	 * Conditional tags key.
	 */
	public const SETTINGS_GREENHOUSE_CONDITIONAL_TAGS_KEY = 'greenhouse-conditional-tags';

	/**
	 * Instance variable for Greenhouse data.
	 *
	 * @var ClientInterface
	 */
	protected $greenhouseClient;

	/**
	 * Instance variable for Greenhouse form data.
	 *
	 * @var MapperInterface
	 */
	protected $greenhouse;

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $greenhouseClient Inject Greenhouse which holds Greenhouse connect data.
	 * @param MapperInterface $greenhouse Inject Greenhouse which holds Greenhouse form data.
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(
		ClientInterface $greenhouseClient,
		MapperInterface $greenhouse,
		SettingsFallbackDataInterface $settingsFallback
	) {
		$this->greenhouseClient = $greenhouseClient;
		$this->greenhouse = $greenhouse;
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

		$jobKey = $this->getSettingsValue(self::SETTINGS_GREENHOUSE_JOB_ID_KEY, $formId);

		if (empty($jobKey)) {
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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_GREENHOUSE_USE_KEY, self::SETTINGS_GREENHOUSE_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyGreenhouse()) ? Variables::getApiKeyGreenhouse() : $this->getOptionValue(self::SETTINGS_GREENHOUSE_API_KEY_KEY);
		$boardToken = !empty(Variables::getBoardTokenGreenhouse()) ? Variables::getBoardTokenGreenhouse() : $this->getOptionValue(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY);

		if (!$isUsed || empty($apiKey) || empty($boardToken)) {
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
		$items = $this->greenhouseClient->getItems(false);

		// Bailout if integration can't fetch data.
		if (!$items) {
			return $this->getNoIntegrationFetchDataOutput($type);
		}

		// Find selected form id.
		$selectedFormId = $this->getSettingsValue(self::SETTINGS_GREENHOUSE_JOB_ID_KEY, $formId);

		$output = [];

		// If the user has selected the form id populate additional config.
		if ($selectedFormId) {
			$formFields = $this->greenhouse->getFormFields($formId);

			// Output additonal tabs for config.
			$output = [
				'component' => 'tabs',
				'tabsContent' => [
					$this->getOutputIntegrationFields(
						$formId,
						$formFields,
						$type,
						self::SETTINGS_GREENHOUSE_INTEGRATION_FIELDS_KEY,
					),
					$this->getOutputConditionalTags(
						$formId,
						$formFields,
						self::SETTINGS_GREENHOUSE_CONDITIONAL_TAGS_KEY
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
				self::SETTINGS_GREENHOUSE_JOB_ID_KEY
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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_GREENHOUSE_USE_KEY, self::SETTINGS_GREENHOUSE_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$apiKey = Variables::getApiKeyGreenhouse();
		$boardToken = Variables::getBoardTokenGreenhouse();

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
								'inputName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
								'inputId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
								'inputFieldLabel' => \__('API key', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'password',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
								'inputIsDisabled' => !empty($apiKey),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
								'inputId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
								'inputFieldLabel' => \__('Job Board name', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => !empty($boardToken) ? $boardToken : $this->getOptionValue(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
								'inputIsDisabled' => !empty($boardToken),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_FILE_UPLOAD_LIMIT_KEY),
								'inputId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_FILE_UPLOAD_LIMIT_KEY),
								'inputFieldLabel' => \__('File size upload limit', 'eightshift-forms'),
								'inputFieldHelp' => \__('Limit the size of files users can send via upload files. 5 MB by default, 25 MB maximum.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputIsNumber' => true,
								'inputIsRequired' => true,
								'inputValue' => $this->getOptionValue(self::SETTINGS_GREENHOUSE_FILE_UPLOAD_LIMIT_KEY) ?: self::SETTINGS_GREENHOUSE_FILE_UPLOAD_LIMIT_DEFAULT, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
								'inputMin' => 1,
								'inputMax' => 25,
								'inputStep' => 1,
							],
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsGreenhouse::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'layout',
								'layoutType' => 'layout-grid-2',
								'layoutItems' => [
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
				],
			],
		];
	}
}
