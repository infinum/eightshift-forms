<?php

/**
 * File containing an abstract class for holding Assets Manifest functionality.
 *
 * It is used to provide manifest.json file location used with Webpack to fetch correct file locations.
 *
 * @package EightshiftForms\Manifest
 */

declare(strict_types=1);

namespace EightshiftForms\Manifest;

use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Manifest\UtilsManifest;

/**
 * Class Manifest
 */
class Manifest extends UtilsManifest
{
	/**
	 * Register all hooks. Changed filter name to manifest.
	 *
	 * @return void
	 */
	public function register(): void
	{
		parent::register();
		\add_filter(UtilsConfig::MAIN_PLUGIN_MANIFEST_ITEM_HOOK_NAME, [$this, 'getAssetsManifestItem']);
	}
}
