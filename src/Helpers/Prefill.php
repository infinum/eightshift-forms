<?php

/**
 * Helpers for prefill
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\Hooks\Filters;

/**
 * Helpers for prefill
 */
class Prefill implements Filters
{

	public const FAILED_TO_PREFILL_LABEL = 'Unable to prefill options';

	/**
	 * Returns the data of project-defined prefill source.
	 *
	 * Needs to return array with the following 2 keys:
	 * - label
	 * - value
	 *
	 * @param  string $prefillSourceName Name of the prefill source as defined in project.
	 * @param  string $filterName         Name of the filter we're getting data for.
	 * @return array
	 */
	public static function getPrefillSourceData(string $prefillSourceName, string $filterName): array
	{
		if (! has_filter(self::PREFILL_GENERIC_MULTI)) {
			return [
				[
					'label' => esc_html__('Unable to prefill options, selected options not defined', 'eightshift-forms'),
					'value' => 'no-value',
				],
			];
		}

		$prefillData = apply_filters($filterName, []);

		if (! isset($prefillData[$prefillSourceName], $prefillData[$prefillSourceName]['data'])) {
			return [
				[
					'label' => esc_html__('Unable to prefill options, no data defined', 'eightshift-forms'),
					'value' => 'no-value',
				],
			];
		}

		return $prefillData[$prefillSourceName]['data'];
	}

	/**
	 * Returns the data of project-defined prefill source (used for single input blocks - such as <input>)
	 *
	 * Needs to return a string.
	 *
	 * @param  string $prefillSourceName Name of the prefill source as defined in project.
	 * @param  string $filterName         Name of the filter we're getting data for.
	 * @return mixed
	 */
	public static function getPrefillSourceDataSingle(string $prefillSourceName, string $filterName)
	{
		if (! has_filter(self::PREFILL_GENERIC_SINGLE)) {
			return esc_html__('Unable to prefill options, no data defined', 'eightshift-forms');
		}

		$prefillData = apply_filters($filterName, []);

		if (! isset($prefillData[$prefillSourceName], $prefillData[$prefillSourceName]['data'])) {
			return esc_html__('Unable to prefill options, no data defined', 'eightshift-forms');
		}

		return $prefillData[$prefillSourceName]['data'];
	}
}
