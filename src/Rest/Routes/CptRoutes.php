<?php

/**
 * The class that hold hooks for custom post type routes.
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Class CptRoutes
 */
class CptRoutes implements ServiceInterface
{
	/**
	 * Register all the hooks
	 */
	public function register(): void
	{
		\add_filter('rest_pre_echo_response', $this->getCptLimits(...), 10, 3);
	}

	/**
	 * Get the cpt limits.
	 *
	 * @param mixed $results The response data.
	 * @param WP_REST_Server $server The server object.
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return mixed
	 */
	public function getCptLimits($results, WP_REST_Server $server, WP_REST_Request $request)
	{
		$disableRoutes = [
			'/wp/v2/' . Config::SLUG_POST_TYPE,
			'/wp/v2/' . Config::SLUG_RESULT_POST_TYPE,
		];
		$find = \array_any($disableRoutes, fn($route): bool => \str_contains($request->get_route(), (string) $route));

		if ($find && !\current_user_can(Config::CAP_SETTINGS)) {
			return ApiHelpers::getApiErrorPublicOutput(
				\__('You don\'t have enough permissions to perform this action!', 'eightshift-libs'),
			);
		}

		return $results;
	}
}
