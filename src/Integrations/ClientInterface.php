<?php

/**
 * File containing Connect interface
 *
 * @package EightshiftForms\Integrations
 */

namespace EightshiftForms\Integrations;

/**
 * Interface for a Client
 */
interface ClientInterface
{
	/**
	 * Stored time constant name.
	 */
	public const TRANSIENT_STORED_TIME = 'transientStoredTime';

	/**
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determine if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array;

	/**
	 * Return item with cache option for faster loading.
	 *
	 * @param string $itemId Item ID to search by.
	 *
	 * @return array<string, mixed>
	 */
	public function getItem(string $itemId): array;

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
