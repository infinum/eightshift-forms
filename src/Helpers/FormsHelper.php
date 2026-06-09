<?php

/**
 * Class that holds all generic helpers.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Helpers\SettingsHelpers;
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
	 */
	public static function getComparator(string $action, string $start, string $value, string $end = ''): bool
	{
		$operator = Helpers::getSettings()['comparator'];
		$operatorExtended = Helpers::getSettings()['comparatorExtended'];

		return match ($action) {
									$operator['IS'] => $value === $start,
									$operator['ISN'] => $value !== $start,
									$operator['GT'] => \floatval($value) > \floatval($start),
									$operator['GTE'] => \floatval($value) >= \floatval($start),
									$operator['LT'] => \floatval($value) < \floatval($start),
									$operator['LTE'] => \floatval($value) <= \floatval($start),
									$operator['C'] => \str_contains($value, $start),
									$operator['CN'] => !\str_contains($value, $start),
									$operator['SW'] => \str_starts_with($value, $start),
									$operator['EW'] => \str_ends_with($value, $start),
									$operatorExtended['B'] => \floatval($value) > \floatval($start) && \floatval($value) < \floatval($end),
									$operatorExtended['BS'] => \floatval($value) >= \floatval($start) && \floatval($value) <= \floatval($end),
									$operatorExtended['BN'] => \floatval($value) < \floatval($start) || \floatval($value) > \floatval($end),
									$operatorExtended['BNS'] => \floatval($value) <= \floatval($start) || \floatval($value) >= \floatval($end),
									default => false,
		};
	}

	/**
	 * Check if the result output success should be shown.
	 *
	 * @param string $name     Name of the item.
	 * @param string $operator Operator to use.
	 * @param string $start    Start value.
	 * @param string $end      End value.
	 *
	 * @return array<string, bool>
	 */
	public static function checkResultOutputSuccess(string $name, string $operator, string $start, string $end): array
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
			if (!$key) {
													continue;
			}
			if (!isset($value)) {
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

		$filter = \is_admin() ? 'tailwindSelectorsAdmin' : 'tailwindSelectors';

		$filterName = HooksHelpers::getFilterName(['blocks', $filter]);
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

		if ($data === []) {
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
	 * @param string $suffix Suffix to add to the selector.
	 */
	public static function getTwBase(array $data, string $selector, string $suffix = ''): string
	{
		$base = $data[$selector]['base'] ?? [];

		if (!$base) {
			return $suffix;
		}

		return \implode(' ', \is_array($base) ? \array_merge($base, [$suffix]) : [$base, $suffix]);
	}

	/**
	 * Return Tailwind selectors part output.
	 *
	 * @param array<string, string> $data Data to get part from.
	 * @param string $parentSelector Parent selector to get data for.
	 * @param string $selector Selector to get data for.
	 * @param string $suffix Suffix to add to the selector.
	 */
	public static function getTwPart(array $data, string $parentSelector, string $selector, string $suffix = ''): string
	{
		$parts = $data[$parentSelector]['parts'] ?? [];

		if (!$parts) {
			return $suffix;
		}

		$part = $parts[$selector] ?? [];

		if (!$part) {
			return $suffix;
		}

		return \implode(' ', \is_array($part) ? \array_merge($part, [$suffix]) : [$part, $suffix]);
	}

	/**
	 * Return Tailwind selectors output.
	 *
	 * @param array<string, string> $data Data to get output from.
	 * @param string $type Type of the selectors.
	 */
	public static function getTwSelectorsOutput(array $data, string $type): string
	{
		$output = match ($type) {
									'select', 'country' => [
					'containerOuter' => $data['base'] ?? [],
					'containerInner' => $data['parts']['select-choices-inner'] ?? [],
					'input' => $data['parts']['select-input'] ?? [],
					'inputCloned' => $data['parts']['select-input-cloned'] ?? [],
					'list' => $data['parts']['select-list'] ?? [],
					'listMultiple' => $data['parts']['select-list-multiple'] ?? [],
					'listSingle' => $data['parts']['select-list-single'] ?? [],
					'listDropdown' => $data['parts']['select-list-dropdown'] ?? [],
					'item' => $data['parts']['select-item'] ?? [],
					'itemSelectable' => $data['parts']['select-item-selectable'] ?? [],
					'itemDisabled' => $data['parts']['select-item-disabled'] ?? [],
					'itemChoice' => $data['parts']['select-item-choice'] ?? [],
					'placeholder' => $data['parts']['select-placeholder'] ?? [],
					'button' => $data['parts']['select-button'] ?? [],
									],
									'phone' => [
									'containerOuter' => $data['parts']['select'] ?? [],
									'containerInner' => $data['parts']['select-choices-inner'] ?? [],
									'input' => $data['parts']['select-input'] ?? [],
									'inputCloned' => $data['parts']['select-input-cloned'] ?? [],
									'list' => $data['parts']['select-list'] ?? [],
									'listMultiple' => $data['parts']['select-list-multiple'] ?? [],
									'listSingle' => $data['parts']['select-list-single'] ?? [],
									'listDropdown' => $data['parts']['select-list-dropdown'] ?? [],
									'item' => $data['parts']['select-item'] ?? [],
									'itemSelectable' => $data['parts']['select-item-selectable'] ?? [],
									'itemDisabled' => $data['parts']['select-item-disabled'] ?? [],
									'itemChoice' => $data['parts']['select-item-choice'] ?? [],
									'placeholder' => $data['parts']['select-placeholder'] ?? [],
									'button' => $data['parts']['select-button'] ?? [],
									],
									'date' => $data['parts']['picker'] ?? [],
									default => [],
		};

					return \wp_json_encode($output);
	}

	/**
	 * Get unique form hash.
	 */
	public static function getFormUniqueHash(): string
	{
		return \str_pad((string) \wp_rand(1, 9999999999), 10, '0', \STR_PAD_LEFT);
	}

	/**
	 * Get increment.
	 *
	 * @param string $formId Form Id.
	 */
	public static function getIncrement(string $formId): string
	{
		$value = SettingsHelpers::getSettingValue(SettingsGeneral::INCREMENT_META_KEY, $formId);
		if ($value === '' || $value === '0') {
			$value = 0;
		}

		$length = SettingsHelpers::getSettingValue(SettingsGeneral::SETTINGS_INCREMENT_LENGTH_KEY, $formId);
		if ($length !== '' && $length !== '0') {
			$value = \str_pad((string) $value, (int) $length, '0', \STR_PAD_LEFT);
		}

		return (string) $value;
	}

	/**
	 * Set increment.
	 *
	 * @param string $formId Form Id.
	 */
	public static function setIncrement(string $formId): string
	{
		$start = SettingsHelpers::getSettingValue(SettingsGeneral::SETTINGS_INCREMENT_START_KEY, $formId);
		$value = SettingsHelpers::getSettingValue(SettingsGeneral::INCREMENT_META_KEY, $formId);

		if ($value === '' || $value === '0') {
			$value = $start;
		}

		if ((int) $start > (int) $value) {
			$value = $start;
		}

		$value = (int) $value + 1;

		\update_post_meta((int) $formId, SettingsHelpers::getSettingName(SettingsGeneral::INCREMENT_META_KEY), $value);

		return self::getIncrement($formId);
	}

	/**
	 * Reset increment.
	 *
	 * @param string $formId Form Id.
	 */
	public static function resetIncrement(string $formId): bool
	{
		$value = SettingsHelpers::getSettingValue(SettingsGeneral::SETTINGS_INCREMENT_START_KEY, $formId);

		if ($value === '' || $value === '0') {
			$value = 0;
		}

		$update = \update_post_meta((int) $formId, SettingsHelpers::getSettingName(SettingsGeneral::INCREMENT_META_KEY), $value);

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
		return \array_reduce($params, fn($carry, $paramKey): mixed => $carry ?: ($paramKey['name'] === $key ? $paramKey['value'] : ''), '');
	}

	/**
	 * Get locale from country code.
	 */
	public static function getLocaleFromCountryCode(): string
	{
		$locale = I18nHelpers::getLocale();

		$languages = \apply_filters('wpml_active_languages', []);

		return \array_values(\array_filter($languages, fn(array $language): bool => $language['code'] === $locale))[0]['default_locale'] ?? 'en_US';
	}

	/**
	 * Get project settings with filters applied.
	 *
	 * @return array<mixed>
	 */
	public static function getProjectSettings(): array
	{
		$settings = Helpers::getSettings();

		// Update media breakpoints from the filter.
		$filterName = HooksHelpers::getFilterName(['blocks', 'mediaBreakpoints']);

		if (\has_filter($filterName)) {
			$customMediaBreakpoints = \apply_filters($filterName, []);

			if (
				\is_array($customMediaBreakpoints) &&
				isset($customMediaBreakpoints['mobile']) &&
				isset($customMediaBreakpoints['tablet']) &&
				isset($customMediaBreakpoints['desktop']) &&
				isset($customMediaBreakpoints['large'])
			) {
				$settings['globalVariables']['breakpoints'] = $customMediaBreakpoints;
			}
		}

		return $settings;
	}
}
