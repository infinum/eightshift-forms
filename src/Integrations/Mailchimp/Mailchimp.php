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
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
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
			'type' => SettingsMailchimp::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
			'innerId' => $innerId,
			'fields' => [],
		];

		// Get fields.
		$item = $this->mailchimpClient->getItem($itemId);
		if (empty($item)) {
			return $output;
		}

		$fields = $this->getFields($item, $formId, $itemId);

		if (!$fields) {
			return $output;
		}

		$output['fields'] = $fields;

		return $output;
	}

	/**
	 * Map Mailchimp fields to our components.
	 *
	 * @param array<string, mixed> $data Fields.
	 * @param string $formId Form ID.
	 * @param string $itemId Integration item id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $data, string $formId, string $itemId): array
	{
		$output = [];

		if (!$data) {
			return $output;
		}

		$output[] = [
			'component' => 'input',
			'inputName' => 'email_address',
			'inputFieldLabel' => \__('Email address', 'eightshift-forms'),
			'inputType' => 'text',
			'inputIsEmail' => true,
			'inputIsRequired' => true,
			'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
				'inputIsRequired',
				'inputIsEmail',
				'inputType',
			]),
		];

		foreach ($data['fields'] as $field) {
			if (empty($field)) {
				continue;
			}

			$type = $field['type'] ?? '';
			$name = $field['tag'] ?? '';
			$label = $field['name'] ?? '';
			$required = $field['required'] ?? false;
			$value = $field['default_value'] ?? '';
			$dateFormat = $field['options']['date_format'] ?? '';
			$options = $field['options']['choices'] ?? [];
			$id = $name;

			switch ($type) {
				case 'text':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'text',
						'inputIsRequired' => $required,
						'inputValue' => $value,
						'inputValidationPattern' => $dateFormat,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$required ? 'inputIsRequired' : '',
							$dateFormat ? 'inputValidationPattern' : '',
						]),
					];
					break;
				case 'address':
					$output[] = [
						'component' => 'input',
						'inputName' => 'address',
						'inputTracking' => 'address',
						'inputFieldLabel' => $label,
						'inputType' => 'text',
						'inputIsRequired' => $required,
						'inputValue' => $value,
						'inputValidationPattern' => $dateFormat,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$required ? 'inputIsRequired' : '',
							$dateFormat ? 'inputValidationPattern' : '',
						]),
					];
					break;
				case 'number':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'number',
						'inputIsRequired' => $required,
						'inputValue' => $value,
						'inputValidationPattern' => $dateFormat,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$required ? 'inputIsRequired' : '',
							$dateFormat ? 'inputValidationPattern' : '',
						]),
					];
					break;
				case 'phone':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'tel',
						'inputIsRequired' => $required,
						'inputValue' => $value,
						'inputValidationPattern' => $dateFormat,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$required ? 'inputIsRequired' : '',
							$dateFormat ? 'inputValidationPattern' : '',
						]),
					];
					break;
				case 'birthday':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'text',
						'inputIsRequired' => $required,
						'inputValue' => $value,
						'inputValidationPattern' => $dateFormat,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$required ? 'inputIsRequired' : '',
							$dateFormat ? 'inputValidationPattern' : '',
						]),
					];
					break;
				case 'radio':
					$output[] = [
						'component' => 'radios',
						'radiosName' => $name,
						'radiosFieldLabel' => $label,
						'radiosIsRequired' => $required,
						'radiosContent' => \array_map(
							function ($radio) use ($name) {
								return [
									'component' => 'radio',
									'radioLabel' => $radio,
									'radioValue' => $radio,
									'radioTracking' => $name,
									'radioDisabledOptions' => $this->prepareDisabledOptions('radio', [
										'radioValue',
									], false),
								];
							},
							$options
						),
						'radiosDisabledOptions' => $this->prepareDisabledOptions('radios', [
							$required ? 'radiosIsRequired' : '',
						]),
					];
					break;
				case 'dropdown':
					$output[] = [
						'component' => 'select',
						'selectName' => $name,
						'selectFieldLabel' => $label,
						'selectTracking' => $name,
						'selectIsRequired' => $required,
						'selectContent' => \array_map(
							function ($option) {
								return [
									'component' => 'select-option',
									'selectOptionLabel' => $option,
									'selectOptionValue' => $option,
									'selectOptionDisabledOptions' => $this->prepareDisabledOptions('select-option', [
										'selectOptionValue',
									], false),
								];
							},
							$options
						),
						'selectDisabledOptions' => $this->prepareDisabledOptions('select', [
							$required ? 'selectIsRequired' : '',
						]),
					];
					break;
			}
		}

		$tagsItems = $this->mailchimpClient->getTags($itemId);

		if ($tagsItems) {
			$output = [
				...$output,
				...$this->getTagsFields($formId, $tagsItems),
			];
		}

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitFieldUseError' => false,
			'submitDisabledOptions' => $this->prepareDisabledOptions('submit'),
		];

		// Change the final output if necesery.
		$dataFilterName = Filters::getIntegrationFilterName(SettingsMailchimp::SETTINGS_TYPE_KEY, 'data');
		if (\has_filter($dataFilterName) && !\is_admin()) {
			$output = \apply_filters($dataFilterName, $output, $formId) ?? [];
		}

		return $output;
	}

	/**
	 * Get tags field output depending on the settings type.
	 *
	 * @param string $formId Form Id.
	 * @param array<mixed> $items Items from the original build.
	 *
	 * @return array<mixed>
	 */
	private function getTagsFields(string $formId, array $items): array
	{
		if (!$items) {
			return [];
		}

		$customTagParamName = AbstractBaseRoute::CUSTOM_FORM_PARAMS['mailchimpTags'];

		switch ($this->getSettingsValue(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId)) {
			case 'select':
				return [
					[
						'component' => 'select',
						'selectFieldLabel' => \__('Tags', 'eightshift-forms'),
						'selectName' => $customTagParamName,
						'selectTracking' => $customTagParamName,
						'selectContent' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => '',
								'selectOptionValue' => '',
							],
							...\array_map(
								function ($option) {
									$name = $option['name'] ?? '';

									return [
										'component' => 'select-option',
										'selectOptionLabel' => $name,
										'selectOptionValue' => $name,
										'selectOptionDisabledOptions' => $this->prepareDisabledOptions('select-option', [
											'selectOptionValue',
										], false),
									];
								},
								$items
							),
						],
						'selectDisabledOptions' => $this->prepareDisabledOptions('select'),
					],
				];
			case 'checkboxes':
				return [
					[
						'component' => 'checkboxes',
						'checkboxesFieldLabel' => \__('Tags', 'eightshift-forms'),
						'checkboxesName' => $customTagParamName,
						'checkboxesContent' => \array_map(
							function ($option) use ($customTagParamName) {
								$name = $option['name'] ?? '';

								return [
									'component' => 'checkbox',
									'checkboxLabel' => $name,
									'checkboxValue' => $name,
									'checkboxTracking' => $customTagParamName,
									'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
										'checkboxValue',
									], false),
								];
							},
							$items
						),
						'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes'),
					]
				];
			case 'radios':
				return [
					[
						'component' => 'radios',
						'radiosFieldLabel' => \__('Tags', 'eightshift-forms'),
						'radiosName' => $customTagParamName,
						'radiosContent' => \array_map(
							function ($option) use ($customTagParamName) {
								$name = $option['name'] ?? '';

								return [
									'component' => 'radio',
									'radioLabel' => $name,
									'radioValue' => $name,
									'radioTracking' => $customTagParamName,
									'radioDisabledOptions' => $this->prepareDisabledOptions('radio', [
										'radioValue',
									], false),
								];
							},
							$items
						),
						'radiosDisabledOptions' => $this->prepareDisabledOptions('radios'),
					],
				];
			case 'hidden':
				return [
					[
						'component' => 'checkboxes',
						'checkboxesFieldLabel' => \__('Tags', 'eightshift-forms'),
						'checkboxesFieldHidden' => true,
						'checkboxesName' => $customTagParamName,
						'checkboxesContent' => \array_map(
							function ($option) use ($customTagParamName) {
								$name = $option['name'] ?? '';

								return [
									'component' => 'checkbox',
									'checkboxLabel' => $name,
									'checkboxValue' => $name,
									'checkboxTracking' => $customTagParamName,
									'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
										'checkboxValue',
									], false),
								];
							},
							$items
						),
						'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
							'checkboxesIsRequired'
						]),
					],
				];
			default:
				return [];
		}
	}
}
