<?php

/**
 * Mailerlite integration class.
 *
 * @package EightshiftForms\Integrations\Mailerlite
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailerlite;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Mailerlite integration class.
 */
class Mailerlite extends AbstractFormBuilder implements MapperInterface, ServiceInterface
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
	public const FILTER_MAPPER_NAME = 'es_mailerlite_mapper_filter';

	/**
	 * Filter form fields.
	 *
	 * @var string
	 */
	public const FILTER_FORM_FIELDS_NAME = 'es_mailerlite_form_fields_filter';

	/**
	 * Field Mailerlite Tags.
	 *
	 * @var string
	 */
	public const FIELD_MAILERLITE_TAGS_KEY = 'es-form-mailerlite-tags';

	/**
	 * Instance variable for Mailerlite data.
	 *
	 * @var ClientInterface
	 */
	protected $mailerliteClient;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	public $validator;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $mailerliteClient Inject Mailerlite which holds Mailerlite connect data.
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 */
	public function __construct(
		ClientInterface $mailerliteClient,
		ValidatorInterface $validator
	) {
		$this->mailerliteClient = $mailerliteClient;
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
		\add_filter(static::FILTER_MAPPER_NAME, [$this, 'getForm']);
		\add_filter(static::FILTER_FORM_FIELDS_NAME, [$this, 'getFormFields']);
	}

	/**
	 * Map form to our components.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getForm(string $formId): string
	{
		$formAdditionalProps = [];

		$formIdDecoded = (string) Helper::encryptor('decrypt', $formId);

		// Get post ID prop.
		$formAdditionalProps['formPostId'] = $formId;

		// Get form type.
		$formAdditionalProps['formType'] = SettingsMailerlite::SETTINGS_TYPE_KEY;

		return $this->buildForm(
			$this->getFormFields($formIdDecoded),
			array_merge($formAdditionalProps, $this->getFormAdditionalProps($formIdDecoded))
		);
	}

	/**
	 * Get mapped form fields.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFormFields(string $formId): array
	{
		// Get item Id.
		$itemId = $this->getSettingsValue(SettingsMailerlite::SETTINGS_MAILERLITE_LIST_KEY, (string) $formId);
		if (empty($itemId)) {
			return [];
		}

		// Get fields.
		$fields = $this->mailerliteClient->getItem($itemId);
		if (empty($fields)) {
			return [];
		}

		return $this->getFields($fields, $formId);
	}

	/**
	 * Map Mailerlite fields to our components.
	 *
	 * @param array<string, mixed> $data Fields.
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $data, string $formId): array
	{
		$output = [];

		if (!$data) {
			return $output;
		}

		foreach ($data as $field) {
			if (empty($field)) {
				continue;
			}

			$type = $field['type'] ? strtolower($field['type']) : '';
			$name = $field['key'] ?? '';
			$label = $field['title'] ?? '';
			$id = $name;

			switch ($type) {
				case 'text':
				case 'date':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => 'text',
						'inputIsRequired' => $name === 'email',
						'inputIsEmail' => $name === 'email',
					];
					break;
				case 'number':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => 'number',
					];
					break;
			}
		}

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitId' => 'submit',
			'submitFieldUseError' => false,
			'submitFieldOrder' => count($output) + 1,
		];

		return $this->getIntegrationFieldsValue(
			$this->getSettingsValueGroup(SettingsMailerlite::SETTINGS_MAILERLITE_INTEGRATION_FIELDS_KEY, $formId),
			$output
		);
	}
}
