<?php

/**
 * Class that holds all generic helpers.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

/**
 * FormsHelper class.
 */
final class FormsHelper
{
	/**
	 * Return field type internal enum values by name.
	 *
	 * @param string $name Name of the enum.
	 *
	 * @return string
	 */
	public static function getStateFieldType(string $name): string
	{
		return Helpers::getSettings()['enums']['typeInternal'][$name] ?? '';
	}
}
