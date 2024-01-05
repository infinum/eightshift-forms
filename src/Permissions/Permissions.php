<?php

/**
 * File containing an user Permissions class.
 *
 * @package EightshiftForms\Permissions
 */

declare(strict_types=1);

namespace EightshiftForms\Permissions;

/**
 * Class Permissions
 */
class Permissions
{
	/**
	 * Default user role to assign permissions.
	 */
	public const DEFAULT_MINIMAL_ROLES = [
		'editor',
		'administrator',
	];
}
