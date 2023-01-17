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
use EightshiftForms\Validation\ValidationPatternsInterface;
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
	 * Instance variable of ValidationPatternsInterface data.
	 *
	 * @var ValidationPatternsInterface
	 */
	protected $validationPatterns;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $momentsClient Inject Moments which holds Moments connect data.
	 * @param ValidationPatternsInterface $validationPatterns Inject ValidationPatternsInterface which holds validation methods.
	 */
	public function __construct(
		ClientInterface $momentsClient,
		ValidationPatternsInterface $validationPatterns
	) {
		$this->momentsClient = $momentsClient;
		$this->validationPatterns = $validationPatterns;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		// Blocks string to value filter name constant.
		\add_filter(static::FILTER_FORM_FIELDS_NAME, [$this, 'getFormFields'], 10, 3);
	}

	/**
	 * Get mapped form fields from integration.
	 *
	 * @param string $formId Form Id.
	 * @param string $itemId Integration/external form ID.
	 * @param string $innerId Integration/external additional inner form ID.
	 *
	 * @return array<string, array<int, array<string, mixed>>|string>
	 */
	public function getFormFields(string $formId, string $itemId, string $innerId): array
	{
		$output = [
			'type' => SettingsMoments::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
			'innerId' => $innerId,
			'fields' => [],
		];

		// Get fields.
		$item = $this->momentsClient->getItem($itemId);

		if (empty($item)) {
			return $output;
		}

		$fields = $this->getFields($item, $formId);

		if (!$fields) {
			return $output;
		}

		$output['fields'] = $fields;

		return $output;
	}

	/**
	 * Map Moments fields to our components.
	 *
	 * @param array<string, mixed> $data Fields.
	 * @param string $formId Form ID.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $data, string $formId): array
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
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
						]),
					];
					break;
				case 'date':
					$pattern = $this->validationPatterns->getValidationPattern('momentsDate');

					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => $isHidden ? 'hidden' : 'date',
						'inputIsRequired' => $isRequired,
						'inputPlaceholder' => $placeholder,
						'inputValidationPattern' => $pattern,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
							$pattern ? 'inputValidationPattern' : '',
						]),
					];
					break;
				case 'datetime':
					$pattern = $this->validationPatterns->getValidationPattern('momentsDateTime');

					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => $isHidden ? 'hidden' : 'datetime',
						'inputIsRequired' => $isRequired,
						'inputPlaceholder' => $placeholder,
						'inputValidationPattern' => $pattern,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
							$pattern ? 'inputValidationPattern' : '',
						]),
					];
					break;
				case 'email':
					$pattern = $this->validationPatterns->getValidationPattern('momentsEmail');

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
						'inputValidationPattern' => $pattern,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
							$pattern ? 'inputValidationPattern' : '',
						]),
					];
					break;
				case 'number':
					$pattern = $this->validationPatterns->getValidationPattern('momentsNumber');

					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputFieldHidden' => $isHidden,
						'inputType' => $isHidden ? 'hidden' : 'number',
						'inputPlaceholder' => $placeholder,
						'inputValidationPattern' => $pattern,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
							$pattern ? 'inputValidationPattern' : '',
						]),
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
						'textareaDisabledOptions' => $this->prepareDisabledOptions('textarea', [
							$isRequired ? 'textareaIsRequired' : '',
						]),
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
						'selectContent' => \array_values(
							\array_merge(
								[
									[
										'component' => 'select-option',
										'selectOptionLabel' => \__('Select option', 'eightshift-forms'),
										'selectOptionValue' => ' ',
										'selectOptionIsSelected' => true,
										'selectOptionIsDisabled' => true,
										'selectOptionDisabledOptions' => $this->prepareDisabledOptions('select-option', [], false),
									],
								],
								\array_map(
									function ($selectOption) {
										return [
											'component' => 'select-option',
											'selectOptionLabel' => $selectOption['name'],
											'selectOptionValue' => $selectOption['value'],
											'selectOptionDisabledOptions' => $this->prepareDisabledOptions('select-option', [
												'selectOptionValue',
											], false),
										];
									},
									$options
								)
							)
						),
						'selectDisabledOptions' => $this->prepareDisabledOptions('select', [
							$isRequired ? 'selectIsRequired' : '',
						]),
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
							function ($radio) use ($name) {
								return [
									'component' => 'radio',
									'radioLabel' => $radio['name'],
									'radioValue' => $radio['value'],
									'radioTracking' => $name,
									'radioDisabledOptions' => $this->prepareDisabledOptions('radio', [
										'radioValue',
									], false),
								];
							},
							$options
						),
						'radiosDisabledOptions' => $this->prepareDisabledOptions('radios', [
							$isRequired ? 'radiosIsRequired' : '',
						]),
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
								'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [], false),
							],
						],
						'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes'),
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
							function ($radio) use ($name) {
								return [
									'component' => 'checkbox',
									'checkboxLabel' => $radio['name'],
									'checkboxValue' => $radio['value'],
									'checkboxTracking' => $name,
									'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
										'checkboxValue',
									], false),
								];
							},
							$options
						),
						'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
							$isRequired ? 'checkboxesIsRequired' : '',
						]),
					];
					break;
			}
		}

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitId' => 'submit',
			'submitFieldUseError' => false,
			'submitDisabledOptions' => $this->prepareDisabledOptions('submit'),
		];

		// Change the final output if necesery.
		$dataFilterName = Filters::getIntegrationFilterName(SettingsMoments::SETTINGS_TYPE_KEY, 'data');
		if (\has_filter($dataFilterName) && !\is_admin()) {
			$output = \apply_filters($dataFilterName, $output, $formId) ?? [];
		}

		return $output;
	}
}
