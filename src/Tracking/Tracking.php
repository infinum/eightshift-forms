<?php

/**
 * Tracking class.
 *
 * @package EightshiftForms\Tracking
 */

declare(strict_types=1);

namespace EightshiftForms\Tracking;

use EightshiftForms\Hooks\Filters;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

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
	 * Return encripted data from get url param.
	 *
	 * @return string
	 */
	public function getTrackingToLocalStorage(): string
	{
		if (\is_admin()) {
			return '';
		}

		$request = Components::sanitizeArray($_GET, 'sanitize_text_field'); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if (!$request) {
			return '';
		}

		$output = [];

		$allowedTags = \array_flip($this->getAllowedTags());

		foreach ($request as $key => $value) {
			if (!isset($allowedTags[$key])) {
				continue;
			}

			$output[$key] = $value;
		}

		if (!$output) {
			return '';
		}

		return \wp_json_encode($output);
	}

	/**
	 * Return allowed tags to store in local storage.
	 *
	 * @return array<string>
	 */
	private function getAllowedTags(): array
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
