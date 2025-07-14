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
	 * @param array<string, mixed> $formDetails Form details.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(array $formDetails): array;

	/**
	 * Get test api.
	 *
	 * @return array<mixed>
	 */
	public function getTestApi(): array;
}
