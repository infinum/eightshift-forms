<?php

/**
 * File containing Mailchimp Connect interface
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

namespace EightshiftForms\Integrations\Mailchimp;

/**
 * Interface for a MailchimpClient
 */
interface MailchimpClientInterface
{
	/**
	 * Get Mailchimp lists with cache.
	 *
	 * @return array
	 */
	public function getLists(): array;

	/**
	 * Return list fields with cache option for faster loading.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	public function getListFields(string $formId): array;

	/**
	 * API request to post mailchimp subscription to Mailchimp.
	 *
	 * @param string $listId List id.
	 * @param array $params Params array.
	 *
	 * @return array
	 */
	public function postMailchimpSubscription(string $listId, array $params): array;
}
