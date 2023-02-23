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
	 * Get validation pattern - output from pattern.
	 *
	 * @param string $pattern Pattern to serach.
	 *
	 * @return array<string, string>
	 */
	public function getValidationPatternOutput(string $pattern): array;
}
