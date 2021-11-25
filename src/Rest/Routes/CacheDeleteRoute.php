<?php

/**
 * The class register route for deleting transient cache endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class CacheDeleteRoute
 */
class CacheDeleteRoute extends AbstractBaseRoute
{

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	public $validator;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 */
	public function __construct(
		ValidatorInterface $validator
	) {
		$this->validator = $validator;
	}

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/cache-delete';

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
	 * Method that returns rest response
	 *
	 * @param \WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return \WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(\WP_REST_Request $request)
	{
		if (! current_user_can(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) {
			\rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => esc_html__('Error, you don\'t have enough permissions to perform this action!', 'eightshift-form'),
			]);
		}

		$params = $request->get_body_params();

		if (!isset($params['type'])) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => esc_html__('Error, no cache type key was provided.', 'eightshift-form'),
			]);
		}

		$type = $params['type'];

		if (!isset(SettingsCache::ALL_CACHE[$type])) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => esc_html__('Error, provided cache type doesn\'t exist.', 'eightshift-form'),
			]);
		}

		foreach (SettingsCache::ALL_CACHE[$type] as $item) {
			delete_transient($item);
		}

		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			// translators: %s will be replaced with the cache type.
			'message' => sprintf(esc_html__('%s cache successfully deleted!', 'eightshift-form'), ucfirst($type)),
		]);
	}
}
