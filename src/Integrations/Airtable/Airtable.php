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
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Validation\ValidatorInterface;
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
	 * Filter mapper.
	 *
	 * @var string
	 */
	public const FILTER_MAPPER_NAME = 'es_airtable_mapper_filter';

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
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $airtableClient Inject Airtable which holds Airtable connect data.
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 */
	public function __construct(
		ClientInterface $airtableClient,
		ValidatorInterface $validator
	) {
		$this->airtableClient = $airtableClient;
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
		\add_filter(static::FILTER_MAPPER_NAME, [$this, 'getForm'], 10, 2);
		\add_filter(static::FILTER_FORM_FIELDS_NAME, [$this, 'getFormFields'], 11, 2);
	}

	/**
	 * Map form to our components.
	 *
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $formAdditionalProps Additional props.
	 *
	 * @return string
	 */
	public function getForm(string $formId, array $formAdditionalProps = []): string
	{
		// Get post ID prop.
		$formAdditionalProps['formPostId'] = $formId;

		// Get form type.
		$type = SettingsAirtable::SETTINGS_TYPE_KEY;
		$formAdditionalProps['formType'] = $type;

		// Check if it is loaded on the front or the backend.
		$ssr = (bool) ($formAdditionalProps['ssr'] ?? false);

		// Add conditional tags.
		$formConditionalTags = $this->getGroupDataWithoutKeyPrefix($this->getSettingsValueGroup(SettingsAirtable::SETTINGS_AIRTABLE_CONDITIONAL_TAGS_KEY, $formId));
		$formAdditionalProps['formConditionalTags'] = $formConditionalTags ? \wp_json_encode($formConditionalTags) : '';

		return $this->buildForm(
			$this->getFormFields($formId, $ssr),
			\array_merge($formAdditionalProps, $this->getFormAdditionalProps($formId, $type))
		);
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
		$itemId = $this->getSettingsValue(SettingsAirtable::SETTINGS_AIRTABLE_LIST_KEY, (string) $formId);
		if (empty($itemId)) {
			return [];
		}

		// Get selected field.
		$fieldId = $this->getSettingsValue(SettingsAirtable::SETTINGS_AIRTABLE_FIELD_KEY, (string) $formId);
		if (empty($fieldId)) {
			return [];
		}

		// Get fields.
		$fields = $this->airtableClient->getItem($itemId);
		if (empty($fields)) {
			return [];
		}

		$fields = $fields['items'][$fieldId] ?? [];

		return $this->getFields($fields, $formId, $ssr);
	}

	/**
	 * Map Airtable fields to our components.
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
						'inputId' => $id,
						'inputType' => 'text',
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
						'inputType' => 'text',
						'inputIsEmail' => true,
						'blockSsr' => $ssr,
					];
					break;
				case 'url':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => 'text',
						'inputIsUrl' => true,
						'blockSsr' => $ssr,
					];
					break;
				case 'phoneNumber':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => 'text',
						'inputIsNumber' => true,
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
						'inputType' => 'text',
						'inputAttrs' => [
							AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['fieldTypeInternal'] => 'number',
						],
						'blockSsr' => $ssr,
					];
					break;
				case 'multilineText':
					$output[] = [
						'component' => 'textarea',
						'textareaName' => $name,
						'textareaTracking' => $name,
						'textareaFieldLabel' => $label,
						'textareaId' => $id,
						'blockSsr' => $ssr,
					];
					break;
				case 'singleSelect':
					$output[] = [
						'component' => 'select',
						'selectName' => $name,
						'selectTracking' => $name,
						'selectId' => $id,
						'selectFieldLabel' => $label,
						'selectOptions' => \array_map(
							static function ($selectOption) {
								return [
									'component' => 'select-option',
									'selectOptionLabel' => $selectOption['name'],
									'selectOptionValue' => $selectOption['id'],
								];
							},
							$options['choices'] ?? []
						),
						'blockSsr' => $ssr,
					];
					break;
				case 'multipleSelects':
					$output[] = [
						'component' => 'checkboxes',
						'checkboxesName' => $name,
						'checkboxesTracking' => $name,
						'checkboxesId' => $id,
						'checkboxesFieldLabel' => $label,
						'checkboxesContent' => \array_map(
							static function ($checkbox) {
								return [
									'component' => 'checkbox',
									'checkboxLabel' => $checkbox['name'],
									'checkboxValue' => $checkbox['id'],
									'checkboxAttrs' => [
										AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['fieldTypeInternal'] => 'multiCheckbox',
									],
								];
							},
							$options['choices'] ?? []
						),
						'blockSsr' => $ssr,
					];
					break;
				case 'checkbox':
					$output[] = [
						'component' => 'checkboxes',
						'checkboxesName' => $name,
						'checkboxesTracking' => $name,
						'checkboxesFieldHideLabel' => true,
						'checkboxesId' => $id,
						'checkboxesContent' => [
							[
								'component' => 'checkbox',
								'checkboxLabel' => $label,
								'checkboxValue' => true,
								'checkboxAttrs' => [
									AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['fieldTypeInternal'] => 'singleCheckbox',
								],
							]
						],
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
		$dataFilterName = Filters::getIntegrationFilterName(SettingsAirtable::SETTINGS_TYPE_KEY, 'data');
		if (\has_filter($dataFilterName) && !\is_admin()) {
			$output = \apply_filters($dataFilterName, $output, $formId) ?? [];
		}

		return $this->getIntegrationFieldsValue(
			$this->getSettingsValueGroup(SettingsAirtable::SETTINGS_AIRTABLE_INTEGRATION_FIELDS_KEY, $formId),
			$output,
			SettingsAirtable::SETTINGS_TYPE_KEY
		);
	}
}
