<?php

/**
 * The class that hold hooks for custom post type routes.
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
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
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter('rest_pre_echo_response', [$this, 'getCptLimits'], 10, 3);
	}

	/**
	 * Get the cpt limits.
	 *
	 * @param array<string, mixed> $results The response data.
	 * @param WP_REST_Server $server The server object.
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array<string, mixed>
	 */
	public function getCptLimits(array $results, WP_REST_Server $server, WP_REST_Request $request): array
	{
		$disableRoutes = [
			'/wp/v2/' . UtilsConfig::SLUG_POST_TYPE,
			'/wp/v2/' . UtilsConfig::SLUG_RESULT_POST_TYPE,
		];

		$find = false;
		foreach ($disableRoutes as $route) {
			if (\str_contains($request->get_route(), $route)) {
				$find = true;
				break;
			}
		}

		if ($find && !\current_user_can(UtilsConfig::CAP_SETTINGS)) {
			return UtilsApiHelper::getApiPermissionsErrorPublicOutput();
		}

		return $results;
	}
}
