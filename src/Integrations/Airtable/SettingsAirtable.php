<?php

/**
 * Airtable Settings class.
 *
 * @package EightshiftForms\Integrations\Airtable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Airtable;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsAirtable class.
 */
class SettingsAirtable implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_airtable';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'airtable';

	/**
	 * Airtable Use key.
	 */
	public const SETTINGS_AIRTABLE_USE_KEY = 'airtable-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_AIRTABLE_API_KEY_KEY = 'airtable-api-key';

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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_AIRTABLE_USE_KEY, self::SETTINGS_AIRTABLE_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyAirtable()) ? Variables::getApiKeyAirtable() : $this->getOptionValue(self::SETTINGS_AIRTABLE_API_KEY_KEY);

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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_AIRTABLE_USE_KEY, self::SETTINGS_AIRTABLE_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$apiKey = Variables::getApiKeyAirtable();

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
								'inputName' => $this->getSettingsName(self::SETTINGS_AIRTABLE_API_KEY_KEY),
								'inputFieldLabel' => \__('API key', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'password',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_AIRTABLE_API_KEY_KEY),
								'inputIsDisabled' => !empty($apiKey),
							],
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsAirtable::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Airtable Account.', 'eightshift-forms'),
									// translators: %s will be replaced with the link.
									\sprintf(\__('Go to the <a target="_blank" rel="noopener noreferrer" href="%s">Developers Hub</a>.', 'eightshift-forms'), 'https://airtable.com/create/tokens/new'),
									\__('Create a new Personal access token with the scopes <strong>"data.records:write"</strong> and <strong>"schema.bases:read"</strong>.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
