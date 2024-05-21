<?php

/**
 * The class register route for deleting transient cache endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Rest\Routes\AbstractUtilsBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use WP_REST_Request;

/**
 * Class CacheDeleteRoute
 */
class CacheDeleteRoute extends AbstractUtilsBaseRoute
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
	 * @param ValidatorInterface $validator Inject validation methods.
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

		$params = $this->prepareSimpleApiParams($request);

		$type = $params['type'] ?? '';
		if (!$type) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\esc_html__('Type key was not provided.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$data = \apply_filters(UtilsConfig::FILTER_SETTINGS_DATA, []);

		if ($type === 'all') {
			$allItems = Helpers::flattenArray(\array_map(
				static function ($item) {
					if (isset($item['cache'])) {
						return $item['cache'];
					}
				},
				$data
			));

			if ($allItems) {
				foreach ($allItems as $item) {
					\delete_transient($item);
				}
			}
		} else {
			$cacheTypes = $data[$type]['cache'] ?? [];
			if (!$cacheTypes) {
				return \rest_ensure_response(
					UtilsApiHelper::getApiErrorPublicOutput(
						\esc_html__('Provided cache type doesn\'t exist.', 'eightshift-forms'),
						[],
						$debug
					)
				);
			}

			foreach ($cacheTypes as $item) {
				\delete_transient($item);
			}
		}

		// Clear WP-Rocket cache if cache is cleared.
		if (\function_exists('rocket_clean_domain')) {
			\rocket_clean_domain();
		}

		// Finish.
		return \rest_ensure_response(
			UtilsApiHelper::getApiSuccessPublicOutput(
				// translators: %s will be replaced with the form type.
				\sprintf(\esc_html__('%s cache deleted successfully!', 'eightshift-forms'), \ucfirst($type)),
				[],
				$debug
			)
		);
	}
}
