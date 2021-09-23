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
	 * @param string $formId Form Id.
	 * @param array $params Get params.
	 * @param array $files Get files.
	 *
	 * @return array
	 */
	public function validate(string $formId, array $params = [], array $files = []): array;
}
