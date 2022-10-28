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
	 * Filter mapper.
	 *
	 * @var string
	 */
	public const FILTER_MAPPER_NAME = 'es_active_campaign_mapper_filter';

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
		$type = SettingsActiveCampaign::SETTINGS_TYPE_KEY;
		$formAdditionalProps['formType'] = $type;

		// Check if it is loaded on the front or the backend.
		$ssr = (bool) ($formAdditionalProps['ssr'] ?? false);

		return $this->buildForm(
			$this->getFormFields($formId, $ssr),
			\array_merge($formAdditionalProps, $this->getFormAdditionalProps($formId, $type))
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
		$itemId = $this->getSettingsValue(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_LIST_KEY, (string) $formId);
		if (empty($itemId)) {
			return [];
		}

		// Get fields.
		$fields = $this->activeCampaignClient->getItem($itemId);
		if (empty($fields)) {
			return [];
		}

		return $this->getFields($fields, $formId, $ssr);
	}

	/**
	 * Map ActiveCampaign fields to our components.
	 *
	 * @param array<string, mixed> $data Fields.
	 * @param string $formId Form ID.
	 * @param bool $ssr Does form load using SSR.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $data, string $formId, bool $ssr): array
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
			$required = $field['isRequired'] ?? false;
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
						'inputId' => 'firstName',
						'inputType' => 'text',
						'inputIsRequired' => $required,
						'blockSsr' => $ssr,
					];
					break;
				case 'lastname':
					$output[] = [
						'component' => 'input',
						'inputName' => 'lastName',
						'inputTracking' => 'lastName',
						'inputFieldLabel' => $label,
						'inputId' => 'lastName',
						'inputType' => 'text',
						'inputIsRequired' => $required,
						'blockSsr' => $ssr,
					];
					break;
				case 'fullname':
					$output[] = [
						'component' => 'input',
						'inputName' => 'fullName',
						'inputTracking' => 'fullName',
						'inputFieldLabel' => $label,
						'inputId' => 'fullName',
						'inputType' => 'text',
						'inputIsRequired' => $required,
						'blockSsr' => $ssr,
					];
					break;
				case 'hidden':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputId' => $id,
						'inputType' => 'hidden',
						'inputFieldHidden' => 'hidden',
						'blockSsr' => $ssr,
					];
					break;
				case 'textarea':
					$output[] = [
						'component' => 'textarea',
						'textareaName' => $name,
						'textareaTracking' => $name,
						'textareaFieldLabel' => $label,
						'textareaId' => $id,
						'textareaIsRequired' => $required,
						'blockSsr' => $ssr,
					];
					break;
				case 'email':
					$output[] = [
						'component' => 'input',
						'inputName' => 'email',
						'inputFieldLabel' => $header,
						'inputId' => 'email',
						'inputType' => 'text',
						'inputIsEmail' => true,
						'inputIsRequired' => 1,
						'blockSsr' => $ssr,
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
						'blockSsr' => $ssr,
					];
					break;
				case 'checkbox':
					$output[] = [
						'component' => 'checkboxes',
						'checkboxesId' => $id,
						'checkboxesName' => $name,
						'checkboxesFieldLabel' => $label,
						'checkboxesIsRequired' => $required,
						'checkboxesContent' => \array_map(
							static function ($checkbox) use ($name) {
								return [
									'component' => 'checkbox',
									'checkboxLabel' => $checkbox['value'],
									'checkboxValue' => $checkbox['value'],
									'checkboxTracking' => $name,
								];
							},
							$options
						),
						'blockSsr' => $ssr,
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
							static function ($radio) use ($name) {
								return [
									'component' => 'radio',
									'radioLabel' => $radio['value'],
									'radioValue' => $radio['value'],
									'radioTracking' => $name,
								];
							},
							$options
						),
						'blockSsr' => $ssr,
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
							static function ($option) {
								return [
									'component' => 'select-option',
									'selectOptionLabel' => $option['value'],
									'selectOptionValue' => $option['value'],
								];
							},
							$options
						),
						'blockSsr' => $ssr,
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
					'inputName' => 'action',
					'inputId' => "action{$action}[$key]",
					'inputType' => 'hidden',
					'inputValue' => $actionValue,
					'blockSsr' => $ssr,
				];
			}
		}

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitId' => 'submit',
			'submitFieldUseError' => false,
			'submitFieldOrder' => \count($output) + 1,
			'submitServerSideRender' => $ssr,
			'blockSsr' => $ssr,
		];

		// Change the final output if necesery.
		$dataFilterName = Filters::getIntegrationFilterName(SettingsActiveCampaign::SETTINGS_TYPE_KEY, 'data');
		if (\has_filter($dataFilterName) && !\is_admin()) {
			$output = \apply_filters($dataFilterName, $output, $formId) ?? [];
		}

		return $this->getIntegrationFieldsValue(
			$this->getSettingsValueGroup(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_INTEGRATION_FIELDS_KEY, $formId),
			$output,
			SettingsActiveCampaign::SETTINGS_TYPE_KEY
		);
	}
}
