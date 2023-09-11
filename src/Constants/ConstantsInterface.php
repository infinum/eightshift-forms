<?php

/**
 * Constants interface main class.
 *
 * @package EightshiftForms\Constants
 */

declare(strict_types=1);

namespace EightshiftForms\Constants;

/**
 * Constants class.
 */
interface ConstantsInterface
{
	/**
	 * Change constant definition in the constantsOutput.php file.
	 *
	 * @param string $name Name of the setting.
	 * @param boolean $value Value of the setting.
	 *
	 * @return void
	 */
	public function changeConstants(string $name, bool $value): void;
}
