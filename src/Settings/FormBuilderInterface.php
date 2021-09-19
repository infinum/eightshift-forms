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
interface FormBuilderInterface
{
	/**
	 * Set all settings page field keys.
	 *
	 * @return array
	 */
	public function getFormFields(): array;
}
