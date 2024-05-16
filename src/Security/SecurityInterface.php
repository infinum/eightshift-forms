<?php

/**
 * Interface that holds all methods for getting forms security usage.
 *
 * @package EightshiftForms\Security
 */

declare(strict_types=1);

namespace EightshiftForms\Security;

/**
 * Interface for SecurityInterface
 */
interface SecurityInterface
{
	/**
	 * Detect if the request is valid using rate limiting.
	 *
	 * @param string $formType Form type.
	 *
	 * @return boolean
	 */
	public function isRequestValid(string $formType): bool;

	/**
	 * Get users Ip address.
	 *
	 * @param bool $secure Determine if the function will return normal IP or hashed IP.
	 *
	 * @return string
	 */
	public function getIpAddress(bool $secure = false): string;
}
