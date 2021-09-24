<?php

/**
 * Class that holds all methods for building admin settings pages forms.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Helpers\TraitHelper;
use EightshiftFormsPluginVendor\EightshiftLibs\Helpers\Components as HelpersComponents;

/**
 * FormBuilder class.
 */
abstract class AbstractFormBuilder
{
	/**
	 * Use General helper trait.
	 */
	use TraitHelper;

	/**
	 * Build settings page form.
	 *
	 * @param array $formItems Form array to build from.
	 * @param string $formId Form ID.
	 * @param bool $isSettings Set values for settings pages.
	 * @param bool $refresh To refresh form after success or not.
	 *
	 * @return string
	 */
	public function buildForm(array $formItems, string $formId, bool $isSettings = false, bool $refresh = false): string
	{
		$form = '';

		foreach ($formItems as $item) {
			$form .= $this->buildComponent($item, $formId, $isSettings);
		}

		if ($isSettings) {
			$form .= Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'divider',
				Components::props('divider', []),
				'',
				true
			);
			$form .= Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'submit',
				Components::props('submit', [
					'submitValue' => __('Save settings', 'eightshift-forms'),
				]),
				'',
				true
			);
		}

		$formProps = [
			'formPostId' => Helper::encryptor('encrypt', $formId),
			'formContent' => $form,
		];

		if ($refresh) {
			$request = isset($_SERVER['REQUEST_URI']) ? \sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$formProps['formSuccessRedirect'] = admin_url(sprintf(basename($request)));
		}

		return Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'form',
			Components::props('form', [], $formProps),
			'',
			true
		);
	}

	/**
	 * Build component from arrya of items.
	 *
	 * @param array $attributes Array of form components.
	 * @param string $formId Form ID.
	 * @param bool $isSettings Set values for settings pages.
	 *
	 * @return string
	 */
	protected function buildComponent(array $attributes, string $formId, bool $isSettings = false): string
	{
		$component = $attributes['component'] ? HelpersComponents::kebabToCamelCase($attributes['component']) : '';

		$name = $attributes["{$component}Name"] ?? '';
		$value = $attributes["{$component}Value"] ?? '';

		if (isset($attributes['checkboxesContent'])) {
			$attributes['checkboxesContent'] = $this->getInnerComponent($attributes, 'checkboxesContent', $formId, $isSettings);
		}

		if (isset($attributes['radiosContent'])) {
			$attributes['radiosContent'] = $this->getInnerComponent($attributes, 'radiosContent', $formId, $isSettings);
		}

		if (isset($attributes['selectOptions'])) {
			$attributes['selectOptions'] = $this->getInnerComponent($attributes, 'selectOptions', $formId, $isSettings);
		}

		if ($isSettings) {
			$attributes["{$component}Name"] = $this->getSettingsName($name);
			if (empty($value)) {
				$attributes["{$component}Value"] = \get_post_meta($formId, $this->getSettingsName($name), true);
			}
		}

		return Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$attributes['component'],
			Components::props($component, $attributes),
			'',
			true
		);
	}

	/**
	 * Build inner component
	 *
	 * @param array $attributes Array of form components.
	 * @param string $key Key to check.
	 * @param string $formId Form ID.
	 * @param bool $isSettings Set values for settings pages.
	 *
	 * @return string
	 */
	protected function getInnerComponent(array $attributes, string $key, string $formId, bool $isSettings = false): string
	{
		$output = '';

		if (!$attributes[$key]) {
			return $output;
		}

		$parentComponent = $attributes['component'] ? HelpersComponents::kebabToCamelCase($attributes['component']) : '';

		foreach ($attributes[$key] as $item) {
			$component = $item['component'] ? HelpersComponents::kebabToCamelCase($item['component']) : '';

			if ($component) {
				if($isSettings) {
					switch ($parentComponent) {
						case 'radios':
							$newName = $this->getSettingsName($attributes["{$parentComponent}Name"]);
							$savedValue = \get_post_meta($formId, $newName, true);

							$item["{$component}Name"] = $newName;

							if ($savedValue === $item["{$component}Value"]) {
								$item["{$component}IsChecked"] = true;
							}
							break;

						case 'select':
							$newName = $this->getSettingsName($attributes["{$parentComponent}Name"]);
							$savedValue = \get_post_meta($formId, $newName, true);

							$item["{$component}Name"] = $newName;

							if ($savedValue === $item["{$component}Value"]) {
								$item["{$component}IsSelected"] = true;
							}
							break;

						default:
							$newName = $this->getSettingsName($item["{$component}Name"]);
							$savedValue = \get_post_meta($formId, $newName, true);

							$item["{$component}Name"] = $newName;

							if ($savedValue === $item["{$component}Value"]) {
								$item["{$component}IsChecked"] = true;
							}
							break;
					}
				}

				$output .= Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$item['component'],
					Components::props($component, $item),
					'',
					true
				);
			}
		}

		return $output;
	}

	/**
	 * Get remote form by url
	 *
	 * @param string $url Url to check.
	 *
	 * @return string
	 */
	protected function getRemoteForm(string $url)
	{
		$form = wp_remote_get($url);

		return $form;
	}

	/**
	 * Get Integration Remote form Body.
	 *
	 * @param string $url Remote url.
	 *
	 * @return string
	 */
	protected function getIntegrationRemoteForm(string $url): string
	{
		$form = $this->getRemoteForm($url);

		if (\is_wp_error($form)) {
			return '';
		}

		return $form['body'] ?? '';
	}
}
