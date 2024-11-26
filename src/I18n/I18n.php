<?php

/**
 * Class that holds all the internationalization functionality.
 *
 * @package EightshiftForms\I18n
 */

declare(strict_types=1);

namespace EightshiftForms\I18n;

use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * I18n class.
 */
class I18n implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('init', [$this, 'loadTextdomain']);
	}

	/**
	 * Load the plugin text domain.
	 *
	 * @return void
	 */
	public function loadTextdomain(): void
	{
		\load_plugin_textdomain('eightshift-forms', false, \plugin_basename(__DIR__) . '/languages');
	}
}
