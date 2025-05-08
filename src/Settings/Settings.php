<?php

/**
 * Class that holds data for admin forms settings.
 *
 * @package EightshiftForms\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Helpers\UtilsHelper;

/**
 * Settings class.
 */
class Settings extends AbstractFormBuilder implements SettingsBuilderInterface
{
	/**
	 * Get sidebar settings array for building settings page.
	 *
	 * @param string $formId Form ID.
	 * @param string $integrationTypeUsed Used integration in this form.
	 *
	 * @return array<int|string, mixed>
	 */
	public function getSettingsSidebar(string $formId = '', string $integrationTypeUsed = ''): array
	{
		$internalType = Config::SETTINGS_GLOBAL_TYPE_NAME;

		if ($formId) {
			$internalType = Config::SETTINGS_TYPE_NAME;
		}

		$output = [];

		foreach (\apply_filters(Config::FILTER_SETTINGS_DATA, []) as $key => $value) {
			// Determine if there is filter key name.
			if (!isset($value[$internalType])) {
				continue;
			}

			$type = $value['type'];
			$settingsForceShow = $value['settingsForceShow'] ?? false;

			// Skip integration forms if they are not used in the Block editor.
			// Mailer should be available on all integrations because it can be used as a backup option.
			if (!$settingsForceShow) {
				if ($formId && $type === Config::SETTINGS_INTERNAL_TYPE_INTEGRATION && $key !== $integrationTypeUsed) {
					continue;
				}
			}

			$isUsedKey = $value['use'] ?? '';

			// Bailout if used key is missing.
			if ($isUsedKey && !SettingsHelpers::isOptionCheckboxChecked($isUsedKey, $isUsedKey)) {
				continue;
			}

			$labels = $value['labels'] ?? [];

			// Populate sidebar data.
			$output[$type][] = [
				'label' => $labels['title'] ?? '',
				'desc' => $labels['desc'] ?? '',
				'url' => $formId ? GeneralHelpers::getSettingsPageUrl($formId, $key) : GeneralHelpers::getSettingsGlobalPageUrl($key),
				'icon' => $labels['icon'] ?? '',
				'type' => $type,
				'key' => $key,
			];
		}

		// Return all settings data with the correct order.
		return SettingsHelpers::sortSettingsByOrder($output);
	}

	/**
	 * Get form settings array for building settings page.
	 *
	 * @param string $type Form settings Type to show.
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getSettingsForm(string $type, string $formId): string
	{
		$internalType = Config::SETTINGS_GLOBAL_TYPE_NAME;

		if ($formId) {
			$internalType = Config::SETTINGS_TYPE_NAME;
		}

		// Find settings page.
		$filter = \apply_filters(Config::FILTER_SETTINGS_DATA, [])[$type][$internalType] ?? '';

		// Determine if there is a filter for settings page.
		if (!\has_filter($filter)) {
			return '';
		}

		// Get filter data.
		$data = \apply_filters($filter, $formId);

		$formAdditionalProps['formAttrs'] = [
			UtilsHelper::getStateAttribute('formFid') => $formId,
			UtilsHelper::getStateAttribute('formId') => FormsHelper::getFormUniqueHash(),
			UtilsHelper::getStateAttribute('formType') => $internalType,
			UtilsHelper::getStateAttribute('settingsType') => $type,
		];

		// Populate and build form.
		return $this->buildSettingsForm(
			$data ?? [],
			$formAdditionalProps
		);
	}
}
