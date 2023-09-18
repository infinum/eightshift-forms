<?php

/**
 * Security settings class.
 *
 * @package EightshiftForms\Security
 */

declare(strict_types=1);

namespace EightshiftForms\Security;

use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsSecurity class.
 */
class SettingsSecurity implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_security';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_security';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'security';

	/**
	 * Security use key.
	 */
	public const SETTINGS_SECURITY_USE_KEY = 'security-use';

	/**
	 * Data data key.
	 */
	public const SETTINGS_SECURITY_DATA_KEY = 'security-data';

	/**
	 * Rate limit key.
	 */
	public const SETTINGS_SECURITY_RATE_LIMIT_KEY = 'security-rate-limit';

	/**
	 * Rate limit window key.
	 */
	public const SETTINGS_SECURITY_RATE_LIMIT_WINDOW_KEY = 'security-rate-limit-window';

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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_SECURITY_USE_KEY, self::SETTINGS_SECURITY_USE_KEY);

		if (!$isUsed) {
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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_SECURITY_USE_KEY, self::SETTINGS_SECURITY_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
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
								'inputName' => $this->getSettingsName(self::SETTINGS_SECURITY_RATE_LIMIT_KEY),
								'inputFieldLabel' => \__('Number of requests', 'eightshift-forms'),
								'inputFieldHelp' => \__('Define the maximum number of requests a user can make in the given time period.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputMin' => 1,
								'inputMax' => 300,
								'inputStep' => 1,
								'inputPlaceholder' => Security::RATE_LIMIT,
								'inputFieldAfterContent' => \__('per min', 'eightshift-forms'),
								'inputFieldInlineBeforeAfterContent' => true,
								'inputValue' => $this->getOptionValue(self::SETTINGS_SECURITY_RATE_LIMIT_KEY),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_SECURITY_RATE_LIMIT_WINDOW_KEY),
								'inputFieldLabel' => \__('Limit window', 'eightshift-forms'),
								'inputFieldHelp' => \__('Define a time period in which the rate limit will be checked.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputMin' => 5,
								'inputMax' => 3600,
								'inputStep' => 1,
								'inputPlaceholder' => Security::RATE_LIMIT_WINDOW,
								'inputFieldAfterContent' => \__('sec', 'eightshift-forms'),
								'inputFieldInlineBeforeAfterContent' => true,
								'inputValue' => $this->getOptionValue(self::SETTINGS_SECURITY_RATE_LIMIT_WINDOW_KEY),
							],
						],
					],
				],
			],
		];
	}
}
