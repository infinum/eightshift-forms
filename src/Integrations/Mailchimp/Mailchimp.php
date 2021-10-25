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
	 * Filter Name
	 */
	public const FILTER_MAPPER_NAME = 'es_mailchimp_mapper_filter';

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
	}

	/**
	 * Map Mailchimp form to our components.
	 *
	 * @param array<string, mixed> $formAdditionalProps Additional props to pass to form.
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
		$formId = $this->getSettingsValue(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_KEY, (string) $formId);
		if (empty($formId)) {
			return '';
		}

		// Get fields.
		$fields = $this->mailchimpClient->getListFields($formId);
		if (empty($fields)) {
			return '';
		}

		return $this->buildForm(
			$this->getFields($fields, (string) $formId),
			$formAdditionalProps
		);
	}

	/**
	 * Map Mailchimp fields to our components.
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

		$output[] = [
			'component' => 'input',
			'inputName' => 'email_address',
			'inputFieldLabel' => __('Email adress', 'eightshift-forms'),
			'inputId' => 'email_address',
			'inputType' => 'email',
			'inputIsEmail' => true,
			'inputIsRequired' => true,
		];

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
			$id = $field['merge_id'] ?? '';

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
				case 'phone':
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
