<?php

/**
 * ActiveCampaign integration class.
 *
 * @package EightshiftForms\Integrations\ActiveCampaign
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\ActiveCampaign;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\ActiveCampaign\ActiveCampaignClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * ActiveCampaign integration class.
 */
class ActiveCampaign extends AbstractFormBuilder implements MapperInterface, ServiceInterface
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
	public const FILTER_FORM_FIELDS_NAME = 'es_active_campaign_form_fields_filter';

	/**
	 * List all standard fields that must be mapped differently than custom fields.
	 *
	 * @var array<int, string>
	 */
	public const STANDARD_FIELDS = [
		'firstName',
		'lastName',
		'fullName',
		'phone',
		'email',
	];

	/**
	 * Instance variable for ActiveCampaign data.
	 *
	 * @var ActiveCampaignClientInterface
	 */
	private $activeCampaignClient;

	/**
	 * Create a new instance.
	 *
	 * @param ActiveCampaignClientInterface $activeCampaignClient Inject ActiveCampaign which holds ActiveCampaign connection data.
	 */
	public function __construct(ActiveCampaignClientInterface $activeCampaignClient)
	{
		$this->activeCampaignClient = $activeCampaignClient;
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
			'type' => SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
			'innerId' => $innerId,
			'fields' => [],
		];

		// Get fields.
		$item = $this->activeCampaignClient->getItem($itemId);

		if (empty($item)) {
			return $output;
		}

		$fields = $this->getFields($item, $formId);

		if (!$fields) {
			return $output;
		}

		$output['fields'] = $fields;

		return $output;
	}

	/**
	 * Map ActiveCampaign fields to our components.
	 *
	 * @param array<string, mixed> $data Fields.
	 * @param string $formId Form ID.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $data, string $formId): array
	{
		$output = [];

		// Bailout if data is empty.
		if (!$data) {
			return $output;
		}

		// Find fields.
		$fields = $data['fields'] ?? [];

		// Bailout if fields are empty.
		if (!$fields) {
			return $output;
		}

		// Loop fields and map.
		foreach ($fields as $field) {
			if (empty($field)) {
				continue;
			}

			$type = $field['type'] ?? '';
			$name = $field['name'] ?? '';
			$label = $field['label'] ?? '';
			$header = $field['header'] ?? '';
			$isRequired = isset($field['isRequired']) ? (bool) $field['isRequired'] : false;
			$options = $field['options'] ?? [];
			$id = $field['id'] ?? '';

			if (!$name) {
				$name = $id;
			}

			// Some fields will not have label so use header.
			if (!$label) {
				$label = $header;
			}

			switch ($type) {
				case 'firstname':
					$output[] = [
						'component' => 'input',
						'inputName' => 'firstName',
						'inputTracking' => 'firstName',
						'inputFieldLabel' => $label,
						'inputType' => 'text',
						'inputIsRequired' => (bool) $isRequired,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
						]),
					];
					break;
				case 'lastname':
					$output[] = [
						'component' => 'input',
						'inputName' => 'lastName',
						'inputTracking' => 'lastName',
						'inputFieldLabel' => $label,
						'inputType' => 'text',
						'inputIsRequired' => (bool) $isRequired,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
						]),
					];
					break;
				case 'fullname':
					$output[] = [
						'component' => 'input',
						'inputName' => 'fullName',
						'inputTracking' => 'fullName',
						'inputFieldLabel' => $label,
						'inputType' => 'text',
						'inputIsRequired' => (bool) $isRequired,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'inputIsRequired' : '',
						]),
					];
					break;
				case 'hidden':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldHidden' => true,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							'inputFieldHidden',
						]),
					];
					break;
				case 'textarea':
					$output[] = [
						'component' => 'textarea',
						'textareaName' => $name,
						'textareaTracking' => $name,
						'textareaFieldLabel' => $label,
						'textareaIsRequired' => (bool) $isRequired,
						'textareaDisabledOptions' => $this->prepareDisabledOptions('textarea', [
							$isRequired ? 'textareaIsRequired' : '',
						]),
					];
					break;
				case 'email':
					$output[] = [
						'component' => 'input',
						'inputName' => 'email',
						'inputFieldLabel' => $header,
						'inputType' => 'text',
						'inputIsEmail' => true,
						'inputIsRequired' => 1,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							'inputIsRequired',
							'inputIsEmail',
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
						'inputIsRequired' => (bool) $isRequired,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$isRequired ? 'textareaIsRequired' : '',
						]),
					];
					break;
				case 'checkbox':
					$output[] = [
						'component' => 'checkboxes',
						'checkboxesName' => $name,
						'checkboxesFieldLabel' => $label,
						'checkboxesIsRequired' => (bool) $isRequired,
						'checkboxesContent' => \array_map(
							function ($checkbox) use ($name) {
								return [
									'component' => 'checkbox',
									'checkboxLabel' => $checkbox['value'],
									'checkboxValue' => $checkbox['value'],
									'checkboxTracking' => $name,
									'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [], false),
								];
							},
							$options
						),
						'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
							$isRequired ? 'checkboxesIsRequired' : '',
						]),
					];
					break;
				case 'radio':
					$output[] = [
						'component' => 'radios',
						'radiosName' => $name,
						'radiosFieldLabel' => $label,
						'radiosIsRequired' => (bool) $isRequired,
						'radiosTracking' => $name,
						'radiosContent' => \array_map(
							function ($radio) {
								return [
									'component' => 'radio',
									'radioLabel' => $radio['value'],
									'radioValue' => $radio['value'],
									'radioDisabledOptions' => $this->prepareDisabledOptions('radio', [
										'radioValue',
									], false),
								];
							},
							$options
						),
						'radiosDisabledOptions' => $this->prepareDisabledOptions('radios', [
							$isRequired ? 'radiosIsRequired' : '',
						]),
					];
					break;
				case 'dropdown':
					$output[] = [
						'component' => 'select',
						'selectName' => $name,
						'selectFieldLabel' => $label,
						'selectTracking' => $name,
						'selectIsRequired' => (bool) $isRequired,
						'selectContent' => \array_map(
							function ($option) {
								return [
									'component' => 'select-option',
									'selectOptionLabel' => $option['value'],
									'selectOptionValue' => $option['value'],
									'selectOptionDisabledOptions' => $this->prepareDisabledOptions('select-option', [
										'selectOptionValue',
									], false),
								];
							},
							$options
						),
						'selectDisabledOptions' => $this->prepareDisabledOptions('select', [
							$isRequired ? 'selectIsRequired' : '',
						]),
					];
					break;
			}
		}

		// Find if we have actions in the data set.
		$actions = $data['actions'] ?? [];

		if ($actions) {
			foreach ($actions as $key => $value) {
				$action = $value['action'] ? \ucfirst($value['action']) : '';
				$actionValue = $value['value'] ?? '';

				if (!$action || !$actionValue) {
					continue;
				}

				// Map actions to hidden input fields.
				$output[] = [
					'component' => 'input',
					'inputFieldLabel' => $action,
					'inputFieldHidden' => true,
					'inputName' => 'action',
					'inputValue' => $actionValue,
					'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
						'inputFieldHidden',
					]),
				];
			}
		}

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitFieldUseError' => false,
			'submitDisabledOptions' => $this->prepareDisabledOptions('submit'),
		];

		// Change the final output if necesery.
		$filterName = Filters::getFilterName(['integrations', SettingsActiveCampaign::SETTINGS_TYPE_KEY, 'data']);
		if (\has_filter($filterName) && \is_admin()) {
			$output = \apply_filters($filterName, $output, $formId) ?? [];
		}

		return $output;
	}
}
