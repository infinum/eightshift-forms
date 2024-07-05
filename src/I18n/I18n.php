<?php

/**
 * Class that holds all the internationalization functionality.
 *
 * @package EightshiftForms\I18n
 */

declare(strict_types=1);

namespace EightshiftForms\I18n;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
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
		\add_filter('load_textdomain_mofile', [$this, 'getTranslationFile'], 10, 2);
	}

	/**
	 * Get the translation file for the plugin.
	 *
	 * @param string $mofile The path to the translation file.
	 * @param string $domain The text domain.
	 *
	 * @return string
	 */
	public function getTranslationFile(string $mofile, string $domain): string
	{
		if ($domain !== 'eightshift-forms') {
			return $mofile;
		}

		$locale = \determine_locale();

		// Default to en_US if the locale so no need for a translation file.
		if ($locale === 'en_US') {
			return $mofile;
		}

		$externalFile = Helpers::joinPaths([\WP_LANG_DIR, 'plugins', "eightshift-forms-countries-{$locale}.mo"]);

		if (\file_exists($externalFile)) {
			return $mofile;
		}

		$internalFile = Helpers::joinPaths([__DIR__, 'languages', 'countries', "eightshift-forms-countries-{$locale}.mo"]);

		if (!\file_exists($internalFile)) {
			return $mofile;
		}

		return $internalFile;
	}
}
