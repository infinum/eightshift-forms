<?php

/**
 * Class that holds all generic helpers.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
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
	 * is  - is                               - if value is exact match.
	 * isn - is not                           - if value is not exact match.
	 * gt  - greater than                     - if value is greater than.
	 * gte - greater/equal than               - if value is greater/equal than.
	 * lt  - less than                        - if value is less than.
	 * lte - less/equal than                  - if value is less/equal than.
	 * c   - contains                         - if value contains value.
	 * sw  - starts with                      - if value starts with value.
	 * ew  - ends with                        - if value starts with value.
	 * b   - between range                    - if value is between two values.
	 * bs  - between range strict             - if value is between two values strict.
	 * bn  - not between range                - if value is not between two values.
	 * bns - not between between range strict - if value is not between two values strict.
	 *
	 * @param string $action Action to perform.
	 * @param string $start  Start value.
	 * @param string $value  Value to compare.
	 * @param string $end    End value.
	 *
	 * @return array<string, callable>
	 */
	public static function getComparator(string $action, string $start, string $value, string $end = '') {
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
		$data = isset($_GET[UtilsHelper::getStateSuccessRedirectUrlKey('data')]) ? json_decode(esFormsDecryptor(sanitize_text_field(wp_unslash($_GET[UtilsHelper::getStateSuccessRedirectUrlKey('data')]))), true) : [];

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
	 * Get result output success item part shortcode value.
	 *
	 * @param string $name Name of the item.
	 *
	 * @return string
	 */
	public static function getResultOutputSuccessItemPartShortcodeValue(string $name): array
	{
		$data = isset($_GET[UtilsHelper::getStateSuccessRedirectUrlKey('data')]) ? json_decode(esFormsDecryptor(sanitize_text_field(wp_unslash($_GET[UtilsHelper::getStateSuccessRedirectUrlKey('data')]))), true) : [];

		if (!$data) {
			return [
				'isRedirectPage' => false,
				'value' => '',
			];
		}

		$variationData = $data[UtilsHelper::getStateSuccessRedirectUrlKey('variation')] ?? [];

		if (!$variationData) {
			return [
				'isRedirectPage' => false,
				'value' => '',
			];
		}

		$output = '';

		foreach ($variationData as $key => $value) {
			if (!$key || !$value) {
				continue;
			}
	
			if ($name !== $key) {
				continue;
			}

			$output = $value;
			break;
		}

		if (!$output) {
			[
				'isRedirectPage' => true,
				'value' => '',
			];
		}

		return [
			'isRedirectPage' => true,
			'value' => $output,
		];
	}
}
