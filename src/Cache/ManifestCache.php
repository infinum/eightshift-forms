<?php

/**
 * File containing an implementation of the ManifestCache class.
 *
 * It is used to provide manifest.json file location stored in the transient cache.
 *
 * @package EightshiftForms\Cache
 */

declare(strict_types=1);

namespace EightshiftForms\Cache;

use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\Cache\AbstractManifestCache;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use Override;

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
	public const TLD_KEY = 'tld';

	/**
	 * Get cache name.
	 *
	 * @return string Cache name.
	 */
	public function getCacheName(): string
	{
		return Config::MAIN_PLUGIN_MANIFEST_CACHE_NAME;
	}

	/**
	 * Get cache version.
	 */
	public function getVersion(): string
	{
		return Helpers::getPluginVersion();
	}

	/**
				 * Get cache for geolocation
				 */
				#[Override]
	public function useGeolocation(): bool
	{
		return true;
	}

	/**
	 * Get cache builder.
	 *
	 * @return array<string, array<mixed>> Array of cache builder.
	 */
	#[Override]
	protected function getCacheBuilder(): array
	{
		$sep = \DIRECTORY_SEPARATOR;

		return \array_merge(
			parent::getCacheBuilder(),
			[
			self::TYPE_FORMS => [
			self::TLD_KEY => [
			'path' => 'src',
			'fileName' => "Validation{$sep}manifest.json",
			]
					],
					]
		);
	}
}
