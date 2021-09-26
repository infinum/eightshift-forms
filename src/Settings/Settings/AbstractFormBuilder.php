<?php

/**
 * Class that holds all methods for building form settings pages.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Helpers\TraitHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components as HelpersComponents;

/**
 * FormBuilder class.
 */
abstract class AbstractFormBuilder
{
	/**
	 * Use general helper trait.
	 */
	use TraitHelper;

	/**
	 * Undocumented function
	 *
	 * @param array $formItems
	 * @param string $formId
	 * @param boolean $refresh
	 * @return string
	 */
	public function buildForm(array $formItems, array $formAdditionalProps = []): string
	{
		return $this->getForm($formItems, $formAdditionalProps);
	}

	/**
	 * Undocumented function
	 *
	 * @param array $formItems
	 * @param string $formId
	 * @param boolean $refresh
	 * @return string
	 */
	public function buildSettingsForm(array $formItems, array $formAdditionalProps = []): string
	{
		$formContent = '';

		$formContent .= Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'divider',
			Components::props('divider', []),
			'',
			true
		);
		$formContent .= Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'submit',
			Components::props('submit', [
				'submitValue' => __('Save settings', 'eightshift-forms'),
			]),
			'',
			true
		);

		if (isset($formAdditionalProps['formSuccessRedirect']) && $formAdditionalProps['formSuccessRedirect']) {
			$request = isset($_SERVER['REQUEST_URI']) ? \sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$formAdditionalProps['formSuccessRedirect'] = admin_url(sprintf(basename($request)));
		}

		return $this->getForm($formItems, $formAdditionalProps, $formContent);
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
		$form = wp_remote_get($url);

		if (\is_wp_error($form)) {
			return '';
		}

		return $form['body'] ?? '';
	}

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	protected function getAdminRefreshUrl(): string
	{
		$request = isset($_SERVER['REQUEST_URI']) ? \sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return admin_url(sprintf(basename($request)));
	}

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
	private function getForm(array $formItems, array $formAdditionalProps = [], string $formContent = ''): string
	{
		$form = '';

		foreach ($formItems as $item) {
			$form .= $this->buildComponent($item);
		}

		if (!empty($formContent)) {
			$form .= $formContent;
		}

		$formProps = [
			'formContent' => $form,
		];

		if ($formAdditionalProps) {
			$formProps = array_merge($formProps, $formAdditionalProps);
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
	 *
	 * @return string
	 */
	private function buildComponent(array $attributes): string
	{
		$component = $attributes['component'] ? HelpersComponents::kebabToCamelCase($attributes['component']) : '';

		if ($component === 'checkboxes' || $component === 'selct' || $component === 'radios') {
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
			}

			foreach ($attributes[$key] as $item) {
				$innercComponent = $item['component'] ? HelpersComponents::kebabToCamelCase($item['component']) : '';
	
				$output .= Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$item['component'],
					Components::props($innercComponent,  $item),
					'',
					true
				);
			}

			$attributes[$key] = $output;
		}

		return Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$attributes['component'],
			Components::props($component, $attributes),
			'',
			true
		);
	}
}
