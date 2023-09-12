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
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;

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
	 * Settings sidebar type - miscellaneous.
	 *
	 * @var string
	 */
	public const SETTINGS_SIEDBAR_TYPE_MISCELLANEOUS = 'sidebar-miscellaneous';

	/**
	 * Setting local type name.
	 *
	 * @var string
	 */
	public const SETTINGS_TYPE_NAME = 'settings';

	/**
	 * Setting global type name.
	 *
	 * @var string
	 */
	public const SETTINGS_GLOBAL_TYPE_NAME = 'settingsGlobal';

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
		$internalType = self::SETTINGS_GLOBAL_TYPE_NAME;

		if ($formId) {
			$internalType = self::SETTINGS_TYPE_NAME;
		}

		$output = [];

		foreach (Filters::ALL as $key => $value) {
			// Determin if there is filter key name.
			if (!isset($value[$internalType])) {
				continue;
			}

			$type = $value['type'];

			// Skip integration forms if they are not used in the Block editor.
			// Mailer should be available on all integrations because it can be used as a backup option.
			if ($key !== SettingsMailer::SETTINGS_TYPE_KEY) {
				if ($formId && $type === Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION && $key !== $integrationTypeUsed) {
					continue;
				}
			}

			$isUsedKey = $value['use'] ?? '';

			// Bailout if used key is missing.
			if ($isUsedKey && !$this->isCheckboxOptionChecked($isUsedKey, $isUsedKey)) {
				continue;
			}

			// Populate sidebar data.
			$output[$type][] = [
				'label' => Filters::getSettingsLabels($key),
				'desc' => Filters::getSettingsLabels($key, 'desc'),
				'url' => $formId ? Helper::getSettingsPageUrl($formId, $key) : Helper::getSettingsGlobalPageUrl($key),
				'icon' => Helper::getProjectIcons($key),
				'type' => $type,
				'key' => $key,
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
	public function getSettingsForm(string $type, string $formId): string
	{
		$internalType = self::SETTINGS_GLOBAL_TYPE_NAME;

		if ($formId) {
			$internalType = self::SETTINGS_TYPE_NAME;
		}

		// Find settings page.
		$filter = Filters::ALL[$type][$internalType] ?? '';

		// Determine if there is a filter for settings page.
		if (!\has_filter($filter)) {
			return '';
		}

		// Get filter data.
		$data = \apply_filters($filter, $formId);

		$formAdditionalProps['formAttrs'] = [
			AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['formId'] => $formId,
			AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['formType'] => $internalType,
			AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['settingsType'] => $type,
			AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['successRedirect'] => Helper::getCurrentUrl(),
		];

		// Populate and build form.
		return $this->buildSettingsForm(
			$data ?? [],
			$formAdditionalProps
		);
	}
}
