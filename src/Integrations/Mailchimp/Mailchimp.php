<?php

/**
 * Mailchimp integration class.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Mailchimp integration class.
 */
class Mailchimp extends AbstractFormBuilder implements MapperInterface, ServiceInterface
{

	/**
	 * Filter mapper.
	 *
	 * @var string
	 */
	public const FILTER_MAPPER_NAME = 'es_mailchimp_mapper_filter';

	/**
	 * Filter form fields.
	 *
	 * @var string
	 */
	public const FILTER_FORM_FIELDS_NAME = 'es_mailchimp_form_fields_filter';

	/**
	 * Instance variable for Mailchimp data.
	 *
	 * @var MailchimpClientInterface
	 */
	protected $mailchimpClient;

	/**
	 * Create a new instance.
	 *
	 * @param MailchimpClientInterface $mailchimpClient Inject Mailchimp which holds Mailchimp connect data.
	 */
	public function __construct(MailchimpClientInterface $mailchimpClient)
	{
		$this->mailchimpClient = $mailchimpClient;
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
	 * Map Mailchimp form to our components.
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

		return $this->buildForm(
			$this->getFormFields((string) $formId),
			$formAdditionalProps
		);
	}

	/**
	 * Get Mailchimp maped form fields.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFormFields(string $formId): array
	{
		// Get List Id.
		$listId = $this->getSettingsValue(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_KEY, (string) $formId);
		if (empty($listId)) {
			return [];
		}

		// Get fields.
		$fields = $this->mailchimpClient->getListFields($listId);
		if (empty($fields)) {
			return [];
		}

		return $this->getFields($fields);
	}

	/**
	 * Map Mailchimp fields to our components.
	 *
	 * @param array<string, mixed> $data Fields.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $data): array
	{
		$output = [];

		if (!$data) {
			return $output;
		}

		$output[] = [
			'component' => 'input',
			'inputName' => 'email_address',
			'inputFieldLabel' => __('Email adress', 'eightshift-forms'),
			'inputId' => 'email_address',
			'inputType' => 'email',
			'inputIsEmail' => true,
			'inputIsRequired' => true,
		];

		error_log( print_r( ( $data ), true ) );
		

		foreach ($data as $field) {
			if (empty($field)) {
				continue;
			}

			$type = $field['type'] ?? '';
			$name = $field['tag'] ?? '';
			$label = $field['name'] ?? '';
			$required = $field['required'] ?? false;
			$public = $field['public'] ?? false;
			$value = $field['default_value'] ?? '';
			$dateFormat = $field['options']['date_format'] ?? '';
			$options = $field['options']['choices'] ?? [];
			$id = $name;

			if (!$public) {
				continue;
			}

			switch ($type) {
				case 'text':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => 'text',
						'inputIsRequired' => $required,
						'inputValue' => $value,
					];
					break;
				case 'address':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => 'text',
						'inputIsRequired' => $required,
						'inputValue' => $value,
					];
					break;
				case 'number':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => 'number',
						'inputIsRequired' => $required,
						'inputValue' => $value,
					];
					break;
				case 'phone':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => 'tel',
						'inputIsRequired' => $required,
						'inputValue' => $value,
					];
				case 'birthday':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => 'date',
						'inputIsRequired' => $required,
						'inputValue' => $value,
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
									'radioLabel' => $radio,
									'radioValue' => $radio,
								];
							},
							$options
						),
					];
					break;
			}
		}

		$output[] = [
			'component' => 'submit',
			'submitValue' => __('Subscribe', 'eightshift-forms'),
			'submitFieldUseError' => false
		];

		return $output;
	}
}
