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
			'type' => SettingsWorkable::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
			'innerId' => $innerId,
			'fields' => [],
		];

		// Get fields.
		$item = $this->workableClient->getItem($itemId);

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
	 * @param array<string, mixed> $data Fields.
	 * @param string $formId Form ID.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $data, string $formId): array
	{
		if (!$data) {
			return [];
		}

		$output = [
			[
				'component' => 'input',
				'inputName' => 'firstname',
				'inputTracking' => 'firstname',
				'inputFieldLabel' => \esc_html__('First name', 'eightshift-forms'),
				'inputId' => 'firstname',
				'inputType' => 'text',
				'inputMaxLength' => 255,
				'inputIsRequired' => true,
				'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
					'inputIsRequired',
					'inputMaxLength',
				]),
			],
			[
				'component' => 'input',
				'inputName' => 'lastname',
				'inputTracking' => 'lastname',
				'inputFieldLabel' => \esc_html__('Last name', 'eightshift-forms'),
				'inputId' => 'lastname',
				'inputType' => 'text',
				'inputMaxLength' => 255,
				'inputIsRequired' => true,
				'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
					'inputIsRequired',
					'inputMaxLength',
				]),
			],
			[
				'component' => 'input',
				'inputName' => 'email',
				'inputTracking' => 'email',
				'inputFieldLabel' => \esc_html__('Email', 'eightshift-forms'),
				'inputId' => 'email',
				'inputType' => 'email',
				'inputIsRequired' => true,
				'inputIsEmail' => true,
				'inputMaxLength' => 255,
				'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
					'inputIsRequired',
					'inputIsEmail',
					'inputType',
					'inputMaxLength',
				]),
			],
		];

		foreach ($data['fields'] as $item) {
			if (!$item) {
				continue;
			}

			$type = $item['type'] ?? '';
			$name = $item['key'] ?? '';
			$label = $item['label'] ?? '';
			$fields = $item['choices'] ?? [];
			$required = isset($item['required']) ? (bool) $item['required'] : false;

			if (!$label) {
				$label = $item['body'] ?? '';
			}

			if (!$name) {
				$name = $item['id'] ?? '';
			}

			switch ($type) {
				case 'short_text':
				case 'string':
					if ($name === 'phone') {
						$output[] = [
							'component' => 'phone',
							'phoneName' => $name,
							'phoneTracking' => $name,
							'phoneFieldLabel' => $label,
							'phoneIsRequired' => $required,
							'inputMaxLength' => 255,
							'phoneAttrs' => [
								'data-type-internal' => 'phone',
							],
							'phoneDisabledOptions' => $this->prepareDisabledOptions('phone', [
								$required ? 'phoneIsRequired' : '',
								'inputMaxLength',
							]),
						];
					} else {
						$output[] = [
							'component' => 'input',
							'inputName' => $name,
							'inputTracking' => $name,
							'inputFieldLabel' => $label,
							'inputType' => 'text',
							'inputIsRequired' => $required,
							'inputMaxLength' => 255,
							'inputAttrs' => [
								'data-type-internal' => $type,
							],
							'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
								$required ? 'inputIsRequired' : '',
								'inputMaxLength',
							]),
						];
					}
					break;
				case 'file':
					$maxFileSize = $this->getOptionValueWithFallback(SettingsWorkable::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_KEY, (string) SettingsWorkable::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_DEFAULT);

					$output[] = [
						'component' => 'file',
						'fileName' => $name,
						'fileTracking' => $name,
						'fileFieldLabel' => $label,
						'fileIsRequired' => $required,
						'fileAccept' => 'pdf,doc,docx,rtf',
						'fileMinSize' => '1',
						'fileMaxSize' => \strval((int) $maxFileSize * 1000),
						'fileAttrs' => [
							'data-type-internal' => $type,
						],
						'fileDisabledOptions' => $this->prepareDisabledOptions('file', [
							$required ? 'fileIsRequired' : '',
							'fileAccept',
							'fileMinSize',
							'fileMaxSize',
						]),
					];
					break;
				case 'dropdown':
				case 'multiple_choice':
					$output[] = [
						'component' => 'select',
						'selectName' => $name,
						'selectTracking' => $name,
						'selectFieldLabel' => $label,
						'selectIsRequired' => $required,
						'selectContent' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => '',
								'selectOptionValue' => '',
							],
							...\array_map(
								function ($selectOption) {
									return [
										'component' => 'select-option',
										'selectOptionLabel' => $selectOption['body'],
										'selectOptionValue' => $selectOption['id'],
										'selectOptionDisabledOptions' => $this->prepareDisabledOptions('select-option', [
											'selectOptionValue',
										], false),
									];
								},
								$fields
							),
						],
						'selectAttrs' => [
							'data-type-internal' => $type,
						],
						'selectDisabledOptions' => $this->prepareDisabledOptions('select', [
							$required ? 'selectIsRequired' : '',
						]),
					];
					break;
				case 'free_text':
						$output[] = [
							'component' => 'textarea',
							'textareaName' => $name,
							'textareaTracking' => $name,
							'textareaFieldLabel' => $label,
							'textareaIsRequired' => $required,
							'textareaAttrs' => [
								'data-type-internal' => $type,
							],
							'textareaDisabledOptions' => $this->prepareDisabledOptions('textarea', [
								$required ? 'textareaIsRequired' : '',
							]),
						];
					break;
				case 'boolean':
					$output[] = [
						'component' => 'checkboxes',
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
								],
								'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
									'checkboxValue',
								], false),
							]
						],
						'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
							$required ? 'checkboxesIsRequired' : '',
						]),
					];
					break;
			}
		}

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitFieldUseError' => false,
			'submitDisabledOptions' => $this->prepareDisabledOptions('submit'),
		];

		// Change the final output if necesery.
		$filterName = Filters::getFilterName(['integrations', SettingsWorkable::SETTINGS_TYPE_KEY, 'data']);
		if (\has_filter($filterName) && \is_admin()) {
			$output = \apply_filters($filterName, $output, $formId) ?? [];
		}

		return $output;
	}
}
