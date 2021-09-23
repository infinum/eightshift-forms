<?php

/**
 * Class that holds all methods for building admin settings pages forms.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Helpers\Components;
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
	 * @param bool $refresh To refresh form after success or not.
	 *
	 * @return string
	 */
	public function buildForm(array $formItems, string $formId, bool $refresh = false): string
	{
		$form = '';

		foreach ($formItems as $item) {
			$form .= $this->buildComponent($item, $formId);
		}

		$form .= Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'submit',
			Components::props('submit', [
				'submitValue' => __('Save settings', 'eightshift-forms'),
			]),
			'',
			true
		);

		$formProps = [
			'formContent' => $form,
			'formMethod' => 'POST',
			'formPostId' => $formId,
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
	 *
	 * @return string
	 */
	protected function buildComponent(array $attributes, string $formId): string
	{
		$component = $attributes['component'] ? HelpersComponents::kebabToCamelCase($attributes['component']) : '';

		$name = $attributes["{$component}Name"] ?? '';

		if (isset($attributes['checkboxesContent'])) {
			$attributes['checkboxesContent'] = $this->getInnerComponent($attributes, 'checkboxesContent', $formId);
		}

		if (isset($attributes['radiosContent'])) {
			$attributes['radiosContent'] = $this->getInnerComponent($attributes, 'radiosContent', $formId);
		}

		if (isset($attributes['selectOptions'])) {
			$attributes['selectOptions'] = $this->getInnerComponent($attributes, 'selectOptions', $formId);
		}

		$attributes["{$component}Name"] = $this->getSettingsName($name);
		$attributes["{$component}Value"] = \get_post_meta($formId, $this->getSettingsName($name), true);

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
	 *
	 * @return string
	 */
	protected function getInnerComponent(array $attributes, string $key, string $formId): string
	{
		$output = '';

		if (!$attributes[$key]) {
			return $output;
		}

		$parentComponent = $attributes['component'] ? HelpersComponents::kebabToCamelCase($attributes['component']) : '';

		foreach ($attributes[$key] as $item) {
			$component = $item['component'] ? HelpersComponents::kebabToCamelCase($item['component']) : '';

			if ($component) {
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
}
