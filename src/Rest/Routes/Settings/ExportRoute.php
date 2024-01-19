<?php

/**
 * The class to provide CSV export.
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Entries\EntriesHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Rest\Routes\AbstractUtilsBaseRoute;
use WP_REST_Request;

/**
 * Class ExportRoute
 */
class ExportRoute extends AbstractUtilsBaseRoute
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'export';

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return self::ROUTE_SLUG;
	}

	/**
	 * Method that returns rest response
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		$premission = $this->checkUserPermission();
		if ($premission) {
			return \rest_ensure_response($premission);
		}

		$debug = [
			'request' => $request,
		];

		$params = $this->prepareSimpleApiParams($request, $this->getMethods());

		$ids = isset($params['ids']) ? \json_decode($params['ids'], true) : [];

		if (!$ids) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\__('There are no selected entries.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$formId = $params['formId'] ?? '';
		if (!$formId) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\__('Form Id type is missing.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$output = [];
		foreach ($ids as $id) {
			$entry = EntriesHelper::getEntry((string) $id);

			if (!$entry) {
				continue;
			}

			$entryValues = $entry['entryValue'] ?? [];

			if (!$entryValues) {
				continue;
			}

			$outputInner = [];

			$outputInner['formEntryId'] = $entry['id'] ?? '';
			$outputInner['formId'] = $entry['formId'] ?? '';
			$outputInner['formEntryCreatedAt'] = $entry['createdAt'] ?? '';

			foreach ($entryValues as $key => $value) {
				if (\gettype($value) === 'array') {
					if (\array_key_first($value) === 0) {
						$outputInner[$key] = \implode(UtilsConfig::DELIMITER, $value);
					} else {
						$outputItems = \array_map(
							function ($value, $key) {
								return "{$key}={$value}";
							},
							$value,
							\array_keys($value)
						);
						$outputInner[$key] = \implode(UtilsConfig::DELIMITER, $outputItems);
					}
				} else {
					$outputInner[$key] = $value;
				}
			}

			$output[] = $outputInner;
		}


		if (!$output) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\__('Data for export is empty.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		return \rest_ensure_response(
			UtilsApiHelper::getApiSuccessPublicOutput(
				\__('Data export finished with success.', 'eightshift-forms'),
				[
					'output' => \wp_json_encode($output),
				],
				$debug
			)
		);
	}
}
