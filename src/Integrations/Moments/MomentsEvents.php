<?php

/**
 * Moments Events integration class.
 *
 * @package EightshiftForms\Integrations\Moments
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Moments;

use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;

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
		$email = \rawurlencode(UtilsGeneralHelper::getFieldDetailsByName($params, $emailKey)['value']);

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
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsMoments::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			[],
			'',
			$formId,
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		UtilsDeveloperHelper::setQmLogsOutput($details);

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return UtilsApiHelper::getIntegrationSuccessInternalOutput($details);
		}

		// Output error.
		return UtilsApiHelper::getIntegrationErrorInternalOutput($details);
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
	private function prepareParams(array $params, string $eventName, array $map, string $formId): array
	{
		// Prepare output.
		$output = [
			'definitionId' => $eventName,
			'personIdentifierType' => 'EMAIL',
			'properties' => [],
		];

		$properties = [];

		// Filter params.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsMoments::SETTINGS_TYPE_KEY, 'prePostEventParams']);
		if (\has_filter($filterName)) {
			$params = \apply_filters($filterName, $params, $eventName, $map, $formId) ?? [];
		}

		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

		// Map params.
		if ($map) {
			foreach ($map as $mapKey => $mapItem) {
				$foundItem = UtilsGeneralHelper::getFieldDetailsByName($params, $mapItem);

				if (!$foundItem) {
					continue;
				}

				$properties[$mapKey] = $foundItem['value'];
			}
		}

		// Filter params.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsMoments::SETTINGS_TYPE_KEY, 'prePostEventParamsAfter']);
		if (\has_filter($filterName)) {
			$properties = \apply_filters($filterName, $properties, $params, $eventName, $formId) ?? [];
		}

		// Add custom properties.
		$output['properties'] = $properties;

		return $output;
	}
}
