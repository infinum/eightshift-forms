<?php

/**
 * ClientInterface interface
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations;

/**
 * ClientInterface interface.
 */
interface ClientInterface
{

	/**
	 * Returns the build client
	 *
	 * @return mixed
	 */
	public function getClient();

	/**
	 * Sets the config because we can't set config during construction (filters aren't yet registered)
	 *
	 * @return void
	 */
	public function setConfig();
}
