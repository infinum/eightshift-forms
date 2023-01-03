<?php

/**
 * Hubspot Mapper integration class.
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Hubspot;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Hubspot integration class.
 */
class Hubspot extends AbstractFormBuilder implements MapperInterface
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
	public const FILTER_FORM_FIELDS_NAME = 'es_hubspot_form_fields_filter';

	/**
	 * Instance variable for Hubspot data.
	 *
	 * @var HubspotClientInterface
	 */
	protected $hubspotClient;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Create a new instance.
	 *
	 * @param HubspotClientInterface $hubspotClient Inject Hubspot which holds Hubspot connect data.
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 */
	public function __construct(
		HubspotClientInterface $hubspotClient,
		ValidatorInterface $validator
	) {
		$this->hubspotClient = $hubspotClient;
		$this->validator = $validator;
	}

	public function getFormFields(string $formId, bool $ssr = false): array
	{
		return [];
	}

	/**
	 * Get Hubspot mapped form fields for block editor grammar.
	 *
	 * @param string $formId Form Id.
	 * @param string $itemId Integration item id.
	 *
	 * @return array
	 */
	public function getFormBlockGrammarArray(string $formId, string $itemId): array
	{
		$output = [
			'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
			'fields' => [],
		];

		// Get fields.
		$item = $this->hubspotClient->getItem($itemId);
		if (empty($item)) {
			return $output;
		}

		$fields = $this->getFields($item, $formId);

		if (!$fields) {
			return $output;
		}

		$output['itemId'] = $itemId;
		$output['fields'] = $fields;

		return $output;
	}

	/**
	 * Map Hubspot fields to our components.
	 *
	 * @param array<string, mixed> $data Item object.
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

		if (!$data['fields']) {
			return $output;
		}

		// Find local but fallback to global settings.
		$allowedFileTypes = $this->getSettingsValue(SettingsHubspot::SETTINGS_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY, $formId) ?: $this->getOptionValue(SettingsHubspot::SETTINGS_GLOBAL_HUBSPOT_UPLOAD_ALLOWED_TYPES_KEY);  // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		if ($allowedFileTypes) {
			$allowedFileTypes = \str_replace('.', '', $allowedFileTypes);
			$allowedFileTypes = \str_replace(' ', '', $allowedFileTypes);
		}

		foreach ($data['fields'] as $key => $item) {
			if (empty($item)) {
				continue;
			}

			$richText = $item['richText']['content'] ?? '';

			if ($richText) {
				$output[] = [
					'component' => 'rich-text',
					'richTextId' => "rich-text-{$key}",
					'richTextName' => "rich-text-{$key}",
					'richTextFieldLabel' => \__('Rich text', 'eightshift-forms') . '-' . $key,
					'richTextContent' => $richText,
				];
			}

			$fields = $item['fields'] ?? [];

			if (!$fields) {
				continue;
			}

			foreach ($fields as $field) {
				$objectTypeId = $field['objectTypeId'] ?? '';
				$name = $field['name'] ?? '';
				$label = $field['label'] ?? '';
				$type = $field['fieldType'] ?? '';
				$required = $field['required'] ?? false;
				$value = $field['defaultValue'] ?? '';
				$placeholder = $field['placeholder'] ?? '';
				$options = $field['options'] ?? '';
				$validation = $field['validation']['data'] ?? '';
				$id = $name;
				$metaData = $field['metaData'] ?? [];
				$enabled = $field['enabled'] ?? true;
				$hidden = $field['hidden'] ?? false;

				$validation = \explode(':', $validation);
				$min = $validation[0] ?? '';
				$max = $validation[1] ?? '';

				if (!$enabled) {
					continue;
				}

				switch ($type) {
					case 'text':
						$item = [
							'component' => 'input',
							'inputFieldHidden' => $hidden,
							'inputFieldLabel' => $label,
							'inputId' => $id,
							'inputName' => $name,
							'inputTracking' => $name,
							'inputType' => $hidden ? 'hidden' : 'text',
							'inputPlaceholder' => $placeholder,
							'inputIsRequired' => $required,
							'inputValue' => $value,
							'inputAttrs' => [
								'data-object-type-id' => $objectTypeId,
							],
							'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
								$required ? 'inputIsRequired' : '',
								$min ? 'inputMinLength' : '',
								$max ? 'inputMaxLength' : '',
							]),
						];

						if ($min) {
							$item['inputMinLength'] = (int) $min;
						}

						if ($max) {
							$item['inputMaxLength'] = (int) $max;
						}

						if ($name === 'email') {
							$item['inputValidationPattern'] = 'simpleEmail';
							$item['inputType'] = $hidden ? 'hidden' : 'email';
						}

						$output[] = $item;
						break;
					case 'number':
						$item = [
							'component' => 'input',
							'inputFieldHidden' => $hidden,
							'inputName' => $name,
							'inputTracking' => $name,
							'inputFieldLabel' => $label,
							'inputId' => $id,
							'inputType' => $hidden ? 'hidden' : 'number',
							'inputIsRequired' => $required,
							'inputValue' => $value,
							'inputAttrs' => [
								'data-object-type-id' => $objectTypeId,
							],
							'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
								$required ? 'inputIsRequired' : '',
								$min ? 'inputMin' : '',
								$max ? 'inputMax' : '',
							]),
						];

						if ($min) {
							$item['inputMin'] = $min;
						}

						if ($max) {
							$item['inputMax'] = $max;
						}

						$output[] = $item;
						break;
					case 'textarea':
						$output[] = [
							'component' => 'textarea',
							'textareaFieldHidden' => $hidden,
							'textareaFieldLabel' => $label,
							'textareaId' => $id,
							'textareaName' => $name,
							'textareaTracking' => $name,
							'textareaType' => 'textarea',
							'textareaPlaceholder' => $placeholder,
							'textareaIsRequired' => $required,
							'textareaValue' => $value,
							'textareaAttrs' => [
								'data-object-type-id' => $objectTypeId,
							],
							'textareaDisabledOptions' => $this->prepareDisabledOptions('textarea', [
								$required ? 'textareaIsRequired' : '',
							]),
						];
						break;
					case 'file':
						$isMultiple = \array_filter(
							$metaData,
							static function ($item) {
								$name = $item['name'] ?? '';
								$value = $item['value'] ?? '';
								return $name === 'isMultipleFileUpload' && $value === 'true';
							}
						);

						$fileOutput = [
							'component' => 'file',
							'fileFieldHidden' => $hidden,
							'fileFieldLabel' => $label,
							'fileId' => $id,
							'fileName' => $name,
							'fileTracking' => $name,
							'fileType' => 'text',
							'filePlaceholder' => $placeholder,
							'fileIsRequired' => $required,
							'fileValue' => $value,
							'fileIsMultiple' => !empty($isMultiple),
							'fileAttrs' => [
								'data-object-type-id' => $objectTypeId,
							],
							'fileDisabledOptions' => $this->prepareDisabledOptions('file', [
								$required ? 'fileIsRequired' : '',
								$allowedFileTypes ? 'fileAccept' : '',
							]),
						];

						if ($allowedFileTypes) {
							$fileOutput['fileAccept'] = $allowedFileTypes;
						}

						$output[] = $fileOutput;
						break;
					case 'select':
						$selectedOption = $field['selectedOptions'][0] ?? '';

						$output[] = [
							'component' => 'select',
							'selectFieldHidden' => $hidden,
							'selectFieldLabel' => $label,
							'selectId' => $id,
							'selectName' => $name,
							'selectTracking' => $name,
							'selectType' => 'select',
							'selectPlaceholder' => $placeholder,
							'selectIsRequired' => $required,
							'selectValue' => $value,
							'selectAttrs' => [
								'data-object-type-id' => $objectTypeId,
							],
							'selectOptions' => \array_values(
								\array_map(
									function ($selectOption) use ($selectedOption) {
										return [
											'component' => 'select-option',
											'selectOptionIsSelected' => !empty($selectedOption) && $selectOption['value'] === $selectedOption,
											'selectOptionLabel' => $selectOption['label'],
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
								$required ? 'selectIsRequired' : '',
							]),
						];
						break;
					case 'booleancheckbox':
						$selectedOption = $field['selectedOptions'][0] ?? false;

						$output[] = [
							'component' => 'checkboxes',
							'checkboxesFieldHidden' => $hidden,
							'checkboxesId' => $id,
							'checkboxesName' => $name,
							'checkboxesIsRequired' => $required,
							'checkboxesContent' => [
								[
									'component' => 'checkbox',
									'checkboxLabel' => $label,
									'checkboxTracking' => $name,
									'checkboxIsChecked' => (bool) $selectedOption,
									'checkboxAttrs' => [
										'data-object-type-id' => $objectTypeId,
									],
								]
							],
							'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
								$required ? 'checkboxesIsRequired' : '',
							]),
						];
						break;
					case 'checkbox':
						$selectedOption = $field['selectedOptions'] ?? [];

						$output[] = [
							'component' => 'checkboxes',
							'checkboxesFieldHidden' => $hidden,
							'checkboxesId' => $id,
							'checkboxesName' => $name,
							'checkboxesFieldLabel' => $label,
							'checkboxesIsRequired' => $required,
							'checkboxesContent' => \array_map(
								function ($checkbox) use ($name, $objectTypeId, $selectedOption) {
									return [
										'component' => 'checkbox',
										'checkboxLabel' => $checkbox['label'],
										'checkboxValue' => $checkbox['value'],
										'checkboxIsChecked' => \in_array($checkbox['value'], $selectedOption, true),
										'checkboxTracking' => $name,
										'checkboxAttrs' => [
											'data-object-type-id' => $objectTypeId,
										],
										'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
											'checkboxValue',
										], false),
									];
								},
								$options
							),
							'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
								$required ? 'checkboxesIsRequired' : '',
							]),
						];
						break;
					case 'radio':
						$selectedOption = $field['selectedOptions'] ?? [];

						$output[] = [
							'component' => 'radios',
							'radiosFieldHidden' => $hidden,
							'radiosId' => $id,
							'radiosName' => $name,
							'radiosFieldLabel' => $label,
							'radiosIsRequired' => $required,
							'radiosContent' => \array_map(
								function ($radio) use ($name, $objectTypeId, $selectedOption) {
									return [
										'component' => 'radio',
										'radioIsChecked' => \in_array($radio['value'], $selectedOption, true),
										'radioLabel' => $radio['label'],
										'radioValue' => $radio['value'],
										'radioTracking' => $name,
										'radioAttrs' => [
											'data-object-type-id' => $objectTypeId,
										],
										'radioDisabledOptions' => $this->prepareDisabledOptions('radio', [
											'radioValue',
										], false),
									];
								},
								$options
							),
							'radiosDisabledOptions' => $this->prepareDisabledOptions('radios', [
								$required ? 'radiosIsRequired' : '',
							]),
						];
						break;
					case 'consent':
						$output[] = [
							'component' => 'checkboxes',
							'checkboxesFieldHidden' => $hidden,
							'checkboxesFieldBeforeContent' => $field['beforeText'] ?? '',
							'checkboxesFieldAfterContent' => $field['afterText'] ?? '',
							'checkboxesId' => $id,
							'checkboxesFieldHideLabel' => true,
							'checkboxesName' => $name,
							'checkboxesIsRequired' => $required,
							'checkboxesContent' => \array_map(
								function ($checkbox) use ($name, $objectTypeId) {
									return [
										'component' => 'checkbox',
										'checkboxLabel' => $checkbox['label'],
										'checkboxValue' => $checkbox['label'],
										'checkboxTracking' => $name,
										'checkboxAttrs' => [
											'data-object-type-id' => $objectTypeId,
										],
										'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
											'checkboxValue',
										], false),
									];
								},
								$options
							),
							'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
								$required ? 'checkboxesIsRequired' : '',
							]),
						];
						break;
				}
			}
		}

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitValue' => $data['submitButtonText'] ?? '',
			'submitId' => 'submit',
			'submitFieldUseError' => false,
		];

		// Change the final output if necesery.
		$dataFilterName = Filters::getIntegrationFilterName(SettingsHubspot::SETTINGS_TYPE_KEY, 'data');
		if (\has_filter($dataFilterName) && !\is_admin()) {
			$output = \apply_filters($dataFilterName, $output, $formId) ?? [];
		}

		return $output;
	}
}
