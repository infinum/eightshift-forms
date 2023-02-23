<?php

/**
 * Interface that holds all methods for building single form settings form - Clearbit specific.
 *
 * @package EightshiftForms\Integrations\Clearbit
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Clearbit;

/**
 * Interface for SettingsClearbitDataInterface.
 */
interface SettingsClearbitDataInterface
{
	/**
	 * Output array settings for form.
	 *
	 * @param string $formId Form ID.
	 * @param string $key Key for use toggle.
	 *
	 * @return array<string, array<int, array<string, array<int, array<string, mixed>>|bool|string>>|string>
	 */
	public function getOutputClearbit(string $formId, string $key): array;

	/**
	 * Output array settings for form - global.
	 *
	 * @param array<string, string> $properties Array of properties from integration.
	 * @param array<string, string> $keys Array of keys to get data from.
	 *
	 * @return array<string, array<int, array<string, array<int, array<string, array<int, array<string, array<int|string, array<string, bool|string>>|string>>|bool|string>>|string>>|string>
	 */
	public function getOutputGlobalClearbit(array $properties, array $keys): array;
}
