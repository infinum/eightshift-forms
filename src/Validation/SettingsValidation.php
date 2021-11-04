<?php

/**
 * Validation Settings class.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsValidation class.
 */
class SettingsValidation implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Custom validation patterns.
	 */
	public const VALIDATION_PATTERNS = [
		'DD/MM' => '[1-12]{2}\/[01-31]{2}$',
		'MM/DD' => '[01-31]{2}\/[1-12]{2}$'
	];

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_validation';

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
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => __('Validation', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg"><g fill-rule="nonzero" fill="none"><path d="M15.82 22.09A7.9 7.9 0 017.91 30 7.9 7.9 0 010 22.09a7.9 7.9 0 017.91-7.91 7.9 7.9 0 017.91 7.91z" fill="#61D7A8"/><path d="M15.82 22.09A7.9 7.9 0 017.91 30V14.18a7.9 7.9 0 017.91 7.91z" fill="#00AB94"/><path fill="#FFF5F5" d="M11.162 20.947l-3.252 3.27-.879.879-2.373-2.39 1.23-1.231L7.032 22.6l.88-.88 2.02-2.003z"/><path fill="#EFE2DD" d="M11.162 20.947l-3.252 3.27V21.72l2.022-2.004z"/><path d="M23.115 18.064l-2.478 2.479c-.774.791-1.846 1.16-3.024 1.16-1.986 0-4.342-1.055-6.293-3.023-3.146-3.13-3.955-7.225-1.863-9.317l2.478-2.478 11.18 11.18z" fill="#FDBF00"/><path d="M23.115 18.064l-2.478 2.479c-.774.791-1.846 1.16-3.024 1.16-1.986 0-4.342-1.055-6.293-3.023l6.205-6.205 5.59 5.59z" fill="#FF9100"/><path d="M23.115 18.064c-.756.756-1.793 1.143-3.006 1.143-.457 0-.949-.053-1.459-.176-1.687-.387-3.41-1.388-4.851-2.83-1.441-1.441-2.444-3.164-2.83-4.851-.44-1.829-.088-3.428.966-4.465 1.653-1.67 4.676-1.512 7.506.369l3.305 3.305c1.881 2.83 2.04 5.853.37 7.505z" fill="#596C76"/><path d="M23.115 18.064c-.756.756-1.793 1.143-3.006 1.143-.457 0-.949-.053-1.459-.176-1.687-.387-3.41-1.388-4.851-2.83l7.295-7.295 1.652 1.653c1.881 2.83 2.04 5.853.37 7.505z" fill="#465A61"/><path d="M28.805 5.549l-9.768 7.91a2.43 2.43 0 01-1.758-.738 2.43 2.43 0 01-.738-1.758l7.904-9.774C25.57-.252 27.75-.428 29.086.908c1.336 1.318 1.178 3.498-.281 4.64z" fill="#FDBF00"/><path d="M28.805 5.549l-9.768 7.91a2.43 2.43 0 01-1.758-.738L29.086.908c1.336 1.318 1.178 3.498-.281 4.64z" fill="#FF9100"/></g></svg>',
		];
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
				'introTitle' => __('Form validation messages', 'eightshift-forms'),
				'introSubtitle' => __('Configure your form validation messages in one place.', 'eightshift-forms'),
			]
		];

		// List all labels for settings override.
		foreach ($this->labels->getLabels() as $key => $label) {
			$output[] = [
				'component' => 'input',
				'inputName' => $this->getSettingsName($key),
				'inputId' => $this->getSettingsName($key),
				'inputFieldLabel' => ucfirst($key),
				'inputPlaceholder' => $label,
				'inputValue' => $this->getSettingsValue($key, $formId),
			];
		}

		return $output;
	}

		/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$output = '';
		foreach (self::VALIDATION_PATTERNS as $key => $value) {
			$output .= "{$key} : {$value}<br/>";
		}

		return [
			[
				'component' => 'textarea',
				'textareaId' => $this->getSettingsName(self::SETTINGS_VALIDATION_PATTERNS_KEY),
				'textareaFieldLabel' => __('Validation Patterns', 'eightshift-forms'),
				'textareaFieldHelp' => __("
					List all your custom validation patterns here and they will show in the editor. Each item must be in a new line written like key value pair separated with colon(:) with space before and after.<br/><br/>
					Here are our predefined patterns that you can use: <br/><br/> {$output}", 'eightshift-forms'),
				'textareaValue' => $this->getOptionValue(self::SETTINGS_VALIDATION_PATTERNS_KEY),
			],
		];
	}
}
