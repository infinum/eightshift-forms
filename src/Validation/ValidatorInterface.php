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

	/**
	 * Get validation label from cache or db on multiple items.
	 *
	 * @param array<string, string> $items Array of items to get label.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, string>
	 */
	public function getValidationLabelItems(array $items, string $formId): array;
}
