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
		\add_filter(static::FILTER_MAPPER_NAME, [$this, 'getForm']);
		\add_filter(static::FILTER_FORM_FIELDS_NAME, [$this, 'getFormFields']);
	}

	/**
	 * Map Hubspot form to our components.
	 *
	 * @param array<string, string|int> $formAdditionalProps Additional props to pass to form.
	 *
	 * @return string
	 */
	public function getForm(array $formAdditionalProps): string
	{
		// Get post ID prop.
		$formId = (string) $formAdditionalProps['formPostId'] ? Helper::encryptor('decrypt', (string) $formAdditionalProps['formPostId']) : '';
		if (empty($formId)) {
			return '';
		}
		

		// Return form to the frontend.
		return $this->buildForm(
			$this->getFormFields((string) $formId),
			$formAdditionalProps
		);
	}

	/**
	 * Get Hubspot maped form fields.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFormFields(string $formId): array
	{
		// Get Job Id.
		$formId = $this->getSettingsValue(SettingsHubspot::SETTINGS_HUBSPOT_FORM_ID_KEY, (string) $formId);
		if (empty($formId)) {
			return [];
		}

		// Get Form.
		$form = $this->hubspotClient->getForm($formId);
		if (empty($form)) {
			return [];
		}

		return $this->getFields($form['fields']);
	}

	/**
	 * Map Hubspot fields to our components.
	 *
	 * @param array<string, mixed> $data Fields.
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $data): array
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
							'selectOptions' => array_map(
								function ($selectOption) {
									return [
										'component' => 'select-option',
										'selectOptionLabel' => $selectOption['label'],
										'selectOptionValue' => $selectOption['value'],
									];
								},
								$options
							),
						];
						break;
					case 'booleancheckbox':
						$output[] = [
							'component' => 'checkboxes',
							'checkboxesContent' => [
								[
									'component' => 'checkbox',
									'checkboxLabel' => $label,
									'checkboxId' => $id,
									'checkboxName' => $name,
									'checkboxIsRequired' => $required,
								]
							],
						];
						break;
					case 'checkbox':
						$output[] = [
							'component' => 'checkboxes',
							'checkboxesContent' => array_map(
								function ($checkbox) use ($name, $required) {
									return [
										'component' => 'checkbox',
										'checkboxLabel' => $checkbox['label'],
										'checkboxId' => "{$name}.{$checkbox['value']}",
										'checkboxName' => "{$name}.{$checkbox['value']}",
										'checkboxValue' => $checkbox['value'],
										'checkboxIsRequired' => $required,
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
							'radiosIsRequired' => $required,
							'radiosContent' => array_map(
								function ($radio) {
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
							'checkboxesContent' => array_map(
								function ($checkbox) {
									return [
										'component' => 'checkbox',
										'checkboxLabel' => $checkbox['label'],
										'checkboxId' => $checkbox['id'],
										'checkboxName' => $checkbox['name'],
										'checkboxIsRequired' => $checkbox['required'],
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
			'submitValue' => __('Submit', 'eightshift-forms'),
			'submitFieldUseError' => false
		];

		return $output;
	}
}
