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
use EightshiftForms\Helpers\Helper;
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
	 * Filter Name
	 */
	public const FILTER_MAPPER_NAME = 'es_greenhouse_mapper_filter';

	/**
	 * Instance variable for Greenhouse data.
	 *
	 * @var GreenhouseClientInterface
	 */
	protected $greenhouseClient;

	/**
	 * Create a new instance.
	 *
	 * @param GreenhouseClientInterface $greenhouseClient Inject Greenhouse which holds Greenhouse connect data.
	 */
	public function __construct(GreenhouseClientInterface $greenhouseClient)
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
		\add_filter(static::FILTER_MAPPER_NAME, [$this, 'getForm']);
	}

	/**
	 * Map Greenhouse form to our components.
	 *
	 * @param array<string, mixed>  $formAdditionalProps Additional props to pass to form.
	 *
	 * @return string
	 */
	public function getForm(array $formAdditionalProps): string
	{
		// Get post ID prop.
		$formId = (string) $formAdditionalProps['formPostId'] ? Helper::encryptor('decrypt', $formAdditionalProps['formPostId']) : '';
		if (empty($formId)) {
			return '';
		}

		// Get Job Id.
		$jobId = $this->getSettingsValue(SettingsGreenhouse::SETTINGS_GREENHOUSE_JOB_ID_KEY, (string) $formId);
		if (empty($jobId)) {
			return '';
		}

		// Get Job questions.
		$questions = $this->greenhouseClient->getJobQuestions($jobId);
		if (empty($questions)) {
			return '';
		}

		// Return form to the frontend.
		return $this->buildForm(
			$this->getFields($questions, (string) $formId),
			$formAdditionalProps
		);
	}

	/**
	 * Map Greenhouse fields to our components.
	 *
	 * @param array<string, mixed> $data Fields.
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFields(array $data, string $formId): array
	{
		$output = [];

		if (!$data) {
			return $output;
		}

		foreach ($data as $question) {
			if (empty($question)) {
				continue;
			}

			$fields = $question['fields'] ?? '';
			$label = $question['label'] ?? '';
			$required = $question['required'] ?? false;


			foreach ($fields as $field) {
				$type = $field['type'] ?? '';
				$name = $field['name'] ?? '';
				$values = $field['values'];

				if (
					$field['name'] === 'resume_text' &&
					$this->getSettingsValue(SettingsGreenhouse::SETTINGS_GREENHOUSE_HIDE_RESUME_TEXTAREA_KEY, $formId)
				) {
					continue;
				}

				if (
					$field['name'] === 'cover_letter_text' &&
					$this->getSettingsValue(SettingsGreenhouse::SETTINGS_GREENHOUSE_HIDE_COVER_LETTER_TEXTAREA_KEY, $formId)
				) {
					continue;
				}

				// In GH select and check box is the same, addes some conditions to fine tune output.
				switch ($type) {
					case 'input_text':
						$output[] = [
							'component' => 'input',
							'inputName' => $name,
							'inputFieldLabel' => $label,
							'inputId' => $name,
							'inputType' => $name === 'email' ? 'email' : 'text',
							'inputIsRequired' => $required,
							'inputIsEmail' => $name === 'email' ? 'true' : ''
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
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxName' => $name,
										'checkboxId' => $name,
										'checkboxIsRequired' => $required,
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
								'selectFieldLabel' => $label,
								'selectIsRequired' => $required,
								'selectOptions' => array_map(
									function ($selectOption) {
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

		$output[] = [
			'component' => 'submit',
			'submitValue' => __('Submit', 'eightshift-forms'),
			'submitFieldUseError' => false
		];

		return $output;
	}
}
