<?php

/**
 * File containing Nationbuilder specific interface.
 *
 * @package EightshiftForms\Integrations\Nationbuilder
 */

namespace EightshiftForms\Integrations\Nationbuilder;

use EightshiftForms\Integrations\ClientMappingInterface;

/**
 * Interface for a Client
 */
interface NationbuilderClientInterface extends ClientMappingInterface
{
	/**
	 * Return custom fields.
	 *
	 * @param bool $hideUpdateTime Determine if update time will be in the output or not.
	 *
	 * @return array<mixed>
	 */
	public function getCustomFields(bool $hideUpdateTime = true): array;

	/**
	 * Return lists.
	 *
	 * @param bool $hideUpdateTime Determine if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getLists(bool $hideUpdateTime = true): array;

	/**
	 * Return Tags.
	 *
	 * @param bool $hideUpdateTime Determine if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getTags(bool $hideUpdateTime = true): array;

	/**
	 * API request to post list.
	 *
	 * @param string $listId List id.
	 * @param string $signupId Signup id.
	 *
	 * @return array<string, mixed>
	 */
	public function postList(string $listId, string $signupId): array;

	/**
	 * API request to post tag.
	 *
	 * @param string $tagId Tag id.
	 * @param string $signupId Signup id.
	 *
	 * @return array<string, mixed>
	 */
	public function postTag(string $tagId, string $signupId): array;
}
