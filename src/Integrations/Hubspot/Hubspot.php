<?php

/**
 * Hubspot Mapper integration class.
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Hubspot;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Hubspot integration class.
 */
class Hubspot extends AbstractFormBuilder implements MapperInterface, ServiceInterface
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
	public const FILTER_MAPPER_NAME = 'es_hubspot_mapper_filter';

	/**
	 * Filter form fields.
	 *
	 * @var string
	 */
	public const FILTER_FORM_FIELDS_NAME = 'es_hubspot_form_fields_filter';

	/**
	 * Instance variable for Hubspot data.
	 *
	 * @var HubspotClientInterface
	 */
	protected $hubspotClient;

	/**
	 * Create a new instance.
	 *
	 * @param HubspotClientInterface $hubspotClient Inject Hubspot which holds Hubspot connect data.
	 */
	public function __construct(HubspotClientInterface $hubspotClient)
	{
		$this->hubspotClient = $hubspotClient;
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
		$formAdditionalProps['formType'] = SettingsHubspot::SETTINGS_TYPE_KEY;

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

		// Return form to the frontend.
		return $this->buildForm(
			$this->getFormFields($formIdDecoded),
			$formAdditionalProps
		);
	}

	/**
	 * Get Hubspot maped form fields.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFormFields(string $formId): array
	{
		// Get Job Id.
		$formId = $this->getSettingsValue(SettingsHubspot::SETTINGS_HUBSPOT_FORM_ID_KEY, (string) $formId);
		if (empty($formId)) {
			return [];
		}

		// Get Form.
		$form = $this->hubspotClient->getForm($formId);
		if (empty($form)) {
			return [];
		}

		return $this->getFields($form['fields'], $formId);
	}

	/**
	 * Map Hubspot fields to our components.
	 *
	 * @param array<string, mixed> $data Fields.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $data, string $formId): array
	{
		$output = [];

		if (!$data) {
			return $output;
		}

		foreach ($data as $item) {
			if (empty($item)) {
				continue;
			}

			$fields = $item['fields'] ?? [];

			if (!$fields) {
				continue;
			}

			foreach ($fields as $field) {
				$property = $field['propertyObjectType'] ?? 'CONTACT';
				$name = $field['name'] ?? '';
				$label = $field['label'] ?? '';
				$type = $field['fieldType'] ?? '';
				$required = $field['required'] ?? false;
				$value = $field['default_value'] ?? '';
				$placeholder = $field['placeholder'] ?? '';
				$options = $field['options'] ?? '';
				$id = $name;

				if ($property !== 'CONTACT') {
					$name = "{$property}.{$name}";
				}

				switch ($type) {
					case 'text':
						$output[] = $this->getIntegrationFieldsValue(
							SettingsHubspot::SETTINGS_HUBSPOT_INTEGRATION_BREAKPOINTS_KEY,
							$formId,
							[
								'component' => 'input',
								'inputFieldLabel' => $label,
								'inputId' => $id,
								'inputName' => $name,
								'inputType' => 'text',
								'inputPlaceholder' => $placeholder,
								'inputIsRequired' => $required,
								'inputValue' => $value,
							]
						);
						break;
					case 'textarea':
						$output[] = $this->getIntegrationFieldsValue(
							SettingsHubspot::SETTINGS_HUBSPOT_INTEGRATION_BREAKPOINTS_KEY,
							$formId,
							[
								'component' => 'textarea',
								'textareaFieldLabel' => $label,
								'textareaId' => $id,
								'textareaName' => $name,
								'textareaType' => 'textarea',
								'textareaPlaceholder' => $placeholder,
								'textareaIsRequired' => $required,
								'textareaValue' => $value,
							]
						);
						break;
					case 'file':
						$output[] = $this->getIntegrationFieldsValue(
							SettingsHubspot::SETTINGS_HUBSPOT_INTEGRATION_BREAKPOINTS_KEY,
							$formId,
							[
								'component' => 'file',
								'fileFieldLabel' => $label,
								'fileId' => $id,
								'fileName' => $name,
								'fileType' => 'text',
								'filePlaceholder' => $placeholder,
								'fileIsRequired' => $required,
								'fileValue' => $value,
							]
						);
						break;
					case 'select':
						$output[] = $this->getIntegrationFieldsValue(
							SettingsHubspot::SETTINGS_HUBSPOT_INTEGRATION_BREAKPOINTS_KEY,
							$formId,
							[
								'component' => 'select',
								'selectFieldLabel' => $label,
								'selectId' => $id,
								'selectName' => $name,
								'selectType' => 'select',
								'selectPlaceholder' => $placeholder,
								'selectIsRequired' => $required,
								'selectValue' => $value,
								'selectOptions' => array_map(
									function ($selectOption) {
										return [
											'component' => 'select-option',
											'selectOptionLabel' => $selectOption['label'],
											'selectOptionValue' => $selectOption['value'],
										];
									},
									$options
								),
							]
						);
						break;
					case 'booleancheckbox':
						$output[] = $this->getIntegrationFieldsValue(
							SettingsHubspot::SETTINGS_HUBSPOT_INTEGRATION_BREAKPOINTS_KEY,
							$formId,
							[
								'component' => 'checkboxes',
								'checkboxesId' => $id,
								'checkboxesName' => $name,
								'checkboxesIsRequired' => $required,
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => $label,
									]
								],
							]
						);
						break;
					case 'checkbox':
						$output[] = $this->getIntegrationFieldsValue(
							SettingsHubspot::SETTINGS_HUBSPOT_INTEGRATION_BREAKPOINTS_KEY,
							$formId,
							[
								'component' => 'checkboxes',
								'checkboxesId' => $name,
								'checkboxesName' => $name,
								'checkboxesIsRequired' => $required,
								'checkboxesContent' => array_map(
									function ($checkbox) {
										return [
											'component' => 'checkbox',
											'checkboxLabel' => $checkbox['label'],
											'checkboxValue' => $checkbox['value'],
										];
									},
									$options
								),
							]
						);
						break;
					case 'radio':
						$output[] = $this->getIntegrationFieldsValue(
							SettingsHubspot::SETTINGS_HUBSPOT_INTEGRATION_BREAKPOINTS_KEY,
							$formId,
							[
								'component' => 'radios',
								'radiosId' => $id,
								'radiosName' => $name,
								'radiosIsRequired' => $required,
								'radiosContent' => array_map(
									function ($radio) {
										return [
											'component' => 'radio',
											'radioLabel' => $radio['label'],
											'radioValue' => $radio['value'],
										];
									},
									$options
								),
							]
						);
						break;
					case 'consent':
						$output[] = $this->getIntegrationFieldsValue(
							SettingsHubspot::SETTINGS_HUBSPOT_INTEGRATION_BREAKPOINTS_KEY,
							$formId,
							[
								'component' => 'checkboxes',
								'checkboxesFieldBeforeContent' => $field['beforeText'] ?? '',
								'checkboxesFieldAfterContent' => $field['afterText'] ?? '',
								'checkboxesId' => $id,
								'checkboxesName' => $name,
								'checkboxesIsRequired' => $required,
								'checkboxesContent' => array_map(
									function ($checkbox) {
										return [
											'component' => 'checkbox',
											'checkboxLabel' => $checkbox['label'],
											'checkboxValue' => $checkbox['label'],
										];
									},
									$options
								),
							]
						);
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
