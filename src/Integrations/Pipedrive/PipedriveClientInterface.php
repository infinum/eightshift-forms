<?php

/**
 * File containing Pipedrive specific interface.
 *
 * @package EightshiftForms\Integrations\Pipedrive
 */

namespace EightshiftForms\Integrations\Pipedrive;

use EightshiftForms\Integrations\ClientMappingInterface;

/**
 * Interface for a Client
 */
interface PipedriveClientInterface extends ClientMappingInterface
{
	/**
	 * Return person fields.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getPersonFields(bool $hideUpdateTime = true): array;

	/**
	 * Return leads fields.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getLeadsFields(bool $hideUpdateTime = true): array;
}
