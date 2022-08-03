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
	 * Return encripted data from get url param.
	 *
	 * @return string
	 */
	public function getTrackingToLocalStorage(): string;
}
