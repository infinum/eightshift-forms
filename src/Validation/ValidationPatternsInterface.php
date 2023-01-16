<?php

/**
 * The class for form ValidationPatternsInterface.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

/**
 * Class ValidationPatternsInterface
 */
interface ValidationPatternsInterface
{
	/**
	 * Prepare validation patterns for editor select output.
	 *
	 * @return array<int, array<string, string>>
	 */
	public function getValidationPatternsEditor(): array;

	/**
	 * Get validation pattern - pattern from name.
	 *
	 * @param string $name Name to serach.
	 *
	 * @return string
	 */
	public function getValidationPattern(string $name): string;

	/**
	 * Get validation pattern - output from pattern.
	 *
	 * @param string $pattern Pattern to serach.
	 *
	 * @return string
	 */
	public function getValidationPatternOutput(string $pattern): string;
}
