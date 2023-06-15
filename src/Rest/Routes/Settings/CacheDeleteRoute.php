<?php

/**
 * The class register route for deleting transient cache endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use WP_REST_Request;

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
	protected $validator;

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
	public const ROUTE_SLUG = 'cache-delete';

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

		$params = $request->get_body_params();

		// Used for JS api fecth from editor.
		if (!$params) {
			$params = $request->get_json_params();

			if (\is_string($params)) {
				$params = \json_decode($params, true);
			}
		}

		if (!isset($params['type'])) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('Type key was not provided.', 'eightshift-forms'),
				)
			);
		}

		$type = $params['type'];

		if ($type === 'all') {
			$allItems = Components::flattenArray(\array_map(
				static function ($item) {
					if (isset($item['cache'])) {
						return $item['cache'];
					}
				},
				Filters::ALL
			));

			if ($allItems) {
				foreach ($allItems as $item) {
					\delete_transient($item);
				}
			}
		} else {
			if (!isset(Filters::ALL[$type]['cache'])) {
				return \rest_ensure_response(
					$this->getApiErrorOutput(
						\esc_html__('Provided cache type doesn\'t exist.', 'eightshift-forms'),
					)
				);
			}

			foreach (Filters::ALL[$type]['cache'] as $item) {
				\delete_transient($item);
			}
		}

		// Clear WP-Rocket cache if cache is cleared.
		if (\function_exists('rocket_clean_domain')) {
			\rocket_clean_domain();
		}

		// Finish.
		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				// translators: %s will be replaced with the form type.
				\sprintf(\esc_html__('%s cache deleted successfully!', 'eightshift-forms'), \ucfirst($type))
			)
		);
	}
}
