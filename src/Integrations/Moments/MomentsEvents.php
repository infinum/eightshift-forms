<?php

/**
 * Moments Events integration class.
 *
 * @package EightshiftForms\Integrations\Moments
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Moments;

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\HooksHelpers;

/**
 * MomentsEvents integration class.
 */
class MomentsEvents extends AbstractMoments implements MomentsEventsInterface
{
	/**
	 * Post event.
	 *
	 * @param array<string, mixed> $params Form fields params.
	 * @param string $emailKey Email key value.
	 * @param string $eventName Event name value.
	 * @param array<string> $map Map value.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	public function postEvent(
		array $params,
		string $emailKey,
		string $eventName,
		array $map,
		string $formId
	): array {
		$email = \rawurlencode(FormsHelper::getParamValue($emailKey, $params));

		$url = "{$this->getBaseUrl()}peopleevents/1/persons/{$email}/definitions/{$eventName}/events";

		$body = $this->prepareParams($params, $eventName, $map, $formId);

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsMoments::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			[],
			'',
			$formId,
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		// On success return output.
		if ($code >= Config::API_RESPONSE_CODE_SUCCESS && $code <= Config::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return ApiHelpers::getIntegrationSuccessInternalOutput($details);
		}

		// Output error.
		return ApiHelpers::getIntegrationErrorInternalOutput($details);
	}

	/**
	 * Prepare params
	 *
	 * @param array<string, mixed> $params Form fields params.
	 * @param string $eventName Event name value.
	 * @param array<string, mixed> $map Map value.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareParams(
		array $params,
		string $eventName,
		array $map,
		string $formId
	): array {
		// Prepare output.
		$output = [
			'definitionId' => $eventName,
			'personIdentifierType' => 'EMAIL',
			'properties' => [],
		];

		$properties = [];

		// Filter params.
		$filterName = HooksHelpers::getFilterName(['integrations', SettingsMoments::SETTINGS_TYPE_KEY, 'prePostEventParams']);
		if (\has_filter($filterName)) {
			$params = \apply_filters($filterName, $params, $eventName, $map, $formId) ?? [];
		}

		// Remove unnecessary params.
		$params = GeneralHelpers::removeUnnecessaryParamFields($params);

		// Map params.
		if ($map) {
			foreach ($map as $mapKey => $mapItem) {
				$foundItem = FormsHelper::getParamValue($mapItem, $params);

				if (!$foundItem) {
					continue;
				}

				$properties[$mapKey] = $foundItem;
			}
		}

		// Filter params.
		$filterName = HooksHelpers::getFilterName(['integrations', SettingsMoments::SETTINGS_TYPE_KEY, 'prePostEventParamsAfter']);
		if (\has_filter($filterName)) {
			$properties = \apply_filters($filterName, $properties, $params, $eventName, $formId) ?? [];
		}

		// Add custom properties.
		$output['properties'] = $properties;

		return $output;
	}
}
