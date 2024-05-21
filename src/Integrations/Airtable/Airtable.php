<?php

/**
 * Airtable integration class.
 *
 * @package EightshiftForms\Integrations\Airtable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Airtable;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Airtable integration class.
 */
class Airtable extends AbstractFormBuilder implements MapperInterface, ServiceInterface
{
	/**
	 * Filter form fields.
	 *
	 * @var string
	 */
	public const FILTER_FORM_FIELDS_NAME = 'es_airtable_form_fields_filter';

	/**
	 * Instance variable for Airtable data.
	 *
	 * @var AirtableClientInterface
	 */
	protected $airtableClient;

	/**
	 * Create a new instance.
	 *
	 * @param AirtableClientInterface $airtableClient Inject Airtable which holds Airtable connect data.
	 */
	public function __construct(AirtableClientInterface $airtableClient)
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

		// Recreate dynamic block data for frontend.
		\add_filter(UtilsHooksHelper::getFilterName(['block', 'dynamic', 'dataOutput']), [$this, 'getDynamicBlockOutput'], 10, 2);
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

		$fields = $this->getFields($item[$innerId] ?? [], $formId, $item, $itemId);

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
	 * @param array<string, mixed> $items Items.
	 * @param string $itemId Item ID.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $data, string $formId, array $items, string $itemId): array
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
						'inputTypeCustom' => 'email',
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							'inputIsEmail',
							'inputType',
							'inputTypeCustom',
						]),
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
						'inputTypeCustom' => 'url',
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							'inputIsUrl',
							'inputType',
							'inputTypeCustom',
						]),
					];
					break;
				case 'phoneNumber':
					$output[] = [
						'component' => 'phone',
						'phoneName' => $name,
						'phoneTracking' => $name,
						'phoneFieldLabel' => $label,
						'phoneTypeCustom' => 'phone',
						'phoneIsNumber' => true,
						'phoneDisabledOptions' => $this->prepareDisabledOptions('phone', [
							'phoneTypeCustom',
							'phoneIsNumber',
						]),
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
				case 'currency':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'number',
						'inputIsNumber' => true,
						'inputTypeCustom' => 'number',
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							'inputIsNumber',
							'inputType',
							'inputTypeCustom',
						]),
					];
					break;
				case 'multilineText':
					$output[] = [
						'component' => 'textarea',
						'textareaName' => $name,
						'textareaTracking' => $name,
						'textareaFieldLabel' => $label,
						'textareatDisabledOptions' => $this->prepareDisabledOptions('textarea'),
					];
					break;
				case 'singleSelect':
				case 'multipleSelects':
					$output[] = [
						'component' => 'select',
						'selectName' => $name,
						'selectTracking' => $name,
						'selectFieldLabel' => $label,
						'selectIsMultiple' => $type === 'multipleSelects',
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
						'selectDisabledOptions' => $this->prepareDisabledOptions('select', [
							'selectIsMultiple',
						]),
					];
					break;
				case 'checkbox':
					$output[] = [
						'component' => 'checkboxes',
						'checkboxesName' => $name,
						'checkboxesTracking' => $name,
						'checkboxesFieldHideLabel' => true,
						'checkboxesTypeCustom' => 'singleCheckbox',
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
					];
					break;
				case 'multipleRecordLinks':
					$linkedItemId = $field['options']['linkedTableId'] ?? '';
					if (!$linkedItemId) {
						break;
					}

					$linkedItem = $items[$linkedItemId] ?? [];
					if (!$linkedItem) {
						break;
					}

					$output[] = [
						'component' => 'dynamic',
						'dynamicType' => 'select',
						'dynamicName' => $name,
						'dynamicTracking' => $name,
						'dynamicIsDeactivated' => true,
						'dynamicFieldLabel' => $label,
						'dynamicTypeCustom' => 'dynamicSelect',
						// translators: %1$s will be replaced with field label and %2$s with field name.
						'dynamicCustomLabel' => \sprintf(\__('We will display dynamic data on the frontend for %1$s field - %2$s.', 'eightshift-forms'), $label, $name),
						'dynamicData' => \wp_json_encode([
							'baseId' => $itemId,
							'listId' => $linkedItemId,
						]),
						'dynamicDisabledOptions' => $this->prepareDisabledOptions('dynamic'),
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
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsAirtable::SETTINGS_TYPE_KEY, 'data']);
		if (\has_filter($filterName)) {
			$output = \apply_filters($filterName, $output, $formId) ?? [];
		}

		return $output;
	}

	/**
	 * Recreate dynamic block data for frontend.
	 *
	 * @param array<string, mixed> $attributes Attributes.
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getDynamicBlockOutput(array $attributes, string $formId): string
	{
		$formDetails = UtilsGeneralHelper::getFormDetails($formId);
		$type = $formDetails['type'] ?? '';

		if ($type !== SettingsAirtable::SETTINGS_TYPE_KEY) {
			return '';
		}

		$manifest = Helpers::getComponent('dynamic');
		$data = Helpers::checkAttr('dynamicData', $attributes, $manifest);

		if (!$data) {
			return '';
		}

		$data = \json_decode($data, true);

		$baseId = $data['baseId'] ?? '';
		$listId = $data['listId'] ?? '';

		if (!$baseId || !$listId) {
			return '';
		}

		$records = $this->airtableClient->getItemDetails($baseId, $listId);

		if (!$records) {
			return '';
		}

		$content = '';

		foreach ($records as $record) {
			$id = $record['id'] ?? '';
			$title = $record['title'] ?? '';

			if (!$id || !$title) {
				continue;
			}

			if (\is_array($title)) {
				continue;
			}

			$content .= Helpers::render(
				'select-option',
				[
					'selectOptionLabel' => $title,
					'selectOptionValue' => $id,
				]
			);
		}

		return Helpers::render(
			'select',
			Helpers::props('select', [
				'selectName' => Helpers::checkAttr('dynamicName', $attributes, $manifest),
				'selectTracking' => Helpers::checkAttr('dynamicTracking', $attributes, $manifest),
				'selectFieldLabel' => Helpers::checkAttr('dynamicFieldLabel', $attributes, $manifest),
				'selectIsMultiple' => Helpers::checkAttr('dynamicIsMultiple', $attributes, $manifest),
				'selectIsRequired' => Helpers::checkAttr('dynamicIsRequired', $attributes, $manifest),
				'selectTypeCustom' => Helpers::checkAttr('dynamicTypeCustom', $attributes, $manifest),
				'selectContent' => $content,
			])
		);
	}
}
