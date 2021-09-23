<?php

/**
 * Interface that holds all methods for building admin settings pages forms.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

/**
 * Interface for admin content listing
 */
interface FormOptionInterface
{
	/**
	 * Set all settings page field keys.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return array
	 */
	public function getFormFields(string $formId): array;
}
