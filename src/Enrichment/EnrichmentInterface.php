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
	 * @return array<string, mixed>
	 */
	public function getEnrichmentConfig(): array;

	/**
	 * Map enrichment fields with forms fields.
	 *
	 * @param array<string, mixed> $params Params to match.
	 *
	 * @return array<string, mixed>
	 */
	public function mapEnrichmentFields(array $params): array;
}
