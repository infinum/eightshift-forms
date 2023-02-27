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
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Hubspot integration class.
 */
class Hubspot extends AbstractFormBuilder implements MapperInterface, ServiceInterface
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
	 * Create a new instance.
	 *
	 * @param HubspotClientInterface $hubspotClient Inject Hubspot which holds Hubspot connect data.
	 */
	public function __construct(HubspotClientInterface $hubspotClient)
	{
		$this->hubspotClient = $hubspotClient;
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
			'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
			'innerId' => $innerId,
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

		$output['fields'] = $fields;

		return $output;
	}

	/**
	 * Map fields to our components.
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

		foreach ($data['fields'] as $item) {
			if (empty($item)) {
				continue;
			}

			$fields = $item['fields'] ?? [];

			if (!$fields) {
				continue;
			}

			foreach ($fields as $field) {
				$objectTypeId = $field['objectTypeId'] ?? '';
				$description = $field['description'] ?? '';
				$name = $field['name'] ?? '';
				$label = $field['label'] ?? '';
				$type = $field['fieldType'] ?? '';
				$isRequired = isset($field['required']) ? (bool) $field['required'] : false;
				$value = $field['defaultValue'] ?? '';
				$placeholder = $field['placeholder'] ?? '';
				$options = $field['options'] ?? '';
				$validation = $field['validation']['data'] ?? '';
				$metaData = $field['metaData'] ?? [];
				$enabled = $field['enabled'] ?? true;
				$isHidden = isset($field['hidden']) ? (bool) $field['hidden'] : false;

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
							'inputFieldHidden' => $isHidden,
							'inputFieldLabel' => $label,
							'inputFieldHelp' => $description,
							'inputName' => $name,
							'inputTracking' => $name,
							'inputType' => 'text',
							'inputPlaceholder' => $placeholder,
							'inputIsRequired' => $isRequired,
							'inputValue' => $value,
							'inputAttrs' => [
								'data-object-type-id' => $objectTypeId,
							],
							'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
								$isRequired ? 'inputIsRequired' : '',
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
							$item['inputType'] = 'email';
							$item['inputDisabledOptions'][] = 'inputType';
							$item['inputDisabledOptions'][] = 'inputValidationPattern';
						}

						$output[] = $item;
						break;
					case 'date':
						$item = [
							'component' => 'date',
							'dateFieldHidden' => $isHidden,
							'dateFieldLabel' => $label,
							'dateFieldHelp' => $description,
							'dateName' => $name,
							'dateTracking' => $name,
							'dateType' => 'date',
							'datePlaceholder' => $placeholder,
							'dateIsRequired' => $isRequired,
							'dateValue' => $value,
							'datePreviewFormat' => Helper::getCorrectLibDateFormats($metaData[0]['value'], $metaData[1]['value']),
							'dateAttrs' => [
								'data-object-type-id' => $objectTypeId,
							],
							'dateDisabledOptions' => $this->prepareDisabledOptions('date', [
								$isRequired ? 'dateIsRequired' : '',
								$min ? 'dateMinLength' : '',
								$max ? 'dateMaxLength' : '',
								'dateOutputFormat',
							]),
						];

						if (isset($metaData[0]['value']) && isset($metaData[1]['value'])) {
							$item['dateOutputFormat'] = 'Y-m-d 00:00:00';
						}

						$output[] = $item;
						break;
					case 'number':
						$item = [
							'component' => 'input',
							'inputFieldHidden' => $isHidden,
							'inputFieldHelp' => $description,
							'inputName' => $name,
							'inputTracking' => $name,
							'inputFieldLabel' => $label,
							'inputType' => 'number',
							'inputIsRequired' => $isRequired,
							'inputValue' => $value,
							'inputAttrs' => [
								'data-object-type-id' => $objectTypeId,
							],
							'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
								$isRequired ? 'inputIsRequired' : '',
								$min ? 'inputMin' : '',
								$max ? 'inputMax' : '',
								'inputType',
							]),
						];

						if ($min) {
							$item['inputMin'] = (int) $min;
						}

						if ($max) {
							$item['inputMax'] = (int) $max;
						}

						$output[] = $item;
						break;
					case 'phonenumber':
						$item = [
							'component' => 'phone',
							'phoneFieldHidden' => $isHidden,
							'phoneFieldHelp' => $description,
							'phoneName' => $name,
							'phoneTracking' => $name,
							'phoneFieldLabel' => $label,
							'phoneIsRequired' => $isRequired,
							'phoneValue' => $value,
							'phoneAttrs' => [
								'data-object-type-id' => $objectTypeId,
							],
							'phoneDisabledOptions' => $this->prepareDisabledOptions('phone', [
								$isRequired ? 'phoneIsRequired' : '',
							]),
						];

						$output[] = $item;
						break;
					case 'textarea':
						$output[] = [
							'component' => 'textarea',
							'textareaFieldHidden' => $isHidden,
							'textareaFieldLabel' => $label,
							'textareaFieldHelp' => $description,
							'textareaName' => $name,
							'textareaTracking' => $name,
							'textareaPlaceholder' => $placeholder,
							'textareaIsRequired' => $isRequired,
							'textareaValue' => $value,
							'textareaAttrs' => [
								'data-object-type-id' => $objectTypeId,
							],
							'textareaDisabledOptions' => $this->prepareDisabledOptions('textarea', [
								$isRequired ? 'textareaIsRequired' : '',
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
							'fileFieldHidden' => $isHidden,
							'fileFieldLabel' => $label,
							'fileFieldHelp' => $description,
							'fileName' => $name,
							'fileTracking' => $name,
							'filePlaceholder' => $placeholder,
							'fileIsRequired' => $isRequired,
							'fileValue' => $value,
							'fileIsMultiple' => !empty($isMultiple),
							'fileAttrs' => [
								'data-object-type-id' => $objectTypeId,
							],
							'fileDisabledOptions' => $this->prepareDisabledOptions('file', [
								$isRequired ? 'fileIsRequired' : '',
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
							'selectFieldHidden' => $isHidden,
							'selectFieldLabel' => $label,
							'selectFieldHelp' => $description,
							'selectName' => $name,
							'selectTracking' => $name,
							'selectPlaceholder' => $placeholder,
							'selectIsRequired' => $isRequired,
							'selectValue' => $value,
							'selectAttrs' => [
								'data-object-type-id' => $objectTypeId,
							],
							'selectContent' => \array_values(
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
								$isRequired ? 'selectIsRequired' : '',
							]),
						];
						break;
					case 'booleancheckbox':
						$selectedOption = $field['selectedOptions'][0] ?? false;

						$output[] = [
							'component' => 'checkboxes',
							'checkboxesFieldHidden' => $isHidden,
							'checkboxesFieldHelp' => $description,
							'checkboxesName' => $name,
							'checkboxesIsRequired' => $isRequired,
							'checkboxesContent' => [
								[
									'component' => 'checkbox',
									'checkboxLabel' => $label,
									'checkboxTracking' => $name,
									'checkboxValue' => 'on',
									'checkboxIsChecked' => (bool) $selectedOption,
									'checkboxAttrs' => [
										'data-object-type-id' => $objectTypeId,
									],
									'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
										'checkboxValue',
									], false),
								]
							],
							'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
								$isRequired ? 'checkboxesIsRequired' : '',
							]),
						];
						break;
					case 'checkbox':
						$selectedOption = $field['selectedOptions'] ?? [];

						$output[] = [
							'component' => 'checkboxes',
							'checkboxesFieldHidden' => $isHidden,
							'checkboxesFieldHelp' => $description,
							'checkboxesName' => $name,
							'checkboxesFieldLabel' => $label,
							'checkboxesIsRequired' => $isRequired,
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
								$isRequired ? 'checkboxesIsRequired' : '',
							]),
						];
						break;
					case 'radio':
						$selectedOption = $field['selectedOptions'] ?? [];

						$output[] = [
							'component' => 'radios',
							'radiosFieldHidden' => $isHidden,
							'radiosFieldHelp' => $description,
							'radiosName' => $name,
							'radiosFieldLabel' => $label,
							'radiosIsRequired' => $isRequired,
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
								$isRequired ? 'radiosIsRequired' : '',
							]),
						];
						break;
					case 'consent':
						$output[] = [
							'component' => 'checkboxes',
							'checkboxesFieldHidden' => $isHidden,
							'checkboxesFieldBeforeContent' => $field['beforeText'] ?? '',
							'checkboxesFieldAfterContent' => $field['afterText'] ?? '',
							'checkboxesFieldHideLabel' => true,
							'checkboxesFieldHelp' => $description,
							'checkboxesName' => $name,
							'checkboxesIsRequired' => $isRequired,
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
								$isRequired ? 'checkboxesIsRequired' : '',
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
			'submitFieldUseError' => false,
			'submitDisabledOptions' => $this->prepareDisabledOptions('submit'),
		];

		// Change the final output if necesery.
		$filterName = Filters::getFilterName(['integrations', SettingsHubspot::SETTINGS_TYPE_KEY, 'data']);
		if (\has_filter($filterName) && \is_admin()) {
			$output = \apply_filters($filterName, $output, $formId) ?? [];
		}

		return $output;
	}
}
