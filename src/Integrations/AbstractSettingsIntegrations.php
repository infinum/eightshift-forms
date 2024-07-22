<?php

/**
 * Abstract class for shared functionality for all integrations.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations;

use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;

/**
 * Abstract class for shared functionality for all integrations.
 */
abstract class AbstractSettingsIntegrations
{
	/**
	 * Get global settings for the integration.
	 *
	 * @param string $integrationType Integration type.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	protected function getGlobalGeneralSettings(string $integrationType): array
	{
		$successRedirectUrl = FiltersOuputMock::getSuccessRedirectUrlFilterValue($integrationType, '');
		$variation = FiltersOuputMock::getVariationFilterValue($integrationType, '', []);

		return [
			[
				'component' => 'input',
				'inputName' => UtilsSettingsHelper::getOptionName($integrationType . '-' . SettingsGeneral::SETTINGS_GLOBAL_REDIRECT_SUCCESS_KEY),
				'inputFieldLabel' => \__('Redirect to URL', 'eightshift-forms'),
				'inputFieldHelp' => \sprintf(
					/* translators: %s is the integration type */
					\__('After a successful submission, the user will be redirected to the provided URL and the success message will <b>not</b> be shown.
					This settings will be used in all your %s form types.', 'eightshift-forms'),
					\ucfirst($integrationType)
				),
				'inputType' => 'url',
				'inputIsUrl' => true,
				'inputValue' => $successRedirectUrl['dataGlobal'],
			],
			[
				'component' => 'textarea',
				'textareaFieldLabel' => \__('Variation', 'eightshift-forms'),
				'textareaIsMonospace' => true,
				'textareaSaveAsJson' => true,
				'textareaName' => UtilsSettingsHelper::getSettingName($integrationType . '-' . SettingsGeneral::SETTINGS_VARIATION_KEY),
				'textareaFieldHelp' => \sprintf(
					/* translators: %1$s is the integration type, %2$s is the global settings link */
					\__('Define redirection value that you can use in your Result output items.<br />
					Each key must be in a separate line.<br />
					This settings will be used in all your %1$s form types.
					%2$s', 'eightshift-forms'),
					\ucfirst($integrationType),
					$variation['settingsGlobal'],
				),
				'textareaValue' => UtilsSettingsHelper::getOptionValueAsJson($integrationType . '-' . SettingsGeneral::SETTINGS_VARIATION_KEY, 2),
			],
		];
	}
}
