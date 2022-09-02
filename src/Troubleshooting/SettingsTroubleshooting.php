<?php

/**
 * Troubleshooting Settings class.
 *
 * @package EightshiftForms\Troubleshooting
 */

declare(strict_types=1);

namespace EightshiftForms\Troubleshooting;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsTroubleshooting class.
 */
class SettingsTroubleshooting implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

		/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_troubleshooting';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_troubleshooting';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_troubleshooting';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'troubleshooting';

	/**
	 * Troubleshooting Use key.
	 */
	public const SETTINGS_TROUBLESHOOTING_USE_KEY = 'troubleshooting-use';

	/**
	 * Fallback Email key.
	 */
	public const SETTINGS_TROUBLESHOOTING_FALLBACK_EMAIL_KEY = 'troubleshooting-fallback-email';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_TROUBLESHOOTING_USE_KEY, self::SETTINGS_TROUBLESHOOTING_USE_KEY);
		$email = $this->getOptionValue(self::SETTINGS_TROUBLESHOOTING_FALLBACK_EMAIL_KEY);

		if (!$isUsed || empty($email)) {
			return false;
		}

		return true;
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => \__('Troubleshooting', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => Filters::ALL[self::SETTINGS_TYPE_KEY]['icon'],
		];
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
		return [];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$output = [
			[
				'component' => 'intro',
				'introTitle' => \__('Fallback emails', 'eightshift-forms'),
				'introSubtitle' => \__('Your forms will send email fallbacks with all the data if there is any kind of error. This can email can be used to debug or provide manual input of the data to any integration.', 'eightshift-forms'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => '',
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_TROUBLESHOOTING_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_TROUBLESHOOTING_USE_KEY),
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => \__('Use Fallback emails', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_TROUBLESHOOTING_USE_KEY, self::SETTINGS_TROUBLESHOOTING_USE_KEY),
						'checkboxValue' => self::SETTINGS_TROUBLESHOOTING_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];

		$isUsedFallbackEmails = $this->isCheckboxOptionChecked(self::SETTINGS_TROUBLESHOOTING_USE_KEY, self::SETTINGS_TROUBLESHOOTING_USE_KEY);

		if ($isUsedFallbackEmails) {
			$output[] = [
				'component' => 'input',
				'inputName' => $this->getSettingsName(self::SETTINGS_TROUBLESHOOTING_FALLBACK_EMAIL_KEY),
				'inputId' => $this->getSettingsName(self::SETTINGS_TROUBLESHOOTING_FALLBACK_EMAIL_KEY),
				'inputFieldLabel' => \__('Fallback e-mail', 'eightshift-forms'),
				'inputFieldHelp' => \__('Set email where all fallback emails will be sent.', 'eightshift-forms'),
				'inputType' => 'text',
				'inputIsEmail' => true,
				'inputIsRequired' => true,
				'inputValue' => $this->getOptionValue(self::SETTINGS_TROUBLESHOOTING_FALLBACK_EMAIL_KEY),
			];
		}

		return $output;
	}
}
