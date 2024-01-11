<?php

/**
 * Moments integration class.
 *
 * @package EightshiftForms\Integrations\Moments
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Moments;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Moments integration class.
 */
class Moments extends AbstractFormBuilder implements MapperInterface, ServiceInterface
{
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
			$isRequired = isset($field['isRequired']) ? (bool) $field['isRequired'] : false;
			$isHidden = isset($field['isHidden']) ? (bool) $field['isHidden'] : false;
			$validationRules = $field['validationRules'] ?? [];
			$validationMaxLength = $validationRules['maxLength'] ?? '';
			$validationPattern = $validationRules['pattern'] ?? '';

			switch ($type) {
				case 'text':
					switch ($name) {
						case 'country':
							$input = [
								'component' => 'country',
								'countryFieldLabel' => $label,
								'countryFieldHidden' => $isHidden,
								'countryName' => $name,
								'countryTracking' => $name,
								'countryIsRequired' => $isRequired,
								'countryTypeCustom' => 'country',
								'countryDisabledOptions' => $this->prepareDisabledOptions('country', [
									$isRequired ? 'countryIsRequired' : '',
									'countryTypeCustom',
								]),
							];
							break;
						default:
							$input = [
								'component' => 'input',
								'inputName' => $name,
								'inputTracking' => $name,
								'inputFieldLabel' => $label,
								'inputFieldHidden' => $isHidden,
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
							break;
					}

					$output[] = $input;
					break;
				case 'msisdn':
					$phone = [
						'component' => 'phone',
						'phoneName' => $name,
						'phoneTracking' => $name,
						'phoneFieldLabel' => $label,
						'phoneFieldHidden' => $isHidden,
						'phoneIsRequired' => $isRequired,
						'phoneIsNumber' => true,
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
					// Removed validation patterns because there is no need.
					$output[] = [
						'component' => 'date',
						'dateName' => $name,
						'dateTracking' => $name,
						'dateFieldLabel' => $label,
						'dateFieldHidden' => $isHidden,
						'dateType' => 'date',
						'datePreviewFormat' => 'F j, Y',
						'dateOutputFormat' => 'Z',
						'dateIsRequired' => $isRequired,
						'dateDisabledOptions' => $this->prepareDisabledOptions('date', [
							$isRequired ? 'dateIsRequired' : '',
							'dateType',
							'dateOutputFormat',
						]),
					];
					break;
				case 'datetime':
					// Removed validation patterns because there is no need.
					$output[] = [
						'component' => 'date',
						'dateName' => $name,
						'dateTracking' => $name,
						'dateFieldLabel' => $label,
						'dateFieldHidden' => $isHidden,
						'dateType' => 'datetime-local',
						'dateOutputFormat' => 'Z',
						'dateIsRequired' => $isRequired,
						'dateDisabledOptions' => $this->prepareDisabledOptions('date', [
							$isRequired ? 'dateIsRequired' : '',
							'dateType',
							'dateOutputFormat',
						]),
					];
					break;
				case 'email':
					$email = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputFieldHidden' => $isHidden,
						'inputType' => 'email',
						'inputIsEmail' => true,
						'inputIsRequired' => $isRequired,
						'inputValidationPattern' => $validationPattern ? 'momentsEmail' : '',
						'inputTypeCustom' => 'email',
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
							$validationMaxLength ? 'inputMaxLength' : '',
							$validationPattern ? 'inputValidationPattern' : '',
							'inputIsEmail',
							'inputType',
							'inputTypeCustom',
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
						'inputFieldHidden' => $isHidden,
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
						'textareaFieldHidden' => $isHidden,
						'textareaName' => $name,
						'textareaTracking' => $name,
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
					switch ($name) {
						case 'country':
							$dropdown = [
								'component' => 'country',
								'countryFieldLabel' => $label,
								'countryFieldHidden' => $isHidden,
								'countryName' => $name,
								'countryTracking' => $name,
								'countryIsRequired' => $isRequired,
								'countryTypeCustom' => 'country',
								'countryDisabledOptions' => $this->prepareDisabledOptions('country', [
									$isRequired ? 'countryIsRequired' : '',
									'countryTypeCustom',
								]),
							];
							break;
						default:
							$dropdown = [
								'component' => 'select',
								'selectFieldLabel' => $label,
								'selectFieldHidden' => $isHidden,
								'selectName' => $name,
								'selectPlaceholder' => \__('Select option', 'eightshift-forms'),
								'selectTracking' => $name,
								'selectIsRequired' => $isRequired,
								'selectContent' => \array_values(
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
								),
								'selectDisabledOptions' => $this->prepareDisabledOptions('select', [
									$isRequired ? 'selectIsRequired' : '',
								]),
							];
							break;
					}

					$output[] = $dropdown;
					break;
				case 'radiobutton':
					$output[] = [
						'component' => 'radios',
						'radiosName' => $name,
						'radiosFieldLabel' => $label,
						'radiosFieldHidden' => $isHidden,
						'radiosIsRequired' => $isRequired,
						'radiosTracking' => $name,
						'radiosContent' => \array_map(
							function ($radio) {
								return [
									'component' => 'radio',
									'radioLabel' => $radio['name'],
									'radioValue' => $radio['value'],
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
						'checkboxesFieldHidden' => $isHidden,
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
						'checkboxesFieldHidden' => $isHidden,
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
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsMoments::SETTINGS_TYPE_KEY, 'data']);
		if (\has_filter($filterName)) {
			$output = \apply_filters($filterName, $output, $formId) ?? [];
		}

		return $output;
	}
}
