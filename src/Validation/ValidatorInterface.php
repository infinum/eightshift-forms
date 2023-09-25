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
	 * @param bool $strictValidation Is validation is strict.
	 *
	 * @return array<string, mixed>
	 */
	public function validateParams(array $data, bool $strictValidation = true): array;

	/**
	 * Validate files from the validation reference.
	 *
	 * @param array<string, mixed> $data Date to check from reference helper.
	 *
	 * @return array<int|string, string>
	 */
	public function validateFiles(array $data): array;

	/**
	 * Validate all manadatory fields that are passed from the `getFormDataReference` function.
	 * If these fields are missing it can be that the form is not configured correctly or it could be a unauthorized request.
	 *
	 * @param array<string, mixed> $data Date to check from reference helper.
	 *
	 * @return boolean
	 */
	public function validateFormManadatoryProperies(array $data): bool;

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
