<?php

/**
 * Troubleshooting Settings class.
 *
 * @package EightshiftForms\Troubleshooting
 */

declare(strict_types=1);

namespace EightshiftForms\Troubleshooting;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\Settings\SettingsAll;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsTroubleshooting class.
 */
class SettingsTroubleshooting implements ServiceInterface, SettingsTroubleshootingDataInterface
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
	 * Troubleshooting debugging key.
	 */
	public const SETTINGS_TROUBLESHOOTING_DEBUGGING_KEY = 'troubleshooting-debugging';
	public const SETTINGS_TROUBLESHOOTING_SKIP_VALIDATION_KEY = 'skip-validation';
	public const SETTINGS_TROUBLESHOOTING_SKIP_RESET_KEY = 'skip-reset';
	public const SETTINGS_TROUBLESHOOTING_LOG_MODE_KEY = 'log-mode';

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
			'type' => SettingsAll::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING,
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
				'introSubtitle' => \__('Your forms will send email fallbacks with all the data if there is any kind of error. This email can be used to debug or provide manual input of the data to any integration.', 'eightshift-forms'),
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
				'inputFieldHelp' => \__('Set email where all fallback emails will be sent. Use comma to separate multiple emails.', 'eightshift-forms'),
				'inputType' => 'text',
				'inputIsRequired' => true,
				'inputValue' => $this->getOptionValue(self::SETTINGS_TROUBLESHOOTING_FALLBACK_EMAIL_KEY),
			];
		}

		$outputDebugging = [
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => \__('Debugging', 'eightshift-forms'),
				'introSubtitle' => \__('Settings used for debugging forms. USE WITH CAUTION!', 'eightshift-forms'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => '',
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_TROUBLESHOOTING_DEBUGGING_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_TROUBLESHOOTING_DEBUGGING_KEY),
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => \__('Skip validation', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_TROUBLESHOOTING_SKIP_VALIDATION_KEY, self::SETTINGS_TROUBLESHOOTING_DEBUGGING_KEY),
						'checkboxValue' => self::SETTINGS_TROUBLESHOOTING_SKIP_VALIDATION_KEY,
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => \__('Skip form reset after submit', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_TROUBLESHOOTING_SKIP_RESET_KEY, self::SETTINGS_TROUBLESHOOTING_DEBUGGING_KEY),
						'checkboxValue' => self::SETTINGS_TROUBLESHOOTING_SKIP_RESET_KEY,
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => \__('Output logs', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_TROUBLESHOOTING_LOG_MODE_KEY, self::SETTINGS_TROUBLESHOOTING_DEBUGGING_KEY),
						'checkboxValue' => self::SETTINGS_TROUBLESHOOTING_LOG_MODE_KEY,
					]
				]
			],
		];

		return [
			...$output,
			...$outputDebugging,
		];
	}

	/**
	 * Output array settings for form.
	 *
	 * @param string $integration Integration name used for troubleshooting.
	 *
	 * @return array<int, array<string, array<int|string, array<string, mixed>>|bool|string>>
	 */
	public function getOutputGlobalTroubleshooting(string $integration): array
	{
		$isValid = $this->isSettingsGlobalValid();

		if (!$isValid) {
			return [];
		}

		return [
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => \__('Troubleshooting', 'eightshift-forms'),
				'introSubtitle' => \__('Your forms will send email fallbacks with all the data if there is any kind of error. This email can be used to debug or provide manual input of the data to any integration.', 'eightshift-forms'),
				'introTitleSize' => 'medium',
			],
			[
				'component' => 'input',
				'inputName' => $this->getSettingsName(self::SETTINGS_TROUBLESHOOTING_FALLBACK_EMAIL_KEY . '-' . $integration),
				'inputId' => $this->getSettingsName(self::SETTINGS_TROUBLESHOOTING_FALLBACK_EMAIL_KEY . '-' . $integration),
				'inputFieldLabel' => \__('Fallback e-mail', 'eightshift-forms'),
				'inputFieldHelp' => \__('Set email where this integration fallback emails will be sent. This field will be used as "cc", main "from" field will be used from the main Troubleshooting global setting page. Use comma to separate multiple emails.', 'eightshift-forms'),
				'inputType' => 'text',
				'inputIsRequired' => true,
				'inputValue' => $this->getOptionValue(self::SETTINGS_TROUBLESHOOTING_FALLBACK_EMAIL_KEY . '-' . $integration),
			],
		];
	}
}
