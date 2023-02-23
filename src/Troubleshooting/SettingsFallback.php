<?php

/**
 * Troubleshooting Settings class.
 *
 * @package EightshiftForms\Troubleshooting
 */

declare(strict_types=1);

namespace EightshiftForms\Troubleshooting;

use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsFallback class.
 */
class SettingsFallback implements ServiceInterface, SettingsFallbackDataInterface, SettingGlobalInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_fallback';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_fallback';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'fallback';

	/**
	 * Fallback Use key.
	 */
	public const SETTINGS_FALLBACK_USE_KEY = 'fallback-use';

	/**
	 * Fallback Email key.
	 */
	public const SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY = 'fallback-email';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_FALLBACK_USE_KEY, self::SETTINGS_FALLBACK_USE_KEY);
		$email = $this->getOptionValue(self::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY);

		if (!$isUsed || empty($email)) {
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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_FALLBACK_USE_KEY, self::SETTINGS_FALLBACK_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Emails', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('In the event of an error on the forms, Eightshift Forms can send an email notification containing all the form submission data as a fallback. The email can be used to debug or allow manual data processing.', 'eightshift-forms'),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY),
								'inputFieldLabel' => \__('Fallback e-mail', 'eightshift-forms'),
								'inputFieldHelp' => \__('Set the email where the integration fallback emails will be sent. This field will be used as the "cc" field. The main "from" field will be used from the main fallback global setting page. Use commas to separate multiple emails.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => $this->getOptionValue(self::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY),
							],
						],
					],
				]
			],
		];
	}

	/**
	 * Output array settings for form.
	 *
	 * @param string $integration Integration name used for fallback.
	 *
	 * @return array<string, array<int, array<string, bool|string>>|string>
	 */
	public function getOutputGlobalFallback(string $integration): array
	{
		return $this->isSettingsGlobalValid() ? [
			'component' => 'tab',
			'tabLabel' => \__('Fallback', 'eightshift-forms'),
			'tabContent' => [
				[
					'component' => 'intro',
					'introSubtitle' => \__('In the event of an error on the forms, Eightshift Forms can send an email notification containing all the form submission data as a fallback. The email can be used to debug or allow manual data processing.', 'eightshift-forms'),
				],
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY . '-' . $integration),
					'inputFieldLabel' => \__('Fallback e-mail', 'eightshift-forms'),
					'inputFieldHelp' => \__('Set the email where the integration fallback emails will be sent. This field will be used as the "cc" field. The main "from" field will be used from the main fallback global setting page. Use commas to separate multiple emails.', 'eightshift-forms'),
					'inputType' => 'text',
					'inputIsRequired' => true,
					'inputValue' => $this->getOptionValue(self::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY . '-' . $integration),
				],
			],
		] : [];
	}
}
