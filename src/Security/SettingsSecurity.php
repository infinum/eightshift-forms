<?php

/**
 * Security settings class.
 *
 * @package EightshiftForms\Security
 */

declare(strict_types=1);

namespace EightshiftForms\Security;

use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Settings\SettingInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsSecurity class.
 */
class SettingsSecurity implements SettingGlobalInterface, ServiceInterface, SettingInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_security';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_security';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_security';

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_security';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'security';

	/**
	 * Security use key.
	 */
	public const SETTINGS_SECURITY_USE_KEY = 'security-use';

	/**
	 * Rate limit key.
	 */
	public const SETTINGS_SECURITY_RATE_LIMIT_KEY = 'security-rate-limit';

	/**
	 * Rate limit calculator key.
	 */
	public const SETTINGS_SECURITY_RATE_LIMIT_CALCULATOR_KEY = 'security-rate-limit-calculator';

	/**
	 * Rate limit window key.
	 */
	public const SETTINGS_SECURITY_RATE_LIMIT_WINDOW_KEY = 'security-rate-limit-window';

	/**
	 * IP ignore key.
	 */
	public const SETTINGS_SECURITY_IP_IGNORE_KEY = 'security-ip-ignore';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsValid']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsValid(): bool
	{
		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		return true;
	}


	/**
	 * Get settings data.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{

		$rateLimit = \intval(SettingsHelpers::getSettingValue(Security::RATE_LIMIT_SETTING_NAME, $formId));
		$rateLimit = ($rateLimit > 0) ? $rateLimit : '';

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Rate limiting', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => SettingsHelpers::getSettingName(Security::RATE_LIMIT_SETTING_NAME),
								'inputFieldLabel' => \__('Rate limit (submission attempts / seconds)', 'eightshift-forms'),
								'inputFieldHelp' => \__('If set, the form will be rate limited based on the provided value, in addition to global rate limits.', 'eightshift-forms'),
								'inputFieldAfterContent' => \__('per second', 'eightshift-forms'),
								'inputFieldInlineBeforeAfterContent' => true,
								'inputType' => 'number',
								'inputMin' => 1,
								'inputMax' => 1000,
								'inputStep' => 1,
								'inputIsDisabled' => false,
								'inputValue' => $rateLimit,
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
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_SECURITY_USE_KEY, self::SETTINGS_SECURITY_USE_KEY)) {
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
		if (!$this->isSettingsGlobalValid()) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Internal storage', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__("
									We have implemented a rate-limiting function that keeps track of the number of requests made by each user and compares it to the limit set. <br/><br/>
									The rate limit is based on the user's IP address, but we scramble each IP address we store to ensure that your forms comply with GDPR regulations.
									", 'eightshift-forms'),
							],
							[
								'component' => 'input',
								'inputName' => SettingsHelpers::getOptionName(self::SETTINGS_SECURITY_RATE_LIMIT_KEY),
								'inputFieldLabel' => \__('Number of default requests', 'eightshift-forms'),
								'inputFieldHelp' => \__('Define the maximum number of requests a user can make in the given time period for all forms excluding specifics listed below.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputMin' => 1,
								'inputMax' => 300,
								'inputStep' => 1,
								'inputPlaceholder' => Security::RATE_LIMIT,
								'inputFieldAfterContent' => \__('per min', 'eightshift-forms'),
								'inputFieldInlineBeforeAfterContent' => true,
								'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_SECURITY_RATE_LIMIT_KEY),
							],
							[
								'component' => 'input',
								'inputName' => SettingsHelpers::getOptionName(self::SETTINGS_SECURITY_RATE_LIMIT_CALCULATOR_KEY),
								'inputFieldLabel' => \__('Number of requests for calculator', 'eightshift-forms'),
								'inputFieldHelp' => \__('Define the maximum number of requests a user can make in the given time period for calculator forms with single submit.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputMin' => 1,
								'inputMax' => 300,
								'inputStep' => 1,
								'inputPlaceholder' => Security::RATE_LIMIT,
								'inputFieldAfterContent' => \__('per min', 'eightshift-forms'),
								'inputFieldInlineBeforeAfterContent' => true,
								'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_SECURITY_RATE_LIMIT_CALCULATOR_KEY),
							],
							[
								'component' => 'input',
								'inputName' => SettingsHelpers::getOptionName(self::SETTINGS_SECURITY_RATE_LIMIT_WINDOW_KEY),
								'inputFieldLabel' => \__('Limit window', 'eightshift-forms'),
								'inputFieldHelp' => \__('Define a time period in which the rate limit will be checked.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputMin' => 5,
								'inputMax' => 3600,
								'inputStep' => 1,
								'inputPlaceholder' => Security::RATE_LIMIT_WINDOW,
								'inputFieldAfterContent' => \__('sec', 'eightshift-forms'),
								'inputFieldInlineBeforeAfterContent' => true,
								'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_SECURITY_RATE_LIMIT_WINDOW_KEY),
							],
							[
								'component' => 'textarea',
								'textareaName' => SettingsHelpers::getOptionName(self::SETTINGS_SECURITY_IP_IGNORE_KEY),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('Ignore IPs', 'eightshift-forms'),
								// translators: %s will be replaced with local validation patterns.
								'textareaFieldHelp' => GeneralHelpers::minifyString(\__("
									If you need to ignore specific IPs, you can add them here. <br />
									Enter one IP per line, in the following format:<br />
									Example:
									<ul>
									<li>192.168.1.100</li>
									<li>192.168.1.101</li>
									</ul>", 'eightshift-forms')),
								'textareaValue' => SettingsHelpers::getOptionValueAsJson(self::SETTINGS_SECURITY_IP_IGNORE_KEY, 1),
							],
						],
					],
				],
			],
		];
	}
}
