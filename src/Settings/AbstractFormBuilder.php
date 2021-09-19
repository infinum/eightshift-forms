<?php

/**
 * Class that holds all methods for building admin settings pages forms.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftForms\Helpers\Components;
use EightshiftFormsPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * FormBuilder class.
 */
abstract class AbstractFormBuilder implements FormBuilderInterface, ServiceInterface
{

	/**
	 * Get card article props hook name.
	 *
	 * @return string
	 */
	public const SETTINGS_PAGE_FORM_BUILDER = 'es_settings_page_form_builder';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::SETTINGS_PAGE_FORM_BUILDER, [$this, 'buildForm'], 10, 1);
	}

	/**
	 * Build settings page form.
	 *
	 * @param array $formItems Form array to build from.
	 * 
	 * @return string
	 */
	public function buildForm(array $formItems): string
	{
		$form = '';
		foreach ($formItems as $item) {
			$component = $item['component'] ?? '';

			if (!$component) {
				continue;
			}

			$form .= Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$component,
				Components::props($component, $item),
				'',
				true
			);
		}


		return Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'form',
			Components::props('form', [], [
				'formContent' => $form,
			]),
			'',
			true
		);;
	}

	/**
	 * Return admin option key depending on language
	 *
	 * @param string $key Providing key.
	 *
	 * @return string
	 */
	public function getOptionKey(string $key): string
	{
		$options = array_flip($this->getFormFields());

		if (!isset($options[$key])) {
			return '';
		}

		return $this->appendLocale($key);
	}

	/**
	 * Append locale to string
	 *
	 * @param string $string Providing string to append to.
	 *
	 * @return string
	 */
	public function appendLocale(string $string): string
	{
		return $string . '_' . $this->getLocale();
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
