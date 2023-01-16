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
	 * @param array<string, mixed> $validationReference Reference of form data to check by.
	 *
	 * @return array<string, mixed>
	 */
	public function validate(array $validationReference): array;
}
