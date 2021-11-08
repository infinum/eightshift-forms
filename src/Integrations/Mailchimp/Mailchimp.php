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
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingsGeneral;
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
	 * Instance variable for Mailchimp data.
	 *
	 * @var ClientInterface
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
	 * @param ClientInterface $mailchimpClient Inject Mailchimp which holds Mailchimp connect data.
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 */
	public function __construct(
		ClientInterface $mailchimpClient,
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
		$formAdditionalProps['formType'] = SettingsMailchimp::SETTINGS_TYPE_KEY;

		// Reset form on success.
		$formAdditionalProps['formResetOnSuccess'] = !Variables::isDevelopMode();

		// Disable scroll to field on error.
		$formAdditionalProps['formDisableScrollToFieldOnError'] = $this->isCheckboxOptionChecked(
			SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
			SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);

		// Disable scroll to global message on success.
		$formAdditionalProps['formDisableScrollToGlobalMessageOnSuccess'] = $this->isCheckboxOptionChecked(
			SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
			SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);

		// Tracking event name.
		$formAdditionalProps['formTrackingEventName'] = $this->getSettingsValue(
			SettingsGeneral::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY,
			$formIdDecoded
		);

		// Success redirect url.
		$formAdditionalProps['formSuccessRedirect'] = $this->getSettingsValue(
			SettingsGeneral::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY,
			$formIdDecoded
		);

		return $this->buildForm(
			$this->getFormFields($formIdDecoded),
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

		return $this->getFields($fields, $formId);
	}

	/**
	 * Map Mailchimp fields to our components.
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

		$integrationBreakpointsFields = $this->getSettingsValueGroup(SettingsMailchimp::SETTINGS_MAILCHIMP_INTEGRATION_BREAKPOINTS_KEY, $formId);

		$output[] = $this->getIntegrationFieldsValue(
			$integrationBreakpointsFields,
			[
				'component' => 'input',
				'inputName' => 'email_address',
				'inputFieldLabel' => __('Email adress', 'eightshift-forms'),
				'inputId' => 'email_address',
				'inputType' => 'email',
				'inputIsEmail' => true,
				'inputIsRequired' => true,
			]
		);

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
					$output[] = $this->getIntegrationFieldsValue(
						$integrationBreakpointsFields,
						[
							'component' => 'input',
							'inputName' => $name,
							'inputFieldLabel' => $label,
							'inputId' => $id,
							'inputType' => 'text',
							'inputIsRequired' => $required,
							'inputValue' => $value,
							'inputValidationPattern' => $dateFormat,
						]
					);
					break;
				case 'address':
					$output[] = $this->getIntegrationFieldsValue(
						$integrationBreakpointsFields,
						[
							'component' => 'input',
							'inputName' => 'address',
							'inputFieldLabel' => $label,
							'inputId' => $id,
							'inputType' => 'text',
							'inputIsRequired' => $required,
							'inputValue' => $value,
							'inputValidationPattern' => $dateFormat,
						]
					);
					break;
				case 'number':
					$output[] = $this->getIntegrationFieldsValue(
						$integrationBreakpointsFields,
						[
							'component' => 'input',
							'inputName' => $name,
							'inputFieldLabel' => $label,
							'inputId' => $id,
							'inputType' => 'number',
							'inputIsRequired' => $required,
							'inputValue' => $value,
							'inputValidationPattern' => $dateFormat,
						]
					);
					break;
				case 'phone':
					$output[] = $this->getIntegrationFieldsValue(
						$integrationBreakpointsFields,
						[
							'component' => 'input',
							'inputName' => $name,
							'inputFieldLabel' => $label,
							'inputId' => $id,
							'inputType' => 'tel',
							'inputIsRequired' => $required,
							'inputValue' => $value,
							'inputValidationPattern' => $dateFormat,
						]
					);
					break;
				case 'birthday':
					$output[] = $this->getIntegrationFieldsValue(
						$integrationBreakpointsFields,
						[
							'component' => 'input',
							'inputName' => $name,
							'inputFieldLabel' => $label,
							'inputId' => $id,
							'inputType' => 'text',
							'inputIsRequired' => $required,
							'inputValue' => $value,
							'inputValidationPattern' => $dateFormat,
						]
					);
					break;
				case 'radio':
					$output[] = $this->getIntegrationFieldsValue(
						$integrationBreakpointsFields,
						[
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
						]
					);
					break;
				case 'dropdown':
					$output[] = $this->getIntegrationFieldsValue(
						$integrationBreakpointsFields,
						[
							'component' => 'select',
							'selectId' => $id,
							'selectName' => $name,
							'selectIsRequired' => $required,
							'selectOptions' => array_map(
								function ($option) {
									return [
										'component' => 'select-option',
										'selectOptionLabel' => $option,
										'selectOptionValue' => $option,
									];
								},
								$options
							),
						]
					);
					break;
			}
		}

		$output[] = [
			'component' => 'submit',
			'submitValue' => __('Subscribe', 'eightshift-forms'),
			'submitFieldUseError' => false,
			'submitFieldOrder' => count($output) + 1,
		];

		return $output;
	}
}
