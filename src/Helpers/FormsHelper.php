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
		$data = isset($_GET[UtilsHelper::getStateSuccessRedirectUrlKey('data')]) ? \json_decode(\esFormsDecryptor(\sanitize_text_field(\wp_unslash($_GET[UtilsHelper::getStateSuccessRedirectUrlKey('data')]))), true) : [];

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
}
