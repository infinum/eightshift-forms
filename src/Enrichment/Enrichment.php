<?php

/**
 * Enrichment class.
 *
 * @package EightshiftForms\Enrichment
 */

declare(strict_types=1);

namespace EightshiftForms\Enrichment;

use EightshiftForms\Settings\SettingsHelper;

/**
 * Enrichment class.
 */
class Enrichment implements EnrichmentInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Enrichment expiration time in days const.
	 *
	 * @var string
	 */
	public const ENRICHMENT_EXPIRATION = '30';

	/**
	 * Enrichment default allowed tags const.
	 *
	 * @var array<int, string>
	 */
	public const ENRICHMENT_DEFAULT_ALLOWED_TAGS = [
		'gh_src',
		'gh_jid',
		'_hsq',
		'utm',
		'utm_source',
		'utm_content',
		'utm_campaign',
		'utm_term',
		'utm_medium',
	];

	/**
	 * Return enrichment config.
	 *
	 * @return array<string>
	 */
	public function getEnrichmentConfig(): array
	{
		$useEnrichment = \apply_filters(SettingsEnrichment::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);

		if (!$useEnrichment) {
			return [];
		}

		$tags = $this->getOptionValue(SettingsEnrichment::SETTINGS_ENRICHMENT_ALLOWED_TAGS_KEY) ?: ''; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		return [
			'expiration' => $this->getOptionValue(SettingsEnrichment::SETTINGS_ENRICHMENT_EXPIRATION_TIME_KEY) ?: self::ENRICHMENT_EXPIRATION, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'allowed' => \array_values(\array_filter(\explode(\PHP_EOL, $tags), 'strlen')) ?: self::ENRICHMENT_DEFAULT_ALLOWED_TAGS, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
		];
	}
}
