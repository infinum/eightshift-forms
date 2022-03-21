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
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\ClientInterface;
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
	 * Filter mapper.
	 *
	 * @var string
	 */
	public const FILTER_MAPPER_NAME = 'es_hubspot_mapper_filter';

	/**
	 * Filter form fields.
	 *
	 * @var string
	 */
	public const FILTER_FORM_FIELDS_NAME = 'es_hubspot_form_fields_filter';

	/**
	 * Instance variable for Hubspot data.
	 *
	 * @var ClientInterface
	 */
	protected $hubspotClient;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $hubspotClient Inject Hubspot which holds Hubspot connect data.
	 */
	public function __construct(ClientInterface $hubspotClient)
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
		\add_filter(static::FILTER_MAPPER_NAME, [$this, 'getForm'], 10, 3);
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
		$type = SettingsHubspot::SETTINGS_TYPE_KEY;
		$formAdditionalProps['formType'] = $type;

		// Check if it is loaded on the front or the backend.
		$ssr = (bool) ($formAdditionalProps['ssr'] ?? false);

		// Return form to the frontend.
		return $this->buildForm(
			$this->getFormFields($formId, $ssr),
			array_merge($formAdditionalProps, $this->getFormAdditionalProps($formId, $type))
		);
	}

	/**
	 * Get Hubspot mapped form fields.
	 *
	 * @param string $formId Form Id.
	 * @param bool $ssr Does form load using ssr.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFormFields(string $formId, bool $ssr = false): array
	{
		// Get item Id.
		$itemId = $this->getSettingsValue(SettingsHubspot::SETTINGS_HUBSPOT_ITEM_ID_KEY, (string) $formId);
		if (empty($itemId)) {
			return [];
		}

		// Get fields.
		$item = $this->hubspotClient->getItem($itemId);
		if (empty($item)) {
			return [];
		}

		return $this->getFields($item, $formId, $ssr);
	}

	/**
	 * Map Hubspot fields to our components.
	 *
	 * @param array<string, mixed> $data Item object.
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

		if (!$data['fields']) {
			return $output;
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
					'richTextFieldLabel' => __('Rich text', 'eightshift-form') . '-' . $key,
					'richTextContent' => $richText,
				];
			}

			$fields = $item['fields'] ?? [];

			if (!$fields) {
				continue;
			}

			foreach ($fields as $field) {
				$property = $field['propertyObjectType'] ?? 'CONTACT';
				$name = $field['name'] ?? '';
				$label = $field['label'] ?? '';
				$type = $field['fieldType'] ?? '';
				$required = $field['required'] ?? false;
				$value = $field['default_value'] ?? '';
				$placeholder = $field['placeholder'] ?? '';
				$options = $field['options'] ?? '';
				$validation = $field['validation']['data'] ?? '';
				$id = $name;
				$metaData = $field['metaData'] ?? [];

				$validation = explode(':', $validation);
				$min = $validation[0] ?? '';
				$max = $validation[1] ?? '';

				if ($property !== 'CONTACT') {
					$name = "{$property}.{$name}";
				}

				switch ($type) {
					case 'text':
						$item = [
							'component' => 'input',
							'inputFieldLabel' => $label,
							'inputId' => $id,
							'inputName' => $name,
							'inputTracking' => $name,
							'inputType' => 'text',
							'inputPlaceholder' => $placeholder,
							'inputIsRequired' => $required,
							'inputValue' => $value,
						];

						if ($min) {
							$item['inputMinLength'] = (int) $min;
						}

						if ($max) {
							$item['inputMaxLength'] = (int) $max;
						}

						$output[] = $item;
						break;
					case 'number':
						$item = [
							'component' => 'input',
							'inputName' => $name,
							'inputTracking' => $name,
							'inputFieldLabel' => $label,
							'inputId' => $id,
							'inputType' => 'number',
							'inputIsRequired' => $required,
							'inputValue' => $value,
						];

						if ($min) {
							$item['inputMinLength'] = $min;
						}

						if ($max) {
							$item['inputMaxLength'] = $max;
						}

						$output[] = $item;
						break;
					case 'textarea':
						$output[] = [
							'component' => 'textarea',
							'textareaFieldLabel' => $label,
							'textareaId' => $id,
							'textareaName' => $name,
							'textareaTracking' => $name,
							'textareaType' => 'textarea',
							'textareaPlaceholder' => $placeholder,
							'textareaIsRequired' => $required,
							'textareaValue' => $value,
						];
						break;
					case 'file':
						$isMultiple = array_filter(
							$metaData,
							static function ($item) {
								$name = $item['name'] ?? '';
								$value = $item['value'] ?? '';
								return $name === 'isMultipleFileUpload' && $value === 'true';
							}
						);

						$output[] = [
							'component' => 'file',
							'fileFieldLabel' => $label,
							'fileId' => $id,
							'fileName' => $name,
							'fileTracking' => $name,
							'fileType' => 'text',
							'filePlaceholder' => $placeholder,
							'fileIsRequired' => $required,
							'fileValue' => $value,
							'fileIsMultiple' => !empty($isMultiple),
						];
						break;
					case 'select':
						$output[] = [
							'component' => 'select',
							'selectFieldLabel' => $label,
							'selectId' => $id,
							'selectName' => $name,
							'selectTracking' => $name,
							'selectType' => 'select',
							'selectPlaceholder' => $placeholder,
							'selectIsRequired' => $required,
							'selectValue' => $value,
							'selectOptions' => array_values(
								array_merge(
									[
										[
											'component' => 'select-option',
											'selectOptionLabel' => __('Select option', 'eightshift-forms'),
											'selectOptionValue' => ' ',
											'selectOptionIsSelected' => true,
											'selectOptionIsDisabled' => true,
										],
									],
									array_map(
										static function ($selectOption) {
											return [
												'component' => 'select-option',
												'selectOptionLabel' => $selectOption['label'],
												'selectOptionValue' => $selectOption['value'],
											];
										},
										$options
									)
								)
							),
						];
						break;
					case 'booleancheckbox':
						$output[] = [
							'component' => 'checkboxes',
							'checkboxesId' => $id,
							'checkboxesName' => $name,
							'checkboxesIsRequired' => $required,
							'checkboxesContent' => [
								[
									'component' => 'checkbox',
									'checkboxLabel' => $label,
									'checkboxTracking' => $name,
								]
							],
						];
						break;
					case 'checkbox':
						$output[] = [
							'component' => 'checkboxes',
							'checkboxesId' => $id,
							'checkboxesName' => $name,
							'checkboxesFieldLabel' => $label,
							'checkboxesIsRequired' => $required,
							'checkboxesContent' => array_map(
								static function ($checkbox) use ($name) {
									return [
										'component' => 'checkbox',
										'checkboxLabel' => $checkbox['label'],
										'checkboxValue' => $checkbox['value'],
										'checkboxTracking' => $name,
									];
								},
								$options
							),
						];
						break;
					case 'radio':
						$output[] = [
							'component' => 'radios',
							'radiosId' => $id,
							'radiosName' => $name,
							'radiosFieldLabel' => $label,
							'radiosIsRequired' => $required,
							'radiosContent' => array_map(
								static function ($radio) use ($name) {
									return [
										'component' => 'radio',
										'radioLabel' => $radio['label'],
										'radioValue' => $radio['value'],
										'radioTracking' => $name,
									];
								},
								$options
							),
						];
						break;
					case 'consent':
						$output[] = [
							'component' => 'checkboxes',
							'checkboxesFieldBeforeContent' => $field['beforeText'] ?? '',
							'checkboxesFieldAfterContent' => $field['afterText'] ?? '',
							'checkboxesId' => $id,
							'checkboxesFieldHideLabel' => true,
							'checkboxesName' => $name,
							'checkboxesIsRequired' => $required,
							'checkboxesContent' => array_map(
								static function ($checkbox) use ($name) {
									return [
										'component' => 'checkbox',
										'checkboxLabel' => $checkbox['label'],
										'checkboxValue' => $checkbox['label'],
										'checkboxTracking' => $name,
									];
								},
								$options
							),
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
			'submitFieldOrder' => count($output) + 1,
			'submitServerSideRender' => $ssr,
		];

		// Change the final output if necesery.
		$dataFilterName = Filters::getIntegrationFilterName(SettingsHubspot::SETTINGS_TYPE_KEY, 'data');
		if (has_filter($dataFilterName) && !is_admin()) {
			$output = \apply_filters($dataFilterName, $output, $formId) ?? [];
		}

		return $this->getIntegrationFieldsValue(
			$this->getSettingsValueGroup(SettingsHubspot::SETTINGS_HUBSPOT_INTEGRATION_FIELDS_KEY, $formId),
			$output,
			SettingsHubspot::SETTINGS_TYPE_KEY
		);
	}
}
