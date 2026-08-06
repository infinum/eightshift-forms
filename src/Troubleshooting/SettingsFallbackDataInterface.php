<?php

/**
 * Interface that holds all methods for Fallback settings.
 *
 * @package EightshiftForms\Troubleshooting
 */

declare(strict_types=1);

namespace EightshiftForms\Troubleshooting;

/**
 * Interface for SettingsFallbackDataInterface.
 */
interface SettingsFallbackDataInterface
{
	/**
	 * Output array settings for form.
	 *
	 * @param string $integration Integration name used for fallback.
	 *
	 * @return array<string, array<int, array<string, bool|string>>|string>
	 */
	public function getOutputGlobalFallback(string $integration): array;
}
