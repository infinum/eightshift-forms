<?php

/**
 * Workable Mapper integration class.
 *
 * @package EightshiftForms\Integrations\Workable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Workable;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Workable integration class.
 */
class Workable extends AbstractFormBuilder implements MapperInterface, ServiceInterface
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
	public const FILTER_MAPPER_NAME = 'es_workable_mapper_filter';

	/**
	 * Filter form fields.
	 *
	 * @var string
	 */
	public const FILTER_FORM_FIELDS_NAME = 'es_workable_form_fields_filter';

	/**
	 * Instance variable for Workable data.
	 *
	 * @var ClientInterface
	 */
	protected $workableClient;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $workableClient Inject Workable which holds Workable connect data.
	 */
	public function __construct(ClientInterface $workableClient)
	{
		$this->workableClient = $workableClient;
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
		$type = SettingsWorkable::SETTINGS_TYPE_KEY;
		$formAdditionalProps['formType'] = $type;

		// Check if it is loaded on the front or the backend.
		$ssr = $formAdditionalProps['ssr'] ?? false;

		// Return form to the frontend.
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
		// Get Item Id.
		$itemId = $this->getSettingsValue(SettingsWorkable::SETTINGS_WORKABLE_JOB_ID_KEY, (string) $formId);
		if (empty($itemId)) {
			return [];
		}

		// Get Form.
		$fields = $this->workableClient->getItem($itemId);
		if (empty($fields)) {
			return [];
		}

		return $this->getFields($fields, $formId, $ssr);
	}

	/**
	 * Map Workable fields to our components.
	 *
	 * @param array<string, mixed> $data Fields.
	 * @param string $formId Form ID.
	 * @param bool $ssr Does form load using ssr.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $data, string $formId, bool $ssr): array
	{
		if (!$data) {
			return [];
		}

		$output = [
			[
				'component' => 'input',
				'inputName' => 'firstname',
				'inputTracking' => 'firstname',
				'inputFieldLabel' => __('First name', 'eightshift-forms'),
				'inputId' => 'firstname',
				'inputType' => 'text',
				'inputIsRequired' => true,
				'blockSsr' => $ssr,
			],
			[
				'component' => 'input',
				'inputName' => 'lastname',
				'inputTracking' => 'lastname',
				'inputFieldLabel' => __('Last name', 'eightshift-forms'),
				'inputId' => 'lastname',
				'inputType' => 'text',
				'inputIsRequired' => true,
				'blockSsr' => $ssr,
			],
			[
				'component' => 'input',
				'inputName' => 'email',
				'inputTracking' => 'email',
				'inputFieldLabel' => __('Email', 'eightshift-forms'),
				'inputId' => 'email',
				'inputType' => 'email',
				'inputIsRequired' => true,
				'inputIsEmail' => true,
				'blockSsr' => $ssr,
			],
		];

		foreach ($data as $item) {
			if (empty($item)) {
				continue;
			}

			$type = $item['type'] ?? '';
			$name = $item['key'] ?? '';
			$label = $item['label'] ?? '';
			$fields = $item['choices'] ?? [];
			$required = (bool) $item['required'] ?? false;

			if (!$label) {
				$label = $item['body'] ?? '';
			}

			if (!$name) {
				$name = $item['id'] ?? '';
			}

			if ($name === 'photo' || $name === 'avatar') {
				continue;
			}

			switch ($type) {
				case 'short_text':
				case 'string':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $name,
						'inputType' => 'text',
						'inputIsRequired' => $required,
						'inputIsNumber' => $name === 'phone',
						'blockSsr' => $ssr,
						'inputAttrs' => [
							'data-type-internal' => $type,
						]
					];
					break;
				case 'file':
					$maxFileSize = $this->getOptionValue(SettingsWorkable::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_KEY) ?: SettingsWorkable::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_DEFAULT; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

					$suport = $item['supported_file_types'] ?? [];

					$output[] = [
						'component' => 'file',
						'fileName' => $name,
						'fileTracking' => $name,
						'fileFieldLabel' => $label,
						'fileId' => $name,
						'fileIsRequired' => $required,
						'fileAccept' => implode(',', $suport),
						'fileMinSize' => 1,
						'fileMaxSize' => (int) $maxFileSize * 1000,
						'blockSsr' => $ssr,
						'fileAttrs' => [
							'data-type-internal' => $type,
						]
					];
					break;
				case 'dropdown':
				case 'multiple_choice':
					$output[] = [
						'component' => 'select',
						'selectName' => $name,
						'selectTracking' => $name,
						'selectId' => $name,
						'selectFieldLabel' => $label,
						'selectIsRequired' => $required,
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => '',
								'selectOptionValue' => '',
							],
							...\array_map(
								static function ($selectOption) {
									return [
										'component' => 'select-option',
										'selectOptionLabel' => $selectOption['body'],
										'selectOptionValue' => $selectOption['id'],
									];
								},
								$fields
							),
						],
						'blockSsr' => $ssr,
						'selectAttrs' => [
							'data-type-internal' => $type,
						]
					];
					break;
				case 'free_text':
						$output[] = [
							'component' => 'textarea',
							'textareaName' => $name,
							'textareaTracking' => $name,
							'textareaFieldLabel' => $label,
							'textareaId' => $name,
							'textareaIsRequired' => $required,
							'blockSsr' => $ssr,
							'textareaAttrs' => [
								'data-type-internal' => $type,
							]
						];
					break;
				case 'boolean':
					$output[] = [
						'component' => 'checkboxes',
						'checkboxesId' => $name,
						'checkboxesName' => $name,
						'checkboxesFieldHideLabel' => true,
						'checkboxesIsRequired' => $required,
						'checkboxesContent' => [
							[
								'component' => 'checkbox',
								'checkboxLabel' => $label,
								'checkboxTracking' => $name,
								'checkboxValue' => 'true',
								'checkboxUncheckedValue' => 'false',
								'checkboxAttrs' => [
									'data-type-internal' => $type,
								]
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
		$dataFilterName = Filters::getIntegrationFilterName(SettingsWorkable::SETTINGS_TYPE_KEY, 'data');
		if (\has_filter($dataFilterName) && !\is_admin()) {
			$output = \apply_filters($dataFilterName, $output, $formId) ?? [];
		}

		return $this->getIntegrationFieldsValue(
			$this->getSettingsValueGroup(SettingsWorkable::SETTINGS_WORKABLE_INTEGRATION_FIELDS_KEY, $formId),
			$output,
			SettingsWorkable::SETTINGS_TYPE_KEY
		);
	}
}
