<?php

/**
 * Class that holds all methods for building form settings pages, integrations forms, etc.
 *
 * @package EightshiftForms\Form
 */

declare(strict_types=1);

namespace EightshiftForms\Form;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\Settings\SettingsGeneral;
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
	 * @var array
	 */
	private const NESTED_KEYS = [
		'checkboxesContent',
		'radiosContent',
		'selectOptions',
		'groupContent',
		'tabsContent',
		'tabContent',
		'layout',
		'layoutItems',
		'card',
		'cardContent',
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
				'submitValue' => \__('Save settings', 'eightshift-forms'),
				'submitIcon' => 'save',
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
	 * Return Integration form additional props
	 *
	 * @param string $formId Form ID.
	 * @param string $type Form Type.
	 *
	 * @return array<string, mixed>
	 */
	protected function getFormAdditionalProps(string $formId, string $type): array
	{
		$formAdditionalProps = [];

		// Tracking event name.
		$formAdditionalProps['formTrackingEventName'] = $this->getAdditionalPropsItem(
			$formId,
			$type,
			SettingsGeneral::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY,
			'trackingEventName'
		);

		// Success redirect url.
		$formAdditionalProps['formSuccessRedirect'] = $this->getAdditionalPropsItem(
			$formId,
			$type,
			SettingsGeneral::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY,
			'successRedirectUrl'
		);

		return $formAdditionalProps;
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

			$nestedKeys = array_flip(self::NESTED_KEYS);

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
	 * @param array $options Options to check.
	 * @param bool $useDefault Append default options.
	 *
	 * @return array
	 */
	protected function prepareDisabledOptions(string $component, array $options, bool $useDefault = true): array {
		$component = Components::kebabToCamelCase($component);

		$default = [
			"{$component}Name",
		];

		return [
			...($useDefault ? $default : []),
			...array_filter($options),
		];
	}

	/**
	 * Get full form block created from integration components.
	 *
	 * @param string $type Integration type, used as a parent block.
	 * @param array $fields Fields pre created from the integration.
	 * @param string $itemId Item id from the integration.
	 *
	 * @return array
	 */
	protected function getFormBlock(string $type, array $fields, string $itemId): array
	{
		$namespace = Components::getSettingsNamespace();

		$integrationBlocks = $this->getFormBlockInnerFields($fields);

		$integration = [
			[
				'blockName' => "{$namespace}/{$type}",
				'attrs' => [
					"{$type}IntegrationId" => $itemId,
				],
				'innerContent' => $integrationBlocks,
				'innerHTML' => '',
				'innerBlocks' => $integrationBlocks,
			],
		];

		return [
			'blockName' => "{$namespace}/form-selector",
			'attrs' => [],
			'innerContent' => $integration,
			'innerHTML' => '',
			'innerBlocks' => $integration,
		] ?? [];
	}

	/**
	 * Convert form block inner fileds from integration components.
	 *
	 * @param array $components Integration components.
	 *
	 * @return array
	 */
	private function getFormBlockInnerFields(array $components): array
	{
		$output = [];

		$namespace = Components::getSettingsNamespace();

		foreach($components as $component) {

			$componentName = $component['component'] ?? '';

			if (!$componentName) {
				continue;
			}

			$fieldsBlock = $this->getFormBlockInnerField($component);

			$output[] = [
				'blockName' => "{$namespace}/{$componentName}",
				'attrs' => $fieldsBlock['attrs'] ?? [],
				'innerContent' => $fieldsBlock['innerBlocks'] ?? [],
				'innerHTML' => '',
				'innerBlocks' => $fieldsBlock['innerBlocks'] ?? [],
			];
		}

		return $output;
	}

	/**
	 * Convert one block inner field from integration component.
	 *
	 * @param array $attributes Integration field attributes.
	 *
	 * @return array
	 */
	private function getFormBlockInnerField(array $attributes): array
	{
		$output = [
			'attrs' => [],
			'innerContent' => [],
			'innerHTML' => '',
			'innerBlocks' => [],
		];

		$componentName = $attributes['component'] ?? '';

		if (!$componentName) {
			return [];
		}

		$prefix = Components::kebabToCamelCase($componentName, '-');

		$nestedKeys = array_flip(self::NESTED_KEYS);

		foreach ($attributes as $key => $value) {
			if ($key === 'component') {
				continue;
			}

			if (isset($nestedKeys[$key])) {
				$innerBlocks = $this->getFormBlockInnerFields($value);
				$output['innerBlocks'] = $innerBlocks;
				$output['innerContent'] = $innerBlocks;
			} else {
				$newName = ucfirst($key);

				if ($key === 'disabledOptions') {
					$value = array_values(array_map(
						static function($item) use ($prefix) {
							return "{$prefix}" . ucfirst($item);
						},
						$value
					));

					$newName = ucfirst($prefix) . "DisabledOptions";
				}

				if (!$value) {
					continue;
				}

				$output['attrs']["{$prefix}{$newName}"] = is_string($value) ? $this->prepareAttributes($value) : $value;
			}
		}

		return $output;
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

		// Check if it is loaded on the front or the backend.
		if (isset($formAdditionalProps['ssr'])) {
			$formProps['formServerSideRender'] = $formAdditionalProps['ssr'];

			unset($formAdditionalProps['ssr']);
		}

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
	 * Get additional props item details with filter.
	 *
	 * @param string $formId Form Id.
	 * @param string $type Form Type.
	 * @param string $key Settings key.
	 * @param string $filter Filter name.
	 *
	 * @return string
	 */
	private function getAdditionalPropsItem(string $formId, string $type, string $key, string $filter): string
	{
		$output = $this->getSettingsValue(
			$key,
			$formId
		);

		$filterName = Filters::getBlockFilterName('form', $filter);
		if (\has_filter($filterName)) {
			$value = \apply_filters($filterName, $type, $formId) ?? '';

			// Ignore filter if empty.
			if (!empty($value)) {
				$output = $value;
			}
		}

		return $output ? $output : '';
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

	/**
	 * Convert all special characters in attributes.
	 * Logic got from the core `serialize_block_attributes` function.
	 *
	 * @param string $attribute
	 * @return string
	 */
	private function prepareAttributes(string $attribute): string
	{
		$attribute = preg_replace('\\u002d\\u002d', '/--/', $attribute);
		$attribute = preg_replace('\\u003c', '/</', $attribute);
		$attribute = preg_replace('\\u003e', '/>/', $attribute);
		$attribute = preg_replace('\\u0026', '/&/', $attribute);
		// Regex: /\\"/
		$attribute = preg_replace('\\u0022', '/\\\\"/', $attribute);

		return $attribute;
	}
}
