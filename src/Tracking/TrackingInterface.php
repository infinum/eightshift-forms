<?php

/**
 * Tracking data interface.
 *
 * @package EightshiftLibs\Tracking
 */

declare(strict_types=1);

namespace EightshiftForms\Tracking;

/**
 * Interface TrackingInterface
 */
interface TrackingInterface
{
	/**
	 * Return tracking expiration time in days.
	 *
	 * @return string
	 */
	public function getTrackingExpiration(): string;

	/**
	 * Return allowed tags to store in local storage.
	 *
	 * @return array<string>
	 */
	public function getAllowedTags(): array;
}
