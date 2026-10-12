<?php

/**
 * File that holds class for PublishPress Capabilities integration.
 *
 * @package EightshiftForms\Permissions
 */

declare(strict_types=1);

namespace EightshiftForms\Permissions;

use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * PublishPressCapabilities class.
 */
class PublishPressCapabilities implements ServiceInterface
{
	/**
	 * Tab title in the PublishPress Capabilities role editor.
	 */
	private const string TAB_TITLE = 'Eightshift Forms';

	/**
	 * Register custom hooks.
	 */
	public function register(): void
	{
		\add_filter('cme_plugin_capabilities', $this->addPluginCapabilities(...));
	}

	/**
	 * Group plugin capabilities under a dedicated tab.
	 *
	 * Post type capabilities already listed in the PublishPress post type tables are skipped by PublishPress.
	 *
	 * @param array<string, array<string>> $pluginCaps Capabilities grouped by plugin title.
	 *
	 * @return array<string, array<string>>
	 */
	public function addPluginCapabilities(array $pluginCaps): array
	{
		$pluginCaps[self::TAB_TITLE] = Config::CAPS;

		return $pluginCaps;
	}
}
