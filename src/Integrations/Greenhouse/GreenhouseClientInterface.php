<?php

/**
 * File containing Greenhouse Connect interface
 *
 * @package EightshiftForms\Integrations
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
	 * @return array
	 */
	public function getJobs(): array;

	/**
	 * Return job with cache option for faster loading.
	 *
	 * @param string $jobId Job id to search by.
	 *
	 * @return array
	 */
	public function getJobQuestions(string $jobId): array;

	/**
	 * API request to post job application to Greenhouse.
	 *
	 * @param string $jobId Job id to search.
	 * @param array $params Params array.
	 * @param array $files Files array.
	 *
	 * @return array
	 */
	public function postGreenhouseApplication(string $jobId, array $params, array $files): array;
}
