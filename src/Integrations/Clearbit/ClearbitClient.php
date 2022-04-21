<?php

/**
 * Clearbit Client integration class.
 *
 * @package EightshiftForms\Integrations\Clearbit
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Clearbit;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\ObjectHelperTrait;

/**
 * ClearbitClient integration class.
 */
class ClearbitClient implements ClearbitClientInterface
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
	 * Return Clearbit base url.
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://person-stream.clearbit.com/v2/';

	/**
	 * API request to post application.
	 *
	 * @param string $emailKey Email key to map in params.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, string> $mapData Map data from settings.
	 *
	 * @return array<string, mixed>
	 */
	public function getApplication(string $emailKey, array $params, array $mapData): array
	{
		$email = isset($params[$emailKey]['value']) ? $params[$emailKey]['value'] : '';

		if (!$email) {
			$output = [
				'status' => 'error',
				'code' => 400,
				'message' => 'clearbitMissingEmail',
			];

			Helper::logger([
				'integration' => 'clearbit',
				'email' => $email,
				'mapKeys' => $mapData,
				'output' => $output,
			]);

			return $output;
		}

		if (!$mapData) {
			$output = [
				'status' => 'error',
				'code' => 400,
				'message' => 'clearbitMissingMapKeys',
			];

			Helper::logger([
				'integration' => 'clearbit',
				'email' => $email,
				'mapKeys' => $mapData,
				'output' => $output,
			]);

			return $output;
		}

		$response = \wp_remote_get(
			self::BASE_URL . "combined/find?email={$email}",
			[
				'headers' => $this->getHeaders(),
			]
		);

		if (\is_wp_error($response)) {
			return [
				'status' => 'error',
				'code' => 400,
				'message' => $this->getErrorMsg('submitWpError'),
			];
		}

		$code = $response['response']['code'] ?? 200;

		$responseBody = \json_decode(\wp_remote_retrieve_body($response), true);

		if ($code === 200) {
			$dataOutput = [];

			foreach ($this->prepareParams($responseBody) as $key => $value) {
				if (\array_key_exists($key, $mapData) && !empty($value) && !empty($mapData[$key])) {
					$dataOutput[$mapData[$key]] = $value;
				}
			}

			return [
				'status' => 'success',
				'code' => $code,
				'message' => 'clearbitSuccess',
				'email' => $email,
				'data' => $dataOutput,
			];
		}

		$responseMessage = $responseBody['error']['type'] ?? '';

		$output = [
			'status' => 'error',
			'code' => $code,
			'email' => $email,
			'mapKeys' => $mapData,
			'message' => $this->getErrorMsg($responseMessage),
		];

		Helper::logger([
			'integration' => 'clearbit',
			'email' => $email,
			'mapKeys' => $mapData,
			'response' => $response['response'],
			'responseBody' => $responseBody,
			'output' => $output,
		]);

		return $output;
	}

	/**
	 * Get mapped params.
	 *
	 * @return array<int, string>
	 */
	public function getParams(): array
	{
		$output = [];

		foreach ($this->prepareParams() as $key => $value) {
			$output[] = $key;
		}

		return $output;
	}

	/**
	 * Prepare params
	 *
	 * @param array<string, mixed> $params Params.
	 *
	 * @return array<string, string>
	 */
	private function prepareParams(array $params = []): array
	{
		$person = $params['person'] ?? [];
		$company = $params['company'] ?? [];

		$output = [
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
			'person-facebook-profile' => isset($person['facebook']['handle']) ? "https://facebook.com/" . $person['facebook']['handle']  : '',
			'person-github' => $person['github']['handle'] ?? '',
			'person-github-profile' => isset($person['github']['handle']) ? "https://github.com/" . $person['github']['handle']  : '',
			'person-github-id' => $person['github']['id'] ?? '',
			'person-github-avatar' => $person['github']['avatar'] ?? '',
			'person-github-company' => $person['github']['company'] ?? '',
			'person-github-blog' => $person['github']['blog'] ?? '',
			'person-github-followers' => $person['github']['followers'] ?? '',
			'person-github-following' => $person['github']['following'] ?? '',
			'person-twitter' => $person['twitter']['handle'] ?? '',
			'person-twitter-profile' => isset($person['twitter']['handle']) ? "https://twitter.com/" . $person['twitter']['handle']  : '',
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
			'person-linkedin-profile' => isset($person['linkedin']['handle']) ? "https://linkedin.com/in/" . $person['linkedin']['handle']  : '',
			'person-email-provider' => $person['emailProvider'] ?? '',

			// company.
			'company-name' => $company['name'] ?? '',
			'company-legal-name' => $company['legalName'] ?? '',
			'company-domain' => $company['domain'] ?? '',
			'company-domain-aliases' => isset($company['domainAliases']) ? \implode(', ', $company['domainAliases']) : '',
			'company-site-phone-numbers' => isset($company['site']['phoneNumbers']) ? \implode(', ', $company['site']['phoneNumbers']) : '',
			'company-site-email-addresses' => isset($company['site']['emailAddresses']) ? \implode(', ', $company['site']['emailAddresses']) : '',
			'company-sector' => $company['category']['sector'] ?? '',
			'company-industry-group' => $company['category']['industryGroup'] ?? '',
			'company-industry' => $company['category']['industry'] ?? '',
			'company-sub-industry' => $company['category']['subIndustry'] ?? '',
			'company-tags' => isset($company['tags']) ? \implode(', ', $company['tags']) : '',
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
			'company-facebook-profile' => isset($company['facebook']['handle']) ? "https://facebook.com/" . $company['facebook']['handle']  : '',
			'company-facebook-likes' => $company['facebook']['likes'] ?? '',
			'company-linkedin' => $company['linkedin']['handle'] ?? '',
			'company-linkedin-profile' => isset($company['linkedin']['handle']) ? "https://linkedin.com/in/" . $company['linkedin']['handle']  : '',
			'company-twitter' => $company['twitter']['handle'] ?? '',
			'company-twitter-profile' => isset($company['twitter']['handle']) ? "https://twitter.com/" . $company['twitter']['handle']  : '',
			'company-twitter-id' => $company['twitter']['id'] ?? '',
			'company-twitter-bio' => $company['twitter']['bio'] ?? '',
			'company-twitter-followers' => $company['twitter']['followers'] ?? '',
			'company-twitter-following' => $company['twitter']['following'] ?? '',
			'company-twitter-location' => $company['twitter']['location'] ?? '',
			'company-twitter-site' => $company['twitter']['site'] ?? '',
			'company-twitter-avatar' => $company['twitter']['avatar'] ?? '',
			'company-crunchbase' => $company['crunchbase']['handle'] ?? '',
			'company-email-provider' => $company['emailProvider'] ?? '',
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
			'company-tech' => isset($company['tech']) ? \implode(', ', $company['tech']) : '',
			'company-tech-categories' => isset($company['techCategories']) ? \implode(', ', $company['techCategories']) : '',
			'company-parent-domain' => $company['parent']['domain'] ?? '',
			'company-ultimate-parent-domain' => $company['ultimateParent']['domain'] ?? '',
		];

		$filterName = Filters::getIntegrationFilterName(SettingsClearbit::SETTINGS_TYPE_KEY, 'map');
		if (\has_filter($filterName)) {
			return \apply_filters($filterName, $output) ?? [];
		}

		return $output;
	}

	/**
	 * Map service messages with our own.
	 *
	 * @param string $msg Message got from the API.
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
	 * Return Api Key from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		$apiKey = Variables::getApiKeyClearbit();

		return $apiKey ? $apiKey : $this->getOptionValue(SettingsClearbit::SETTINGS_CLEARBIT_API_KEY_KEY);
	}
}
