<?php

/**
 * Class that holds all helpers for settings.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\Config\Config;

/**
 * SettingsHelpers class.
 */
final class SettingsHelpers
{
	// --------------------------------------------------
	// Settings helper methods
	// --------------------------------------------------
	/**
	 * Get settings value.
	 *
	 * @param string $key Key to find in db settings.
	 * @param string $formId Form Id.
	 *
	 * @return string
	 */
	public static function getSettingValue(string $key, string $formId): string
	{
		return (string) \get_post_meta((int) $formId, self::getSettingName($key), true);
	}

	/**
	 * Get option value string saved as json array - used for textarea with : delimiter.
	 *
	 * @param string $key Providing string to append to.
	 * @param string $formId Form Id.
	 * @param int $useNumber Number of items to use.
	 *
	 * @return string
	 */
	public static function getSettingValueAsJson(string $key, string $formId, int $useNumber = 2): string
	{
		$values = self::getSettingValueGroup($key, $formId);
		if (!$values) {
			return '';
		}

		return self::getSavedValueAsJson($values, $useNumber);
	}

	/**
	 * Get settings value with fallback.
	 *
	 * @param string $key Key to find in db settings.
	 * @param string $optionKey Key to find in db options.
	 * @param string $fallback Fallback value.
	 * @param string $formId Form Id.
	 *
	 * @return string
	 */
	public static function getSettingValueWithFallback(string $key, string $optionKey, string $fallback, string $formId): string
	{
		$value = self::getSettingValue($key, $formId);

		if (!$value) {
			$value = self::getOptionValue($optionKey);
		}

		if (!$value) {
			$value = $fallback;
		}

		return $value;
	}

	/**
	 * Get settings value with fallback only
	 *
	 * @param string $key Key to find in db settings.
	 * @param string $fallback Fallback value.
	 * @param string $formId Form Id.
	 *
	 * @return string
	 */
	public static function getSettingValueWithFallbackOnly(string $key, string $fallback, string $formId): string
	{
		$value = self::getSettingValue($key, $formId);

		if (!$value) {
			$value = $fallback;
		}

		return $value;
	}

	/**
	 * Get settings value array.
	 *
	 * @param string $key Providing string to append to.
	 * @param string $formId Form Id.
	 *
	 * @return array<string, mixed>
	 */
	public static function getSettingValueGroup(string $key, string $formId): array
	{
		$value = \get_post_meta((int) $formId, self::getSettingName($key), true);
		if (!$value) {
			return [];
		}

		$value = \maybe_unserialize($value);
		if (!\is_array($value)) {
			return [];
		}

		return $value;
	}

	/**
	 * Determine if settings is checked (used for radio, and select box).
	 *
	 * @param string $key Key to find.
	 * @param string $id Checkboxes ID.
	 * @param string $formId Form Id.
	 *
	 * @return bool
	 */
	public static function isSettingChecked(string $key, string $id, string $formId): bool
	{
		return self::getSettingValue($id, $formId) === $key;
	}

	/**
	 * Determine if checkbox settings is checked (used for checkbox).
	 *
	 * @param string $key Key to find.
	 * @param string $id Checkboxes ID.
	 * @param string $formId Form Id.
	 *
	 * @return bool
	 */
	public static function isSettingCheckboxChecked(string $key, string $id, string $formId): bool
	{
		return \in_array($key, \explode(Config::DELIMITER, self::getSettingValue($id, $formId)), true);
	}

	/**
	 * Get string setting name.
	 *
	 * @param string $key Providing string to append to.
	 *
	 * @return string
	 */
	public static function getSettingName(string $key): string
	{
		return Config::SETTINGS_NAME_PREFIX . "-{$key}";
	}

	// --------------------------------------------------
	// Options helper methods
	// --------------------------------------------------

	/**
	 * Get option value.
	 *
	 * @param string $key Providing string to append to.
	 *
	 * @return string
	 */
	public static function getOptionValue(string $key): string
	{
		return (string) \get_option(self::getOptionName($key), '');
	}

	/**
	 * Get option with constant.
	 *
	 * @param string $constantValue Constant value.
	 * @param string $key Option name.
	 *
	 * @return string
	 */
	public static function getOptionWithConstant(
		string $constantValue,
		string $key,
	): string {
		return empty($constantValue) ? self::getOptionValue($key) : $constantValue;
	}

	/**
	 * Get option value with fallback.
	 *
	 * @param string $key Key to find in db settings.
	 * @param string $fallback Fallback value.
	 *
	 * @return string
	 */
	public static function getOptionValueWithFallback(string $key, string $fallback): string
	{
		$value = self::getOptionValue($key);

		if (!$value) {
			$value = $fallback;
		}

		return $value;
	}

	/**
	 * Get option value array.
	 *
	 * @param string $key Providing string to append to.
	 *
	 * @return array<string, mixed>
	 */
	public static function getOptionValueGroup(string $key): array
	{
		$value = \get_option(self::getOptionName($key), []);

		if (!$value) {
			return [];
		}

		$value = \maybe_unserialize($value);
		if (!\is_array($value)) {
			return [];
		}

		return $value;
	}

	/**
	 * Get option value string saved as json array - used for textarea with : delimiter.
	 *
	 * @param string $key Providing string to append to.
	 * @param int $useNumber Number of items to use.
	 *
	 * @return string
	 */
	public static function getOptionValueAsJson(string $key, int $useNumber = 2): string
	{
		$values = self::getOptionValueGroup($key);
		if (!$values) {
			return '';
		}

		return self::getSavedValueAsJson($values, $useNumber);
	}

	/**
	 * Get option checkbox multiple value.
	 *
	 * @param string $key Providing string to append to.
	 *
	 * @return array<int, string>
	 */
	public static function getOptionCheckboxValues(string $key): array
	{
		$value = self::getOptionValue($key);

		if (!$value) {
			return [];
		};

		return \explode(Config::DELIMITER, $value);
	}

	/**
	 * Determine if global is checked (used for radio, and select box).
	 *
	 * @param string $key Key to find.
	 * @param string $id Checkboxes ID.
	 *
	 * @return bool
	 */
	public static function isOptionChecked(string $key, string $id): bool
	{
		return self::getOptionValue($id) === $key;
	}

	/**
	 * Determine if checkbox global is checked (used for checkbox).
	 *
	 * @param string $key Key to find.
	 * @param string $id Checkboxes ID.
	 *
	 * @return bool
	 */
	public static function isOptionCheckboxChecked(string $key, string $id): bool
	{
		return \in_array($key, \explode(Config::DELIMITER, self::getOptionValue($id)), true);
	}

	/**
	 * Get string option name with locale.
	 *
	 * @param string $key Providing string to append to.
	 *
	 * @return string
	 */
	public static function getOptionName(string $key): string
	{
		$suffix = '';

		$data = \EIGHTSHIFT_FORMS[Config::PUBLIC_NONE_TRANSLATABLE_NAMES_NAME] ?? []; // @phpstan-ignore-line

		if (!isset(\array_flip($data)[$key])) {
			$locale = I18nHelpers::getLocale();

			if ($locale) {
				$delimiter = Config::DELIMITER;
				$suffix = "{$delimiter}{$locale}";
			}
		}

		return Config::SETTINGS_NAME_PREFIX . "-{$key}{$suffix}";
	}

	// --------------------------------------------------
	// General helper methods
	// --------------------------------------------------

	/**
	 * Get settings in the correct order by order value.
	 *
	 * @param array<mixed> $data Data to sort.
	 *
	 * @return array<mixed>
	 */
	public static function sortSettingsByOrder(array $data): array
	{
		$correctOrder = [];
		$output = [];

		// Generate correct order of settings.
		foreach (\apply_filters(Config::FILTER_SETTINGS_DATA, []) as $key => $item) {
			$order = $item['order'] ?? 0;
			if (!$order) {
				continue;
			}

			if (!\is_int($order)) {
				continue;
			}

			$correctOrder[$order] = $key;
		}

		// Sort the array.
		\ksort($correctOrder);

		// Output the sorted array.
		foreach ([...$correctOrder] as $key) {
			if (!\array_key_exists($key, $data)) {
				continue;
			}

			$output[$key] = $data[$key];
		}

		// Return the sorted array.
		return $output;
	}

	// --------------------------------------------------
	// Private helper methods
	// --------------------------------------------------

	/**
	 * Get saved value string saved as json array - used for textarea with : delimiter.
	 *
	 * @param array<string, mixed> $values Values provided from settings.
	 * @param int $useNumber Number of items to use.
	 *
	 * @return string
	 */
	private static function getSavedValueAsJson(array $values, int $useNumber = 2): string
	{
		$output = [];
		$i = 1;
		foreach ($values as $value) {
			if (!$value) {
				continue;
			}

			$value = \array_filter(
				$value,
				static function ($item) use ($useNumber) {
					return $item <= $useNumber - 1;
				},
				\ARRAY_FILTER_USE_KEY
			);

			// Remove keys that are note set properly.
			if (\count($value) < $useNumber) {
				continue;
			}

			$output[] = \implode(' : ', $value);

			$i++;
		}

		return \implode(\PHP_EOL, $output);
	}
}
