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
	 * Return encripted data from get url param.
	 *
	 * @return string
	 */
	public function getTrackingToLocalStorage(): string;
}
