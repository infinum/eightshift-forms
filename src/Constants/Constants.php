<?php

/**
 * Constants main class.
 *
 * @package EightshiftForms\Constants
 */

declare(strict_types=1);

namespace EightshiftForms\Constants;

use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Misc\SettingsCloudflare;
use EightshiftForms\Misc\SettingsWpRocket;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Constants class.
 */
class Constants implements ConstantsInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Map of constants with settings names.
	 */
	private const CONSTANTS = [
		SettingsGeolocation::SETTINGS_GEOLOCATION_USE_KEY => [
			'ES_GEOLOCATION_USE',
		],
		SettingsWpRocket::SETTINGS_WPROCKET_USE_KEY => [
			'ES_GEOLOCATION_USE_WP_ROCKET',
		],
		SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY => [
			'ES_GEOLOCATION_USE_CLOUDFLARE',
		],
	];

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('admin_init', [$this, 'setConstants']);
	}

	/**
	 * Set constants based on the settings values.
	 *
	 * @return void
	 */
	public function setConstants(): void
	{
		foreach (\array_keys(self::CONSTANTS) as $key) {
			$this->changeConstants($key, $this->isCheckboxOptionChecked($key, $key));
		}
	}

	/**
	 * Change constant definition in the constantsOutput.php file.
	 *
	 * @param string $name Name of the setting.
	 * @param boolean $value Value of the setting.
	 *
	 * @return void
	 */
	public function changeConstants(string $name, bool $value): void
	{
		// Bailout if constant is not defined.
		if (!isset(self::CONSTANTS[$name])) {
			return;
		}

		// Find the constants file path.
		$path = __DIR__ . \DIRECTORY_SEPARATOR . 'constantsOutput.php';

		// Bailout if constants file does not exist.
		if (!\file_exists($path)) {
			return;
		}

		// Get constants file content.
		$constantFile = \file_get_contents($path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		// Loop all constants and change the value.
		foreach (self::CONSTANTS[$name] as $constantName) {
			// Bailout if constant is not defined.
			if (!\defined($constantName)) {
				continue;
			}

			// Bailout if constant is already set to the value.
			if (\constant($constantName) === $value) {
				continue;
			}

			// Change the constant value.
			if (\constant($constantName) === false && $value === true) {
				$constantFile = \str_replace("define('{$constantName}', false);", "define('{$constantName}', true);", $constantFile);
			} else {
				$constantFile = \str_replace("define('{$constantName}', true);", "define('{$constantName}', false);", $constantFile);
			}
		}

		// If contents output file is empty use defaults file to provide constants.
		if (!$constantFile) {
			$pathDefaults = __DIR__ . \DIRECTORY_SEPARATOR . 'constantsOutputDefaults.php';

			// Check if defaults file exists.
			if (\file_exists($pathDefaults)) {
				// Get defaults file content.
				$constantFileDefaults = \file_get_contents($path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

				// Bailout if defaults file is empty.
				if ($constantFileDefaults) {
					// Save the new constants file from defaults.
					\file_put_contents($path, $constantFileDefaults); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
				}

				return;
			}
		}

		// Save the new constants file.
		\file_put_contents($path, $constantFile); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
	}
}
