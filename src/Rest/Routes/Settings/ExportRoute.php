<?php

/**
 * The class to provide CSV export.
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Entries\EntriesHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use WP_REST_Request;

/**
 * Class ExportRoute
 */
class ExportRoute extends AbstractBaseRoute
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

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
	 * Get callback arguments array
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => $this->getMethods(),
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => [$this, 'permissionCallback'],
		];
	}

	/**
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::CREATABLE;
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
				$this->getApiErrorOutput(
					\__('There are no selected entries.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$formId = $params['formId'] ?? '';
		if (!$formId) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
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
				$outputInner[$key] = $value;

				if (\gettype($value) === 'array') {
					$outputInner[$key] = \implode(AbstractBaseRoute::DELIMITER, $value);
				}
			}

			$output[] = $outputInner;
		}


		if (!$output) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\__('Data for export is empty.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				\__('Data export finished with success.', 'eightshift-forms'),
				[
					'output' => \wp_json_encode($output),
				],
				$debug
			)
		);
	}
}
