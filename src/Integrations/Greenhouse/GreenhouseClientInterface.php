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
	 * Return jobs with cache option for faster loading.
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
	public function getJob(string $jobId): array;

	/**
	 * API request to post job application to Greenhouse.
	 *
	 * @param string $jobId Job id to search.
	 * @param array $body Field and Files to send.
	 *
	 * @return array
	 */
	public function postGreenhouseApplication(string $jobId, array $body): array;

	/**
	 * Get confirmation email name key from filter data.
	 *
	 * @return string
	 */
	public function getConfirmationName(): string;

	/**
	 * Get confirmation email, email key from filter data.
	 *
	 * @return string
	 */
	public function getConfirmationEmail(): string;

	/**
	 * Get confirmation email subject key from filter data.
	 *
	 * @return string
	 */
	public function getConfirmationSubject(): string;


	/**
	 * Get Fallback email, email key from filter data.
	 *
	 * @return string
	 */
	public function getFallbackEmail(): string;


	/**
	 * Get Fallback email subject key from filter data.
	 *
	 * @return string
	 */
	public function getFallbackSubject(): string;
}
