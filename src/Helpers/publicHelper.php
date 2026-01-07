<?php

/**
 * File that holds all public helpers to be used in the other projects.
 *
 * @package EightshiftForms\Helpers
 */

use EightshiftForms\Countries\Countries;
use EightshiftForms\Entries\EntriesHelper;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Geolocation\Geolocation;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Helpers\EncryptionHelpers;
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
	return SettingsHelpers::getSettingValue(SettingsGeneral::SETTINGS_FORM_CUSTOM_NAME_KEY, $formId);
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
	return EncryptionHelpers::decryptor($value);
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
 * Get users geolocation.
 * 
 * @return string
 */
function esFormsGetUsersGeolocation(): string
{
	return (new Geolocation())->getUsersGeolocation();
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
	return GeneralHelpers::getSelectOptionsArrayFromString($options);
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
 * Renders a block forms manually using provided form ID.
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
			'formsVariation' => $attributes['variation'] ?? [],
		],
		'blocks'
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
	return GeneralHelpers::getBlockLocations($formId, $type);
}

/**
 * Get param value.
 *
 * @param string $key Key to check.
 * @param array<mixed> $params Params to check.
 *
 * @return string|array<mixed>
 */
function getParamValue(string $key, array $params): string|array
{
	return FormsHelper::getParamValue($key, $params);
}

/**
 * Get form details.
 *
 * @param string $formId Form ID.
 *
 * @return array<string, mixed>
 */
function getFormDetails(string $formId): array
{
	return GeneralHelpers::getFormDetails($formId);
}
