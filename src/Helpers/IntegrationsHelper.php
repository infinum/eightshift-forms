<?php

/**
 * Class that holds all Integration helpers.
 *
 * @package EightshiftLibs\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Helpers\SettingsHelper;

/**
 * Class IntegrationsHelper
 */
final class IntegrationsHelper
{
	/**
	 * Get all active integration on specific form.
	 *
	 * @param string $id Form Id.
	 *
	 * @return array<string, string>
	 */
	public static function getIntegrationDetailsById(string $id): array
	{
		$integrationDetails = Helper::getFormDetailsById($id);

		if (!$integrationDetails) {
			return [];
		}

		$type = $integrationDetails['typeFilter'];
		$useFilter = \apply_filters(Filters::FILTER_SETTINGS_DATA, [])[$type]['use'] ?? '';

		return [
			'label' => $integrationDetails['label'],
			'icon' => $integrationDetails['icon'],
			'value' => $type,
			'isActive' => $useFilter ? SettingsHelper::isOptionCheckboxChecked($useFilter, $useFilter) : false,
			'isValid' => $integrationDetails['isValid'],
			'isApiValid' => $integrationDetails['isApiValid'],
		];
	}

	/**
	 * Get list of all active integrations
	 *
	 * @return array<int, string>
	 */
	public static function getActiveIntegrations(): array
	{
		$output = [];

		foreach (\apply_filters(Filters::FILTER_SETTINGS_DATA, []) as $key => $value) {
			$useFilter = $value['use'] ?? '';

			if (!$useFilter) {
				continue;
			}

			$type = $value['type'] ?? '';

			if ($type !== Filters::SETTINGS_INTERNAL_TYPE_INTEGRATION) {
				continue;
			}

			$isUsed = SettingsHelper::isOptionCheckboxChecked($useFilter, $useFilter);

			if (!$isUsed) {
				continue;
			}

			$output[] = $key;
		}

		return $output;
	}
}
