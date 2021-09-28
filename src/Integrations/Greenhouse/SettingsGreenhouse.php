<?php

/**
 * Greenhouse Settings class.
 *
 * @package EightshiftForms\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Greenhouse;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGreenhouse class.
 */
class SettingsGreenhouse implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use TraitHelper;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_greenhouse';

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
	public const SETTINGS_GREENHOUSE_USE_KEY = 'greenhouseUse';

	/**
	 * API Key.
	 */
	public const SETTINGS_GREENHOUSE_API_KEY_KEY = 'greenhouseApiKey';

	/**
	 * Board Token Key.
	 */
	public const SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY = 'greenhouseBoardToken';

	/**
	 * Job ID Key.
	 */
	public const SETTINGS_GREENHOUSE_JOB_ID_KEY = 'greenhouseJobId';

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
		$optionsSet = $this->getOptionValue(self::SETTINGS_GREENHOUSE_API_KEY_KEY);

		if (!$optionsSet) {
			return [];
		}

		return [
			'sidebar' => [
				'label' => __('Greenhouse', 'eightshift-forms'),
				'value' => self::SETTINGS_TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => [
				[
					'component' => 'intro',
					'introTitle' => \__('Greenhouse settings', 'eightshift-forms'),
					'introSubtitle' => \__('Configure your greenhouse settings in one place.', 'eightshift-forms'),
				],
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_JOB_ID_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_JOB_ID_KEY),
					'inputFieldLabel' => \__('Job ID', 'eightshift-forms'),
					'inputFieldHelp' => \__('Open your Greenhouse account and provide API key. You can provide API key using global variable also.', 'eightshift-forms'),
					'inputType' => 'text',
					'inputIsRequired' => true,
					'inputValue' => $this->getOptionValue(self::SETTINGS_GREENHOUSE_JOB_ID_KEY),
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
		$apiKey = Variables::getApiKeyGreenhouse();
		$boardToken = Variables::getBoardTokenGreenhouse();

		return [
			'sidebar' => [
				'label' => __('Greenhouse', 'eightshift-forms'),
				'value' => self::SETTINGS_TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => [
				[
					'component' => 'intro',
					'introTitle' => \__('Greenhouse settings', 'eightshift-forms'),
					'introSubtitle' => \__('Configure your Greenhouse settings in one place.', 'eightshift-forms'),
				],
				[
					'component' => 'checkboxes',
					'checkboxesFieldLabel' => \__('Check options to use', 'eightshift-forms'),
					'checkboxesFieldHelp' => \__('Select integrations you want to use in your form.', 'eightshift-forms'),
					'checkboxesContent' => [
						[
							'component' => 'checkbox',
							'checkboxName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_USE_KEY),
							'checkboxId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_USE_KEY),
							'checkboxLabel' => __('Use Greenhouse', 'eightshift-forms'),
							'checkboxIsChecked' => !empty($this->getOptionValue(self::SETTINGS_GREENHOUSE_USE_KEY)),
							'checkboxValue' => 'true',
						]
					]
				],
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
					'inputFieldLabel' => \__('API Key', 'eightshift-forms'),
					'inputFieldHelp' => \__('Open your Greenhouse account and provide API key. You can provide API key using global variable also.', 'eightshift-forms'),
					'inputType' => 'text',
					'inputIsRequired' => true,
					'inputValue' => $apiKey ?? $this->getOptionValue(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
					'inputIsReadOnly' => !empty($apiKey),
				],
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
					'inputFieldLabel' => \__('Board Token', 'eightshift-forms'),
					'inputFieldHelp' => \__('Open your Greenhouse account and provide API key. You can provide API key using global variable also.', 'eightshift-forms'),
					'inputType' => 'text',
					'inputIsRequired' => true,
					'inputValue' => $boardToken ?? $this->getOptionValue(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
					'inputIsReadOnly' => !empty($boardToken),
				],
			],
		];
	}
}
