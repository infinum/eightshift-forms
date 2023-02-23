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
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
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
								'inputName' => $this->getSettingsName(self::SETTINGS_WORKABLE_API_KEY_KEY),
								'inputFieldLabel' => \__('API key', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'password',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_WORKABLE_API_KEY_KEY),
								'inputIsDisabled' => !empty($apiKey),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_WORKABLE_SUBDOMAIN_KEY),
								'inputFieldLabel' => \__('Subdomain', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => !empty($subdomain) ? $subdomain : $this->getOptionValue(self::SETTINGS_WORKABLE_SUBDOMAIN_KEY),
								'inputIsDisabled' => !empty($subdomain),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_KEY),
								'inputFieldLabel' => \__('File size upload limit', 'eightshift-forms'),
								'inputFieldHelp' => \__('Limit the size of files users can send via upload files. 5 MB by default, 25 MB maximum.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputIsNumber' => true,
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
								'component' => 'layout',
								'layoutType' => 'layout-grid-2',
								'layoutContent' => [
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
				],
			],
		];
	}
}
