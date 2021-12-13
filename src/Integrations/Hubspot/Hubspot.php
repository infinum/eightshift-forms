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
		$formAdditionalProps['formType'] = SettingsHubspot::SETTINGS_TYPE_KEY;

			// Check if it is loaded on the front or the backend.
			$ssr = (bool) $formAdditionalProps['ssr'] ?? false;

		// Return form to the frontend.
		return $this->buildForm(
			$this->getFormFields($formId, $ssr),
			array_merge($formAdditionalProps, $this->getFormAdditionalProps($formId))
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

		return $this->getFields($item['fields'], $formId, $ssr);
	}

	/**
	 * Map Hubspot fields to our components.
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

		foreach ($data as $item) {
			if (empty($item)) {
				continue;
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
				$id = $name;

				if ($property !== 'CONTACT') {
					$name = "{$property}.{$name}";
				}

				switch ($type) {
					case 'text':
						$output[] = [
							'component' => 'input',
							'inputFieldLabel' => $label,
							'inputId' => $id,
							'inputName' => $name,
							'inputType' => 'text',
							'inputPlaceholder' => $placeholder,
							'inputIsRequired' => $required,
							'inputValue' => $value,
							];
						break;
					case 'textarea':
						$output[] = [
							'component' => 'textarea',
							'textareaFieldLabel' => $label,
							'textareaId' => $id,
							'textareaName' => $name,
							'textareaType' => 'textarea',
							'textareaPlaceholder' => $placeholder,
							'textareaIsRequired' => $required,
							'textareaValue' => $value,
						];
						break;
					case 'file':
						$output[] = [
							'component' => 'file',
							'fileFieldLabel' => $label,
							'fileId' => $id,
							'fileName' => $name,
							'fileType' => 'text',
							'filePlaceholder' => $placeholder,
							'fileIsRequired' => $required,
							'fileValue' => $value,
						];
						break;
					case 'select':
						$output[] = [
							'component' => 'select',
							'selectFieldLabel' => $label,
							'selectId' => $id,
							'selectName' => $name,
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
								static function ($checkbox) {
									return [
										'component' => 'checkbox',
										'checkboxLabel' => $checkbox['label'],
										'checkboxValue' => $checkbox['value'],
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
								static function ($radio) {
									return [
										'component' => 'radio',
										'radioLabel' => $radio['label'],
										'radioValue' => $radio['value'],
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
								static function ($checkbox) {
									return [
										'component' => 'checkbox',
										'checkboxLabel' => $checkbox['label'],
										'checkboxValue' => $checkbox['label'],
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
			'submitId' => 'submit',
			'submitFieldUseError' => false,
			'submitFieldOrder' => count($output) + 1,
			'submitServerSideRender' => $ssr,
		];

		// Change the final output if necesery.
		$dataFilterName = Filters::getIntegrationFilterName(SettingsHubspot::SETTINGS_TYPE_KEY, 'data');
		if (has_filter($dataFilterName) && !is_admin()) {
			$output = \apply_filters($dataFilterName, $output) ?? [];
		}

		return $this->getIntegrationFieldsValue(
			$this->getSettingsValueGroup(SettingsHubspot::SETTINGS_HUBSPOT_INTEGRATION_FIELDS_KEY, $formId),
			$output,
			SettingsHubspot::SETTINGS_TYPE_KEY
		);
	}
}
