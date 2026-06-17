<?php

/**
 * Pardot integration class.
 *
 * @package EightshiftForms\Integrations\Pardot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Pardot;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Pardot integration class.
 */
class Pardot extends AbstractFormBuilder implements MapperInterface, ServiceInterface
{
	/**
	 * Filter form fields.
	 *
	 * @var string
	 */
	public const FILTER_FORM_FIELDS_NAME = 'es_pardot_form_fields_filter';

	/**
	 * Instance variable for Pardot data.
	 *
	 * @var PardotClientInterface
	 */
	protected $pardotClient;

	/**
	 * Create a new instance.
	 *
	 * @param PardotClientInterface $pardotClient Inject Pardot client.
	 */
	public function __construct(PardotClientInterface $pardotClient)
	{
		$this->pardotClient = $pardotClient;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(static::FILTER_FORM_FIELDS_NAME, [$this, 'getFormFields'], 10, 3);
	}

	/**
	 * Get mapped form fields from integration.
	 *
	 * @param string $formId Form Id.
	 * @param string $itemId Integration/external form ID (form handler ID).
	 * @param string $innerId Unused for Pardot (single-level).
	 *
	 * @return array<string, array<int, array<string, mixed>>|string>
	 */
	public function getFormFields(string $formId, string $itemId, string $innerId): array
	{
		$output = [
			'type' => SettingsPardot::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
			'innerId' => $innerId,
			'fields' => [],
		];

		$fields = $this->pardotClient->getItem($itemId);

		if (empty($fields)) {
			return $output;
		}

		$mapped = $this->getFields($fields, $formId);

		if (!$mapped) {
			return $output;
		}

		$output['fields'] = $mapped;

		return $output;
	}

	/**
	 * Map Pardot handler fields to Eightshift form components.
	 *
	 * @param array<string, mixed> $fields Fields from PardotClient::getItem().
	 * @param string $formId Form ID.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $fields, string $formId): array
	{
		$output = [];

		foreach ($fields as $field) {
			if (empty($field)) {
				continue;
			}

			$name = $field['id'] ?? '';
			$label = $field['title'] ?? '';
			$dataFormat = \strtolower($field['dataFormat'] ?? 'text');
			$isRequired = (bool) ($field['isRequired'] ?? false);

			if (!$name) {
				continue;
			}

			switch ($dataFormat) {
				case 'email':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'email',
						'inputIsEmail' => true,
						'inputTypeCustom' => 'email',
						'inputIsRequired' => $isRequired,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							'inputIsEmail',
							'inputType',
							'inputTypeCustom',
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
						'inputIsNumber' => true,
						'inputTypeCustom' => 'number',
						'inputIsRequired' => $isRequired,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							'inputIsNumber',
							'inputType',
							'inputTypeCustom',
						]),
					];
					break;
				case 'phone':
				case 'tel':
					$output[] = [
						'component' => 'phone',
						'phoneName' => $name,
						'phoneTracking' => $name,
						'phoneFieldLabel' => $label,
						'phoneTypeCustom' => 'phone',
						'phoneIsNumber' => true,
						'phoneIsRequired' => $isRequired,
						'phoneDisabledOptions' => $this->prepareDisabledOptions('phone', [
							'phoneTypeCustom',
							'phoneIsNumber',
						]),
					];
					break;
				case 'date':
					$output[] = [
						'component' => 'date',
						'dateName' => $name,
						'dateTracking' => $name,
						'dateFieldLabel' => $label,
						'dateType' => 'date',
						'datePreviewFormat' => 'F j, Y',
						'dateOutputFormat' => 'Y-m-d',
						'dateIsRequired' => $isRequired,
						'dateDisabledOptions' => $this->prepareDisabledOptions('date', [
							'dateType',
							'dateOutputFormat',
						]),
					];
					break;
				case 'textarea':
				case 'text area':
					$output[] = [
						'component' => 'textarea',
						'textareaName' => $name,
						'textareaTracking' => $name,
						'textareaFieldLabel' => $label,
						'textareaIsRequired' => $isRequired,
						'textareaDisabledOptions' => $this->prepareDisabledOptions('textarea'),
					];
					break;
				default:
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputType' => 'text',
						'inputIsRequired' => $isRequired,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input'),
					];
					break;
			}
		}

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitFieldUseError' => false,
		];

		$filterName = HooksHelpers::getFilterName(['integrations', SettingsPardot::SETTINGS_TYPE_KEY, 'data']);
		if (\has_filter($filterName)) {
			$output = \apply_filters($filterName, $output, $formId) ?? [];
		}

		return $output;
	}
}
