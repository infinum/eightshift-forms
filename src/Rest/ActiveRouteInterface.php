<?php

/**
 * ActiveRouteInterface interface
 *
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

/**
 * Interface for routes on which you can read their entire uri.
 */
interface ActiveRouteInterface
{

  /**
   * Returns the build client
   *
   * @return string
   */
	public function getRouteUri(): string;
}
