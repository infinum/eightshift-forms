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
use EightshiftForms\Helpers\Encryption;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

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
	return Encryption::decryptor($value);
}

/**
 * Geolocation countries list method.
 *
 * @return array<int, array<string|array<int, string>>>
 */
function esFormsGeolocationCountriesList(): array
{
	return (new Geolocation())->getCountriesList();
}

/**
 * Output select options ass array from html string.
 *
 * @param string $options Options string.
 *
 * @return array<int, array<string, string>>
 */
function esFormsGetSelectOptionsArrayFromString(string $options): array
{
	return Helper::getSelectOptionsArrayFromString($options);
}

/**
 * Renders a components and (optionally) passes some attributes to it.
 *
 * @param string $component Component's name or full path (ending with .php).
 * @param array<string, mixed> $attributes Array of attributes that's implicitly passed to component.
 *
 * @return string
 */
function esFormsGetComponentsRender(string $component, array $attributes = []): string
{
	return Components::render($component, $attributes);
}
