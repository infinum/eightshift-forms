<?php

/**
 * Clearbit Client integration class.
 *
 * @package EightshiftForms\Integrations\Clearbit
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Clearbit;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\ObjectHelperTrait;

/**
 * ClearbitClient integration class.
 */
class ClearbitClient implements ClientInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Helper trait.
	 */
	use ObjectHelperTrait;

	/**
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = [];

		foreach ($this->prepareParams() as $key => $value) {
			$output[] = $key;
		}

		return $output;
	}

	/**
	 * Return item with cache option for faster loading.
	 *
	 * @param string $itemId Item ID to search by.
	 *
	 * @return array<string, mixed>
	 */
	public function getItem(string $itemId): array
	{
		return [];
	}

	/**
	 * API request to post application.
	 *
	 * @param string $itemId Item id to search.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, array<int, array<string, mixed>>> $files Files array.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(string $itemId, array $params, array $files, string $formId): array
	{

		$email = isset($params[$itemId]) ? $params[$itemId]['value'] : '';

		$response = \wp_remote_get(
			"{$this->getBaseUrl()}combined/find?email={$email}",
			[
				'headers' => $this->getHeaders(true),
			]
		);

		if (is_wp_error($response)) {
			return [
				'status' => 'error',
				'code' => 400,
				'message' => $this->getErrorMsg('submitWpError'),
			];
		}

		$code = $response['response']['code'] ?? 200;

		$responseBody = json_decode(\wp_remote_retrieve_body($response), true);

		if ($code === 200) {
			return [
				'status' => 'success',
				'code' => $code,
				'message' => 'clearbitSuccess',
				'data' => $this->prepareParams($responseBody),
			];
		}

		$responseMessage = $responseBody['error']['type'] ?? '';

		$output = [
			'status' => 'error',
			'code' => $code,
			'message' => $this->getErrorMsg($responseMessage),
		];

		Helper::logger([
			'integration' => 'clearbit',
			'email' => $email,
			'response' => $response['response'],
			'responseBody' => $responseBody,
			'output' => $output,
		]);

		return $output;
	}

	/**
	 * Prepare params
	 *
	 * @param array<string, mixed> $params Params.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function prepareParams(array $fields = []): array
	{
		$person = $fields['person'] ?? [];
		$company = $fields['company'] ?? [];

		return [
			// person.
			'person-full-name' => $person['name']['fullName'] ?? '',
			'person-first-name' => $person['name']['givenName'] ?? '',
			'person-last-name' => $person['name']['familyName'] ?? '',
			'person-email' => $person['email'] ?? '',
			'person-location' => $person['location'] ?? '',
			'person-time-zone' => $person['timeZone'] ?? '',
			'person-utc-offset' => $person['utcOffset'] ?? '',
			'person-city' => $person['geo']['city'] ?? '',
			'person-state' => $person['geo']['state'] ?? '',
			'person-state-code' => $person['geo']['stateCode'] ?? '',
			'person-country' => $person['geo']['country'] ?? '',
			'person-country-code' => $person['geo']['countryCode'] ?? '',
			'person-lat' => $person['geo']['lat'] ?? '',
			'person-lng' => $person['geo']['lng'] ?? '',
			'person-bio' => $person['bio'] ?? '',
			'person-site' => $person['site'] ?? '',
			'person-avatar' => $person['avatar'] ?? '',
			'person-employment-domain' => $person['employment']['domain'] ?? '',
			'person-employment-name' => $person['employment']['name'] ?? '',
			'person-employment-title' => $person['employment']['title'] ?? '',
			'person-employment-role' => $person['employment']['role'] ?? '',
			'person-employment-sub-role' => $person['employment']['subRole'] ?? '',
			'person-employment-seniority' => $person['employment']['seniority'] ?? '',
			'person-facebook' => $person['facebook']['handle'] ?? '',
			'person-github' => $person['github']['handle'] ?? '',
			'person-github-id' => $person['github']['id'] ?? '',
			'person-github-avatar' => $person['github']['avatar'] ?? '',
			'person-github-company' => $person['github']['company'] ?? '',
			'person-github-blog' => $person['github']['blog'] ?? '',
			'person-github-followers' => $person['github']['followers'] ?? '',
			'person-github-following' => $person['github']['following'] ?? '',
			'person-twitter' => $person['twitter']['handle'] ?? '',
			'person-twitter-id' => $person['twitter']['id'] ?? '',
			'person-twitter-bio' => $person['twitter']['bio'] ?? '',
			'person-twitter-followers' => $person['twitter']['followers'] ?? '',
			'person-twitter-following' => $person['twitter']['following'] ?? '',
			'person-twitter-statuses' => $person['twitter']['statuses'] ?? '',
			'person-twitter-favorites' => $person['twitter']['favorites'] ?? '',
			'person-twitter-location' => $person['twitter']['location'] ?? '',
			'person-twitter-site' => $person['twitter']['site'] ?? '',
			'person-twitter-avatar' => $person['twitter']['avatar'] ?? '',
			'person-linkedin' => $person['linkedin']['handle'] ?? '',
			'person-googleplus' => $person['googleplus']['handle'] ?? '',

			// company
			'company-name' => $company['name'] ?? '',
			'company-legal-name' => $company['legalName'] ?? '',
			'company-domain' => $company['domain'] ?? '',
			'company-sector' => $company['category']['sector'] ?? '',
			'company-industry-group' => $company['category']['industryGroup'] ?? '',
			'company-industry' => $company['category']['industry'] ?? '',
			'company-sub-industry' => $company['category']['subIndustry'] ?? '',
			'company-description' => $company['description'] ?? '',
			'company-founded-year' => $company['foundedYear'] ?? '',
			'company-location' => $company['location'] ?? '',
			'company-time-zone' => $company['timeZone'] ?? '',
			'company-utc-offset' => $company['utcOffset'] ?? '',
			'company-street-number' => $company['geo']['streetNumber'] ?? '',
			'company-street-name' => $company['geo']['streetName'] ?? '',
			'company-sub-premise' => $company['geo']['subPremise'] ?? '',
			'company-street-address' => $company['geo']['streetAddress'] ?? '',
			'company-city' => $company['geo']['city'] ?? '',
			'company-postal-code' => $company['geo']['postalCode'] ?? '',
			'company-state' => $company['geo']['state'] ?? '',
			'company-state-code' => $company['geo']['stateCode'] ?? '',
			'company-country' => $company['geo']['country'] ?? '',
			'company-country-code' => $company['geo']['countryCode'] ?? '',
			'company-lat' => $company['geo']['lat'] ?? '',
			'company-lng' => $company['geo']['lng'] ?? '',
			'company-logo' => $company['logo'] ?? '',
			'company-facebook' => $company['facebook']['handle'] ?? '',
			'company-facebook-likes' => $company['facebook']['likes'] ?? '',
			'company-linkedin' => $company['linkedin']['handle'] ?? '',
			'company-twitter' => $company['twitter']['handle'] ?? '',
			'company-twitter-id' => $company['twitter']['id'] ?? '',
			'company-twitter-bio' => $company['twitter']['bio'] ?? '',
			'company-twitter-followers' => $company['twitter']['followers'] ?? '',
			'company-twitter-following' => $company['twitter']['following'] ?? '',
			'company-twitter-location' => $company['twitter']['location'] ?? '',
			'company-twitter-site' => $company['twitter']['site'] ?? '',
			'company-twitter-avatar' => $company['twitter']['avatar'] ?? '',
			'company-crunchbase' => $company['crunchbase']['handle'] ?? '',
			'company-type' => $company['type'] ?? '',
			'company-ticker' => $company['ticker'] ?? '',
			'company-phone' => $company['phone'] ?? '',
			'company-alexa-us-rank' => $company['metrics']['alexaUsRank'] ?? '',
			'company-alexa-global-rank' => $company['metrics']['alexaGlobalRank'] ?? '',
			'company-employees' => $company['metrics']['employees'] ?? '',
			'company-employees-range' => $company['metrics']['employeesRange'] ?? '',
			'company-market-cap' => $company['metrics']['marketCap'] ?? '',
			'company-raised' => $company['metrics']['raised'] ?? '',
			'company-annual-revenue' => $company['metrics']['annualRevenue'] ?? '',
			'company-estimated-annual-revenue' => $company['metrics']['estimatedAnnualRevenue'] ?? '',
			'company-fiscal-year-end' => $company['metrics']['fiscalYearEnd'] ?? '',
		];
	}

	/**
	 * Map service messages with our own.
	 *
	 * @param string $msg Message got from the API.
	 * @param array<string, mixed> $errors Additional errors got from the API.
	 *
	 * @return string
	 */
	private function getErrorMsg(string $msg): string
	{
		switch ($msg) {
			case 'auth_required':
				return 'clearbitAuthRequired';
			default:
				return 'submitWpError';
		}
	}

	/**
	 * Set headers used for fetching data.
	 *
	 * @return array<string, mixed>
	 */
	private function getHeaders(): array
	{
		return [
			'Content-Type' => 'application/json; charset=utf-8',
			'Authorization' => "Bearer {$this->getApiKey()}",
		];
	}

	/**
	 * Return Api Key from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		$apiKey = Variables::getApiKeyClearbit();

		return $apiKey ? $apiKey : $this->getOptionValue(SettingsClearbit::SETTINGS_CLEARBIT_API_KEY_KEY); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Return Clearbit base url.
	 *
	 * @return string
	 */
	private function getBaseUrl(): string
	{
		return 'https://person-stream.clearbit.com/v2/';
	}
}
