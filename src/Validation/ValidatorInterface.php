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
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	public function validateParams(array $formDetails): array;

	/**
	 * Validate files from the validation reference.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<int|string, string>
	 */
	public function validateFiles(array $formDetails): array;

	/**
	 * Validate mandatory params or FormDetails.
	 *
	 * @param array<string, mixed> $params Params to validate or FormDetails.
	 * @param array<string, mixed> $mandatoryParams Mandatory params to validate.
	 *
	 * @return boolean
	 */
	public function validateMandatoryParams(array $params, array $mandatoryParams): bool;

	/**
	 * Get validation label from cache or db on multiple items.
	 *
	 * @param array<string, string> $items Array of items to get label.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, string>
	 */
	public function getValidationLabelItems(array $items, string $formId): array;

	/**
	 * Set validation submit once.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return bool
	 */
	public function setValidationSubmitOnce(string $formId): bool;

	/**
	 * Check if validation submit only logged in is active.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return bool
	 */
	public function validateSubmitOnlyLoggedIn(string $formId): bool;

	/**
	 * Check if user has already submitted the form.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return bool
	 */
	public function validateSubmitOnlyOnce(string $formId): bool;
}
