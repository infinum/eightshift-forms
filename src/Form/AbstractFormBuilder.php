<?php

/**
 * Class that holds all methods for building form settings pages, integrations forms, etc.
 *
 * @package EightshiftForms\Form
 */

declare(strict_types=1);

namespace EightshiftForms\Form;

use EightshiftForms\Helpers\Components;
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
	 * Build public facing form.
	 *
	 * @param array<int, array<string, mixed>> $formItems Form array.
	 * @param array<string, bool|string> $formAdditionalProps Additional attributes for form component.
	 *
	 * @return string
	 */
	public function buildForm(array $formItems, array $formAdditionalProps = []): string
	{
		return $this->getForm($formItems, $formAdditionalProps);
	}

	/**
	 * Build settings form.
	 *
	 * @param array<int, array<string, mixed>> $formItems Form array.
	 * @param array<string, string|int> $formAdditionalProps Additional attributes for form component.
	 *
	 * @return string
	 */
	public function buildSettingsForm(array $formItems, array $formAdditionalProps = []): string
	{
		$formContent = '';

		// Add divider on the bottom of every form.
		$formContent .= Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'divider',
			Components::props('divider', []),
			'',
			true
		);

		// Add submit on the bottom of every form.
		$formContent .= Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'submit',
			Components::props('submit', [
				'additionalClass' => 'es-submit--global',
				'submitValue' => __('Save settings', 'eightshift-forms'),
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
		return $this->getForm($formItems, $formAdditionalProps, $formContent);
	}

	/**
	 * Returns the current admin page url for refresh.
	 *
	 * @return string
	 */
	protected function getAdminRefreshUrl(): string
	{
		$request = isset($_SERVER['REQUEST_URI']) ? \sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return admin_url(sprintf(basename($request)));
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
	private function getForm(array $formItems, array $formAdditionalProps = [], string $formContent = ''): string
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
			$formProps = array_merge($formProps, $formAdditionalProps);
		}

		// Build form component.
		return Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'form',
			Components::props('form', [], $formProps),
			'',
			true
		);
	}

	/**
	 * Build components from arrya of items.
	 *
	 * @param array<string, mixed> $attributes Array of form components.
	 *
	 * @return string
	 */
	public function buildComponent(array $attributes): string
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
			$component === 'group'
		) {
			$output = '';
			switch ($component) {
				case 'checkboxes':
					$key = 'checkboxesContent';
					break;
				case 'radios':
					$key = 'radiosContent';
					break;
				case 'select':
					$key = 'selectOptions';
					break;
				case 'group':
					$key = 'groupContent';
					break;
				default:
					$key = '';
					break;
			}

			// Loop children and do the same ad on top level.
			foreach ($attributes[$key] as $innerKey => $item) {
				// Determine the component's name.
				$innerComponent = $item['component'] ? HelpersComponents::kebabToCamelCase($item['component']) : '';

				// Add new nesting iteration if component is group.
				if ($key === 'groupContent' && is_array($item["groupContent"])) {
					$groupOutput = '';
					foreach ($item["groupContent"] as $group) {
						$groupOutput .= $this->buildComponent($group);
					}

					$item["groupContent"] = $groupOutput;
				}

				// Build child component.
				$output .= Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$item['component'],
					Components::props($innerComponent, $item),
					'',
					true
				);
			}

			// Output child to the parent array.
			$attributes[$key] = $output;
		}

		$additionalAttributes = [];

		if (isset($attributes['additionalFieldClass'])) {
			$additionalAttributes['additionalFieldClass'] = $attributes['additionalFieldClass'];
		}

		// Build the component.
		return Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$attributes['component'],
			array_merge(
				Components::props($component, $attributes),
				$additionalAttributes
			),
			'',
			true
		);
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
		if (has_filter($filterName)) {
			$value = \apply_filters($filterName, $type, $formId) ?? '';

			// Ignore filter if empty.
			if (!empty($value)) {
				$output = $value;
			}
		}

		return $output ?? '';
	}
}
