<?php

/**
 * The class to provide CSV export.
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\ActivityLog\ActivityLogHelper;
use EightshiftForms\Entries\EntriesHelper;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;

/**
 * Class ExportRoute
 */
class ExportRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'export';

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
	 * @param array<string, mixed> $params Params passed from the request.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [
			'ids' => 'string',
			'type' => 'string',
		];
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $params Prepared params.
	 *
	 * @throws BadRequestException If export is missing items.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		$ids = isset($params['ids']) ? \json_decode($params['ids'], true) : [];

		if (!$ids) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->getLabels()->getLabel('exportMissingItems'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => 'exportMissingItems',
				]
			);
			// phpcs:enable
		}

		switch ($params['type']) {
			case 'entry':
				$output = $this->getEntryOutput($ids);
				break;
			case 'activity-log':
				$output = $this->getActivityLogOutput($ids);
				break;
			default:
				$output = [];
				break;
		}

		if (!$output) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->getLabels()->getLabel('exportDataEmpty'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => 'exportDataEmpty',
				]
			);
			// phpcs:enable
		}

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('exportSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => 'exportSuccess',
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('adminExportContent') => \wp_json_encode($output),
			],
		];
	}

	/**
	 * Get entry output.
	 *
	 * @param array<string> $ids Entry Ids.
	 *
	 * @return array<mixed>
	 */
	private function getEntryOutput(array $ids): array
	{
		$output = [];

		foreach ($ids as $id) {
			$entry = EntriesHelper::getEntry((string) $id);

			if (!$entry) {
				continue;
			}

			$entryValues = $entry['entryValue'] ?? [];

			if (!$entryValues) {
				continue;
			}

			$outputInner = [];

			$outputInner['formEntryId'] = $entry['id'] ?? '';
			$outputInner['formId'] = $entry['formId'] ?? '';
			$outputInner['formEntryCreatedAt'] = $entry['createdAt'] ?? '';

			foreach ($entryValues as $key => $value) {
				if (\gettype($value) === 'array') {
					if (\array_key_first($value) === 0) {
						$outputInner[$key] = \implode(Config::DELIMITER, $value);
					} else {
						$outputItems = \array_map(
							function ($value, $key) {
								return "{$key}={$value}";
							},
							$value,
							\array_keys($value)
						);
						$outputInner[$key] = \implode(Config::DELIMITER, $outputItems);
					}
				} else {
					$outputInner[$key] = $value;
				}
			}

			$output[] = $outputInner;
		}

		return $output;
	}

	/**
	 * Get activity log output.
	 *
	 * @param array<string> $ids Activity log Ids.
	 *
	 * @return array<mixed>
	 */
	private function getActivityLogOutput(array $ids): array
	{
		$output = [];

		foreach ($ids as $id) {
			$activityLog = ActivityLogHelper::getActivityLog((string) $id);

			if (!$activityLog) {
				continue;
			}

			$output[] = $activityLog;
		}

		return $output;
	}
}
