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
	 * @param array<string, mixed> $params Get params.
	 * @param array<string, mixed> $files Get files.
	 * @param string $formId Form Id.
	 *
	 * @return array<string, mixed>
	 */
	public function validate(array $params = [], array $files = [], string $formId = ''): array;

	/**
	 * Prepare validation patterns
	 *
	 * @return array<int, array<string, string>>
	 */
	public function getValidationPatterns(): array;

	/**
	 * Get validation pattern - pattern from name.
	 *
	 * @param string $name Name to serach.
	 *
	 * @return string
	 */
	public function getValidationPattern(string $name): string;

	/**
	 * Get validation pattern - name from pattern.
	 *
	 * @param string $pattern Pattern to serach.
	 *
	 * @return string
	 */
	public function getValidationPatternName(string $pattern): string;
}
