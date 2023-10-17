<?php

/**
 * File containing Connect mapping interface.
 * Used on integrations that doesn't have form builder.
 *
 * @package EightshiftForms\Integrations
 */

namespace EightshiftForms\Integrations;

/**
 * Interface for a Client Mapping
 */
interface ClientMappingInterface
{
	/**
	 * API request to post application.
	 *
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, array<int, array<string, mixed>>> $files Files array.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(array $params, array $files, string $formId): array;

	/**
	 * Get test api.
	 *
	 * @return array<mixed>
	 */
	public function getTestApi(): array;
}
