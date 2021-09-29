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
	 * Transient cache name.
	 */
	public const CACHE_GREENHOUSE_MAPPER_TRANSIENT_NAME = 'es_greenhouse_mapper_cache';

	/**
	 * Instance variable for Greenhouse data.
	 *
	 * @var GreenhouseClientInterface
	 */
	protected $greenhouseClient;

	/**
	 * Create a new instance.
	 *
	 * @param GreenhouseClientInterface $greenhouseClient Inject Greenhouse which holds greenhouse connect data.
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
	 * @param array $formAdditionalProps Additional props to pass to form.
	 *
	 * @return string
	 */
	public function getForm(array $formAdditionalProps): string
	{
		// Get post ID prop.
		$formId = $formAdditionalProps['formPostId'] ? Helper::encryptor('decrypt', $formAdditionalProps['formPostId']) : '';
		if (empty($formId)) {
			return '';
		}

		// Get Job Id.
		$jobId = $this->getSettingsValue(SettingsGreenhouse::SETTINGS_GREENHOUSE_JOB_ID_KEY, $formId);
		if (empty($jobId)) {
			return '';
		}

		// Get Job details.
		$job = $this->greenhouseClient->getJob($jobId);
		if (empty($job)) {
			return '';
		}

		// Get questions.
		$questions = $this->getFields($job['questions']);
		if (empty($questions)) {
			return '';
		}

		// Return form to the frontend.
		return $this->buildForm(
			$questions,
			$formAdditionalProps
		);
	}

	/**
	 * Map Greenhouse fields to our components.
	 *
	 * @param array $data Fields.
	 *
	 * @return array
	 */
	public function getFields(array $data): array
	{
		$output = [];

		if (!$data) {
			return $output;
		}

		foreach ($data as $question) {
			$fields = $question['fields'] ?? '';
			$label = $question['label'] ?? '';
			$required = $question['required'] ?? false;

			if (empty($question)) {
				continue;
			}

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
