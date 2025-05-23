<?php

/**
 * Mailerlite integration class.
 *
 * @package EightshiftForms\Integrations\Mailerlite
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailerlite;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Mailerlite integration class.
 */
class Mailerlite extends AbstractFormBuilder implements MapperInterface, ServiceInterface
{
	/**
	 * Filter form fields.
	 *
	 * @var string
	 */
	public const FILTER_FORM_FIELDS_NAME = 'es_mailerlite_form_fields_filter';

	/**
	 * Instance variable for Mailerlite data.
	 *
	 * @var ClientInterface
	 */
	protected $mailerliteClient;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $mailerliteClient Inject Mailerlite which holds Mailerlite connect data.
	 */
	public function __construct(ClientInterface $mailerliteClient)
	{
		$this->mailerliteClient = $mailerliteClient;
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
			'type' => SettingsMailerlite::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
			'innerId' => $innerId,
			'fields' => [],
		];

		// Get fields.
		$item = $this->mailerliteClient->getItem($itemId);

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
	 * Map Mailerlite fields to our components.
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

		foreach ($data['fields'] as $field) {
			if (empty($field)) {
				continue;
			}

			$type = $field['type'] ? \strtolower($field['type']) : '';
			$name = $field['key'] ?? '';
			$label = $field['title'] ?? '';

			switch ($type) {
				case 'text':
					switch ($name) {
						case 'phone':
							$output[] = [
								'component' => 'phone',
								'phoneName' => $name,
								'phoneTracking' => $name,
								'phoneIsNumber' => true,
								'phoneFieldHidden' => true,
								'phoneFieldLabel' => $label,
								'phoneDisabledOptions' => $this->prepareDisabledOptions('phone', [
									'phoneIsNumber',
								]),
								'phoneSyncAttrsSkip' => [
									'phoneFieldHidden',
								],
							];
							break;
						case 'zip':
							$output[] = [
								'component' => 'input',
								'inputName' => $name,
								'inputTracking' => $name,
								'inputFieldHidden' => true,
								'inputFieldLabel' => $label,
								'inputType' => 'number',
								'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
									'inputType',
								]),
								'inputSyncAttrsSkip' => [
									'inputFieldHidden',
								],
							];
							break;
						default:
							$output[] = [
								'component' => 'input',
								'inputName' => $name,
								'inputTracking' => $name,
								'inputFieldLabel' => $label,
								'inputType' => 'text',
								'inputFieldHidden' => $name !== 'email',
								'inputIsRequired' => $name === 'email',
								'inputIsEmail' => $name === 'email',
								'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
									$name === 'email' ? 'inputIsRequired' : '',
									$name === 'email' ? 'inputIsEmail' : '',
								]),
								'inputSyncAttrsSkip' => [
									'inputFieldHidden',
								],
							];
							break;
					}
					break;
				case 'date':
					$output[] = [
						'component' => 'date',
						'dateName' => $name,
						'dateTracking' => $name,
						'dateFieldLabel' => $label,
						'dateFieldHidden' => true,
						'dateType' => 'date',
						'datePreviewFormat' => 'F j, Y',
						'dateOutputFormat' => 'Y-m-d',
						'dateDisabledOptions' => $this->prepareDisabledOptions('date', [
							'dateType',
							'dateOutputFormat',
						]),
						'dateSyncAttrsSkip' => [
							'dateFieldHidden',
						],
					];
					break;
				case 'number':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $name,
						'inputFieldLabel' => $label,
						'inputFieldHidden' => true,
						'inputIsNumber' => true,
						'inputType' => 'number',
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							'inputType',
							'inputIsNumber',
						]),
						'inputSyncAttrsSkip' => [
							'inputFieldHidden',
						],
					];
					break;
			}
		}

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitFieldUseError' => false,
			'submitDisabledOptions' => $this->prepareDisabledOptions('submit'),
		];

		// Change the final output if necesery.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsMailerlite::SETTINGS_TYPE_KEY, 'data']);
		if (\has_filter($filterName)) {
			$output = \apply_filters($filterName, $output, $formId) ?? [];
		}

		return $output;
	}
}
