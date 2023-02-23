<?php

/**
 * Interface that holds all labels.
 *
 * @package EightshiftLibs\Labels
 */

declare(strict_types=1);

namespace EightshiftForms\Labels;

/**
 * Labels Interface.
 */
interface LabelsInterface
{
	/**
	 * Get all labels
	 *
	 * @return array<string, string>
	 */
	public function getLabels(): array;

	/**
	 * Return one label by key
	 *
	 * @param string $key Label key.
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getLabel(string $key, string $formId = ''): string;

	/**
	 * Output all validation labels from cache for fater validation.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return array<string, string>
	 */
	public function getValidationLabelsOutput(string $formId = ''): array;
}
