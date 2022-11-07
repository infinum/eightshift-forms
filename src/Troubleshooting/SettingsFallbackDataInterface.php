<?php

/**
 * Interface that holds all methods for Fallback settings.
 *
 * @package EightshiftForms\Troubleshooting
 */

declare(strict_types=1);

namespace EightshiftForms\Troubleshooting;

use EightshiftForms\Settings\Settings\SettingInterface;

/**
 * Interface for SettingsFallbackDataInterface.
 */
interface SettingsFallbackDataInterface extends SettingInterface
{
	/**
	 * Output array settings for form.
	 *
	 * @param string $integration Integration name used for fallback.
	 *
	 * @return array<int, array<string, array<int|string, array<string, mixed>>|bool|string>>
	 */
	public function getOutputGlobalFallback(string $integration): array;
}
