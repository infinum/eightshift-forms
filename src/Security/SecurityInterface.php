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
	public function isRequestValid(): bool;
}
