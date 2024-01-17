<?php

/**
 * Entries action class.
 *
 * @package EightshiftForms\Entries
 */

declare(strict_types=1);

namespace EightshiftForms\Entries;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Class Entries
 */
class Entries implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action(UtilsHooksHelper::getActionName(['entries', 'saveEntry']), [$this, 'saveEntry']);
	}

	/**
	 * Save entry method.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return void
	 */
	public function saveEntry(array $formDetails): void
	{
		EntriesHelper::setEntryByFormDataRef($formDetails);
	}
}
