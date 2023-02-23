<?php

/**
 * The class for Integration Sync interface.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations;

/**
 * Class IntegrationSync
 */
interface IntegrationSyncInterface
{
	/**
	 * Sync and update form DB.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<string, mixed>
	 */
	public function syncFormDirect(string $formId): array;

	/**
	 * Sync content and integration form and provide the otuput for block editor route to manualy sync forms.
	 *
	 * @param string $formId Form Id.
	 * @param boolean $editorOutput Change output keys depending on the output type.
	 *
	 * @return array<string, mixed>
	 */
	public function syncFormEditor(string $formId, bool $editorOutput = false): array;

	/**
	 * Create new form block output, used for block editor route to populate new integrations after user selection.
	 *
	 * @param string $formId Form Id.
	 * @param string $type Integration type.
	 * @param string $itemId Item integration ID.
	 * @param string $innerId Item integration inner ID.
	 * @param boolean $editorOutput Change output keys depending on the output type.
	 *
	 * @return array<string, mixed>
	 */
	public function createFormEditor(string $formId, string $type, string $itemId, string $innerId, bool $editorOutput = false): array;
}
