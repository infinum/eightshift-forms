<?php

/**
 * Class that holds data for admin forms settings.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Form\AbstractFormBuilder;

/**
 * Settings class.
 */
class Settings extends AbstractFormBuilder implements SettingsInterface
{
	/**
	 * Settings sidebar type - general
	 *
	 * @var string
	 */
	public const SETTINGS_SIEDBAR_TYPE_GENERAL = 'sidebar-general';

	/**
	 * Settings sidebar type - integration
	 *
	 * @var string
	 */
	public const SETTINGS_SIEDBAR_TYPE_INTEGRATION = 'sidebar-integration';

	/**
	 * Settings sidebar type - troubleshooting
	 *
	 * @var string
	 */
	public const SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING = 'sidebar-troubleshooting';

	/**
	 * Get all settings sidebar array for building settings page.
	 *
	 * @return array<int|string, mixed>
	 */
	public function getSettingsSidebar(string $formId = ''): array
	{
		return $this->getSettingsSidebarOutput($formId);
	}

	/**
	 * Get all settings array for building settings page.
	 *
	 * @param string $type Form Type to show.
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getSettingsForm(string $type, string $formId = ''): string
	{
		return $this->getSettingsFormOutput($type, $formId);
	}
}
