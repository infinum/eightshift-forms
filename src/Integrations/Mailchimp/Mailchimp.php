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
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

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
		\add_filter(static::FILTER_FORM_FIELDS_NAME, [$this, 'getFormBlockGrammarArray'], 10, 2);
	}

	public function getFormFields(string $formId, bool $ssr = false): array
	{
		return [];
	}

	public function getFormBlockGrammarArray(string $formId, string $itemId): array
	{
		$output = [
			'type' => SettingsMailchimp::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
			'fields' => [],
		];

		// Get fields.
		$item = $this->mailchimpClient->getItem($itemId);
		if (empty($item)) {
			return $output;
		}

		$fields = $this->getFields($item, $formId);

		if (!$fields) {
			return $output;
		}

		$output['itemId'] = $itemId;
		$output['fields'] = $fields;

		return $output;
	}

	/**
	 * Map Mailchimp fields to our components.
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

		$output[] = [
			'component' => 'input',
			'inputName' => 'email_address',
			'inputFieldLabel' => \__('Email address', 'eightshift-forms'),
			'inputId' => 'email_address',
			'inputType' => 'text',
			'inputIsEmail' => true,
			'inputIsRequired' => true,
			'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
				'inputIsRequired',
				'inputIsEmail',
				'inputType',
			]),
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
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputId' => $id,
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
						'inputId' => $id,
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
						'inputId' => $id,
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
						'inputId' => $id,
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
						'inputId' => $id,
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
						'radiosId' => $id,
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
						'selectId' => $id,
						'selectName' => $name,
						'selectFieldLabel' => $label,
						'selectTracking' => $name,
						'selectIsRequired' => $required,
						'selectOptions' => \array_map(
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

		// $tagsItems = $this->mailchimpClient->getTags($this->getSettingsValue(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_KEY, $formId));

		// if ($tagsItems) {
		// 	$tagsSelected = $this->getSettingsValue(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_TAGS_KEY, $formId);
		// 	$tagsLabels = $this->getSettingsValueGroup(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_TAGS_LABELS_KEY, $formId);
		// 	$tagsShow = $this->getSettingsValue(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId);

		// 	// Detect if some tags are selected to display on the frontend.
		// 	if (!empty($tagsSelected)) {
		// 		// Tags are stored like string so explode is necesery.
		// 		$selectedIds = \array_flip(\explode(', ', $tagsSelected));

		// 		// Map selected items with provided ones.
		// 		$tagsItems = \array_filter(
		// 			$tagsItems,
		// 			static function ($item) use ($selectedIds) {
		// 				return isset($selectedIds[$item['id']]);
		// 			}
		// 		);
		// 	}

		// 	if ($tagsItems) {
		// 		$customTagParamName = AbstractBaseRoute::CUSTOM_FORM_PARAMS['mailchimpTags'];

		// 		switch ($tagsShow) {
		// 			case 'select':
		// 				$output[] = [
		// 					'component' => 'select',
		// 					'selectFieldLabel' => \__('Tags', 'eightshift-forms'),
		// 					'selectId' => $customTagParamName,
		// 					'selectName' => $customTagParamName,
		// 					'selectTracking' => $customTagParamName,
		// 					'selectOptions' => \array_merge(
		// 						[
		// 							[
		// 								'component' => 'select-option',
		// 								'selectOptionLabel' => '',
		// 								'selectOptionValue' => '',
		// 							],
		// 						],
		// 						\array_map(
		// 							static function ($option) use ($tagsLabels) {
		// 								$name = $option['name'] ?? '';
		// 								$id = $option['id'] ?? '';
		// 								$nameOverride = $name;

		// 								// Find tag label override.
		// 								if (isset($tagsLabels[$id]) && !empty($tagsLabels[$id])) {
		// 									$nameOverride = $tagsLabels[$id];
		// 								}

		// 								return [
		// 									'component' => 'select-option',
		// 									'selectOptionLabel' => $nameOverride,
		// 									'selectOptionValue' => $name,
		// 								];
		// 							},
		// 							$tagsItems
		// 						)
		// 					),
		// 				];
		// 				break;
		// 			case 'checkboxes':
		// 				$output[] = [
		// 					'component' => 'checkboxes',
		// 					'checkboxesFieldLabel' => \__('Tags', 'eightshift-forms'),
		// 					'checkboxesId' => $customTagParamName,
		// 					'checkboxesName' => $customTagParamName,
		// 					'checkboxesContent' => \array_map(
		// 						static function ($option) use ($customTagParamName, $tagsLabels) {
		// 							$name = $option['name'] ?? '';
		// 							$id = $option['id'] ?? '';
		// 							$nameOverride = $name;

		// 							// Find tag label override.
		// 							if (isset($tagsLabels[$id]) && !empty($tagsLabels[$id])) {
		// 								$nameOverride = $tagsLabels[$id];
		// 							}

		// 							return [
		// 								'component' => 'checkbox',
		// 								'checkboxLabel' => $nameOverride,
		// 								'checkboxValue' => $name,
		// 								'checkboxTracking' => $customTagParamName,
		// 							];
		// 						},
		// 						$tagsItems
		// 					),
		// 				];
		// 				break;
		// 			default:
		// 				if (!empty($tagsSelected)) {
		// 					$tagsItems = \array_map(
		// 						static function ($item) {
		// 							return $item['name'];
		// 						},
		// 						$tagsItems
		// 					);

		// 					$tagsItems = \implode(', ', $tagsItems);

		// 					$output[] = [
		// 						'component' => 'input',
		// 						'inputType' => 'hidden',
		// 						'inputId' => $customTagParamName,
		// 						'inputName' => $customTagParamName,
		// 						'inputTracking' => $customTagParamName,
		// 						'inputValue' => $tagsItems,
		// 					];
		// 				};
		// 				break;
		// 		}
		// 	}
		// }

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitId' => 'submit',
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
}
