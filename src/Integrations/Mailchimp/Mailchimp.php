<?php

/**
 * Mailchimp integration class.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Mailchimp integration class.
 */
class Mailchimp extends AbstractFormBuilder implements MapperInterface, ServiceInterface
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
	public const FILTER_MAPPER_NAME = 'es_mailchimp_mapper_filter';

	/**
	 * Filter form fields.
	 *
	 * @var string
	 */
	public const FILTER_FORM_FIELDS_NAME = 'es_mailchimp_form_fields_filter';

	/**
	 * Field Mailchimp Tags.
	 *
	 * @var string
	 */
	public const FIELD_MAILCHIMP_TAGS_KEY = 'es-form-mailchimp-tags';

	/**
	 * Instance variable for Mailchimp data.
	 *
	 * @var MailchimpClientInterface
	 */
	protected $mailchimpClient;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	public $validator;

	/**
	 * Create a new instance.
	 *
	 * @param MailchimpClientInterface $mailchimpClient Inject Mailchimp which holds Mailchimp connect data.
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 */
	public function __construct(
		MailchimpClientInterface $mailchimpClient,
		ValidatorInterface $validator
	) {
		$this->mailchimpClient = $mailchimpClient;
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
		$formAdditionalProps['formPostId'] = $formId;

		// Get form type.
		$formAdditionalProps['formType'] = SettingsMailchimp::SETTINGS_TYPE_KEY;

		// Check if it is loaded on the front or the backend.
		$ssr = (bool) $formAdditionalProps['ssr'] ?? false;

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
		// Get item Id.
		$itemId = $this->getSettingsValue(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_KEY, (string) $formId);
		if (empty($itemId)) {
			return [];
		}

		// Get fields.
		$fields = $this->mailchimpClient->getItem($itemId);
		if (empty($fields)) {
			return [];
		}

		return $this->getFields($fields, $formId, $ssr);
	}

	/**
	 * Map Mailchimp fields to our components.
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

		$output[] = [
			'component' => 'input',
			'inputName' => 'email_address',
			'inputFieldLabel' => __('Email address', 'eightshift-forms'),
			'inputId' => 'email_address',
			'inputType' => 'text',
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
			$value = $field['default_value'] ?? '';
			$dateFormat = isset($field['options']['date_format']) ? $this->validator->getValidationPattern($field['options']['date_format']) : '';
			$options = $field['options']['choices'] ?? [];
			$id = $name;

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
						'inputValidationPattern' => $dateFormat,
					];
					break;
				case 'address':
					$output[] = [
						'component' => 'input',
						'inputName' => 'address',
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => 'text',
						'inputIsRequired' => $required,
						'inputValue' => $value,
						'inputValidationPattern' => $dateFormat,
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
						'inputValidationPattern' => $dateFormat,
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
						'inputValidationPattern' => $dateFormat,
					];
					break;
				case 'birthday':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
						'inputType' => 'text',
						'inputIsRequired' => $required,
						'inputValue' => $value,
						'inputValidationPattern' => $dateFormat,
					];
					break;
				case 'radio':
					$output[] = [
						'component' => 'radios',
						'radiosId' => $id,
						'radiosName' => $name,
						'radiosIsRequired' => $required,
						'radiosContent' => array_map(
							static function ($radio) {
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
				case 'dropdown':
					$output[] = [
						'component' => 'select',
						'selectId' => $id,
						'selectName' => $name,
						'selectIsRequired' => $required,
						'selectOptions' => array_map(
							static function ($option) {
								return [
									'component' => 'select-option',
									'selectOptionLabel' => $option,
									'selectOptionValue' => $option,
								];
							},
							$options
						),
					];
					break;
			}
		}

		$tagsItems = $this->mailchimpClient->getTags($this->getSettingsValue(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_KEY, $formId));

		if ($tagsItems) {
			$tagsSelected = $this->getSettingsValue(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_TAGS_KEY, $formId);
			$tagsShow = $this->getSettingsValue(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId);

			switch ($tagsShow) {
				case 'select':
					$selectedOption = explode(', ', $tagsSelected) ?? [];

					$output[] = [
						'component' => 'select',
						'selectFieldLabel' => __('Tags', 'eightshift-forms'),
						'selectId' => self::FIELD_MAILCHIMP_TAGS_KEY,
						'selectName' => self::FIELD_MAILCHIMP_TAGS_KEY,
						'selectOptions' => array_merge(
							[
								[
									'component' => 'select-option',
									'selectOptionLabel' => '',
									'selectOptionValue' => '',
								],
							],
							array_map(
								static function ($option) use ($selectedOption) {
									return [
										'component' => 'select-option',
										'selectOptionLabel' => $option['name'],
										'selectOptionValue' => $option['name'],
										'selectOptionIsSelected' => $selectedOption[0] === $option['name'],
									];
								},
								$tagsItems
							)
						),
					];
					break;
				case 'checkboxes':
					$selectedOption = explode(', ', $tagsSelected) ?? [];

					$output[] = [
						'component' => 'checkboxes',
						'checkboxesFieldLabel' => __('Tags', 'eightshift-forms'),
						'checkboxesId' => self::FIELD_MAILCHIMP_TAGS_KEY,
						'checkboxesName' => self::FIELD_MAILCHIMP_TAGS_KEY,
						'checkboxesContent' => array_map(
							static function ($option) use ($selectedOption) {
								return [
									'component' => 'checkbox',
									'checkboxLabel' => $option['name'],
									'checkboxValue' => $option['name'],
									'checkboxIsChecked' => $selectedOption[0] === $option['name'],
								];
							},
							$tagsItems
						),
					];
					break;
				default:
					$output[] = [
						'component' => 'input',
						'inputType' => 'hidden',
						'inputId' => self::FIELD_MAILCHIMP_TAGS_KEY,
						'inputName' => self::FIELD_MAILCHIMP_TAGS_KEY,
						'inputValue' => $tagsSelected,
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
			'submitServerSideRender' => $ssr,
		];

		// Change the final output if necesery.
		$dataFilterName = Filters::getIntegrationFilterName(SettingsMailchimp::SETTINGS_TYPE_KEY, 'data');
		if (has_filter($dataFilterName) && !is_admin()) {
			$output = \apply_filters($dataFilterName, $output) ?? [];
		}

		return $this->getIntegrationFieldsValue(
			$this->getSettingsValueGroup(SettingsMailchimp::SETTINGS_MAILCHIMP_INTEGRATION_FIELDS_KEY, $formId),
			$output,
			SettingsMailchimp::SETTINGS_TYPE_KEY
		);
	}
}
