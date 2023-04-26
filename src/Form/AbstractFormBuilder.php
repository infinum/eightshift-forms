<?php

/**
 * Class that holds all methods for building form settings pages, integrations forms, etc.
 *
 * @package EightshiftForms\Form
 */

declare(strict_types=1);

namespace EightshiftForms\Form;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components as HelpersComponents;

/**
 * FormBuilder class.
 */
abstract class AbstractFormBuilder
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Nested keys for inner blocks
	 *
	 * @var array<int, string>
	 */
	public const NESTED_KEYS = [
		'checkboxesContent',
		'radiosContent',
		'selectContent',
		'groupContent',
		'tabsContent',
		'tabContent',
		'layout',
		'layoutContent',
		'card',
		'cardContent',
	];

	/**
	 * Nested keys for inner blocks
	 *
	 * @var array<int, string>
	 */
	public const LAYOUT_KEYS = [
		'group',
		'tabs',
		'tab',
		'layout',
		'card',
	];

	/**
	 * Nested keys for inner blocks
	 *
	 * @var array<int, string>
	 */
	public const NESTED_KEYS_NEW = [
		'checkboxes',
		'radios',
		'select',
		...self::LAYOUT_KEYS,
	];

	/**
	 * Build settings form.
	 *
	 * @param array<int, array<string, mixed>> $formItems Form array.
	 * @param array<string, array<string, string>|string> $formAdditionalProps Additional attributes for the form component.
	 *
	 * @return string
	 */
	public function buildSettingsForm(array $formItems, array $formAdditionalProps = []): string
	{
		$formContent = '';

		// Add submit on the bottom of every form.
		$formContent .= Components::render(
			'submit',
			Components::props('submit', [
				'additionalClass' => 'es-submit--global',
				'submitValue' => \__('Save changes', 'eightshift-forms'),
			]),
			'',
			true
		);

		// If form needs refreshing like on general setting just pass formSuccessRedirect as true and the form will refresh on the same settings page.
		if (isset($formAdditionalProps['formSuccessRedirect']) && $formAdditionalProps['formSuccessRedirect']) {
			$formAdditionalProps['formSuccessRedirect'] = $this->getAdminRefreshUrl();
		}

		$formAdditionalProps['formResetOnSuccess'] = false;
		$formAdditionalProps['formDisableScrollToGlobalMessageOnSuccess'] = true;

		// Build form.
		return $this->getFormBuilder($formItems, $formAdditionalProps, $formContent);
	}

	/**
	 * Build components from array of items.
	 *
	 * @param array<string, mixed> $attributes Array of form components.
	 *
	 * @return string
	 */
	protected function buildComponent(array $attributes): string
	{
		if (!$attributes) {
			return '';
		}

		// Determin component name.
		$component = $attributes['component'] ? HelpersComponents::kebabToCamelCase($attributes['component']) : '';

		// Check children components for specific components.
		if (
			$component === 'checkboxes' ||
			$component === 'select' ||
			$component === 'radios' ||
			$component === 'group' ||
			$component === 'layout' ||
			$component === 'tabs'
		) {
			$output = '';

			$nestedKeys = \array_flip(self::NESTED_KEYS);

			foreach ($nestedKeys as $nestedKey => $value) {
				if (isset($attributes[$nestedKey])) {
					// Loop children and do the same on top level.
					foreach ($attributes[$nestedKey] as $item) {
						// Determine the component's name.
						$innerComponent = isset($item['component']) ? HelpersComponents::kebabToCamelCase($item['component']) : '';

						// Build child component.
						if ($item) {
							foreach ($item as $itemKey => $itemValue) {
								if (isset($nestedKeys[$itemKey]) && \is_array($itemValue)) {
									$groupOutput = '';
									foreach ($itemValue as $group) {
										$groupOutput .= $this->buildComponent($group);
									}

									$itemValue = $groupOutput;
								}

								$item[$itemKey] = $itemValue;
							}

							$output .= Components::render(
								$item['component'],
								Components::props($innerComponent, $item),
								'',
								true
							);
						}
					}
				}
				// Output child to the parent array.
				$attributes[$nestedKey] = $output;
			}
		}

		$additionalAttributes = [];

		if (isset($attributes['additionalFieldClass'])) {
			$additionalAttributes['additionalFieldClass'] = $attributes['additionalFieldClass'];
		}

		if (isset($attributes['additionalGroupClass'])) {
			$additionalAttributes['additionalGroupClass'] = $attributes['additionalGroupClass'];
		}

		// Build the component.
		return Components::render(
			$attributes['component'],
			\array_merge(
				Components::props($component, $attributes),
				$additionalAttributes
			),
			'',
			true
		);
	}

	/**
	 * Prepare disabled options and remove empty items.
	 *
	 * @param string $component Component name.
	 * @param array<int, string> $options Options to check.
	 * @param bool $useDefault Append default options.
	 *
	 * @return array<int, string>
	 */
	protected function prepareDisabledOptions(string $component, array $options = [], bool $useDefault = true): array
	{
		$component = Components::kebabToCamelCase($component);

		$default = [
			"{$component}Name",
		];

		return [
			...($useDefault ? $default : []),
			...\array_filter($options),
		];
	}

	/**
	 * Get the actual form for the components.
	 *
	 * @param array<int, array<string, mixed>> $formItems Form array.
	 * @param array<string, bool|int|string> $formAdditionalProps Additional attributes for form component.
	 * @param string $formContent For adding additional form components after every form.
	 *
	 * @return string
	 */
	private function getFormBuilder(array $formItems, array $formAdditionalProps = [], string $formContent = ''): string
	{
		$form = '';

		// Build all top-level component.
		foreach ($formItems as $item) {
			$form .= $this->buildComponent($item);
		}

		// Append additional form components.
		if (!empty($formContent)) {
			$form .= $formContent;
		}

		// Populate form props.
		$formProps = [
			'formContent' => $form,
		];

		// Add additional form props.
		if ($formAdditionalProps) {
			$formProps = \array_merge($formProps, $formAdditionalProps);
		}

		// Build form component.
		return Components::render(
			'form',
			Components::props('form', [], $formProps),
			'',
			true
		);
	}

	/**
	 * Returns the current admin page url for refresh.
	 *
	 * @return string
	 */
	private function getAdminRefreshUrl(): string
	{
		$request = isset($_SERVER['REQUEST_URI']) ? \sanitize_text_field(\wp_unslash($_SERVER['REQUEST_URI'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return \admin_url(\sprintf(\basename($request)));
	}
}
