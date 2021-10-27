<?php

/**
 * Trait that holds all generic helpers used in classes.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

/**
 * SettingsHelper trait.
 */
trait SettingsHelper
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
		return (string) \get_post_meta((int) $formId, $this->getSettingsName($key), true);
	}

	/**
	 * Get option value.
	 *
	 * @param string $key Providing string to append to.
	 *
	 * @return string
	 */
	public function getOptionValue(string $key): string
	{
		return (string) \get_option($this->getSettingsName($key), false);
	}

	/**
	 * Get string name with locale.
	 *
	 * @param string $string Providing string to append to.
	 *
	 * @return string
	 */
	public function getSettingsName(string $string): string
	{
		return "es-forms-{$string}-" . $this->getLocale();
	}

	/**
	 * Set locale depending ond default locale or hook override.
	 *
	 * @return string
	 */
	public function getLocale(): string
	{
		$locale = get_locale();

		if (\has_filter('es_forms_set_locale')) {
			$locale = \apply_filters('es_forms_set_locale', $locale);
		}

		return $locale;
	}
}
