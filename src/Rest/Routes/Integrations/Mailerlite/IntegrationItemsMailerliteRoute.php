<?php

/**
 * The class to provide integration items from cache.
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Mailerlite
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailerlite;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Mailerlite\SettingsMailerlite;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Config\Config;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use WP_REST_Request;

/**
 * Class IntegrationItemsMailerliteRoute
 */
class IntegrationItemsMailerliteRoute extends AbstractBaseRoute
{
	/**
	 * Instance variable for Mailerlite data.
	 *
	 * @var ClientInterface
	 */
	protected $mailerliteClient;

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsMailerlite::SETTINGS_TYPE_KEY;

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . Config::ROUTE_PREFIX_INTEGRATION_ITEMS . '/' . self::ROUTE_SLUG;
	}

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ClientInterface $mailerliteClient Inject HubSpot which holds HubSpot connect data.
	 */
	public function __construct(ClientInterface $mailerliteClient)
	{
		$this->mailerliteClient = $mailerliteClient;
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
		$permission = $this->checkUserPermission(Config::CAP_SETTINGS);
		if ($permission) {
			return \rest_ensure_response($permission);
		}

		$debug = [
			'request' => $request,
		];

		// Check if Mailerlite global settings is valid.
		$isGlobalSettingsValid = \apply_filters(SettingsMailerlite::FILTER_SETTINGS_GLOBAL_NAME, false);

		if (!$isGlobalSettingsValid) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					\esc_html__('Global not configured', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$items = $this->mailerliteClient->getItems();

		if (!$items) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
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
			ApiHelpers::getApiSuccessPublicOutput(
				\esc_html__('Success', 'eightshift-forms'),
				$items,
				$debug
			)
		);
	}
}
