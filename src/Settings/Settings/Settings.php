<?php

/**
 * Class that holds data for admin forms settings.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;

/**
 * Settings class.
 */
class Settings extends AbstractFormBuilder implements SettingsInterface
{
	/**
	 * Settings sidebar type - general.
	 *
	 * @var string
	 */
	public const SETTINGS_SIEDBAR_TYPE_GENERAL = 'sidebar-general';

	/**
	 * Settings sidebar type - integration.
	 *
	 * @var string
	 */
	public const SETTINGS_SIEDBAR_TYPE_INTEGRATION = 'sidebar-integration';

	/**
	 * Settings sidebar type - troubleshooting.
	 *
	 * @var string
	 */
	public const SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING = 'sidebar-troubleshooting';

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
		$internalType = 'settingsGlobal';

		if ($formId) {
			$internalType = 'settings';
		}

		$output = [];

		foreach (Filters::ALL as $key => $value) {
			// Determin if there is filter key name.
			if (!isset($value[$internalType])) {
				continue;
			}

			$type = $value['type'];

			// Skip integration forms if they are not used in the Block editor.
			if ($formId && $type === Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION && $key !== $integrationTypeUsed) {
				continue;
			}

			$isUsedKey = $value['use'] ?? '';

			// Bailout if used key is missing.
			if ($isUsedKey && !$this->isCheckboxOptionChecked($isUsedKey, $isUsedKey)) {
				continue;
			}

			// Populate sidebar data.
			$output[$type][] = [
				'label' => Filters::getSettingsLabels($key),
				'url' => $formId ? Helper::getSettingsPageUrl($formId, $key) : Helper::getSettingsGlobalPageUrl($key),
				'icon' => $value['icon'],
				'type' => $value['type'],
			];
		}

		// Return all settings data.
		return $output;
	}

	/**
	 * Get form settings array for building settings page.
	 *
	 * @param string $type Form settings Type to show.
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getSettingsForm(string $type, string $formId = ''): string
	{
		$internalType = 'settingsGlobal';

		if ($formId) {
			$internalType = 'settings';
		}

		// Find settings page.
		$filter = Filters::ALL[$type][$internalType] ?? '';

		// Determine if there is a filter for settings page.
		if (!\has_filter($filter)) {
			return '';
		}

		// Get filter data.
		$data = \apply_filters($filter, $formId);

		// Add additional props to form component.
		$formAdditionalProps['formType'] = $type;

		if ($formId) {
			$formAdditionalProps['formPostId'] = $formId;
		}

		$formAdditionalProps['formAttrs'] = [
			'data-settings-type' => $internalType,
		];

		// Populate and build form.
		return $this->buildSettingsForm(
			$data ?? [],
			$formAdditionalProps
		);
	}
}
