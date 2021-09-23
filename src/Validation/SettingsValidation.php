<?php

/**
 * Validation Settings Options class.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Labels\InterfaceLabels;
use EightshiftForms\Settings\Settings\SettingsTypeInterface;
use EightshiftFormsPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Validation integration class.
 */
class SettingsValidation implements SettingsTypeInterface, ServiceInterface
{
	/**
	 * Use General helper trait.
	 */
	use TraitHelper;

	// Filter name key.
	public const FILTER_NAME = 'esforms_settings_validation';

	// Settings key.
	public const TYPE_KEY = 'validation';

	/**
	 * Instance variable of form labels data.
	 *
	 * @var InterfaceLabels
	 */
	protected $labels;

	/**
	 * Create a new instance.
	 *
	 * @param InterfaceLabels $labels Inject documentsData which holds form labels data.
	 */
	public function __construct(InterfaceLabels $labels)
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
	 * Get Form options array
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
			'form' => $this->getFields(),
		];
	}

	/**
	 * Get fields.
	 *
	 * @return array
	 */
	public function getFields(): array
	{
		$output = [
			[
				'component' => 'intro',
				'introTitle' => \__('Form validation messages', 'eightshift-forms'),
				'introSubtitle' => \__('Configure your form validation messages in one place.', 'eightshift-forms'),
			],
		];

		foreach ($this->labels->getLabels() as $key => $label) {
			$output[] = [
				'component' => 'input',
				'inputName' => $key,
				'inputId' => $key,
				'inputFieldLabel' => ucfirst($key),
				'inputPlaceholder' => $label,
			];
		}

		return $output;
	}
}
