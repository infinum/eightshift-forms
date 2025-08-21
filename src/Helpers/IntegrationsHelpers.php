<?php

/**
 * Class that holds all Integration helpers.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\Config\Config;

/**
 * Class IntegrationsHelpers
 */
final class IntegrationsHelpers
{
	/**
	 * Get all active integration on specific form.
	 *
	 * @param string $id Form Id.
	 *
	 * @return array<string, mixed>
	 */
	public static function getIntegrationDetailsById(string $id): array
	{
		$formDetails = GeneralHelpers::getFormDetails($id);

		if (!$formDetails) {
			return [];
		}

		$type = $formDetails[Config::FD_TYPE];
		$useFilter = \apply_filters(Config::FILTER_SETTINGS_DATA, [])[$type]['use'] ?? '';

		return [
			'label' => $formDetails[Config::FD_LABEL],
			'icon' => $formDetails[Config::FD_ICON],
			'value' => $type,
			'isActive' => $useFilter ? SettingsHelpers::isOptionCheckboxChecked($useFilter, $useFilter) : false,
			'isValid' => $formDetails[Config::FD_IS_VALID],
			'isApiValid' => $formDetails[Config::FD_IS_API_VALID],
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

		foreach (\apply_filters(Config::FILTER_SETTINGS_DATA, []) as $key => $value) {
			$useFilter = $value['use'] ?? '';

			if (!$useFilter) {
				continue;
			}

			$type = $value['type'] ?? '';

			if ($type !== Config::SETTINGS_INTERNAL_TYPE_INTEGRATION) {
				continue;
			}

			$isUsed = SettingsHelpers::isOptionCheckboxChecked($useFilter, $useFilter);

			if (!$isUsed) {
				continue;
			}

			$output[] = $key;
		}

		return $output;
	}
}
