<?php

/**
 * The class to provide integration items from cache.
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Moments
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Moments;

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Moments\SettingsMoments;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use WP_REST_Request;

/**
 * Class IntegrationItemsMomentsRoute
 */
class IntegrationItemsMomentsRoute extends AbstractBaseRoute
{
	/**
	 * Instance variable for Moments data.
	 *
	 * @var ClientInterface
	 */
	protected $momentsClient;

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/integration-items-moments';

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
	 * @param ClientInterface $momentsClient Inject HubSpot which holds HubSpot connect data.
	 */
	public function __construct(ClientInterface $momentsClient) {
		$this->momentsClient = $momentsClient;
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
	 * By default allow public access to route.
	 *
	 * @return bool
	 */
	// public function permissionCallback(): bool
	// {
	// 	return true;
	// }

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
		if (! \current_user_can(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) {
			\rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('You don\'t have enough permissions to perform this action!', 'eightshift-forms'),
			]);
		}

		// Check if Moments global settings is valid.
		$isGlobalSettingsValid = \apply_filters(SettingsMoments::FILTER_SETTINGS_GLOBAL_NAME, false);

		
		if (!$isGlobalSettingsValid) {
			\rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Global not configured', 'eightshift-forms'),
			]);
		}

		$items = $this->momentsClient->getItems();

		if (!$items) {
			\rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Items missing', 'eightshift-forms'),
			]);
		}

		$items = array_values(array_map(
			static function($item) {
				return [
					'label' => $item['title'],
					'value' => $item['id'],
				];
			},
			$items
		));

		// Exit with success.
		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'data' => $items,
		]);
	}
}
