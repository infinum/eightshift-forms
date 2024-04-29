<?php

/**
 * File containing an abstract class for holding Manifest Cache functionality.
 *
 * It is used to provide manifest.json file location stored in the transient cache.
 *
 * @package EightshiftForms\Cache
 */

declare(strict_types=1);

namespace EightshiftForms\Cache;

use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDataHelper;
use EightshiftFormsVendor\EightshiftLibs\Cache\AbstractManifestCache;

/**
 * The project cache class.
 */
class ManifestCache extends AbstractManifestCache
{
	/**
	 * Cache key for geolocation.
	 *
	 * @var string
	 */
	public const TYPE_FORMS = 'forms';

	/**
	 * Cache key - countries.
	 *
	 * @var string
	 */
	public const COUNTRIES_KEY = 'countries';

	/**
	 * Cache key - countries.
	 *
	 * @var string
	 */
	public const TLD_KEY = 'tld';

	/**
	 * Get cache name.
	 *
	 * @return string Cache name.
	 */
	public function getCacheName(): string
	{
		return UtilsConfig::MAIN_PLUGIN_MANIFEST_CACHE_NAME;
	}

	/**
	 * Set all cache.
	 *
	 * @param array<string> $ignoreCache Array of cache to ignore.
	 *
	 * @return void
	 */
	public function setProjectAllCache($ignoreCache = []): void
	{
		$this->setAllCache($ignoreCache);

		if (!isset($ignoreCache[self::TYPE_FORMS])) {
			$this->setCache(self::TYPE_FORMS);
		}
	}

	/**
	 * Get cache builder.
	 *
	 * @return array<string, array<mixed>> Array of cache builder.
	 */
	protected function getCacheBuilder(): array
	{
		$sep = \DIRECTORY_SEPARATOR;

		return \array_merge(
			parent::getCacheBuilder(),
			[
				self::TYPE_FORMS => [
					self::COUNTRIES_KEY => [
						'pathCustom' => UtilsDataHelper::getDataManifestPath('countries'),
					],
					self::TLD_KEY => [
						'fileName' => "Validation{$sep}manifest.json",
						'path' => 'srcDestination',
					]
				],
			]
		);
	}
}
