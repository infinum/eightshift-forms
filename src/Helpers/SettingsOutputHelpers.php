<?php

/**
 * Class that holds all Settings output helpers.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\Config\Config;

/**
 * Class SettingsOutputHelpers
 */
final class SettingsOutputHelpers
{
	// --------------------------------------------------
	// Full component output helpers.
	// --------------------------------------------------
	/**
	 * No active feature settings output.
	 *
	 * @param string $type Settings/Integration type.
	 *
	 * @return array<string, string>
	 */
	public static function getIntro(string $type): array
	{
		$data = \apply_filters(Config::FILTER_SETTINGS_DATA, [])[$type]['labels'] ?? [];

		if (!$data) {
			return [];
		}

		return [
			'component' => 'intro',
			'introTitle' => $data['title'] ?? '',
			'introSubtitle' => $data['desc'] ?? '',
		];
	}

	/**
	 * No active feature settings output.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function getNoActiveFeature(): array
	{
		return [
			[
				'component' => 'highlighted-content',
				'highlightedContentTitle' => \__('Feature not active', 'eightshift-forms'),
				// translators: %s will be replaced with the global settings url.
				'highlightedContentSubtitle' => \sprintf(\__('Oh no it looks like this feature is not active, please go to your <a href="%s">dashboard</a> and activate it.', 'eightshift-forms'), GeneralHelpers::getSettingsGlobalPageUrl(Config::SLUG_ADMIN_DASHBOARD)),
				'highlightedContentIcon' => 'tools',
			],
		];
	}

	/**
	 * No active feature settings output.
	 *
	 * @param string $type Settings/Integration type.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function getNoValidGlobalConfig(string $type): array
	{
		$title = \apply_filters(Config::FILTER_SETTINGS_DATA, [])[$type]['labels']['title'] ?? [];

		if (!$title) {
			return [];
		}

		return [
			[
				'component' => 'highlighted-content',
				'highlightedContentTitle' => \__('Some config required', 'eightshift-forms'),
				// translators: %s will be replaced with the global settings url.
				'highlightedContentSubtitle' => \sprintf(\__('Before using %1$s you need to configure it in <a href="%2$s" target="_blank" rel="noopener noreferrer">global settings</a>.', 'eightshift-forms'), $title, GeneralHelpers::getSettingsGlobalPageUrl($type)),
				'highlightedContentIcon' => 'tools',
			],
		];
	}

	/**
	 * Settings output data mapped integration missing fields.
	 *
	 * @return array<string, mixed>
	 */
	public static function getDataMappedIntegrationMissingFields(): array
	{
		return [
			'component' => 'intro',
			'introSubtitle' => \__("Your form is missing form fields, please edit your form before making integration connection!", 'eightshift-forms'),
			'introIsHighlighted' => true,
			'introIsHighlightedImportant' => true,
		];
	}

	/**
	 * Settings output misc disclaimer.
	 *
	 * @param string $type Type of disclaimer.
	 *
	 * @return array<string, mixed>
	 */
	public static function getMiscDisclaimer(string $type = ''): array
	{
		return [
			'component' => 'layout',
			'layoutType' => 'layout-v-stack-card',
			'layoutContent' => [
				[
					'component' => 'intro',
					'introTitle' => \__('Disclaimer', 'eightshift-forms'),
					// translators: %s will be replaced with the type of disclaimer.
					'introSubtitle' => \sprintf(\__("Eightshift Forms doesn't configure the %s or any other third-party tools. However, enabling this feature adds necessary configurations in the backend for everything to function correctly.", 'eightshift-forms'), \esc_html($type)),
				],
			],
		];
	}

	/**
	 * Get settings input field with global variable.
	 *
	 * @param string $constantValue Constant value.
	 * @param string $optionName Option name.
	 * @param string $constantName Constant name.
	 * @param string $label Field label.
	 * @param string $help Field help.
	 *
	 * @return array<string, mixed>
	 */
	public static function getInputFieldWithGlobalVariable(
		string $constantValue,
		string $optionName,
		string $constantName,
		string $label,
		string $help = ''
	): array {
		$options = static::getOptionFieldWithConstant($constantValue, $optionName, $constantName);

		$internalHelp = !empty($help) ? $help . '<br/><br/>' : '';
		$optionsHelp = !empty($options['help']) ? "{$internalHelp}{$options['help']}" : $help;

		return [
			'component' => 'input',
			'inputName' => $options['name'],
			'inputFieldLabel' => $label,
			'inputType' => 'text',
			'inputIsRequired' => true,
			'inputFieldHelp' => $optionsHelp,
			'inputValue' => $options['value'],
			'inputIsDisabled' => $options['isDisabled'],
		];
	}

	/**
	 * Get settings password field with global variable.
	 *
	 * @param string $constantValue Constant value.
	 * @param string $optionName Option name.
	 * @param string $constantName Constant name.
	 * @param string $label Field label.
	 * @param string $help Field help.
	 *
	 * @return array<string, mixed>
	 */
	public static function getPasswordFieldWithGlobalVariable(
		string $constantValue,
		string $optionName,
		string $constantName,
		string $label,
		string $help = ''
	): array {
		$options = static::getOptionFieldWithConstant($constantValue, $optionName, $constantName);

		$general = [
			'component' => 'input',
			'inputName' => $options['name'],
			'inputFieldLabel' => $label,
			'inputIsRequired' => true,
			'inputFieldHelp' => "{$options['help']}{$help}",
			'inputIsDisabled' => $options['isDisabled'],
		];

		$isContantValueUsed = $options['isContantValueUsed'] ?? false;
		$value = $options['value'] ?? '';

		if ($isContantValueUsed) {
			// Show only last 3 characters.
			$visibleCharacters = 3;

			// Remove the last 3 characters from the total length.
			$valueLength = \strlen($value) - $visibleCharacters;

			// By default use the number of visible characters.
			$newValue = \str_repeat('*', \strlen($value));

			// If the value is longer than the visible characters, show only the last 3 characters and add * before.
			if ($valueLength >= $visibleCharacters) {
				$newValue = \str_repeat('*', $valueLength) . \substr($value, -$visibleCharacters);
			}

			return \array_merge(
				$general,
				[
					'inputType' => 'text',
					'inputValue' => $newValue,
				]
			);
		}

		return \array_merge(
			$general,
			[
				'inputType' => 'password',
				'inputValue' => $value,
			]
		);
	}

	/**
	 * Get option with constant.
	 *
	 * @param string $constantValue Constant value.
	 * @param string $optionName Option name.
	 * @param string $constantName Constant name.
	 *
	 * @return array<string, mixed>
	 */
	public static function getOptionFieldWithConstant(
		string $constantValue,
		string $optionName,
		string $constantName
	): array {
		$isDisabled = !empty($constantValue);
		$value = '';
		$isContantValueUsed = false;

		$option = SettingsHelpers::getOptionValue($optionName);

		if (empty($constantValue)) {
			$value = $option;
		} else {
			$value = $constantValue;
			$isContantValueUsed = true;
		}

		$helpOutput = '';

		if ($constantName) {
			// translators: %s will be replaced with global variable name.
			$helpOutput .= \sprintf(\__('
				<details class="is-filter-applied">
					<summary>Available global variables</summary>
					<ul>
						<li>%s</li>
					</ul>
					<br />
					This field value can also be set using a global variable via code.
				</details>', 'eightshift-forms'), $constantName);

			if ($isContantValueUsed) {
				$helpOutput = '<span class="is-filter-applied">' . \__('This field value is set with a global variable via code.', 'eightshift-forms') . '</span>';
			}
		}

		return [
			'name' => SettingsHelpers::getOptionName($optionName),
			'value' => $value,
			'isDisabled' => $isDisabled,
			'help' => $helpOutput,
			'constantValue' => $constantValue,
			'isContantValueUsed' => $isContantValueUsed,
		];
	}

	/**
	 * Setting output for Test api connection
	 *
	 * @param string $key Settings key.
	 *
	 * @return array<string, mixed>
	 */
	public static function getTestApiConnection(string $key): array
	{
		return [
			'component' => 'submit',
			'submitValue' => \__('Test API connection', 'eightshift-forms'),
			'submitVariant' => 'outline',
			'submitAttrs' => [
				UtilsHelper::getStateAttribute('testApiType') => $key,
			],
			'additionalClass' => UtilsHelper::getStateSelectorAdmin('testApi') . ' es-submit--api-test',
		];
	}

	/**
	 * Setting output for Test api connection
	 *
	 * @param string $url Oauth url.
	 * @param string $tokenKey Token key.
	 * @param string $allowKey Allow key.
	 *
	 * @return array<string, mixed>
	 */
	public static function getOauthConnection(string $url, string $tokenKey, string $allowKey): array
	{
		$token = SettingsHelpers::getOptionValue($tokenKey);
		$allowIsChecked = SettingsHelpers::isOptionCheckboxChecked($allowKey, $allowKey);

		$msg = isset($_GET['oauthMsg']) ? \sanitize_text_field(\wp_unslash($_GET['oauthMsg'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return [
			'component' => 'layout',
			'layoutType' => 'layout-v-stack-clean-full',
			'layoutContent' => [
				[
					'component' => 'checkboxes',
					'checkboxesFieldLabel' => '',
					'checkboxesName' => SettingsHelpers::getOptionName($allowKey),
					'checkboxesContent' => [
						[
							'component' => 'checkbox',
							'checkboxLabel' => \__('Enable Oauth connection', 'eightshift-forms'),
							'checkboxHelp' => \__('Due to security reasons, the Oauth connection should be disabled unless you are actively using it to connect to the application.', 'eightshift-forms'),
							'checkboxIsChecked' => $allowIsChecked,
							'checkboxValue' => $allowKey,
							'checkboxSingleSubmit' => true,
							'checkboxAsToggle' => true,
						]
					]
				],
				$allowIsChecked ? [
					'component' => 'intro',
					'introSubtitle' => \__('Make sure you turn off the Oauth connection when the connection is created or it will turn off automatically after 5 minutes if your Cron events are set correctly. ', 'eightshift-forms'),
					'introIsHighlighted' => true,
					'introIsHighlightedImportant' => true,
				] : [],
				[
					'component' => 'card-inline',
					'cardInlineTitle' => \__('Connect with Oauth', 'eightshift-forms'),
					'cardInlineSubTitle' => $token ? \__('Oauth connected.', 'eightshift-forms') : \__('Oauth connection required!', 'eightshift-forms'),
					'cardInlineRightContent' => [
						[
							'component' => 'submit',
							'submitValue' => \__('Oauth Connect', 'eightshift-forms'),
							'submitVariant' => $token ? 'success' : 'error',
							'submitButtonAsLink' => true,
							'submitButtonAsLinkUrl' => $url,
							'submitIsDisabled' => $allowIsChecked ? false : true,
						],
					],
				],
				$msg ? [
					'component' => 'intro',
					'introSubtitle' => $msg,
				] : [],
			],
		];
	}

	// --------------------------------------------------
	// Partials output helpers.
	// --------------------------------------------------

	/**
	 * Get response tags output copy.
	 *
	 * @param string $formFieldTags Response tags to output.
	 *
	 * @return string
	 */
	public static function getPartialFieldTags(string $formFieldTags): string
	{
		if (!$formFieldTags) {
			return '';
		}

		// translators: %s will be replaced with form field names.
		return \sprintf(\__('
			Use template tags to use submitted form data (e.g. <code>{field-name}</code>)
			<details class="is-filter-applied">
				<summary>Available tags</summary>
				<ul>
					%s
				</ul>
				<br />
				Tag missing? Make sure its field has a <b>Name</b> set!
			</details>', 'eightshift-forms'), $formFieldTags);
	}

	/**
	 * Get response tags copy output.
	 *
	 * @param string $formResponseTags Response tags to output.
	 *
	 * @return string
	 */
	public static function getPartialResponseTags(string $formResponseTags): string
	{
		if (!$formResponseTags) {
			return '';
		}

		// translators: %s will be replaced with integration response tags.
		return \sprintf(\__('
			<details class="is-filter-applied">
				<summary>Response tags</summary>
				<ul>
					%s
				</ul>
				<br />
				Use response tags to populate the content with the data that the integration sends back.
			</details>', 'eightshift-forms'), $formResponseTags);
	}

	/**
	 * Get all field names from the form.
	 *
	 * @param array<int, string> $fieldNames Form field IDs.
	 * @param string $wrapper Wrapper for the field name.
	 *
	 * @return string
	 */
	public static function getPartialFormFieldNames(array $fieldNames, string $wrapper = '{}'): string
	{
		$output = [];

		// Populate output.
		foreach ($fieldNames as $item) {
			switch ($wrapper) {
				case '$':
					$output[] = "<li><code>$" . $item . "</code></li>";
					break;
				default:
					$output[] = "<li><code>{" . $item . "}</code></li>";
					break;
			}
		}

		return \implode("\n", $output);
	}

	/**
	 * Get all field names from the form.
	 *
	 * @param string $formType Form type to check.
	 *
	 * @return string
	 */
	public static function getPartialFormResponseTags(string $formType): string
	{
		$tags = \apply_filters(Config::FILTER_SETTINGS_DATA, [])[$formType]['emailTemplateTags'] ?? [];

		if ($tags) {
			return self::getPartialFormFieldNames(\array_keys($tags));
		}

		return '';
	}

	/**
	 * Settings output data deactivated integration.
	 *
	 * @param string $key Key to return.
	 *
	 * @return string
	 */
	public static function getPartialDeactivatedIntegration(string $key): string
	{
		$output = [
			'checkboxLabel' => \__('Deactivate integration and send all the data to the fallback email.', 'eightshift-forms'),
			'checkboxHelp' => \__('If you choose to activate this option, the form integration will be disabled and all the data will be sent to the fallback email address set for the form.', 'eightshift-forms'),
			'introSubtitle' => \__('To ensure your form is not lost, it is important to activate the "Stop form syncing" option in the debug settings and avoid clicking on the form sync button.', 'eightshift-forms'),
		];

		return $output[$key] ?? '';
	}
}
