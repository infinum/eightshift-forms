<?php

/**
 * The class for form validator.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

/**
 * Class Validator
 */
interface ValidatorInterface
{
	/**
	 * Validate form and return error if it is not valid.
	 *
	 * @param array $params Get params.
	 * @param array $files Get files.
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	public function validate(array $params = [], array $files = [], string $formId): array;
}
