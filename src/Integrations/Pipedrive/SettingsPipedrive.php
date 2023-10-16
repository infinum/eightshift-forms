<?php

/**
 * Pipedrive Settings class.
 *
 * @package EightshiftForms\Integrations\Pipedrive
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Pipedrive;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsPipedrive class.
 */
class SettingsPipedrive implements ServiceInterface, SettingGlobalInterface, SettingInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Use general helper trait.
	 */
	use FiltersOuputMock;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_pipedrive';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_pipedrive';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_pipedrive';

	/**
	 * Filter settings integration use key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_pipedrive';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'pipedrive';

	/**
	 * Pipedrive Use key.
	 */
	public const SETTINGS_PIPEDRIVE_USE_KEY = 'pipedrive-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_PIPEDRIVE_API_KEY_KEY = 'pipedrive-api-key';

	/**
	 * Pipedrive person-name key.
	 */
	public const SETTINGS_PIPEDRIVE_PERSON_NAME_KEY = 'pipedrive-person-name';

	/**
	 * Pipedrive label person key.
	 */
	public const SETTINGS_PIPEDRIVE_LABEL_PERSON_KEY = 'pipedrive-label-person';

	/**
	 * Pipedrive label lead title key.
	 */
	public const SETTINGS_PIPEDRIVE_LEAD_TITLE_KEY = 'pipedrive-lead-title';

	/**
	 * Pipedrive label lead currency key.
	 */
	public const SETTINGS_PIPEDRIVE_LEAD_CURRENCY_KEY = 'pipedrive-lead-currency';

	/**
	 * Pipedrive label lead key.
	 */
	public const SETTINGS_PIPEDRIVE_LABEL_LEAD_KEY = 'pipedrive-label-lead';

	/**
	 * Pipedrive lead value key.
	 */
	public const SETTINGS_PIPEDRIVE_LEAD_VALUE_KEY = 'pipedrive-lead-value';

	/**
	 * Pipedrive issue type key.
	 */
	public const SETTINGS_PIPEDRIVE_ISSUE_TYPE_KEY = 'pipedrive-issue-type';

	/**
	 * Pipedrive title key.
	 */
	public const SETTINGS_PIPEDRIVE_TITLE_KEY = 'pipedrive-title';

	/**
	 * Pipedrive description key.
	 */
	public const SETTINGS_PIPEDRIVE_DESC_KEY = 'pipedrive-desc';

	/**
	 * Pipedrive params map key.
	 */
	public const SETTINGS_PIPEDRIVE_PARAMS_MAP_KEY = 'pipedrive-params-map';

	/**
	 * Pipedrive use lead key.
	 */
	public const SETTINGS_PIPEDRIVE_USE_LEAD = 'pipedrive-use-lead';

	/**
	 * Skip integration.
	 */
	public const SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY = 'pipedrive-skip-integration';

	/**
	 * Instance variable for Pipedrive data.
	 *
	 * @var PipedriveClientInterface
	 */
	protected $pipedriveClient;

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param PipedriveClientInterface $pipedriveClient Inject Pipedrive which holds Pipedrive connect data.
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(
		PipedriveClientInterface $pipedriveClient,
		SettingsFallbackDataInterface $settingsFallback
	) {
		$this->pipedriveClient = $pipedriveClient;
		$this->settingsFallback = $settingsFallback;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsValid']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings are valid.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Integration type.
	 *
	 * @return boolean
	 */
	public function isSettingsValid(string $formId): bool
	{
		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		$personName = $this->getSettingValue(self::SETTINGS_PIPEDRIVE_PERSON_NAME_KEY, $formId);

		if (!$personName) {
			return false;
		}

		return true;
	}

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		// Bailout if feature is not active.
		if (!$this->isSettingsGlobalValid()) {
			return $this->getSettingOutputNoActiveFeature();
		}

		$formDetails = Helper::getFormDetailsById($formId);

		$fields = $formDetails['fieldNamesTags'] ?? [];

		$personName = $this->getSettingValue(self::SETTINGS_PIPEDRIVE_PERSON_NAME_KEY, $formId);
		$labelsPerson = $this->getSettingValue(self::SETTINGS_PIPEDRIVE_LABEL_PERSON_KEY, $formId);
		$labelsLead = $this->getSettingValue(self::SETTINGS_PIPEDRIVE_LABEL_LEAD_KEY, $formId);
		$leadValue = $this->getSettingValue(self::SETTINGS_PIPEDRIVE_LEAD_VALUE_KEY, $formId);

		$mapParams = $this->getSettingValueGroup(self::SETTINGS_PIPEDRIVE_PARAMS_MAP_KEY, $formId);
		$personFields = $this->pipedriveClient->getPersonFields();
		$leadsFields = $this->pipedriveClient->getLeadsFields();

		$useLead = $this->isSettingCheckboxChecked(self::SETTINGS_PIPEDRIVE_USE_LEAD, self::SETTINGS_PIPEDRIVE_USE_LEAD, $formId);

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Person', 'eightshift-forms'),
						'tabContent' => [
							...($fields ? [
								[
									'component' => 'intro',
									'introTitle' => \__('Mandatory person fields', 'eightshift-forms'),
								],
								[
									'component' => 'select',
									'selectName' => $this->getSettingName(self::SETTINGS_PIPEDRIVE_PERSON_NAME_KEY),
									'selectFieldLabel' => \__('Person name', 'eightshift-forms'),
									'selectSingleSubmit' => true,
									'selectIsRequired' => true,
									'selectPlaceholder' => \__('Select person name field', 'eightshift-forms'),
									'selectContent' => \array_map(
										static function ($option) use ($personName) {
											return [
												'component' => 'select-option',
												'selectOptionLabel' => \ucfirst($option),
												'selectOptionValue' => $option,
												'selectOptionIsSelected' => $personName === $option,
											];
										},
										$fields
									),
								],
								...($personName ? [
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => true,
									],
									[
										'component' => 'intro',
										'introTitle' => \__('Optional person fields', 'eightshift-forms'),
										'introSubtitle' => \__('Please ensure that your form fields are properly connected to integration fields to ensure proper functionality.', 'eightshift-forms'),
									],
									[
										'component' => 'field',
										'fieldLabel' => '<b>' . \__('Form field', 'eightshift-forms') . '</b>',
										'fieldContent' => '<b>' . \__('Person integration field', 'eightshift-forms') . '</b>',
										'fieldBeforeContent' => '&emsp;', // "Em space" to pad it out a bit.
										'fieldIsFiftyFiftyHorizontal' => true,
									],
									[
										'component' => 'group',
										'groupName' => $this->getSettingName(self::SETTINGS_PIPEDRIVE_PARAMS_MAP_KEY),
										'groupSaveOneField' => true,
										'groupStyle' => 'default-listing',
										'groupContent' => [
											...array_filter(\array_map(
												function ($item) use ($mapParams, $personName, $personFields) {
													if ($personName === $item) {
														return [];
													}

													return [
														'component' => 'select',
														'selectName' => $item,
														'selectFieldLabel' => \ucfirst($item),
														'selectFieldIsFiftyFiftyHorizontal' => true,
														'selectFieldBeforeContent' => '&rarr;',
														'selectUseEmptyPlaceholder' => true,
														'selectIsClearable' => true,
														'selectContent' => [
															...(array_filter(\array_map(
																static function ($option) use ($mapParams, $item) {
																	$id  = $option['key'] ?? '';

																	if (!$id) {
																		return [];
																	}

																	if ($id === 'name' || $id === 'label') {
																		return [];
																	}
	
																	return [
																		'component' => 'select-option',
																		'selectOptionLabel' => \ucfirst($option['title']),
																		'selectOptionValue' => $id,
																		'selectOptionIsSelected' => isset($mapParams[$item]) ? $mapParams[$item] === $id : false,
																	];
																},
																$personFields
															))),
														],
													];
												},
												$fields
											)),
										],
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => true,
									],
									[
										'component' => 'intro',
										'introTitle' => \__('Additional person fields', 'eightshift-forms'),
									],
									[
										'component' => 'select',
										'selectName' => $this->getSettingName(self::SETTINGS_PIPEDRIVE_LABEL_PERSON_KEY),
										'selectFieldLabel' => \__('Person label', 'eightshift-forms'),
										'selectPlaceholder' => \__('Select person label', 'eightshift-forms'),
										'selectContent' => array_filter(\array_map(
											static function ($option) use ($labelsPerson) {
												$id  = $option['id'] ?? '';

												if (!$id) {
													return [];
												}

												return [
													'component' => 'select-option',
													'selectOptionLabel' => \ucfirst($option['title']),
													'selectOptionValue' => $id,
													'selectOptionIsSelected' => $labelsPerson === $id,
												];
											},
											array_values(array_filter($personFields, fn($item) => $item['key'] === 'label'))[0]['fields'] ?? []
										)),
									],
								] : []),
							] : [$this->settingDataMappedIntegrationMissingFields()]),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Lead', 'eightshift-forms'),
						'tabContent' => [
							...($fields ? [
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => '',
									'checkboxesName' => $this->getOptionName(self::SETTINGS_PIPEDRIVE_USE_LEAD),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Create new lead when creating a person', 'eightshift-forms'),
											'checkboxHelp' => \__('New leads are automatically created and assigned upon submission.', 'eightshift-forms'),
											'checkboxIsChecked' => $useLead,
											'checkboxValue' => self::SETTINGS_PIPEDRIVE_USE_LEAD,
											'checkboxSingleSubmit' => true,
											'checkboxAsToggle' => true,
										]
									]
								],
								...($useLead ? [
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => true,
									],
									[
										'component' => 'intro',
										'introTitle' => \__('Mandatory lead fields', 'eightshift-forms'),
									],
									[
										'component' => 'input',
										'inputName' => $this->getSettingName(self::SETTINGS_PIPEDRIVE_LEAD_TITLE_KEY),
										'inputFieldLabel' => \__('Lead title', 'eightshift-forms'),
										'inputType' => 'text',
										'inputIsRequired' => true,
										'inputValue' => $this->getSettingValue(self::SETTINGS_PIPEDRIVE_LEAD_TITLE_KEY, $formId),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => true,
									],
									[
										'component' => 'intro',
										'introTitle' => \__('Optional lead fields', 'eightshift-forms'),
										'introSubtitle' => \__('Please ensure that your form fields are properly connected to integration fields to ensure proper functionality.', 'eightshift-forms'),
									],
									[
										'component' => 'select',
										'selectName' => $this->getSettingName(self::SETTINGS_PIPEDRIVE_LEAD_VALUE_KEY),
										'selectFieldLabel' => \__('Lead value', 'eightshift-forms'),
										'selectSingleSubmit' => true,
										'selectIsRequired' => true,
										'selectPlaceholder' => \__('Select lead value name field', 'eightshift-forms'),
										'selectContent' => \array_map(
											static function ($option) use ($leadValue) {
												return [
													'component' => 'select-option',
													'selectOptionLabel' => \ucfirst($option),
													'selectOptionValue' => $option,
													'selectOptionIsSelected' => $leadValue === $option,
												];
											},
											$fields
										),
									],
									[
										'component' => 'input',
										'inputName' => $this->getSettingName(self::SETTINGS_PIPEDRIVE_LEAD_CURRENCY_KEY),
										'inputFieldLabel' => \__('Lead currency', 'eightshift-forms'),
										'inputType' => 'text',
										'inputIsRequired' => true,
										'inputValue' => $this->getSettingValue(self::SETTINGS_PIPEDRIVE_LEAD_CURRENCY_KEY, $formId),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => true,
									],
									[
										'component' => 'intro',
										'introTitle' => \__('Additional lead fields', 'eightshift-forms'),
									],
									[
										'component' => 'select',
										'selectName' => $this->getSettingName(self::SETTINGS_PIPEDRIVE_LABEL_LEAD_KEY),
										'selectFieldLabel' => \__('Lead label', 'eightshift-forms'),
										'selectPlaceholder' => \__('Select lead label', 'eightshift-forms'),
										'selectContent' => array_filter(\array_map(
											static function ($option) use ($labelsLead) {
												$id  = $option['id'] ?? '';

												if (!$id) {
													return [];
												}

												return [
													'component' => 'select-option',
													'selectOptionLabel' => \ucfirst($option['title']),
													'selectOptionValue' => $id,
													'selectOptionIsSelected' => $labelsLead === $id,
												];
											},
											$leadsFields ?? []
										)),
									],
								]
								: []),
							] : []),
						],
					],
				],
			],
		];
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = $this->isOptionCheckboxChecked(self::SETTINGS_PIPEDRIVE_USE_KEY, self::SETTINGS_PIPEDRIVE_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyPipedrive()) ? Variables::getApiKeyPipedrive() : $this->getOptionValue(self::SETTINGS_PIPEDRIVE_API_KEY_KEY);

		if (!$isUsed || empty($apiKey)) {
			return false;
		}

		return true;
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		// Bailout if feature is not active.
		if (!$this->isOptionCheckboxChecked(self::SETTINGS_PIPEDRIVE_USE_KEY, self::SETTINGS_PIPEDRIVE_USE_KEY)) {
			return $this->getSettingOutputNoActiveFeature();
		}

		$apiKey = Variables::getApiKeyPipedrive();

		$successRedirectUrl = $this->getSuccessRedirectUrlFilterValue(self::SETTINGS_TYPE_KEY, '');
		$deactivateIntegration = $this->isOptionCheckboxChecked(self::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY, self::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY);

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('API', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => $this->getOptionName(self::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => $this->settingDataDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => $this->settingDataDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($deactivateIntegration ? [
								[
									'component' => 'intro',
									'introSubtitle' => $this->settingDataDeactivatedIntegration('introSubtitle'),
									'introIsHighlighted' => true,
									'introIsHighlightedImportant' => true,
								],
							] : [
								$this->getSettingsPasswordFieldWithGlobalVariable(
									$this->getOptionName(self::SETTINGS_PIPEDRIVE_API_KEY_KEY),
									\__('API key', 'eightshift-forms'),
									!empty($apiKey) ? $apiKey : $this->getOptionValue(self::SETTINGS_PIPEDRIVE_API_KEY_KEY),
									'ES_API_KEY_PIPEDRIVE',
									!empty($apiKey)
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								$this->settingTestAliConnection(self::SETTINGS_TYPE_KEY),
							]),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getOptionName(self::SETTINGS_TYPE_KEY . '-' . SettingsGeneral::SETTINGS_GLOBAL_REDIRECT_SUCCESS_KEY),
								'inputFieldLabel' => \__('After submit redirect URL', 'eightshift-forms'),
								// translators: %s will be replaced with forms field name and filter output copy.
								'inputFieldHelp' => \sprintf(\__('
									If URL is provided, after a successful submission the user is redirected to the provided URL and the success message will <strong>not</strong> show.
									<br />
									%s', 'eightshift-forms'), $successRedirectUrl['settingsGlobal']),
								'inputType' => 'url',
								'inputIsUrl' => true,
								'inputValue' => $successRedirectUrl['dataGlobal'],
							],
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsPipedrive::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Pipedrive Account.', 'eightshift-forms'),
									\__('Click on your profile picture in the top right corner.', 'eightshift-forms'),
									\__('Click on Personal preferences and open API tab.', 'eightshift-forms'),
									\__('Copy the secret API token into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
