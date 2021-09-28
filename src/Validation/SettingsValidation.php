<?php

/**
 * Validation Settings class.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Helpers\TraitHelper;
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
	use TraitHelper;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_validation';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'validation';

	/**
	 * Instance variable of form labels data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Create a new instance.
	 *
	 * @param LabelsInterface $labels Inject documentsData which holds form labels data.
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
	}

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	public function getSettingsData(string $formId): array
	{
		return [
			'sidebar' => [
				'label' => __('Validation', 'eightshift-forms'),
				'value' => self::SETTINGS_TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => $this->getFields($formId),
		];
	}

	/**
	 * Get fields.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	public function getFields(string $formId): array
	{
		$output = [
			[
				'component' => 'intro',
				'introTitle' => \__('Form validation messages', 'eightshift-forms'),
				'introSubtitle' => \__('Configure your form validation messages in one place.', 'eightshift-forms'),
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
}
