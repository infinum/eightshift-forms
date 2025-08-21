<?php

/**
 * Class that holds all i18n helpers.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

/**
 * Class I18nHelpers
 */
final class I18nHelpers
{
	/**
	 * Set locale depending on default locale or hook override.
	 *
	 * @return string
	 */
	public static function getLocale(): string
	{
		$output = '';

		$filterName = HooksHelpers::getFilterName(['general', 'locale']);
		if (\has_filter($filterName)) {
			$locale = \apply_filters($filterName, []);

			$defaultLanguage = $locale['default'] ?? '';
			$currentLanguage = $locale['current'] ?? '';

			return $defaultLanguage === $currentLanguage ? '' : $currentLanguage;
		}

		return $output;
	}
}
