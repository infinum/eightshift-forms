<?php

/**
 * Hubspot Settings class.
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Hubspot;

use EightshiftForms\Helpers\TraitHelper;
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
	use TraitHelper;

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
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
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
			'sidebar' => [
				'label' => __('Hubspot', 'eightshift-forms'),
				'value' => self::SETTINGS_TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => [
				[
					'component' => 'intro',
					'introTitle' => \__('Hubspot settings', 'eightshift-forms'),
					'introSubtitle' => \__('Configure your hubspot settings in one place.', 'eightshift-forms'),
				],
			]
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
			'sidebar' => [
				'label' => __('Hubspot', 'eightshift-forms'),
				'value' => self::SETTINGS_TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => [
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
					'inputType' => 'text',
					'inputIsRequired' => true,
					'inputValue' => $apiKey ?? $this->getOptionValue(self::SETTINGS_HUBSPOT_API_KEY_KEY),
					'inputIsReadOnly' => !empty($apiKey),
				],
			],
		];
	}
}
