<?php

/**
 * Talentlyft Mapper integration class.
 *
 * @package EightshiftForms\Integrations\Talentlyft
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Talentlyft;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Talentlyft integration class.
 */
class Talentlyft extends AbstractFormBuilder implements MapperInterface, ServiceInterface
{
	/**
	 * Filter form fields.
	 *
	 * @var string
	 */
	public const FILTER_FORM_FIELDS_NAME = 'es_talentlyft_form_fields_filter';

	/**
	 * Instance variable for Talentlyft data.
	 *
	 * @var ClientInterface
	 */
	protected $talentlyftClient;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $talentlyftClient Inject Talentlyft which holds Talentlyft connect data.
	 */
	public function __construct(ClientInterface $talentlyftClient)
	{
		$this->talentlyftClient = $talentlyftClient;
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
			'type' => SettingsTalentlyft::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
			'innerId' => $innerId,
			'fields' => [],
		];

		// Get fields.
		$item = $this->talentlyftClient->getItem($itemId);

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
	 * Map fields to our components.
	 *
	 * @param array<string, mixed> $data Fields.
	 * @param string $formId Form ID.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(array $data, string $formId): array
	{
		if (!$data) {
			return [];
		}

		$output = [];

		foreach ($data['fields'] as $item) {
			if (!$item) {
				continue;
			}

			$key = $item['Key'] ?? '';

			$type = $item['Type'] ?? '';
			$name = !empty($key) ? "q_{$key}" : '';
			$tracking = $key;
			$label = $item['DisplayName'] ?? '';
			$fields = $item['Choices'] ?? [];
			$internalType = $type;
			$required = isset($item['Required']) ? (bool) $item['Required'] : false;

			if (!$name) {
				$name = isset($item['Id']) ? "q_{$item['Id']}" : '';
			}

			if (empty($key)) {
				$internalType = 'customField';
			}

			if (!$name) {
				continue;
			}

			switch ($type) {
				case 'text':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $tracking,
						'inputFieldLabel' => $label,
						'inputType' => 'text',
						'inputIsRequired' => $required,
						'inputTypeCustom' => $internalType,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$required ? 'inputIsRequired' : '',
							'inputTypeCustom',
						]),
					];
					break;
				case 'address':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $tracking,
						'inputFieldLabel' => $label,
						'inputType' => 'text',
						'inputIsRequired' => $required,
						'inputTypeCustom' => $internalType,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$required ? 'inputIsRequired' : '',
							'inputTypeCustom',
						]),
					];
					break;
				case 'email':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $tracking,
						'inputFieldLabel' => $label,
						'inputType' => 'email',
						'inputIsRequired' => $required,
						'inputTypeCustom' => $internalType,
						'inputIsEmail' => true,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$required ? 'inputIsRequired' : '',
							'inputTypeCustom',
							'inputIsEmail',
							'inputType',
						]),
					];
					break;
				case 'phone':
					$output[] = [
						'component' => 'phone',
						'phoneName' => $name,
						'phoneTracking' => $tracking,
						'phoneFieldLabel' => $label,
						'phoneIsRequired' => $required,
						'phoneIsNumber' => true,
						'phoneTypeCustom' => $internalType,
						'phoneDisabledOptions' => $this->prepareDisabledOptions('phone', [
							$required ? 'phoneIsRequired' : '',
							'phoneIsNumber',
							'phoneTypeCustom',
						]),
					];
					break;
				case 'file':
					$maxFileSize = UtilsSettingsHelper::getOptionValueWithFallback(SettingsTalentlyft::SETTINGS_TALENTLYFT_FILE_UPLOAD_LIMIT_KEY, (string) SettingsTalentlyft::SETTINGS_TALENTLYFT_FILE_UPLOAD_LIMIT_DEFAULT);

					$accept = $item['SupportedTypes'] ?? [];

					$output[] = [
						'component' => 'file',
						'fileName' => $name,
						'fileTracking' => $tracking,
						'fileFieldLabel' => $label,
						'fileIsRequired' => $required,
						'fileAccept' => $accept ? \implode(',', $accept) : 'pdf,doc,docx,rtf',
						'fileMinSize' => '1',
						'fileMaxSize' => \strval((int) $maxFileSize * 1000),
						'fileTypeCustom' => $internalType,
						'fileDisabledOptions' => $this->prepareDisabledOptions('file', [
							$required ? 'fileIsRequired' : '',
							'fileAccept',
							'fileMinSize',
							'fileMaxSize',
							'fileTypeCustom',
						]),
					];
					break;
				case 'textarea':
					$output[] = [
						'component' => 'textarea',
						'textareaName' => $name,
						'textareaTracking' => $tracking,
						'textareaFieldLabel' => $label,
						'textareaIsRequired' => $required,
						'textareaTypeCustom' => $internalType,
						'textareaDisabledOptions' => $this->prepareDisabledOptions('textarea', [
							$required ? 'textareaIsRequired' : '',
							'textareaTypeCustom',
						]),
					];
					break;
				case 'select':
					// Salutation is a special case as it expects a different format.
					if ($name === 'q_Salutation') {
						$selectContent = \array_values(
							\array_map(
								function ($selectOption) {
									return [
										'component' => 'select-option',
										'selectOptionValue' => $selectOption['Body'],
										'selectOptionLabel' => $selectOption['DisplayName'] ?? '',
										'selectOptionDisabledOptions' => $this->prepareDisabledOptions('select-option', [
											'selectOptionValue',
										], false),
									];
								},
								$fields
							),
						);
					} else {
						$selectContent = \array_values(
							\array_map(
								function ($selectOption) {
									return [
										'component' => 'select-option',
										'selectOptionValue' => (string) $selectOption['Id'],
										'selectOptionLabel' => $selectOption['DisplayName'] ?? '',
										'selectOptionDisabledOptions' => $this->prepareDisabledOptions('select-option', [
											'selectOptionValue',
										], false),
									];
								},
								$fields
							),
						);
					}

					$output[] = [
						'component' => 'select',
						'selectName' => $name,
						'selectTracking' => $tracking,
						'selectFieldLabel' => $label,
						'selectIsRequired' => $required,
						'selectContent' => $selectContent,
						'selectTypeCustom' => $internalType,
						'selectDisabledOptions' => $this->prepareDisabledOptions('select', [
							$required ? 'selectIsRequired' : '',
							'selectTypeCustom',
						]),
					];
					break;
				case 'decimal':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $tracking,
						'inputFieldLabel' => $label,
						'inputType' => 'number',
						'inputIsRequired' => $required,
						'inputTypeCustom' => $internalType,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$required ? 'inputIsRequired' : '',
							'inputType',
							'inputTypeCustom',
						]),
					];
					break;
				case 'checkbox':
					$output[] = [
						'component' => 'checkboxes',
						'checkboxesName' => $name,
						'checkboxesFieldLabel' => $label,
						'checkboxesIsRequired' => $required,
						'checkboxesTypeCustom' => $internalType,
						'checkboxesContent' => \array_map(
							function ($checkbox) use ($tracking) {
								return [
									'component' => 'checkbox',
									'checkboxValue' => (string) $checkbox['Id'],
									'checkboxLabel' => $checkbox['DisplayName'] ?? '',
									'checkboxTracking' => $tracking,
									'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
										'checkboxValue',
									], false),
								];
							},
							$fields
						),
						'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
							$required ? 'checkboxesIsRequired' : '',
							'checkboxesTypeCustom',
						]),
					];
					break;
				case 'radio':
				case 'yesNo':
					$output[] = [
						'component' => 'radios',
						'radiosName' => $name,
						'radiosFieldLabel' => $label,
						'radiosIsRequired' => $required,
						'radiosTypeCustom' => $internalType,
						'radiosContent' => \array_map(
							function ($radio) use ($tracking) {
								return [
									'component' => 'radio',
									'radioValue' => (string) $radio['Id'],
									'radioLabel' => $radio['DisplayName'] ?? '',
									'radioTracking' => $tracking,
									'radioDisabledOptions' => $this->prepareDisabledOptions('radio', [
										'radioValue',
									], false),
								];
							},
							$fields
						),
						'radiosDisabledOptions' => $this->prepareDisabledOptions('radios', [
							$required ? 'radiosIsRequired' : '',
							'radiosTypeCustom',
						]),
					];
					break;
				case 'url':
					$output[] = [
						'component' => 'input',
						'inputName' => $name,
						'inputTracking' => $tracking,
						'inputFieldLabel' => $label,
						'inputType' => 'url',
						'inputIsUrl' => true,
						'inputIsRequired' => $required,
						'inputTypeCustom' => $internalType,
						'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
							$required ? 'inputIsRequired' : '',
							'inputType',
							'inputIsUrl',
							'inputTypeCustom',
						]),
					];
					break;
				case 'date':
					$output[] = [
						'component' => 'date',
						'dateName' => $name,
						'dateTracking' => $tracking,
						'dateFieldLabel' => $label,
						'dateType' => 'date',
						'datePreviewFormat' => 'F j, Y',
						'dateOutputFormat' => 'Z',
						'dateIsRequired' => $required,
						'dateTypeCustom' => $internalType,
						'dateDisabledOptions' => $this->prepareDisabledOptions('date', [
							$required ? 'dateIsRequired' : '',
							'dateType',
							'dateOutputFormat',
							'dateTypeCustom',
						]),
					];
					break;
			}
		}

		$compliance = $this->getComplianceFields($data['compliance']['Gdpr'] ?? []);

		$output = \array_merge($output, $compliance);

		$output[] = [
			'component' => 'submit',
			'submitName' => 'submit',
			'submitFieldUseError' => false,
			'submitDisabledOptions' => $this->prepareDisabledOptions('submit'),
		];

		// Change the final output if necesery.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsTalentlyft::SETTINGS_TYPE_KEY, 'data']);
		if (\has_filter($filterName)) {
			$output = \apply_filters($filterName, $output, $formId) ?? [];
		}

		return $output;
	}

	/**
	 * Get compliance fields.
	 *
	 * @param array<string, mixed> $compliance Compliance data.
	 *
	 * @return array<mixed>
	 */
	private function getComplianceFields(array $compliance): array
	{
		if (!$compliance) {
			return [];
		}

		$complianceIsEnabled = $compliance['IsEnabled'] ?? false;
		if (!$complianceIsEnabled) {
			return [];
		}

		$privacy = $this->getCompliancePrivacy($compliance);
		$storage = $this->getComplianceStorage($compliance);
		$companies = $this->getComplianceShare($compliance);

		return [$privacy, $storage, $companies];
	}

	/**
	 * Get compliance privacy field.
	 *
	 * @param array<string, mixed> $compliance Compliance data.
	 *
	 * @return array<string, mixed>
	 */
	private function getCompliancePrivacy(array $compliance): array
	{
		$text = UtilsSettingsHelper::getOptionValue(SettingsTalentlyft::SETTINGS_TALENTLYFT_CONSENT_PRIVACY_KEY);

		if (!$text) {
			return [];
		}

		$required = \boolval($compliance['RequirePrivacyPolicyConsent'] ?? false);

		return [
			'component' => 'checkboxes',
			'checkboxesName' => 'compliancePrivacy',
			'checkboxesIsRequired' => true,
			'checkboxesTypeCustom' => 'compliancePrivacy',
			'checkboxesContent' => [
				[
					'component' => 'checkbox',
					'checkboxValue' => 'compliancePrivacy',
					'checkboxLabel' => $text,
					'checkboxTracking' => 'compliancePrivacy',
					'checkboxIsDisabled' => !$required,
					'checkboxIsChecked' => !$required,
					'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
						'checkboxValue',
						'checkboxIsDisabled',
						'checkboxIsChecked',
						'checkboxLabel'
					], false),
				]
			],
			'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
				'checkboxesIsRequired',
				'checkboxesTypeCustom',
			]),
		];
	}

	/**
	 * Get compliance storage field.
	 *
	 * @param array<string, mixed> $compliance Compliance data.
	 *
	 * @return array<string, mixed>
	 */
	private function getComplianceStorage(array $compliance): array
	{
		$text = UtilsSettingsHelper::getOptionValue(SettingsTalentlyft::SETTINGS_TALENTLYFT_CONSENT_STORAGE_KEY);

		if (!$text) {
			return [];
		}

		$period = $compliance['RetentionPeriod'] ?? '1';

		$text = \str_replace('{period}', (string) $period, $text);
		$text = \str_replace('{company}', $compliance['CompanyLegalName'] ?? '', $text);

		return [
			'component' => 'checkboxes',
			'checkboxesName' => 'complianceStorage',
			'checkboxesIsRequired' => false,
			'checkboxesTypeCustom' => 'complianceStorage',
			'checkboxesContent' => [
				[
					'component' => 'checkbox',
					'checkboxValue' => 'complianceStorage',
					'checkboxLabel' => \esc_html($text),
					'checkboxTracking' => 'complianceStorage',
					'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
						'checkboxValue',
						'checkboxLabel',
					], false),
				]
			],
			'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
				'checkboxesTypeCustom',
			]),
		];
	}

	/**
	 * Get compliance share field.
	 *
	 * @param array<string, mixed> $compliance Compliance data.
	 *
	 * @return array<string, mixed>
	 */
	private function getComplianceShare(array $compliance): array
	{
		$required = \boolval($compliance['RequireShareCompliance'] ?? false);

		if (!$required) {
			return [];
		}

		$text = UtilsSettingsHelper::getOptionValue(SettingsTalentlyft::SETTINGS_TALENTLYFT_CONSENT_SHARE_KEY);

		if (!$text) {
			return [];
		}

		$text = \str_replace('{companies}', $compliance['ShareComplianceText'] ?? '', $text);

		return [
			'component' => 'checkboxes',
			'checkboxesName' => 'complianceShare',
			'checkboxesIsRequired' => false,
			'checkboxesTypeCustom' => 'complianceShare',
			'checkboxesContent' => [
				[
					'component' => 'checkbox',
					'checkboxValue' => 'complianceShare',
					'checkboxLabel' => \esc_html($text),
					'checkboxTracking' => 'complianceShare',
					'checkboxDisabledOptions' => $this->prepareDisabledOptions('checkbox', [
						'checkboxValue',
						'checkboxLabel',
					], false),
				]
			],
			'checkboxesDisabledOptions' => $this->prepareDisabledOptions('checkboxes', [
				'checkboxesTypeCustom',
			]),
		];
	}
}
