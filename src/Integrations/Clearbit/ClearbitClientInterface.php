<?php

/**
 * File containing Clearbit specific interface.
 *
 * @package EightshiftForms\Integrations\Clearbit
 */

namespace EightshiftForms\Integrations\Clearbit;

/**
 * Interface for a Client
 */
interface ClearbitClientInterface
{
	/**
	 * Get mapped params.
	 *
	 * @return array<int, string>
	 */
	public function getParams(): array;

	/**
	 * API request to post application.
	 *
	 * @param string $email Email key to map in params.
	 * @param array<string, mixed> $mapData Params array.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	public function getApplication(string $email, array $mapData, string $formId): array;

	/**
	 * Set queue for Clearbit.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return bool
	 */
	public function setQueue(array $formDetails): bool;
}
