<?php

/**
 * Trait that holds all generic helpers.
 *
 * @package EightshiftLibs\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

/**
 * TraitHelper trait.
 */
trait TraitHelper
{
	/**
	 * Get settings value.
	 *
	 * @param string $key Providing string to append to.
	 * @param string $formId Form Id.
	 *
	 * @return string
	 */
	public function getSettingsValue(string $key, string $formId): string
	{
		return (string) \get_post_meta($formId, $this->getSettingsName($key), true);
	}

	/**
	 * Get filed name with locale and prefix.
	 *
	 * @param string $string Providing string to append to.
	 *
	 * @return string
	 */
	public function getSettingsName(string $string): string
	{
		return "{$string}-" . $this->getLocale();
	}

	/**
	 * Set locale depending ond default locale or hook override.
	 *
	 * @return string
	 */
	public function getLocale(): string
	{
		$locale = get_locale();

		if (\has_filter('es_set_locale')) {
			$locale = \apply_filters('es_set_locale', $locale);
		}

		return $locale;
	}
}
