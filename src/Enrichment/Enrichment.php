<?php

/**
 * Enrichment class.
 *
 * @package EightshiftForms\Enrichment
 */

declare(strict_types=1);

namespace EightshiftForms\Enrichment;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;

/**
 * Enrichment class.
 */
class Enrichment implements EnrichmentInterface
{
	/**
	 * Enrichment expiration time in days const.
	 *
	 * @var string
	 */
	public const ENRICHMENT_EXPIRATION = '30';

	/**
	 * Enrichment prefill expiration time in days const.
	 *
	 * @var string
	 */
	public const ENRICHMENT_PREFILL_EXPIRATION = '2';

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
	 * @return array<string, mixed>
	 */
	public function getEnrichmentConfig(): array
	{
		$useEnrichment = \apply_filters(SettingsEnrichment::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);

		if (!$useEnrichment) {
			return [];
		}

		$tags = [];
		$tagsAdditional = UtilsSettingsHelper::getOptionValueAsJson(SettingsEnrichment::SETTINGS_ENRICHMENT_ALLOWED_TAGS_KEY, 1);

		if ($tagsAdditional) {
			$tagsAdditional = \str_replace(' ', \PHP_EOL, $tagsAdditional);
			$tagsAdditional = \str_replace(',', \PHP_EOL, $tagsAdditional);
			$tagsAdditional = \array_values(\array_filter(\explode(\PHP_EOL, $tagsAdditional)));
			$tagsAdditional = \array_unique(\array_map(
				static function ($item) {
					return \preg_replace('/[^a-zA-Z0-9_ -]/s', '', $item);
				},
				$tagsAdditional
			));

			$tags = $tagsAdditional;
		}

		$expiration = UtilsSettingsHelper::getOptionValue(SettingsEnrichment::SETTINGS_ENRICHMENT_EXPIRATION_TIME_KEY);
		$expirationPrefill = UtilsSettingsHelper::getOptionValue(SettingsEnrichment::SETTINGS_ENRICHMENT_PREFILL_EXPIRATION_TIME_KEY);

		$fullAllowed = [
			...$tags,
			...self::ENRICHMENT_DEFAULT_ALLOWED_TAGS,
		];

		$map = [];
		foreach ($fullAllowed as $value) {
			$itemValue = UtilsSettingsHelper::getOptionValue(SettingsEnrichment::SETTINGS_ENRICHMENT_ALLOWED_TAGS_MAP_KEY . '-' . $value);

			if ($itemValue) {
				$itemValue = \str_replace(' ', '', $itemValue);
				$itemValue = \array_flip(\explode(',', $itemValue));

				$map[$value] = $itemValue;
			}
		}

		return [
			'expiration' => $expiration ?: self::ENRICHMENT_EXPIRATION, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'expirationPrefill' => $expirationPrefill ?: self::ENRICHMENT_PREFILL_EXPIRATION, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'allowed' => $fullAllowed,
			'map' => $map,
		];
	}

	/**
	 * Map enrichment fields with forms fields.
	 *
	 * @param array<string, mixed> $params Params to match.
	 *
	 * @return array<string, mixed>
	 */
	public function mapEnrichmentFields(array $params): array
	{
		// Get enrichment map.
		$enrichment = FiltersOuputMock::getEnrichmentManualMapFilterValue($this->getEnrichmentConfig())['config'];

		if (!$enrichment) {
			return $params;
		}

		$enrichment = $enrichment['map'];

		// Get storage param values.
		$storage = $params[UtilsHelper::getStateParam('storage')]['value'] ?? [];

		// Map param values.
		return \array_map(
			static function ($item) use ($enrichment, $storage) {
				// Check param name as a reference.
				$name = $item['name'] ?? '';

				// Find enrichment key name by checking the array of available names.
				// Find only first iteration.
				$enrichmentName = \array_keys(\array_filter(
					$enrichment,
					static function ($inner) use ($name) {
						if (isset($inner[$name])) {
							return true;
						}
					}
				))[0] ?? '';

				// Check if enrichment name is present and storage contains that name.
				if ($enrichmentName && isset($storage[$enrichmentName])) {
					// Populate param value with storage value.
					$item['value'] = $storage[$enrichmentName];
				}

				return $item;
			},
			$params
		);
	}
}
