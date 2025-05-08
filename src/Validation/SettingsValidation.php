<?php

/**
 * Validation Settings class.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Labels\Labels;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Settings\SettingInterface;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsValidation class.
 */
class SettingsValidation implements SettingGlobalInterface, SettingInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_validation';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_validation';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'validation';

	/**
	 * Validation Patterns key.
	 */
	public const SETTINGS_VALIDATION_PATTERNS_KEY = 'validation-patterns';

	/**
	 * Validation use email tld key.
	 */
	public const SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY = 'validation-use-email-tld';

	/**
	 * Validation use submit once key.
	 */
	public const SETTINGS_VALIDATION_USE_SUBMIT_ONCE_KEY = 'validation-use-submit-once';

	/**
	 * Validation use submit only logged in key.
	 */
	public const SETTINGS_VALIDATION_USE_SUBMIT_ONLY_LOGGED_IN_KEY = 'validation-use-submit-only-logged-in';

	/**
	 * Validation use only logged in key.
	 */
	public const SETTINGS_VALIDATION_USE_ONLY_LOGGED_IN_KEY = 'validation-use-only-logged-in';

	/**
	 * Instance variable for labels data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Create a new instance.
	 *
	 * @param LabelsInterface $labels Inject documentsData which holds labels data.
	 */
	public function __construct(LabelsInterface $labels)
	{
		$this->labels = $labels;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		$formType = GeneralHelpers::getFormTypeById($formId);

		if (!$formType) {
			return [];
		}

		$key = "{$formType}Success";

		$loggedInSubmit = SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_VALIDATION_USE_SUBMIT_ONLY_LOGGED_IN_KEY, self::SETTINGS_VALIDATION_USE_SUBMIT_ONLY_LOGGED_IN_KEY, $formId);

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Messages', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => SettingsHelpers::getSettingName($key),
								'inputFieldLabel' => \ucfirst($key),
								'inputPlaceholder' => $this->labels->getLabels()[$key],
								'inputValue' => SettingsHelpers::getSettingValue($key, $formId),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Users', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_VALIDATION_USE_ONLY_LOGGED_IN_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Show only to logged in users', 'eightshift-forms'),
										'checkboxHelp' => \__('The form will be accessible only to users who are logged in.', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_VALIDATION_USE_ONLY_LOGGED_IN_KEY, self::SETTINGS_VALIDATION_USE_ONLY_LOGGED_IN_KEY, $formId),
										'checkboxValue' => self::SETTINGS_VALIDATION_USE_ONLY_LOGGED_IN_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_VALIDATION_USE_SUBMIT_ONLY_LOGGED_IN_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Allow only logged in users to submit', 'eightshift-forms'),
										'checkboxHelp' => \__('If enabled, only logged in users can submit the form.', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_VALIDATION_USE_SUBMIT_ONLY_LOGGED_IN_KEY, self::SETTINGS_VALIDATION_USE_SUBMIT_ONLY_LOGGED_IN_KEY, $formId),
										'checkboxValue' => self::SETTINGS_VALIDATION_USE_SUBMIT_ONLY_LOGGED_IN_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($loggedInSubmit ? [
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => '',
									'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_VALIDATION_USE_SUBMIT_ONCE_KEY),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Use single submit per user', 'eightshift-forms'),
											'checkboxHelp' => \__('If enabled, each logged in user can submit the form only once.', 'eightshift-forms'),
											'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_VALIDATION_USE_SUBMIT_ONCE_KEY, self::SETTINGS_VALIDATION_USE_SUBMIT_ONCE_KEY, $formId),
											'checkboxValue' => self::SETTINGS_VALIDATION_USE_SUBMIT_ONCE_KEY,
											'checkboxSingleSubmit' => true,
											'checkboxAsToggle' => true,
										],
									],
								],
							] : []),
						],
					],
				],
			],
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$validationPatterns = '';
		foreach (ValidationPatterns::VALIDATION_PATTERNS as $pattern) {
			$validationPatterns .= "<li><code>{$pattern['label']} : {$pattern['value']} : {$pattern['output']}</code></li>";
		}

		$labels = \array_flip(Labels::ALL_LOCAL_LABELS);

		$messagesOutput = [
			[
				'component' => 'intro',
				'introSubtitle' => \__('Validation messages are shared between all forms.', 'eightshift-forms'),
			],
		];
		// List all labels for settings override.
		foreach ($this->labels->getLabels() as $key => $label) {
			if (isset($labels[$key])) {
				continue;
			}

			$messagesOutput[] = [
				'component' => 'input',
				'inputName' => SettingsHelpers::getOptionName($key),
				'inputFieldLabel' => \ucfirst($key),
				'inputPlaceholder' => $label,
				'inputValue' => SettingsHelpers::getOptionValue($key),
			];
		}

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('General', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use top level domain validation on all email fields', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY, self::SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY),
										'checkboxValue' => self::SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'textarea',
								'textareaName' => SettingsHelpers::getOptionName(self::SETTINGS_VALIDATION_PATTERNS_KEY),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('Custom validation patterns', 'eightshift-forms'),
								// translators: %s will be replaced with local validation patterns.
								'textareaFieldHelp' => GeneralHelpers::minifyString(\sprintf(\__("
									Patterns defined in this field can be selected in the Form editor.<br />
									If you need help with writing regular expressions (regex), <a href='%1\$s' target='_blank' rel='noopener noreferrer'>take a look at regex101.com</a>.<br /><br />
									Enter one pattern per line, in the following format:<br />
									<code>pattern-name : pattern : output</code><br /><br />
									Example:
									<ul>
									%2\$s
									</ul>", 'eightshift-forms'), 'https://regex101.com/', $validationPatterns)),
								'textareaValue' => SettingsHelpers::getOptionValueAsJson(self::SETTINGS_VALIDATION_PATTERNS_KEY, 3),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Messages', 'eightshift-forms'),
						'tabContent' => $messagesOutput,
					],
				]
			],
		];
	}
}
