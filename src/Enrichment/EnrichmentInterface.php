<?php

/**
 * Enrichment data interface.
 *
 * @package EightshiftLibs\Enrichment
 */

declare(strict_types=1);

namespace EightshiftForms\Enrichment;

/**
 * Interface EnrichmentInterface
 */
interface EnrichmentInterface
{
	/**
	 * Return enrichment config.
	 *
	 * @return array<string>
	 */
	public function getEnrichmentConfig(): array;
}
