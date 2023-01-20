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
	 * Create a new instance.
	 *
	 * @param ClientInterface $momentsClient Inject Moments which holds Moments connect data.
	 */
	public function __construct(ClientInterface $momentsClient)
	{
		$this->momentsClient = $momentsClient;
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
			$options = $field['options'] ?? [];
			$isRequired = $field['isRequired'] ?? false;
			$validationRules = $field['validationRules'] ?? [];
			$validationMaxLength = $validationRules['maxLength'] ?? '';
			$validationPattern = $validationRules['pattern'] ?? '';

			switch ($type) {
				case 'text':
					$input = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'text',
						'inputIsRequired' => $isRequired,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
							$validationMaxLength ? 'inputMaxLength' : '',
						]),
					];

					if ($validationMaxLength) {
						$input['inputMaxLength'] = (int) $validationMaxLength;
					}

					$output[] = $input;
					break;
				case 'msisdn':
					$phone = [
						'component' => 'phone',
						'phoneName' => $name,
						'phoneTracking' => $name,
						'phoneFieldLabel' => $label,
						'phoneIsNumber' => true,
						'phoneIsRequired' => $isRequired,
						'phoneDisabledOptions' => $this->prepareDisabledOptions('phone', [
							$isRequired ? 'phoneIsRequired' : '',
							$validationMaxLength ? 'phoneMaxLength' : '',
							'phoneIsNumber',
						]),
					];

					if ($validationMaxLength) {
						$phone['phoneMaxLength'] = (int) $validationMaxLength;
					}

					$output[] = $phone;
					break;
				case 'date':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'date',
						'inputIsRequired' => $isRequired,
						'inputValidationPattern' => $validationPattern ? 'momentsDate' : '',
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
							$validationPattern ? 'inputValidationPattern' : '',
							'inputType'
						]),
					];
					break;
				case 'datetime':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'datetime-local',
						'inputIsRequired' => $isRequired,
						'inputValidationPattern' => $validationPattern ? 'momentsDateTime' : '',
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
							$validationPattern ? 'inputValidationPattern' : '',
							'inputType'
						]),
					];
					break;
				case 'email':
					$email = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'email',
						'inputIsEmail' => true,
						'inputIsRequired' => $isRequired,
						'inputValidationPattern' => $validationPattern ? 'momentsEmail' : '',
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
							$validationMaxLength ? 'inputMaxLength' : '',
							$validationPattern ? 'inputValidationPattern' : '',
							'inputIsEmail',
							'inputType',
						]),
					];

					if ($validationMaxLength) {
						$email['inputMaxLength'] = (int) $validationMaxLength;
					}

					$output[] = $email;
					break;
				case 'number':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'number',
						'inputIsNumber' => true,
						'inputIsRequired' => $isRequired,
						'inputValidationPattern' => $validationPattern ? 'momentsNumber' : '',
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
							$validationPattern ? 'inputValidationPattern' : '',
							'inputIsNumber',
							'inputType',
						]),
					];
					break;
				case 'textarea':
					$textarea = [
						'component' => 'textarea',
						'textareaFieldLabel' => $label,
						'textareaName' => $name,
						'textareaTracking' => $name,
						'textareaType' => 'textarea',
						'textareaIsRequired' => $isRequired,
						'textareaDisabledOptions' => $this->prepareDisabledOptions('textarea', [
							$isRequired ? 'textareaIsRequired' : '',
							$validationMaxLength ? 'textareaMaxLength' : '',
						]),
					];

					if ($validationMaxLength) {
						$textarea['textareaMaxLength'] = (int) $validationMaxLength;
					}

					$output[] = $textarea;
					break;
				case 'dropdown':
					$output[] = [
						'component' => 'select',
						'selectFieldLabel' => $label,
						'selectName' => $name,
						'selectTracking' => $name,
						'selectType' => 'select',
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
						'checkboxesName' => $name,
						'checkboxesIsRequired' => $isRequired,
						'checkboxesContent' => [
							[
								'component' => 'checkbox',
								'checkboxLabel' => $label,
								'checkboxValue' => 'true',
								'checkboxTracking' => $name,
								'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
									'checkboxValue',
								], false),
							],
						],
						'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
							$isRequired ? 'checkboxesIsRequired' : '',
						]),
					];
					break;
				case 'checkbox_group':
					$output[] = [
						'component' => 'checkboxes',
						'checkboxesName' => $name,
						'checkboxesFieldLabel' => $label,
						'checkboxesIsRequired' => $isRequired,
						'checkboxesContent' => \array_map(
							function ($checkbox) use ($name) {
								return [
									'component' => 'checkbox',
									'checkboxLabel' => $checkbox['name'],
									'checkboxValue' => $checkbox['value'],
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
