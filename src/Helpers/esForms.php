<?php

/**
 * File that holds all public helpers to be used in the other projects.
 *
 * @package EightshiftForms\Helpers
 */

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Geolocation\Geolocation;

/**
 * Outputs the forms custom unique name set in the settings by provided form ID.
 *
 * @example echo esFormsGetFormIdByName('22826');
 *
 * @param string $formId Form ID.
 *
 * @return string
 */
function esFormsGetFormIdByName(string $formId): string
{
	$class = (
		new class() // phpcs:ignore
		{
			use SettingsHelper;
		}
	);

	return $class->getSettingsValue(SettingsGeneral::SETTINGS_GENERAL_FORM_CUSTOM_NAME_KEY, $formId);
}

/**
 * Decrypt method.
 *
 * @param string $value Value used.
 *
 * @return string|bool
 */
function esFormsDecryptor(string $value)
{
	return Helper::decryptor($value);
}

/**
 * Geolocation countries list method.
 *
 * @return array<int, array<string|array<int, string>>>
 */
function esFormsGeolocationCountriesList()
{
	return (new Geolocation())->getCountriesList();
}
