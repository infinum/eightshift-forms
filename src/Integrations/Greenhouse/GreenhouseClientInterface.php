<?php

/**
 * File containing Greenhouse Connect interface
 *
 * @package EightshiftForms\Integrations\Greenhouse
 */

namespace EightshiftForms\Integrations\Greenhouse;

/**
 * Interface for a GreenhouseClient
 */
interface GreenhouseClientInterface
{

	/**
	 * Return jobs simple list from Greenhouse.
	 *
	 * @return array<string, mixed>
	 */
	public function getJobs(): array;

	/**
	 * Return job with cache option for faster loading.
	 *
	 * @param string $jobId Job id to search by.
	 *
	 * @return array<string, mixed>
	 */
	public function getJobQuestions(string $jobId): array;

	/**
	 * API request to post job application to Greenhouse.
	 *
	 * @param string $jobId Job id to search.
	 * @param array<string, mixed>  $params Params array.
	 * @param array<string, mixed>  $files Files array.
	 *
	 * @return array<string, mixed>
	 */
	public function postGreenhouseApplication(string $jobId, array $params, array $files): array;
}
