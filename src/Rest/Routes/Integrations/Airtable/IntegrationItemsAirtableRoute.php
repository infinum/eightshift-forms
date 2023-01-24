<?php

/**
 * The class to provide integration items from cache.
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
 * Class IntegrationItemsAirtableRoute
 */
class IntegrationItemsAirtableRoute extends AbstractBaseRoute
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
	public const ROUTE_SLUG = '/' . AbstractBaseRoute::ROUTE_PREFIX_INTEGRATION_ITEMS . '-airtable/';

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

		// Check if Airtable global settings is valid.
		$isGlobalSettingsValid = \apply_filters(SettingsAirtable::FILTER_SETTINGS_GLOBAL_NAME, false);

		if (!$isGlobalSettingsValid) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('Global not configured', 'eightshift-forms'),
				)
			);
		}

		$items = $this->airtableClient->getItems();

		if (!$items) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('Items missing', 'eightshift-forms'),
				)
			);
		}

		$items = \array_values(\array_map(
			static function ($item) {
				return [
					'label' => $item['title'],
					'value' => $item['id'],
				];
			},
			$items
		));

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
				]
			)
		);
	}
}
