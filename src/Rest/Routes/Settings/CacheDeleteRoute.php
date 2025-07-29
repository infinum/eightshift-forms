<?php

/**
 * The class register route for deleting transient cache endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Misc\SettingsRocketCache;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

/**
 * Class CacheDeleteRoute
 */
class CacheDeleteRoute extends AbstractSimpleFormSubmit
{
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
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	protected function isRouteAdminProtected(): bool
	{
		return true;
	}

	/**
	 * Get mandatory params.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(): array
	{
		return [
			'type' => 'string',
		];
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $params Prepared params.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		$type = $params['type'] ?? '';

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
			default:
				$cacheTypes = $data[$type]['cache'] ?? [];
				$outputTitle = \ucfirst($type);

				if (!$cacheTypes) {
					throw new BadRequestException(
						\sprintf(\esc_html__('%s %s', 'eightshift-forms'), $outputTitle, $this->labels->getLabel('cacheTypeNotFound')),
						[
							AbstractBaseRoute::R_DEBUG_KEY => 'cacheTypeNotFound',
						]
					);
				}

				foreach ($cacheTypes as $item) {
					\delete_transient($item);
				}
				break;
		}

		// Clear WP-Rocket cache if cache is cleared.
		if (\function_exists('rocket_clean_domain') && \apply_filters(SettingsRocketCache::FILTER_SETTINGS_IS_VALID_NAME, false)) {
			\rocket_clean_domain();
		}

		// Finish.
		return [
			AbstractBaseRoute::R_MSG => \sprintf(\esc_html__('%s %s', 'eightshift-forms'), $outputTitle, $this->labels->getLabel('cacheDeletedSuccess')),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => 'cacheDeletedSuccess',
			],
		];
	}
}
