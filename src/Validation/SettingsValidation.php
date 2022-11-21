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
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsValidation class.
 */
class SettingsValidation implements SettingInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Custom validation patterns.
	 */
	public const VALIDATION_PATTERNS = [
		'MM/DD' => '^(1[0-2]|0[1-9])\/(3[01]|[12][0-9]|0[1-9])$',
		'DD/MM' => '^(3[01]|[12][0-9]|0[1-9])\/(1[0-2]|0[1-9])$'
	];

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

		$local = \array_flip(Labels::ALL_LOCAL_LABELS);

		// List all labels for settings override.
		foreach ($this->labels->getLabels() as $key => $label) {
			if (!isset($local[$key])) {
				continue;
			}

			$output[] = [
				'component' => 'input',
				'inputName' => $this->getSettingsName($key),
				'inputId' => $this->getSettingsName($key),
				'inputFieldLabel' => \ucfirst($key),
				'inputPlaceholder' => $label,
				'inputValue' => $this->getSettingsValue($key, $formId),
			];
		}

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Success', 'eightshift-forms'),
						'tabContent' => $output,
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
		foreach (self::VALIDATION_PATTERNS as $key => $value) {
			$validationPatterns .= "<li><code>{$key} : {$value}</code></li>";
		}

		$labels = \array_flip(Labels::ALL_LOCAL_LABELS);

		$messagesOutput = [
			[
				'component' => 'intro',
				'introSubtitle' => \__('In these settings, you can change all validation messages. These settings will be used on all forms.', 'eightshift-forms'),
			],
		];
		// List all labels for settings override.
		foreach ($this->labels->getLabels() as $key => $label) {
			if (isset($labels[$key])) {
				continue;
			}

			$messagesOutput[] = [
				'component' => 'input',
				'inputName' => $this->getSettingsName($key),
				'inputId' => $this->getSettingsName($key),
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
						'tabLabel' => \__('Patterns', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'textarea',
								'textareaId' => $this->getSettingsName(self::SETTINGS_VALIDATION_PATTERNS_KEY),
								'textareaIsMonospace' => true,
								'textareaFieldLabel' => \__('Validation patterns', 'eightshift-forms'),
								// translators: %s will be replaced with local validation patterns.
								'textareaFieldHelp' => Helper::minifyString(\sprintf(\__("
									Custom validation patterns that are defined in this field can be selected inside the Form editor.<br />
									If you need help with writing regular expressions (<i>regex</i>), <a href='%1\$s' target='_blank' rel='noopener noreferrer'>take a look at regex101.com</a>.<br /><br />
									One validation pattern should be provided per line, in the following format:<br />
									<code>pattern-name : pattern </code><br /><br />
									Here are some examples:
									<ul>
									%2\$s
									</ul>", 'eightshift-forms'), 'https://regex101.com/', $validationPatterns)),
								'textareaValue' => $this->getOptionValue(self::SETTINGS_VALIDATION_PATTERNS_KEY),
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
