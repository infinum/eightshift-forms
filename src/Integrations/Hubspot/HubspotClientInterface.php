<?php

/**
 * File containing Hubspot Connect interface
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

namespace EightshiftForms\Integrations\Hubspot;

/**
 * Interface for a HubspotClient
 */
interface HubspotClientInterface
{

	/**
	 * Return forms simple list from Hubspot.
	 *
	 * @return array<string, mixed>
	 */
	public function getForms(): array;

	/**
	 * Return form with cache option for faster loading.
	 *
	 * @param string $formId Form id to search by.
	 *
	 * @return array<string, mixed>
	 */
	public function getForm(string $formId): array;

	/**
	 * API request to post job application to Hubspot.
	 *
	 * @param string $jobId Job id to search.
	 * @param array<string, mixed>  $params Params array.
	 * @param array<string, mixed>  $files Files array.
	 *
	 * @return array<string, mixed>
	 */
	// public function postHubspotApplication(string $jobId, array $params, array $files): array;
}
