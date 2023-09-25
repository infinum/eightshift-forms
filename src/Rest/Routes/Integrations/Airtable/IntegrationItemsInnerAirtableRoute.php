<?php

/**
 * The class to provide integration items inner from cache.
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Airtable
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Airtable;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Airtable\SettingsAirtable;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use WP_REST_Request;

/**
 * Class IntegrationItemsInnerAirtableRoute
 */
class IntegrationItemsInnerAirtableRoute extends AbstractBaseRoute
{
	/**
	 * Instance variable for Airtable data.
	 *
	 * @var ClientInterface
	 */
	protected $airtableClient;

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsAirtable::SETTINGS_TYPE_KEY;

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . AbstractBaseRoute::ROUTE_PREFIX_INTEGRATION_ITEMS_INNER . '/' . self::ROUTE_SLUG;
	}

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ClientInterface $airtableClient Inject HubSpot which holds HubSpot connect data.
	 */
	public function __construct(ClientInterface $airtableClient)
	{
		$this->airtableClient = $airtableClient;
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
		return static::READABLE;
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

		// Check if Airtable global settings is valid.
		$isGlobalSettingsValid = \apply_filters(SettingsAirtable::FILTER_SETTINGS_GLOBAL_NAME, false);

		if (!$isGlobalSettingsValid) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('Global not configured', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$itemId = $request->get_params()['id'] ?? '';

		if (!$itemId) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('Missing item ID.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$items = $this->airtableClient->getItem($itemId);

		if (!$items) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('Items missing', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$items = \array_filter(\array_values(\array_map(
			static function ($item) {
				$id = $item['id'] ?? '';

				if ($id) {
					return [
						'label' => $item['title'] ?? \__('No title', 'eightshift-forms'),
						'value' => $id,
					];
				}
			},
			$items
		)));

		// Finish.
		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				\esc_html__('Success', 'eightshift-forms'),
				[
					[
						'label' => '',
						'value' => '',
					],
					...$items,
				],
				$debug
			)
		);
	}
}
