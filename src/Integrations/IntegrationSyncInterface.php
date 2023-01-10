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
	public function syncForm(string $formId, bool $isPreview = false): array;

	public function createForm(string $formId, string $type, string $itemId, string $innerId): array;
}
