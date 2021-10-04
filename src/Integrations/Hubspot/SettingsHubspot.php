<?php

/**
 * Hubspot Settings class.
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Hubspot;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsHubspot class.
 */
class SettingsHubspot implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_hubspot';

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_hubspot';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_hubspot';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'hubspot';

	/**
	 * Hubspot Use key.
	 */
	public const SETTINGS_HUBSPOT_USE_KEY = 'hubspotUse';

	/**
	 * API Key.
	 */
	public const SETTINGS_HUBSPOT_API_KEY_KEY = 'hubspotApiKey';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => __('Hubspot', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg"><path d="M23.02 9.914V6.356A2.743 2.743 0 0024.6 3.883V3.8a2.748 2.748 0 00-2.74-2.741h-.084a2.748 2.748 0 00-2.74 2.74v.084a2.743 2.743 0 001.581 2.473V9.92a7.774 7.774 0 00-3.696 1.627l-9.784-7.62a3.122 3.122 0 10-1.462 1.901l9.62 7.49a7.796 7.796 0 00.12 8.79l-2.928 2.927a2.514 2.514 0 00-.726-.119 2.541 2.541 0 102.541 2.542 2.506 2.506 0 00-.118-.727l2.896-2.896a7.809 7.809 0 105.933-13.922m-1.2 11.721a4.007 4.007 0 114.016-4.005 4.007 4.007 0 01-4.007 4.007" fill="#FF7A59" fill-rule="nonzero"/></svg>',
		];
	}

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	public function getSettingsData(string $formId): array
	{
		$optionsSet = $this->getOptionValue(self::SETTINGS_HUBSPOT_API_KEY_KEY);

		if (!$optionsSet) {
			return [];
		}

		return [
			[
				'component' => 'intro',
				'introTitle' => \__('Hubspot settings', 'eightshift-forms'),
				'introSubtitle' => \__('Configure your hubspot settings in one place.', 'eightshift-forms'),
			],
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array
	 */
	public function getSettingsGlobalData(): array
	{
		$apiKey = Variables::getApiKeyHubspot();

		return [
			[
				'component' => 'intro',
				'introTitle' => \__('Hubspot settings', 'eightshift-forms'),
				'introSubtitle' => \__('Configure your Hubspot settings in one place.', 'eightshift-forms'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => \__('Check options to use', 'eightshift-forms'),
				'checkboxesFieldHelp' => \__('Select integrations you want to use in your form.', 'eightshift-forms'),
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxName' => $this->getSettingsName(self::SETTINGS_HUBSPOT_USE_KEY),
						'checkboxId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_USE_KEY),
						'checkboxLabel' => __('Use Hubspot', 'eightshift-forms'),
						'checkboxIsChecked' => !empty($this->getOptionValue(self::SETTINGS_HUBSPOT_USE_KEY)),
						'checkboxValue' => 'true',
					]
				]
			],
			[
				'component' => 'input',
				'inputName' => $this->getSettingsName(self::SETTINGS_HUBSPOT_API_KEY_KEY),
				'inputId' => $this->getSettingsName(self::SETTINGS_HUBSPOT_API_KEY_KEY),
				'inputFieldLabel' => \__('API Key', 'eightshift-forms'),
				'inputFieldHelp' => \__('Open your Hubspot account and provide API key. You can provide API key using global variable also.', 'eightshift-forms'),
				'inputType' => 'password',
				'inputIsRequired' => true,
				'inputValue' => !empty($apiKey) ? $apiKey : $this->getOptionValue(self::SETTINGS_HUBSPOT_API_KEY_KEY),
				'inputIsDisabled' => !empty($apiKey),
			],
		];
	}
}
