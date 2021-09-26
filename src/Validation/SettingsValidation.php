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
use EightshiftForms\Settings\Settings\SettingsTypeInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsValidation class.
 */
class SettingsValidation implements SettingsTypeInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use TraitHelper;

	/**
	 * Filter name key.
	 */
	public const FILTER_NAME = 'esforms_settings_validation';

	/**
	 * Settings key.
	 */
	public const TYPE_KEY = 'validation';

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
		\add_filter(self::FILTER_NAME, [$this, 'getSettingsTypeData']);
	}

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	public function getSettingsTypeData(string $formId): array
	{
		return [
			'sidebar' => [
				'label' => __('Validation', 'eightshift-forms'),
				'value' => self::TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => $this->getFields($formId),
		];
	}

	/**
	 * Get fields.
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
			],
		];

		// List all labels for settings override.
		foreach ($this->labels->getLabels() as $key => $label) {
			$output[] = [
				'component' => 'input',
				'inputName' => $this->getSettingsName($key),
				'inputId' => $this->getSettingsName($key),
				'inputFieldLabel' => ucfirst($key),
				'inputPlaceholder' => $label,
				'inputValue' => \get_post_meta($formId, $this->getSettingsName($key), true),
			];
		}

		return $output;
	}
}
