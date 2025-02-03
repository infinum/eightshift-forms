<?php

/**
 * File containing Notionbuilder specific interface.
 *
 * @package EightshiftForms\Integrations\Notionbuilder
 */

namespace EightshiftForms\Integrations\Notionbuilder;

use EightshiftForms\Integrations\ClientMappingInterface;

/**
 * Interface for a Client
 */
interface NotionbuilderClientInterface extends ClientMappingInterface
{
	/**
	 * Return custom fields.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<mixed>
	 */
	public function getCustomFields(bool $hideUpdateTime = true): array;

	/**
	 * Return lists.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getLists(bool $hideUpdateTime = true): array;

	/**
	 * API request to post list.
	 *
	 * @param string $id List id.
	 * @param string $signupId Signup id.
	 *
	 * @return array<string, mixed>
	 */
	public function postList(string $id, string $signupId): array;
}
