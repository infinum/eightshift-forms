<?php

/**
 * Greenhouse Client integration class.
 *
 * @package EightshiftForms\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Greenhouse;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;

/**
 * GreenhouseClient integration class.
 */
class GreenhouseClient implements GreenhouseClientInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Transient cache name for simple jobs.
	 */
	public const CACHE_GREENHOUSE_JOBS_TRANSIENT_NAME = 'es_greenhouse_jobs_cache';

	/**
	 * Transient cache name for jobs questions.
	 */
	public const CACHE_GREENHOUSE_JOBS_QUESTIONS_TRANSIENT_NAME = 'es_greenhouse_jobs_questions_cache';

	/**
	 * Return jobs simple list from Greenhouse.
	 *
	 * @return array
	 */
	public function getJobs(): array
	{
		$output = get_transient(self::CACHE_GREENHOUSE_JOBS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output)) {
			$jobs = $this->getGreenhouseJobs();

			if ($jobs) {
				foreach ($jobs as $job) {
					$jobId = $job['id'] ?? '';

					if (!$jobId) {
						continue;
					}

					$output[$jobId] = [
						'id' => (string) $jobId,
						'title' => $job['title'] ?? '',
						'locations' => explode(', ', $job['location']['name']),
						'updatedAt' => $job['updated_at'],
					];
				}

				set_transient(self::CACHE_GREENHOUSE_JOBS_TRANSIENT_NAME, $output, 3600);
			}
		}

		return $output;
	}

	/**
	 * Return job with cache option for faster loading.
	 *
	 * @param string $jobId Job id to search by.
	 *
	 * @return array
	 */
	public function getJobQuestions(string $jobId): array
	{
		$output = get_transient(self::CACHE_GREENHOUSE_JOBS_QUESTIONS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$jobId]) || empty($output[$jobId])) {
			$job = $this->getGreenhouseJob($jobId);

			$questions = $job['questions'] ?? [];

			if ($jobId && $questions) {
				$output[$jobId] = $job['questions'] ?? [];

				set_transient(self::CACHE_GREENHOUSE_JOBS_QUESTIONS_TRANSIENT_NAME, $output, 3600);
			}
		}

		return $output[$jobId] ?? [];
	}

	/**
	 * API request to post job application to Greenhouse.
	 *
	 * @param string $jobId Job id to search.
	 * @param array $params Params array.
	 * @param array $files Files array.
	 *
	 * @return array
	 */
	public function postGreenhouseApplication(string $jobId, array $params, array $files): array
	{
		$response = \wp_remote_post(
			"{$this->getJobBoardUrl()}boards/{$this->getBoardToken()}/jobs/{$jobId}",
			[
				'headers' => $this->getHeaders(true),
				'body' => wp_json_encode(
					array_merge(
						$this->prepareParams($params),
						$this->prepareFiles($files)
					)
				),
				'data_format' => 'body',
			]
		);

		return json_decode(\wp_remote_retrieve_body($response), true) ?? [];
	}

	/**
	 * API request to get all jobs from Greenhouse.
	 *
	 * @return array
	 */
	private function getGreenhouseJobs()
	{
		$response = \wp_remote_get(
			"{$this->getJobBoardUrl()}boards/{$this->getBoardToken()}/jobs",
			[
				'headers' => $this->getHeaders(),
				'timeout' => 60,
			]
		);

		$body = json_decode(\wp_remote_retrieve_body($response), true);

		if (!isset($body['jobs'])) {
			return [];
		}

		return $body['jobs'];
	}

	/**
	 * API request to get one job by ID from Greenhouse.
	 *
	 * @param string $jobId Job id to search.
	 *
	 * @return array
	 */
	private function getGreenhouseJob(string $jobId)
	{
		$response = \wp_remote_get(
			"{$this->getJobBoardUrl()}boards/{$this->getBoardToken()}/jobs/{$jobId}?questions=true",
			[
				'headers' => $this->getHeaders(),
				'timeout' => 60,
			]
		);

		$body = json_decode(\wp_remote_retrieve_body($response), true);

		if (isset($body['error'])) {
			return [];
		}

		return $body;
	}

	/**
	 * Set headers used for fetching data.
	 *
	 * @param boolean $useAuth If using post method we need to send Authorization header in the request.
	 *
	 * @return array
	 */
	private function getHeaders(bool $useAuth = false): array
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
	 * Prepare params
	 *
	 * @param array $params Params.
	 *
	 * @return array
	 */
	private function prepareParams(array $params): array
	{
		$output = [];

		foreach ($params as $key => $value) {
			$value = json_decode($value, true)['value'];

			$output[$key] = $value;
		}

		return $output;
	}

	/**
	 * Prepare files.
	 *
	 * @param array $files Files.
	 *
	 * @return array
	 */
	private function prepareFiles(array $files): array
	{
		$output = [];

		foreach ($files as $key => $value) {
			$name = explode('-', $key);
			$fileName = explode('/', $value);

			$output["{$name[0]}_content"] = base64_encode((string) file_get_contents($value)); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$output["{$name[0]}_content_filename"] = end($fileName);
		}

		return $output;
	}

	/**
	 * Return Board Token from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getBoardToken(): string
	{
		$boardToken = Variables::getBoardTokenGreenhouse();

		return $boardToken ?? $this->getOptionValue(SettingsGreenhouse::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY);
	}

	/**
	 * Return Api Key from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		$apiKey = Variables::getApiKeyGreenhouse();

		return base64_encode($apiKey ?? $this->getOptionValue(SettingsGreenhouse::SETTINGS_GREENHOUSE_API_KEY_KEY)); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Return Job Board Url.
	 *
	 * @return string
	 */
	private function getJobBoardUrl(): string
	{
		return 'https://boards-api.greenhouse.io/v1/';
	}
}
