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
	 * Validate params.
	 *
	 * @param array<string, mixed> $data Date to check from reference helper.
	 *
	 * @return array<string, mixed>
	 */
	public function validateParams(array $data): array;

	/**
	 * Validate files from the validation reference.
	 *
	 * @param array<string, mixed> $data Date to check from reference helper.
	 *
	 * @return array<int|string, string>
	 */
	public function validateFiles(array $data): array;

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
