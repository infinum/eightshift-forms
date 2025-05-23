<?php

/**
 * Class that holds all generic helpers.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\General\SettingsGeneral;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsI18nHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

/**
 * FormsHelper class.
 */
final class FormsHelper
{
	/**
	 * Return field type internal enum values by name.
	 *
	 * @param string $name Name of the enum.
	 *
	 * @return string
	 */
	public static function getStateFieldType(string $name): string
	{
		return Helpers::getSettings()['enums']['typeInternal'][$name] ?? '';
	}

	/**
	 * Return field type internal enum values by name.
	 *
	 * Is  - is                               - if value is exact match.
	 * Isn - is not                           - if value is not exact match.
	 * Gt  - greater than                     - if value is greater than.
	 * Gte - greater/equal than               - if value is greater/equal than.
	 * Lt  - less than                        - if value is less than.
	 * Lte - less/equal than                  - if value is less/equal than.
	 * c   - contains                         - if value contains value.
	 * Ww  - starts with                      - if value starts with value.
	 * Ew  - ends with                        - if value ends with value.
	 * B   - between range                    - if value is between two values.
	 * Bs  - between range strict             - if value is between two values strict.
	 * Bn  - not between range                - if value is not between two values.
	 * Bns - not between between range strict - if value is not between two values strict.
	 *
	 * @param string $action Action to perform.
	 * @param string $start  Start value.
	 * @param string $value  Value to compare.
	 * @param string $end    End value.
	 *
	 * @return boolean
	 */
	public static function getComparator(string $action, string $start, string $value, string $end = ''): bool
	{
		$operator = Helpers::getSettings()['comparator'];
		$operatorExtended = Helpers::getSettings()['comparatorExtended'];

		switch ($action) {
			case $operator['IS']:
				return $value === $start;
			case $operator['ISN']:
				return $value !== $start;
			case $operator['GT']:
				return \floatval($start) > \floatval($value);
			case $operator['GTE']:
				return \floatval($start) >= \floatval($value);
			case $operator['LT']:
				return \floatval($start) < \floatval($value);
			case $operator['LTE']:
				return \floatval($start) <= \floatval($value);
			case $operator['C']:
				return \strpos($start, $value) !== false;
			case $operator['SW']:
				return \strpos($start, $value) === 0;
			case $operator['EW']:
				return \substr($start, -\strlen($value)) === $value;
			case $operatorExtended['B']:
				return \floatval($start) > \floatval($value) && \floatval($start) < \floatval($end);
			case $operatorExtended['BS']:
				return \floatval($start) >= \floatval($value) && \floatval($start) <= \floatval($end);
			case $operatorExtended['BN']:
				return \floatval($start) < \floatval($value) || \floatval($start) > \floatval($end);
			case $operatorExtended['BNS']:
				return \floatval($start) <= \floatval($value) || \floatval($start) >= \floatval($end);
			default:
				return false;
		}
	}

	/**
	 * Check if the result output success should be shown.
	 *
	 * @param string $name     Name of the item.
	 * @param string $operator Operator to use.
	 * @param string $start    Start value.
	 * @param string $value    Value to compare.
	 * @param string $end      End value.
	 *
	 * @return array<string, bool>
	 */
	public static function checkResultOutputSuccess(string $name, string $operator, string $start, string $value, string $end): array
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$data = isset($_GET[UtilsHelper::getStateSuccessRedirectUrlKey('data')]) ? \json_decode(\esFormsDecryptor(\sanitize_text_field(\wp_unslash($_GET[UtilsHelper::getStateSuccessRedirectUrlKey('data')]))) ?: '', true) : [];

		if (!$data) {
			return [
				'isRedirectPage' => false,
				'showOutput' => false,
			];
		}

		$variationData = $data[UtilsHelper::getStateSuccessRedirectUrlKey('variation')] ?? [];

		if (!$variationData) {
			return [
				'isRedirectPage' => false,
				'showOutput' => false,
			];
		}

		$showOutput = false;

		foreach ($variationData as $key => $value) {
			if (!$key || !$value) {
				continue;
			}

			if ($name !== $key) {
				continue;
			}

			if (FormsHelper::getComparator($operator, $start, $value, $end)) {
				$showOutput = true;
				break;
			}
		}

		if (!$showOutput) {
			return [
				'isRedirectPage' => true,
				'showOutput' => false,
			];
		}

		return [
			'isRedirectPage' => true,
			'showOutput' => true,
		];
	}

	/**
	 * Return Tailwind selectors data filter output.
	 *
	 * @param array<string, string> $attributes The block attributes.
	 *
	 * @return array<mixed>
	 */
	public static function getTwSelectorsData(array $attributes): array
	{
		$blockSsr = $attributes['blockSsr'] ?? false;

		if ($blockSsr) {
			return [];
		}

		$filterName = UtilsHooksHelper::getFilterName(['blocks', 'tailwindSelectors']);
		if (\has_filter($filterName)) {
			return \apply_filters($filterName, [], $attributes);
		}

		return [];
	}

	/**
	 * Return Tailwind selectors data filter output.
	 *
	 * @param array<string> $data Data to get data for.
	 * @param array<string> $selectors Selectors to get data for.
	 *
	 * @return array<mixed>
	 */
	public static function getTwSelectors(array $data, array $selectors): array
	{
		$output = [];

		if (!$data) {
			return $output;
		}

		foreach ($selectors as $selector) {
			if (isset($data[$selector])) {
				$output[$selector] = $data[$selector];
			}
		}

		return $output;
	}

	/**
	 * Return Tailwind selectors base output.
	 *
	 * @param array<string, string> $data Data to get base from.
	 * @param string $selector Selector to get data for.
	 * @param string $sufix Sufix to add to the selector.
	 *
	 * @return string
	 */
	public static function getTwBase(array $data, string $selector, string $sufix = ''): string
	{
		$base = $data[$selector]['base'] ?? [];

		if (!$base) {
			return $sufix;
		}

		return \implode(' ', !\is_array($base) ? [$base, $sufix] : \array_merge($base, [$sufix]));
	}

	/**
	 * Return Tailwind selectors part output.
	 *
	 * @param array<string, string> $data Data to get part from.
	 * @param string $parentSelector Parent selector to get data for.
	 * @param string $selector Selector to get data for.
	 * @param string $sufix Sufix to add to the selector.
	 *
	 * @return string
	 */
	public static function getTwPart(array $data, string $parentSelector, string $selector, string $sufix = ''): string
	{
		$parts = $data[$parentSelector]['parts'] ?? [];

		if (!$parts) {
			return $sufix;
		}

		$part = $parts[$selector] ?? [];

		if (!$part) {
			return $sufix;
		}

		return \implode(' ', !\is_array($part) ? [$part, $sufix] : \array_merge($part, [$sufix]));
	}

	/**
	 * Get unique form hash.
	 *
	 * @return string
	 */
	public static function getFormUniqueHash(): string
	{
		return \str_pad((string) \wp_rand(1, 9999999999), 10, '0', \STR_PAD_LEFT);
	}

	/**
	 * Get increment.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return string
	 */
	public static function getIncrement(string $formId): string
	{
		$value = UtilsSettingsHelper::getSettingValue(SettingsGeneral::INCREMENT_META_KEY, $formId);
		if (!$value) {
			$value = 0;
		}

		$length = UtilsSettingsHelper::getSettingValue(SettingsGeneral::SETTINGS_INCREMENT_LENGTH_KEY, $formId);
		if ($length) {
			$value = \str_pad($value, (int) $length, '0', \STR_PAD_LEFT);
		}

		return (string) $value;
	}

	/**
	 * Set increment.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return string
	 */
	public static function setIncrement(string $formId): string
	{
		$start = UtilsSettingsHelper::getSettingValue(SettingsGeneral::SETTINGS_INCREMENT_START_KEY, $formId);
		$value = UtilsSettingsHelper::getSettingValue(SettingsGeneral::INCREMENT_META_KEY, $formId);

		if (!$value) {
			$value = $start;
		}

		if ((int) $start > (int) $value) {
			$value = $start;
		}

		$value = (int) $value + 1;

		\update_post_meta((int) $formId, UtilsSettingsHelper::getSettingName(SettingsGeneral::INCREMENT_META_KEY), $value);

		return static::getIncrement($formId);
	}

	/**
	 * Reset increment.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return bool
	 */
	public static function resetIncrement(string $formId): bool
	{
		$value = UtilsSettingsHelper::getSettingValue(SettingsGeneral::SETTINGS_INCREMENT_START_KEY, $formId);

		if (!$value) {
			$value = 0;
		}

		$update = \update_post_meta((int) $formId, UtilsSettingsHelper::getSettingName(SettingsGeneral::INCREMENT_META_KEY), $value);

		return (bool) $update;
	}

	/**
	 * Get param value.
	 *
	 * @param string $key Key to check.
	 * @param array<mixed> $params Params to check.
	 *
	 * @return string|array<mixed>
	 */
	public static function getParamValue(string $key, array $params): string|array
	{
		return \array_reduce($params, fn($carry, $paramKey) => $carry ?: ($paramKey['name'] === $key ? $paramKey['value'] : ''), '');
	}

	/**
	 * Get locale from country code.
	 *
	 * @return string
	 */
	public static function getLocaleFromCountryCode(): string
	{
		$locale = UtilsI18nHelper::getLocale();

		$languages = \apply_filters('wpml_active_languages', []);

		return \array_values(\array_filter($languages, fn($language) => $language['code'] === $locale))[0]['default_locale'] ?? 'en_US';
	}
}
