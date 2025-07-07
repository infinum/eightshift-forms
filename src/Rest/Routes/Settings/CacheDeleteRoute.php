<?php

/**
 * The class register route for deleting transient cache endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Misc\SettingsRocketCache;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
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
	 * @param ValidatorInterface $validator Inject validation methods.
	 */
	public function __construct(ValidatorInterface $validator)
	{
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
		$permission = $this->checkUserPermission(Config::CAP_SETTINGS);
		if ($permission) {
			return \rest_ensure_response($permission);
		}

		$debug = [
			'request' => $request,
		];

		$params = $this->prepareSimpleApiParams($request);

		$type = $params['type'] ?? '';
		if (!$type) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					\esc_html__('Type key was not provided.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$data = \apply_filters(Config::FILTER_SETTINGS_DATA, []);

		switch ($type) {
			case 'allOperational':
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

				$outputTitle = \esc_html__('All operational', 'eightshift-forms');
				break;
			case 'allInternal':
				$outputTitle = \esc_html__('All internal', 'eightshift-forms');
				Helpers::clearAllCache();
				break;
			default:
				$cacheTypes = $data[$type]['cache'] ?? [];
				if (!$cacheTypes) {
					return \rest_ensure_response(
						ApiHelpers::getApiErrorPublicOutput(
							\esc_html__('Provided cache type doesn\'t exist.', 'eightshift-forms'),
							[],
							$debug
						)
					);
				}

				foreach ($cacheTypes as $item) {
					\delete_transient($item);
				}

				$outputTitle = \ucfirst($type);
				break;
		}

		// Clear WP-Rocket cache if cache is cleared.
		if (\function_exists('rocket_clean_domain') && \apply_filters(SettingsRocketCache::FILTER_SETTINGS_IS_VALID_NAME, false)) {
			\rocket_clean_domain();
		}

		// Finish.
		return \rest_ensure_response(
			ApiHelpers::getApiSuccessPublicOutput(
				// translators: %s will be replaced with the form type.
				\sprintf(\esc_html__('%s cache deleted successfully!', 'eightshift-forms'), $outputTitle),
				[],
				$debug
			)
		);
	}
}
