<?php

/**
 * Class that holds all filter used in the component and blocks regarding settings.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Settings class.
 */
class Settings implements ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings value key.
	 */
	public const FILTER_SETTINGS_VALUE_NAME = 'es_forms_settings_value';

	/**
	 * Filter settings option value key.
	 */
	public const FILTER_SETTINGS_OPTION_VALUE_NAME = 'es_forms_settings_option_value';

	/**
	 * Filter is checked settings key.
	 */
	public const FILTER_IS_CHECKED_SETTINGS_NAME = 'es_forms_is_checked_settings';

	/**
	 * Filter is checked option key.
	 */
	public const FILTER_IS_CHECKED_OPTION_NAME = 'es_forms_is_checked_option';

	/**
	 * Filter is checkbox settings checked key.
	 */
	public const FILTER_IS_CHECKBOX_SETTINGS_CHECKED_NAME = 'es_forms_is_checkbox_settings_checked';

	/**
	 * Filter is checkbox option checked key.
	 */
	public const FILTER_IS_CHECKBOX_OPTION_CHECKED_NAME = 'es_forms_is_checkbox_option_checked';

	/**
	 * Filter settings option value key.
	 */
	public const FILTER_SETTINGS_NAME_NAME = 'es_forms_settings_name';

	/**
	 * Filter settings option value key.
	 */
	public const FILTER_SETTINGS_LOCALE_VALUE_NAME = 'es_forms_settings_locale';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_VALUE_NAME, [$this, 'getSettingsValue'], 10, 2);
		\add_filter(self::FILTER_SETTINGS_OPTION_VALUE_NAME, [$this, 'getOptionValue']);
		\add_filter(self::FILTER_IS_CHECKED_SETTINGS_NAME, [$this, 'isCheckedSettings'], 10, 3);
		\add_filter(self::FILTER_IS_CHECKED_OPTION_NAME, [$this, 'isCheckedOption'], 10, 2);
		\add_filter(self::FILTER_IS_CHECKBOX_SETTINGS_CHECKED_NAME, [$this, 'isCheckboxSettingsChecked'], 10, 3);
		\add_filter(self::FILTER_IS_CHECKBOX_OPTION_CHECKED_NAME, [$this, 'isCheckboxOptionChecked'], 10, 2);
		\add_filter(self::FILTER_SETTINGS_NAME_NAME, [$this, 'getSettingsName']);
		\add_filter(self::FILTER_SETTINGS_LOCALE_VALUE_NAME, [$this, 'getLocale']);
	}
}
