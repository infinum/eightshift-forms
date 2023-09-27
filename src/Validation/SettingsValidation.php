<?php

/**
 * Validation Settings class.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Labels\Labels;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsValidation class.
 */
class SettingsValidation implements SettingInterface, SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

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
		$output = [
			[
				'component' => 'intro',
				// translators: %s will be replaced with the settings link.
				'introSubtitle' => \sprintf(\__('In these settings, you can change all validation success messages. Global validation options can be configured in the <a href="%s" target="_blank" rel="noopener noreferrer">global settings.</a>', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(self::SETTINGS_TYPE_KEY)),
			],
		];

		$formType = Helper::getFormTypeById($formId);

		if (!$formType) {
			return [];
		}

		$key = "{$formType}Success";

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Messages', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingName($key),
								'inputFieldLabel' => \ucfirst($key),
								'inputPlaceholder' => $this->labels->getLabels()[$key],
								'inputValue' => $this->getSettingValue($key, $formId),
							],
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
				'inputName' => $this->getOptionName($key),
				'inputFieldLabel' => \ucfirst($key),
				'inputPlaceholder' => $label,
				'inputValue' => $this->getOptionValue($key),
			];
		}

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => $this->getOptionName(self::SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use top level domain validation on all email fields', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isOptionCheckboxChecked(self::SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY, self::SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY),
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
								'textareaName' => $this->getOptionName(self::SETTINGS_VALIDATION_PATTERNS_KEY),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('Custom validation patterns', 'eightshift-forms'),
								// translators: %s will be replaced with local validation patterns.
								'textareaFieldHelp' => Helper::minifyString(\sprintf(\__("
									Patterns defined in this field can be selected in the Form editor.<br />
									If you need help with writing regular expressions (regex), <a href='%1\$s' target='_blank' rel='noopener noreferrer'>take a look at regex101.com</a>.<br /><br />
									Enter one pattern per line, in the following format:<br />
									<code>pattern-name : pattern : output</code><br /><br />
									Example:
									<ul>
									%2\$s
									</ul>", 'eightshift-forms'), 'https://regex101.com/', $validationPatterns)),
								'textareaValue' => $this->getOptionValueAsJson(self::SETTINGS_VALIDATION_PATTERNS_KEY, 3),
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
