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
			'type' => SettingsGreenhouse::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
			'innerId' => $innerId,
			'fields' => [],
		];

		// Get fields.
		$item = $this->greenhouseClient->getItem($itemId);

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
		$output = [];

		if (!$data) {
			return $output;
		}

		foreach ($data['fields'] as $item) {
			if (!$item) {
				continue;
			}

			$fields = $item['fields'] ?? [];
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
						switch ($name) {
							case 'phone':
								$output[] = [
									'component' => 'phone',
									'phoneName' => $name,
									'phoneTracking' => $name,
									'phoneFieldLabel' => $label,
									'phoneMeta' => $description,
									'phoneIsRequired' => (bool) $required,
									'phoneDisabledOptions' => $this->prepareDisabledOptions('phone', [
										$required ? 'phoneIsRequired' : '',
									]),
								];
								break;
							case 'email':
								$output[] = [
									'component' => 'input',
									'inputName' => $name,
									'inputTracking' => $name,
									'inputFieldLabel' => $label,
									'inputMeta' => $description,
									'inputType' => 'email',
									'inputIsRequired' => (bool) $required,
									'inputIsEmail' => true,
									'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
										$required ? 'inputIsRequired' : '',
										'inputType',
									]),
								];
								break;
							default:
								$output[] = [
									'component' => 'input',
									'inputName' => $name,
									'inputTracking' => $name,
									'inputFieldLabel' => $label,
									'inputMeta' => $description,
									'inputType' => 'text',
									'inputIsRequired' => (bool) $required,
									'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
										$required ? 'inputIsRequired' : '',
									]),
								];
								break;
						}
						break;
					case 'input_file':
						$maxFileSize = $this->getOptionValue(SettingsGreenhouse::SETTINGS_GREENHOUSE_FILE_UPLOAD_LIMIT_KEY) ?: SettingsGreenhouse::SETTINGS_GREENHOUSE_FILE_UPLOAD_LIMIT_DEFAULT; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

						$output[] = [
							'component' => 'file',
							'fileName' => $name,
							'fileTracking' => $name,
							'fileFieldLabel' => $label,
							'fileMeta' => $description,
							'fileIsRequired' => (bool) $required,
							'fileAccept' => 'pdf,doc,docx,txt,rtf',
							'fileMinSize' => '1',
							'fileMaxSize' => \strval($maxFileSize * 1000),
							'fileDisabledOptions' => $this->prepareDisabledOptions('file', [
								$required ? 'fileIsRequired' : '',
								'fileAccept',
								'fileMinSize',
								'fileMaxSize',
							]),
						];
						break;
					case 'textarea':
						$disableResume = $this->isCheckboxOptionChecked(SettingsGreenhouse::SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_RESUME,SettingsGreenhouse::SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_KEY);
						$disableCoverLetter = $this->isCheckboxOptionChecked(SettingsGreenhouse::SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_COVER_LETTER,SettingsGreenhouse::SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_KEY);

						if ($disableResume || $disableCoverLetter) {
							continue;
						}

						$output[] = [
							'component' => 'textarea',
							'textareaName' => $name,
							'textareaTracking' => $name,
							'textareaFieldLabel' => $label,
							'textareaMeta' => $description,
							'textareaIsRequired' => (bool) $required,
							'textareaDisabledOptions' => $this->prepareDisabledOptions('textarea', [
								$required ? 'textareaIsRequired' : '',
							]),
						];
						break;
					case 'multi_value_single_select':
						if ($values[0]['label'] === 'No' && $values[0]['value'] === 0) {
							$output[] = [
								'component' => 'checkboxes',
								'checkboxesName' => $name,
								'checkboxesMeta' => $description,
								'checkboxesIsRequired' => (bool) $required,
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => $label,
										'checkboxValue' => 'true',
										'checkboxUncheckedValue' => 'false',
										'checkboxTracking' => $name,
										'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
											'checkboxValue',
										], false),
									],
								],
								'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
									$required ? 'checkboxesIsRequired' : '',
								]),
							];
						} else {
							$output[] = [
								'component' => 'select',
								'selectName' => $name,
								'selectTracking' => $name,
								'selectMeta' => $description,
								'selectFieldLabel' => $label,
								'selectIsRequired' => (bool) $required,
								'selectContent' => \array_map(
									function ($selectOption) {
										return [
											'component' => 'select-option',
											'selectOptionLabel' => $selectOption['label'],
											'selectOptionValue' => $selectOption['value'],
											'selectOptionDisabledOptions' => $this->prepareDisabledOptions('select-option', [
												'selectOptionValue',
											], false),
										];
									},
									$values
								),
								'selectDisabledOptions' => $this->prepareDisabledOptions('select', [
									$required ? 'selectIsRequired' : '',
								]),
							];
						}
						break;
				}
			}
		}

		$output[] = [
			'component' => 'input',
			'inputType' => 'hidden',
			'inputFieldLabel' => 'mapped_url_token',
			'inputName' => 'mapped_url_token',
			'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
				'inputType',
				'inputFieldLabel',
				'inputFieldLabel',
			]),
		];
		$output[] = [
			'component' => 'input',
			'inputType' => 'hidden',
			'inputFieldLabel' => 'longitude',
			'inputName' => 'longitude',
			'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
				'inputType',
				'inputFieldLabel',
				'inputFieldLabel',
			]),
		];
		$output[] = [
			'component' => 'input',
			'inputType' => 'hidden',
			'inputFieldLabel' => 'latitude',
			'inputName' => 'latitude',
			'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
				'inputType',
				'inputFieldLabel',
				'inputFieldLabel',
			]),
		];

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitFieldUseError' => false,
			'submitDisabledOptions' => $this->prepareDisabledOptions('submit'),
		];

		// Change the final output if necesery.
		$dataFilterName = Filters::getIntegrationFilterName(SettingsGreenhouse::SETTINGS_TYPE_KEY, 'data');
		if (\has_filter($dataFilterName) && !\is_admin()) {
			$output = \apply_filters($dataFilterName, $output, $formId) ?? [];
		}

		return $output;
	}
}
