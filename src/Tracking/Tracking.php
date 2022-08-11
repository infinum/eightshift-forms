<?php

/**
 * Tracking class.
 *
 * @package EightshiftForms\Tracking
 */

declare(strict_types=1);

namespace EightshiftForms\Tracking;

use EightshiftForms\Hooks\Filters;

/**
 * Tracking class.
 */
class Tracking implements TrackingInterface
{
	/**
	 * Return tracking expiration time in days.
	 *
	 * @return string
	 */
	public function getTrackingExpiration(): string
	{
		$output = '30';

		$filterName = Filters::getTrackingFilterName('expiration');
		if (\has_filter($filterName)) {
			return \apply_filters($filterName, $output) ?? $output;
		}

		return $output;
	}

	/**
	 * Return allowed tags to store in local storage.
	 *
	 * @return array<string>
	 */
	public function getAllowedTags(): array
	{
		$output = [
			'gh_src',
			'gh_jid',
			'_hsq',
			'utm',
		];

		$filterName = Filters::getTrackingFilterName('allowedTags');
		if (\has_filter($filterName)) {
			return \apply_filters($filterName, $output) ?? [];
		}

		return $output;
	}
}
