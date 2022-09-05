<?php

/**
 * Interface that holds all methods for Troubleshooting settings.
 *
 * @package EightshiftForms\Troubleshooting
 */

declare(strict_types=1);

namespace EightshiftForms\Troubleshooting;

use EightshiftForms\Settings\Settings\SettingsDataInterface;

/**
 * Interface for SettingsTroubleshootingDataInterface.
 */
interface SettingsTroubleshootingDataInterface extends SettingsDataInterface
{
	/**
	 * Output array settings for form.
	 *
	 * @param string $integration Integration name used for troubleshooting.
	 *
	 * @return array<int, array<string, array<int|string, array<string, mixed>>|bool|string>>
	 */
	public function getOutputGlobalTroubleshooting(string $integration): array;
}
