<?php

/**
 * Greenhouse Mapper integration class.
 *
 * @package EightshiftForms\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Greenhouse;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Greenhouse integration class.
 */
class Greenhouse extends AbstractFormBuilder implements MapperInterface, ServiceInterface
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
	public const FILTER_MAPPER_NAME = 'es_greenhouse_mapper_filter';

	/**
	 * Filter form fields.
	 *
	 * @var string
	 */
	public const FILTER_FORM_FIELDS_NAME = 'es_greenhouse_form_fields_filter';

	/**
	 * Instance variable for Greenhouse data.
	 *
	 * @var ClientInterface
	 */
	protected $greenhouseClient;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $greenhouseClient Inject Greenhouse which holds Greenhouse connect data.
	 */
	public function __construct(ClientInterface $greenhouseClient)
	{
		$this->greenhouseClient = $greenhouseClient;
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
		$formAdditionalProps['formPostId'] = (string) $formId;

		// Get form type.
		$formAdditionalProps['formType'] = SettingsGreenhouse::SETTINGS_TYPE_KEY;

		// Check if it is loaded on the front or the backend.
		$ssr = $formAdditionalProps['ssr'] ?? false;

		// Return form to the frontend.
		return $this->buildForm(
			$this->getFormFields($formId, $ssr),
			array_merge($formAdditionalProps, $this->getFormAdditionalProps($formId))
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
		// Get Item Id.
		$itemId = $this->getSettingsValue(SettingsGreenhouse::SETTINGS_GREENHOUSE_JOB_ID_KEY, (string) $formId);
		if (empty($itemId)) {
			return [];
		}

		// Get Form.
		$fields = $this->greenhouseClient->getItem($itemId);
		if (empty($fields)) {
			return [];
		}

		return $this->getFields($fields, $formId, $ssr);
	}

	/**
	 * Map Greenhouse fields to our components.
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

			$fields = $item['fields'] ?? '';
			$label = $item['label'] ?? '';
			$description = $item['description'] ?? '';
			$required = $item['required'] ?? false;

			foreach ($fields as $field) {
				$type = $field['type'] ?? '';
				$name = $field['name'] ?? '';
				$values = $field['values'];

				// In GH select and check box is the same, addes some conditions to fine tune output.
				switch ($type) {
					case 'input_text':
						$output[] = [
							'component' => 'input',
							'inputName' => $name,
							'inputFieldLabel' => $label,
							'inputId' => $name,
							'inputType' => 'text',
							'inputIsRequired' => $required,
							'inputIsEmail' => $name === 'email' ? 'true' : '',
							'inputIsNumber' => $name === 'phone' ? 'true' : ''
						];
						break;
					case 'input_file':
						$output[] = [
							'component' => 'file',
							'fileName' => $name,
							'fileFieldLabel' => $label,
							'fileId' => $name,
							'fileIsRequired' => $required,
							'fileAccept' => 'pdf,doc,docx,txt,rtf',
							'fileMinSize' => 1
						];
						break;
					case 'textarea':
						$output[] = [
							'component' => 'textarea',
							'textareaName' => $name,
							'textareaFieldLabel' => $label,
							'textareaId' => $name,
							'textareaIsRequired' => $required,
						];
						break;
					case 'multi_value_single_select':
						if ($values[0]['label'] === 'No' && $values[0]['value'] === 0) {
							$output[] = [
								'component' => 'checkboxes',
								'checkboxesName' => $name,
								'checkboxesId' => $name,
								'checkboxesIsRequired' => $required,
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => $label,
										'checkboxValue' => 1,
									],
								]
							];
						} else {
							$output[] = [
								'component' => 'select',
								'selectName' => $name,
								'selectId' => $name,
								'selectMeta' => $description,
								'selectFieldLabel' => $label,
								'selectIsRequired' => $required,
								'selectOptions' => array_map(
									static function ($selectOption) {
										return [
											'component' => 'select-option',
											'selectOptionLabel' => $selectOption['label'],
											'selectOptionValue' => $selectOption['value'],
										];
									},
									$values
								),
							];
						}
						break;
				}
			}
		}

		if (!is_admin()) {
			$output[] = [
				'component' => 'input',
				'inputType' => 'hidden',
				'inputId' => 'longitude',
				'inputName' => 'longitude',
			];
			$output[] = [
				'component' => 'input',
				'inputType' => 'hidden',
				'inputId' => 'latitude',
				'inputName' => 'latitude',
			];
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
		if (has_filter(Filters::FILTER_INTEGRATION_GREENHOUSE_FORM_DATA_NAME) && !is_admin()) {
			$output = \apply_filters(Filters::FILTER_INTEGRATION_GREENHOUSE_FORM_DATA_NAME, $output) ?? [];
		}

		return $this->getIntegrationFieldsValue(
			$this->getSettingsValueGroup(SettingsGreenhouse::SETTINGS_GREENHOUSE_INTEGRATION_FIELDS_KEY, $formId),
			$output
		);
	}
}
