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
	 * @param int $formId Form ID.
	 *
	 * @return boolean
	 */
	public function isRequestValid(string $formType, int $formId): bool;

	/**
	 * Get users Ip address.
	 *
	 * @param string $secureType Determine if the function will return normal, hashed or anonymized IP.
	 *
	 * @return string
	 */
	public function getIpAddress(string $secureType = 'none'): string;
}
