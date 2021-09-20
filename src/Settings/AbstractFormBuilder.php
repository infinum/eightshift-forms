<?php

/**
 * Class that holds all methods for building admin settings pages forms.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\TraitHelper;
use EightshiftFormsPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * FormBuilder class.
 */
abstract class AbstractFormBuilder implements FormBuilderInterface, ServiceInterface
{

	use TraitHelper;

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
		\add_filter(self::SETTINGS_PAGE_FORM_BUILDER, [$this, 'buildForm'], 10, 2);
	}

	/**
	 * Build settings page form.
	 *
	 * @param array $formItems Form array to build from.
	 * @param array $formItems Form array to build from.
	 * 
	 * @return string
	 */
	public function buildForm(array $formItems, string $formId): string
	{
		$form = '';

		foreach ($formItems as $item) {
			$form .= $this->getNormalComponent($item, $formId);
		}

		$form .= Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'input',
			Components::props('input', $this->getFormId($formId)),
			'',
			true
		);

		$form .= Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'submit',
			Components::props('submit', $this->getSubmit()),
			'',
			true
		);

		return Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'form',
			Components::props('form', [], [
				'formContent' => $form,
				'formMethod' => 'POST',
				'formPostId' => $formId,
			]),
			'',
			true
		);
	}

	/**
	 * Retun form norma component with correct field names like input, textarea, etc.
	 *
	 * @param array $items Array of form component.
	 * @param array $formItems Form array to build from.
	 *
	 * @return string
	 */
	protected function getNormalComponent(array $items, string $formId): string
	{
		$output = [];

		$component = $items['component'] ?? '';

		if (!$component) {
			return [];
		}

		foreach ($items as $key => $value) {
			$component = $items['component'] ?? '';

			$newKey = $component . ucfirst($key);

			if ($key === 'name') {
				$output[$newKey] = $this->getSettingsName($value);
				continue;
			}

			if ($key === 'label') {
				$output["{$component}FieldLabel"] = $value;
				continue;
			}

			$output[$newKey] = $value;
		}

		$output["{$component}Options"] = '';
		$output["{$component}Content"] = '';

		if ($component === 'checkbox' || $component === 'radio' || $component === 'select') {
			foreach ($items['items'] as $item) {
				if ($component === 'checkbox') {
					$output["{$component}Content"] .= $this->getCheckboxComponent($item, $formId, $component);
				}

				if ($component === 'radio') {
					$output["{$component}Content"] .= $this->getRadioComponent($item, $formId, $this->getSettingsName($items['name']));
				}

				if ($component === 'select') {
					$output["{$component}Options"] .= $this->getSelectComponent($item, $formId, $this->getSettingsName($items['name']));
				}
			}
		} else {
			$output["{$component}Value"] = \get_post_meta($formId, $this->getSettingsName($items['name']), true);
		}

		$output["component"] = $component;

		return Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$component,
			Components::props($component, $output),
			'',
			true
		);
	}

	/**
	 * Retun form component with correct field names for nested fields like checkbox.
	 *
	 * @param array $items Array of form component.
	 * @param array $formItems Form array to build from.
	 * @param string $component Field component name.
	 *
	 * @return string
	 */
	protected function getCheckboxComponent(array $items, string $formId, string $componentParent): string
	{
		$output = [];

		$component = 'choice';

		foreach ($items as $key => $value) {
			$newKey = $component . ucfirst($key);

			if ($key === 'name') {
				$output[$newKey] = $this->getSettingsName($value);
				continue;
			}

			$output[$newKey] = $value;
		}

		$savedValue = \get_post_meta($formId, $this->getSettingsName($items['name']), true);

		$output["{$component}IsChecked"] = filter_var($savedValue, FILTER_VALIDATE_BOOLEAN);

		return Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$component,
			Components::props($component, $output),
			'',
			true
		);
	}

	/**
	 * Retun form component with correct field names for nested fields like radio.
	 *
	 * @param array $items Array of form component.
	 * @param array $formItems Form array to build from.
	 * @param string $name Field component name.
	 *
	 * @return string
	 */
	protected function getRadioComponent(array $items, string $formId, string $name): string
	{
		$output = [];

		$component = 'choice';

		$savedValue = \get_post_meta($formId, $name, true);

		foreach ($items as $key => $value) {
			$newKey = $component . ucfirst($key);

			if ($key === 'value') {
				$output["{$component}IsChecked"] = $value === $savedValue;
			}

			$output[$newKey] = $value;
		}

		$output["{$component}Name"] = $name;

		$output["{$component}Type"] = 'radio';

		return Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$component,
			Components::props($component, $output),
			'',
			true
		);
	}

	/**
	 * Retun form component with correct field names for nested fields like select.
	 *
	 * @param array $items Array of form component.
	 * @param array $formItems Form array to build from.
	 * @param string $name Option name.
	 *
	 * @return string
	 */
	protected function getSelectComponent(array $items, string $formId, $name): string
	{
		$output = [];

		$component = 'select-option';
		$attrName = 'selectOption';

		$savedValue = \get_post_meta($formId, $name, true);

		foreach ($items as $key => $value) {
			$newKey = $attrName . ucfirst($key);

			if ($key === 'name') {
				$output[$newKey] = $this->getSettingsName($value);
				continue;
			}

			if ($key === 'value') {
				$output["{$attrName}IsSelected"] = $value === $savedValue;
			}

			$output[$newKey] = $value;
		}

		return Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$component,
			Components::props($component, $output),
			'',
			true
		);
	}

	/**
	 * Get form submit fields.
	 *
	 * @return array
	 */
	protected function getSubmit(): array
	{
		return [
			'submitName' => 'es-submit',
			'submitValue' => \__('Save', 'eightshift-forms'),
		];
	}

	/**
	 * Get form ID fields.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return array
	 */
	protected function getFormId(string $formId): array
	{
		return [
			'inputName' => 'es-form-id',
			'inputValue' => $formId,
			'inputType' => 'hidden',
		];
	}
}
