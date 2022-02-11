<?php

/**
 * File missing data in filter exception
 *
 * @package EightshiftForms\Exception
 */

declare(strict_types=1);

namespace EightshiftForms\Exception;

use EightshiftFormsVendor\EightshiftLibs\Exception\GeneralExceptionInterface;

/**
 * Class MissingFilterInfoException.
 */
final class MissingFilterInfoException extends \InvalidArgumentException implements GeneralExceptionInterface
{
	/**
	 * Throw error if there is something wrong with filters.
	 *
	 * @param string $filter Filter name.
	 * @param string $type Filter internal type.
	 * @param string $name Filter internal name.
	 *
	 * @return static
	 */
	public static function viewException($filter, $type, $name): MissingFilterInfoException
	{
		return new MissingFilterInfoException(
			sprintf(
				/* translators: %1$d is replaced with filter name, %2$d is replaced with filter type, , %3$d is replaced with name. */
				\esc_html__('Filter for %1$s is missing or typed wrong. Provided type: %2$s, provided name: %3$s. Please check your name and try again.', 'eightshift-forms'),
				$filter,
				$type,
				$name
			)
		);
	}
}
