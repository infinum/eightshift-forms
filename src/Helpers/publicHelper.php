<?php

/**
 * File that holds all public helpers to be used in the other projects.
 *
 * @package EightshiftForms\Helpers
 */

use EightshiftForms\Cache\ManifestCache;
use EightshiftForms\Countries\Countries;
use EightshiftForms\Entries\EntriesHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\General\SettingsGeneral;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Geolocation\Geolocation;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsEncryption;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

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
	return UtilsSettingsHelper::getSettingValue(SettingsGeneral::SETTINGS_FORM_CUSTOM_NAME_KEY, $formId);
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
	$cache = new ManifestCache();
	return (new Geolocation($cache))->getCountriesList();
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
	return Helpers::render(
		$component,
		Helpers::props($component, $attributes, [
			'twSelectorsData' => FormsHelper::getTwSelectorsData($attributes),
		])
	);
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
	return Helpers::render(
		'forms',
		[
			'formsFormPostId' => $formId,
			'formsStyle' => $attributes['style'] ?? [],
			'formsFormGeolocation' => $attributes['geolocation'] ?? [],
			'formsFormGeolocationAlternatives' => $attributes['geolocationAlternatives'] ?? [],
		],
		'blocks',
		true
	);
}

/**
 * Get entry by ID.
 *
 * @param string $entryId Entry Id.
 *
 * @return array<string, mixed>
 */
function esFormGetEntry(string $entryId): array
{
	return EntriesHelper::getEntry($entryId);
}

/**
 * Get entry by ID.
 *
 * @param array<string, mixed> $data Data to update.
 * @param string $entryId Entry Id.
 *
 * @return boolean
 */
function esFormUpdateEntry(array $data, string $entryId): bool
{
	return EntriesHelper::updateEntry($data, $entryId);
}

/**
 * Get countries data set depending on the provided filter and default set.
 *
 * @return array<string, mixed>
 */
function getFormsGetCountryList(): array
{
	return (new Countries())->getCountriesDataSet();
}

/**
 * Get form usage location.
 *
 * @param string $formId Form ID.
 * @param string $type Type.
 *
 * @return array<int, mixed>
 */
function getFormUsageLocation(string $formId, string $type = ''): array
{
	return UtilsGeneralHelper::getBlockLocations($formId, $type);
}
