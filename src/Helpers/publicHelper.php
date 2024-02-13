<?php

/**
 * File that holds all public helpers to be used in the other projects.
 *
 * @package EightshiftForms\Helpers
 */

use EightshiftForms\Entries\EntriesHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\General\SettingsGeneral;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Geolocation\Geolocation;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsEncryption;
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
	return UtilsSettingsHelper::getSettingValue(SettingsGeneral::SETTINGS_GENERAL_FORM_CUSTOM_NAME_KEY, $formId);
}

/**
 * Get field details by name.
 *
 * @example echo getFieldDetailsByName([], 'email');
 *
 * @param array<string, mixed> $params Form fields params.
 * @param string $key Field key.
 *
 * @return array<string, mixed>
 */
function getFieldDetailsByName(array $params, string $key): array
{
	return UtilsGeneralHelper::getFieldDetailsByName($params, $key);
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
	return UtilsEncryption::decryptor($value);
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
	return UtilsGeneralHelper::getSelectOptionsArrayFromString($options);
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

/**
 * Renders a block forms manualy using provided form ID.
 *
 * @param string $formId Form Id.
 * @param array<string, mixed> $attributes Array of attributes that's implicitly passed to component.
 *
 * @return string
 */
function esFormRenderForm(string $formId, array $attributes = []): string
{
	return Components::render(
		'forms/forms.php',
		[
			'formsFormPostId' => $formId,
			'formsStyle' => $attributes['style'] ?? [],
			'formsDownloads' => $attributes['downloads'] ?? [],
			'formsFormGeolocation' => $attributes['geolocation'] ?? [],
			'formsFormGeolocationAlternatives' => $attributes['geolocationAlternatives'] ?? [],
		],
		Components::getProjectPaths('blocksDestinationCustom'),
		true
	);
}

/**
 * Get entry by ID.
 *
 * @param string $id Entry Id.
 *
 * @return array<string, mixed>
 */
function esFormGetEntry(string $id): array
{
	return EntriesHelper::getEntry($id);
}
