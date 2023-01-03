<?php

/**
 * Moments integration class.
 *
 * @package EightshiftForms\Integrations\Moments
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Moments;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Moments integration class.
 */
class Moments extends AbstractFormBuilder implements MapperInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter form fields.
	 *
	 * @var string
	 */
	public const FILTER_FORM_FIELDS_NAME = 'es_moments_form_fields_filter';

	/**
	 * Instance variable for Moments data.
	 *
	 * @var ClientInterface
	 */
	protected $momentsClient;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $momentsClient Inject Moments which holds Moments connect data.
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 */
	public function __construct(
		ClientInterface $momentsClient,
		ValidatorInterface $validator
	) {
		$this->momentsClient = $momentsClient;
		$this->validator = $validator;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		// Blocks string to value filter name constant.
		\add_filter(static::FILTER_FORM_FIELDS_NAME, [$this, 'getFormFields'], 11, 2);
	}

	public function getFormBlockGrammarArray(string $formId, string $itemId): array
	{
		return [];
	}

	/**
	 * Get mapped form fields.
	 *
	 * @param string $formId Form Id.
	 * @param bool $ssr Does form load using ssr.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFormFields(string $formId, bool $ssr = false): array
	{
		// Get item Id.
		$itemId = $this->getSettingsValue(SettingsMoments::SETTINGS_MOMENTS_LIST_KEY, (string) $formId);
		if (empty($itemId)) {
			return [];
		}

		// Get fields.
		$fields = $this->momentsClient->getItem($itemId);
		if (empty($fields)) {
			return [];
		}

		return $this->getFields($fields, $formId, $ssr);
	}

	/**
	 * Map Moments fields to our components.
	 *
	 * @param array<string, mixed> $data Fields.
	 * @param string $formId Form ID.
	 * @param bool $ssr Does form load using ssr.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $data, string $formId, bool $ssr): array
	{
		$output = [];

		if (!$data) {
			return $output;
		}

		$fields = $data['fields'] ?? [];

		if (!$fields) {
			return $output;
		}

		foreach ($fields as $field) {
			if (empty($field)) {
				continue;
			}

			$type = $field['component'] ? \strtolower($field['component']) : '';
			$name = $field['fieldId'] ?? '';
			$label = $field['label'] ?? '';
			$placeholder = $field['placeholder'] ?? '';
			$id = $name;
			$options = $field['options'] ?? [];
			$isRequired = $field['isRequired'] ?? false;
			$isHidden = $field['isHidden'] ?? false;

			switch ($type) {
				case 'text':
				case 'msisdn':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => $isHidden ? 'hidden' : 'text',
						'inputIsRequired' => $isRequired,
						'inputPlaceholder' => $placeholder,
						'blockSsr' => $ssr,
					];
					break;
				case 'date':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => $isHidden ? 'hidden' : 'date',
						'inputIsRequired' => $isRequired,
						'inputPlaceholder' => $placeholder,
						'inputValidationPattern' => $this->validator->getValidationPattern('momentsDate'),
						'blockSsr' => $ssr,
					];
					break;
				case 'datetime':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => $isHidden ? 'hidden' : 'datetime',
						'inputIsRequired' => $isRequired,
						'inputPlaceholder' => $placeholder,
						'inputValidationPattern' => $this->validator->getValidationPattern('momentsDateTime'),
						'blockSsr' => $ssr,
					];
					break;
				case 'email':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputFieldHidden' => $isHidden,
						'inputType' => $isHidden ? 'hidden' : 'email',
						'inputIsRequired' => $isRequired,
						'inputPlaceholder' => $placeholder,
						'inputValidationPattern' => $this->validator->getValidationPattern('momentsEmail'),
						'blockSsr' => $ssr,
					];
					break;
				case 'number':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputFieldHidden' => $isHidden,
						'inputType' => $isHidden ? 'hidden' : 'number',
						'inputPlaceholder' => $placeholder,
						'inputValidationPattern' => $this->validator->getValidationPattern('momentsNumber'),
						'blockSsr' => $ssr,
					];
					break;
				case 'textarea':
					$output[] = [
						'component' => 'textarea',
						'textareaFieldHidden' => $isHidden,
						'textareaFieldLabel' => $label,
						'textareaId' => $id,
						'textareaName' => $name,
						'textareaTracking' => $name,
						'textareaType' => 'textarea',
						'textareaIsRequired' => $isRequired,
						'textareaPlaceholder' => $placeholder,
						'blockSsr' => $ssr,
					];
					break;
				case 'dropdown':
					$output[] = [
						'component' => 'select',
						'selectFieldHidden' => $isHidden,
						'selectFieldLabel' => $label,
						'selectId' => $id,
						'selectName' => $name,
						'selectTracking' => $name,
						'selectType' => 'select',
						'selectPlaceholder' => $placeholder,
						'selectIsRequired' => $isRequired,
						'selectOptions' => \array_values(
							\array_merge(
								[
									[
										'component' => 'select-option',
										'selectOptionLabel' => \__('Select option', 'eightshift-forms'),
										'selectOptionValue' => ' ',
										'selectOptionIsSelected' => true,
										'selectOptionIsDisabled' => true,
									],
								],
								\array_map(
									static function ($selectOption) {
										return [
											'component' => 'select-option',
											'selectOptionLabel' => $selectOption['name'],
											'selectOptionValue' => $selectOption['value'],
										];
									},
									$options
								)
							)
						),
						'blockSsr' => $ssr,
					];
					break;
				case 'radiobutton':
					$output[] = [
						'component' => 'radios',
						'radiosFieldHidden' => $isHidden,
						'radiosId' => $id,
						'radiosName' => $name,
						'radiosFieldLabel' => $label,
						'radiosIsRequired' => $isRequired,
						'radiosContent' => \array_map(
							static function ($radio) use ($name) {
								return [
									'component' => 'radio',
									'radioLabel' => $radio['name'],
									'radioValue' => $radio['value'],
									'radioTracking' => $name,
								];
							},
							$options
						),
						'blockSsr' => $ssr,
					];
					break;
				case 'checkbox':
					$output[] = [
						'component' => 'checkboxes',
						'checkboxesFieldHideLabel' => true,
						'checkboxesId' => $id,
						'checkboxesName' => $name,
						'checkboxesContent' => [
							[
								'component' => 'checkbox',
								'checkboxLabel' => $label,
								'checkboxValue' => false,
								'checkboxTracking' => $name,
							],
						],
						'blockSsr' => $ssr,
					];
					break;
				case 'checkbox_group':
					$output[] = [
						'component' => 'checkboxes',
						'checkboxesFieldHidden' => $isHidden,
						'checkboxesId' => $id,
						'checkboxesName' => $name,
						'checkboxesFieldLabel' => $label,
						'checkboxesIsRequired' => $isRequired,
						'checkboxesContent' => \array_map(
							static function ($radio) use ($name) {
								return [
									'component' => 'checkbox',
									'checkboxLabel' => $radio['name'],
									'checkboxValue' => $radio['value'],
									'checkboxTracking' => $name,
								];
							},
							$options
						),
						'blockSsr' => $ssr,
					];
					break;
			}
		}

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitId' => 'submit',
			'submitFieldUseError' => false,
			'submitFieldOrder' => \count($output) + 1,
			'submitServerSideRender' => $ssr,
			'blockSsr' => $ssr,
		];

		// Change the final output if necesery.
		$dataFilterName = Filters::getIntegrationFilterName(SettingsMoments::SETTINGS_TYPE_KEY, 'data');
		if (\has_filter($dataFilterName) && !\is_admin()) {
			$output = \apply_filters($dataFilterName, $output, $formId) ?? [];
		}

		return $output;
	}
}
