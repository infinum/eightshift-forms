<?php

/**
 * Airtable integration class.
 *
 * @package EightshiftForms\Integrations\Airtable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Airtable;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Airtable integration class.
 */
class Airtable extends AbstractFormBuilder implements MapperInterface, ServiceInterface
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
	public const FILTER_FORM_FIELDS_NAME = 'es_airtable_form_fields_filter';

	/**
	 * Instance variable for Airtable data.
	 *
	 * @var ClientInterface
	 */
	protected $airtableClient;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $airtableClient Inject Airtable which holds Airtable connect data.
	 */
	public function __construct(ClientInterface $airtableClient)
	{
		$this->airtableClient = $airtableClient;
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
			'type' => SettingsAirtable::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
			'innerId' => $innerId,
			'fields' => [],
		];

		// Get fields.
		$item = $this->airtableClient->getItem($itemId);

		if (empty($item)) {
			return $output;
		}

		$fields = $this->getFields($item[$innerId] ?? [], $formId);

		if (!$fields) {
			return $output;
		}

		$output['fields'] = $fields;

		return $output;
	}

	/**
	 * Map Airtable fields to our components.
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

		foreach ($data['fields'] as $field) {
			if (empty($field)) {
				continue;
			}

			$type = $field['type'] ?? '';
			$id = $field['id'] ?? '';
			$name = $id;
			$label = $field['name'] ?? '';
			$label = $field['name'] ?? '';
			$options = $field['options'] ?? [];

			switch ($type) {
				case 'singleLineText':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'text',
						'inputDisabledOptions' => $this->prepareDisabledOptions('input'),
					];
					break;
				case 'email':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'email',
						'inputIsEmail' => true,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							'inputIsEmail',
							'inputType',
							'inputTypeCustom',
						]),
						'inputTypeCustom' => 'email',
					];
					break;
				case 'url':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'url',
						'inputIsUrl' => true,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							'inputIsUrl',
							'inputType',
							'inputTypeCustom',
						]),
						'inputTypeCustom' => 'url',
					];
					break;
				case 'phoneNumber':
					$output[] = [
						'component' => 'phone',
						'phoneName' => $name,
						'phoneTracking' => $name,
						'phoneFieldLabel' => $label,
						'phoneDisabledOptions' => $this->prepareDisabledOptions('phone', [
							'phoneTypeCustom',
						]),
						'phoneTypeCustom' => 'phone',
					];
					break;
				case 'dateTime':
					$output[] = [
						'component' => 'date',
						'dateName' => $name,
						'dateTracking' => $name,
						'dateFieldLabel' => $label,
						'dateType' => 'datetime-local',
						'datePreviewFormat' => 'F j, Y',
						'dateOutputFormat' => 'Z',
						'dateDisabledOptions' => $this->prepareDisabledOptions('date', [
							'dateType',
							'dateOutputFormat',
						]),
					];
					break;
				case 'date':
					$output[] = [
						'component' => 'date',
						'dateName' => $name,
						'dateTracking' => $name,
						'dateFieldLabel' => $label,
						'dateType' => 'date',
						'datePreviewFormat' => 'F j, Y',
						'dateOutputFormat' => 'Y-m-d',
						'dateDisabledOptions' => $this->prepareDisabledOptions('date', [
							'dateType',
							'dateOutputFormat',
						]),
					];
					break;
				case 'number':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'number',
						'inputIsNumber' => true,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							'inputIsNumber',
							'inputType',
							'inputTypeCustom',
						]),
						'inputTypeCustom' => 'number',
					];
					break;
				case 'multilineText':
					$output[] = [
						'component' => 'textarea',
						'textareaName' => $name,
						'textareaTracking' => $name,
						'textareaFieldLabel' => $label,
						'textareatDisabledOptions' => $this->prepareDisabledOptions('textareat'),
					];
					break;
				case 'singleSelect':
					$output[] = [
						'component' => 'select',
						'selectName' => $name,
						'selectTracking' => $name,
						'selectFieldLabel' => $label,
						'selectContent' => \array_map(
							function ($selectOption) {
								return [
									'component' => 'select-option',
									'selectOptionLabel' => $selectOption['name'],
									'selectOptionValue' => $selectOption['id'],
									'selectOptionDisabledOptions' => $this->prepareDisabledOptions('selectOption', [
										'selectOptionValue',
									], false),
								];
							},
							$options['choices'] ?? []
						),
						'selectDisabledOptions' => $this->prepareDisabledOptions('select'),
					];
					break;
				case 'multipleSelects':
					$output[] = [
						'component' => 'checkboxes',
						'checkboxesName' => $name,
						'checkboxesTracking' => $name,
						'checkboxesFieldLabel' => $label,
						'checkboxesContent' => \array_map(
							function ($checkbox) {
								return [
									'component' => 'checkbox',
									'checkboxLabel' => $checkbox['name'],
									'checkboxValue' => $checkbox['id'],
									'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
										'checkboxValue'
									], false),
								];
							},
							$options['choices'] ?? []
						),
						'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
							'checkboxesTypeCustom',
						]),
						'checkboxesTypeCustom' => 'multiCheckbox',
					];
					break;
				case 'checkbox':
					$output[] = [
						'component' => 'checkboxes',
						'checkboxesName' => $name,
						'checkboxesTracking' => $name,
						'checkboxesFieldHideLabel' => true,
						'checkboxesContent' => [
							[
								'component' => 'checkbox',
								'checkboxLabel' => $label,
								'checkboxValue' => 'true',
								'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
									'checkboxValue',
								], false),
							]
						],
						'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
							'checkboxesTypeCustom',
						]),
						'checkboxesTypeCustom' => 'singleCheckbox',
					];
					break;
			}
		}

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitFieldUseError' => false,
		];

		// Change the final output if necesery.
		$filterName = Filters::getFilterName(['integrations', SettingsAirtable::SETTINGS_TYPE_KEY, 'data']);
		if (\has_filter($filterName) && \is_admin()) {
			$output = \apply_filters($filterName, $output, $formId) ?? [];
		}

		return $output;
	}
}
