<?php

/**
 * File containing Moments events interface
 *
 * @package EightshiftForms\Integrations\Moments
 */

namespace EightshiftForms\Integrations\Moments;

/**
 * Interface for a MomentsEventsInterface
 */
interface MomentsEventsInterface
{
	/**
	 * Post event.
	 *
	 * @param array<string, mixed> $params Form fields params.
	 * @param string $emailKey Email key value.
	 * @param string $eventName Event name value.
	 * @param array<string> $map Map value.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	public function postEvent(
		array $params,
		string $emailKey,
		string $eventName,
		array $map,
		string $formId
	): array;
}
