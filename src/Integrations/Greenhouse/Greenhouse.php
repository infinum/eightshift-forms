<?php

/**
 * Greenhouse integration class.
 *
 * @package EightshiftForms\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Greenhouse;

use EightshiftForms\Exception\MissingFilterInfoException;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Cache\Cache;

/**
 * Greenhouse integration class.
 */
class Greenhouse implements GreenhouseClientInterface
{

	/**
	 * Transient name for storing GH Jobs
	 *
	 * @var string
	 */
	public const CACHE_JOBS = 'ef_greenhouse_jobs';

	/**
	 * Transient LIFESPAN for storing GH Jobs. Default 6 hours like WP Rocket cache.
	 *
	 * @var string
	 */
	public const CACHE_JOBS_LIFESPAN = 21600;

	/**
	 * Cache object used for caching Mailchimp responses.
	 *
	 * @var Cache
	 */
	private $cache;

	/**
	 * Constructs object
	 *
	 * @param Cache $transientCache Transient cache object.
	 */
	public function __construct(Cache $transientCache)
	{
		$this->cache = $transientCache;
	}

	/**
	 * Return jobs with cache option for faster loading.
	 *
	 * @return array
	 */
	public function getJobs(): array
	{
		$output = $this->cache->get(static::CACHE_JOBS);

		if (!$output) {
			$output = (string) wp_json_encode($this->getJobsRaw());

			$this->cache->save(static::CACHE_JOBS, $output, static::CACHE_JOBS_LIFESPAN);
		}

		return json_decode($output, true);
	}

	/**
	 * Return job with cache option for faster loading.
	 *
	 * @param string $jobId Job id to search by.
	 *
	 * @return array
	 */
	public function getJob(string $jobId): array
	{
		$output = array_filter(
			$this->getJobs(),
			function ($job) use ($jobId) {
				if ($job['id'] === $jobId) {
					return $job;
				}
			}
		);

		return (array) array_values($output)[0];
	}

	/**
	 * API request to post job application to Greenhouse.
	 *
	 * @param string $jobId Job id to search.
	 * @param array $body Field and Files to send.
	 *
	 * @return array
	 */
	public function postGreenhouseApplication(string $jobId, array $body): array
	{
		$this->verifyGreenhouseInfoExists();

		$response = \wp_remote_post(
			"{$this->getJobBoardUrl()}boards/{$this->getBoardToken()}/jobs/{$jobId}",
			[
				'headers' => $this->getHeaders(true),
				'body' => wp_json_encode($body),
				'data_format' => 'body',
			]
		);

		return json_decode(\wp_remote_retrieve_body($response), true) ?? [];
	}

	/**
	 * Get confirmation email name key from filter data.
	 *
	 * @return string
	 */
	public function getConfirmationName(): string
	{
		return \has_filter(Filters::GREENHOUSE_CONFIRMATION) ? \apply_filters(Filters::GREENHOUSE_CONFIRMATION, 'name') : '';
	}

	/**
	 * Get confirmation email, email key from filter data.
	 *
	 * @return string
	 */
	public function getConfirmationEmail(): string
	{
		return \has_filter(Filters::GREENHOUSE_CONFIRMATION) ? \apply_filters(Filters::GREENHOUSE_CONFIRMATION, 'email') : '';
	}

	/**
	 * Get confirmation email subject key from filter data.
	 *
	 * @return string
	 */
	public function getConfirmationSubject(): string
	{
		return \has_filter(Filters::GREENHOUSE_CONFIRMATION) ? \apply_filters(Filters::GREENHOUSE_CONFIRMATION, 'subject') : '';
	}

	/**
	 * Get Fallback email, email key from filter data.
	 *
	 * @return string
	 */
	public function getFallbackEmail(): string
	{
		return \has_filter(Filters::GREENHOUSE_FALLBACK) ? \apply_filters(Filters::GREENHOUSE_FALLBACK, 'email') : '';
	}

	/**
	 * Get Fallback email subject key from filter data.
	 *
	 * @return string
	 */
	public function getFallbackSubject(): string
	{
		return \has_filter(Filters::GREENHOUSE_FALLBACK) ? \apply_filters(Filters::GREENHOUSE_FALLBACK, 'subject') : '';
	}

	/**
	 * Get Board token from filter data.
	 *
	 * @return string
	 */
	public function getBoardToken(): string
	{
		return \has_filter(Filters::GREENHOUSE) ? \apply_filters(Filters::GREENHOUSE, 'apiBoardToken') : '';
	}

	/**
	 * Get url for job boards from filter data.
	 *
	 * @return string
	 */
	public function getJobBoardUrl(): string
	{
		return \has_filter(Filters::GREENHOUSE) ? \apply_filters(Filters::GREENHOUSE, 'jobBoardUrl') : '';
	}

	/**
	 * Get api key from filter data.
	 *
	 * @return string
	 */
	public function getApiKey(): string
	{
		return base64_encode(\has_filter(Filters::GREENHOUSE) ? \apply_filters(Filters::GREENHOUSE, 'apiKey') : ''); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Set headers used for fetching data.
	 *
	 * @param boolean $useAuth If using post method we need to send Authorization header in the request.
	 *
	 * @return array
	 */
	protected function getHeaders(bool $useAuth = false): array
	{
		$headers = [
			'Content-Type' => 'application/json; charset=utf-8',
		];

		if ($useAuth) {
			$headers['Authorization'] = "Basic {$this->getApiKey()}";
		}

		return $headers;
	}

	/**
	 * API request to get all jobs from Greenhouse.
	 *
	 * @return array
	 */
	protected function getGreenhouseJobs()
	{
		$this->verifyGreenhouseInfoExists();

		$response = \wp_remote_get(
			"{$this->getJobBoardUrl()}boards/{$this->getBoardToken()}/jobs",
			[
				'headers' => $this->getHeaders(),
			]
		);

		return json_decode(\wp_remote_retrieve_body($response), true)['jobs'] ?? [];
	}

	/**
	 * API request to get one job by ID from Greenhouse.
	 *
	 * @param string $jobId Job id to search.
	 *
	 * @return array
	 */
	protected function getGreenhouseJob(string $jobId)
	{
		$this->verifyGreenhouseInfoExists();

		$response = \wp_remote_get(
			"{$this->getJobBoardUrl()}boards/{$this->getBoardToken()}/jobs/{$jobId}?questions=true",
			[
				'headers' => $this->getHeaders(),
			]
		);

		return json_decode(\wp_remote_retrieve_body($response), true);
	}

	/**
	 * Get Greenhouse full job data set and do project specific transformations.
	 *
	 * @return array
	 */
	protected function getJobsRaw(): array
	{
		$output = array_map(
			function ($job) {
				$jobId = $job['id'];

				$job = $this->getGreenhouseJob((string) $jobId);

				if (!$job) {
					return [];
				}

				$title = $job['title'] ?? '';
				$locations = $job['location']['name'] ? explode(', ', $job['location']['name']) : [];
				$questions = $job['questions'] ?? [];

				return [
					'id' => (string) $jobId,
					'title' => $title,
					'locations' => $locations,
					'questions' => $this->getJobQuestions($questions),
				];
			},
			$this->getGreenhouseJobs()
		);

		return empty($output[0]) ? [] : $output;
	}

	/**
	 * Transform default Greenhouse Jobs response to project specific questions.
	 *
	 * @param array $questions Array of questions.
	 *
	 * @return array
	 */
	protected function getJobQuestions(array $questions): array
	{
		$output = [];

		if (!$questions) {
			return $output;
		}

		foreach ($questions as $question) {
			$fields = $question['fields'] ?? '';
			$label = $question['label'] ?? '';
			$required = $question['required'] ?? false;

			if (empty($fields)) {
				continue;
			}

			foreach ($fields as $field) {
				$type = $field['type'] ? str_replace('_', '-', $field['type']) : '';
				$name = $field['name'];
				$description = $question['description'] ?? '';
				$values = $field['values'];
				$width = '12';

				// Define item that are going to be two per row.
				$narrowItems = array_flip([
					'first_name',
					'last_name',
					'email',
					'phone',
				]);

				if (isset($narrowItems[$name])) {
					$width = '6';
				}

				// In GH select and check box is the same, addes some conditions to fine tune output.
				switch ($type) {
					case 'multi-value-single-select':
						$type = 'select';

						// Full specific but this is a checbox.
						if ($field['values'][0]['value'] === 0 && $field['values'][1]['value'] === 1) {
							$type = 'checkbox';
						}
						break;
					case 'input-file':
						$type = 'file';
						break;
					case 'input-text':
						$type = 'input';
						break;
				}

				$output[] = [
					'label' => $label,
					'required' => $required,
					'name' => $name,
					'id' => $name,
					'description' => strtolower(str_replace('-', '_', \wp_strip_all_tags($description))),
					'type' => $type,
					'options'  => $values,
					'width'  => $width,
				];
			}
		}

		return $output;
	}

	/**
	 * Make sure we have the data we need defined as filters.
	 *
	 * @throws MissingFilterInfoException When not all required keys are set.
	 *
	 * @return void
	 */
	private function verifyGreenhouseInfoExists(): void
	{
		if (! has_filter(Filters::GREENHOUSE)) {
			throw MissingFilterInfoException::viewException(Filters::GREENHOUSE, esc_html__('entire_filter', 'eightshift-forms'));
		}

		if (empty(\apply_filters(Filters::GREENHOUSE, 'jobBoardUrl'))) {
			throw MissingFilterInfoException::viewException(Filters::GREENHOUSE, 'jobBoardUrl');
		}

		if (empty(\apply_filters(Filters::GREENHOUSE, 'apiKey'))) {
			throw MissingFilterInfoException::viewException(Filters::GREENHOUSE, 'apiKey');
		}

		if (empty(\apply_filters(Filters::GREENHOUSE, 'apiBoardToken'))) {
			throw MissingFilterInfoException::viewException(Filters::GREENHOUSE, 'apiBoardToken');
		}

		// Returning value can't be mocked via tests.
		if (defined('IS_TEST') && ! IS_TEST) {
			if (\apply_filters(Filters::GREENHOUSE, 'jobBoardUrl' === 'jobBoardUrl')) {
				throw MissingFilterInfoException::viewException(Filters::GREENHOUSE, 'jobBoardUrl');
			}

			if (\apply_filters(Filters::GREENHOUSE, 'apiKey' === 'apiKey')) {
				throw MissingFilterInfoException::viewException(Filters::GREENHOUSE, 'apiKey');
			}

			if (\apply_filters(Filters::GREENHOUSE, 'apiBoardToken' === 'apiBoardToken')) {
				throw MissingFilterInfoException::viewException(Filters::GREENHOUSE, 'apiBoardToken');
			}
		}
	}
}
