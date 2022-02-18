<?php

/**
 * File containing Connect interface
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

namespace EightshiftForms\Integrations\Mailchimp;

/**
 * Interface for a Client
 */
interface MailchimpClientInterface
{
	/**
	 * Return items.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(): array;

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
	 * @param string $itemId Item id to search.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, array<int, array<string, mixed>>> $files Files array.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(string $itemId, array $params, array $files): array;

	/**
	 * Return Mailchimp tags for a list.
	 *
	 * @param string $itemId Item id to search.
	 *
	 * @return array<int, mixed>
	 */
	public function getTags(string $itemId): array;
}
