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
	 * Pipedrive organization key.
	 */
	public const SETTINGS_PIPEDRIVE_ORGANIZATION_KEY = 'pipedrive-organization';

	/**
	 * Pipedrive title key.
	 */
	public const SETTINGS_PIPEDRIVE_TITLE_KEY = 'pipedrive-title';

	/**
	 * Pipedrive params map key.
	 */
	public const SETTINGS_PIPEDRIVE_PARAMS_MAP_KEY = 'pipedrive-params-map';

	/**
	 * Pipedrive use lead key.
	 */
	public const SETTINGS_PIPEDRIVE_USE_LEAD = 'pipedrive-use-lead';

	/**
	 * Pipedrive use organization key.
	 */
	public const SETTINGS_PIPEDRIVE_USE_ORGANIZATION = 'pipedrive-use-organization';

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
		$mapParams = $this->getSettingValueGroup(self::SETTINGS_PIPEDRIVE_PARAMS_MAP_KEY, $formId);

		$personName = $this->getSettingValue(self::SETTINGS_PIPEDRIVE_PERSON_NAME_KEY, $formId);
		$personFields = $this->pipedriveClient->getPersonFields();
		$personLabel = $this->getSettingValue(self::SETTINGS_PIPEDRIVE_LABEL_PERSON_KEY, $formId);

		$useLead = $this->isSettingCheckboxChecked(self::SETTINGS_PIPEDRIVE_USE_LEAD, self::SETTINGS_PIPEDRIVE_USE_LEAD, $formId);
		$leadFields = $this->pipedriveClient->getLeadsFields();
		$leadValue = $this->getSettingValue(self::SETTINGS_PIPEDRIVE_LEAD_VALUE_KEY, $formId);
		$leadLabel = $this->getSettingValue(self::SETTINGS_PIPEDRIVE_LABEL_LEAD_KEY, $formId);

		$organization = $this->getSettingValue(self::SETTINGS_PIPEDRIVE_ORGANIZATION_KEY, $formId);
		$useOrganization = $this->isSettingCheckboxChecked(self::SETTINGS_PIPEDRIVE_USE_ORGANIZATION, self::SETTINGS_PIPEDRIVE_USE_ORGANIZATION, $formId);

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			...($fields) ? [
				[
					'component' => 'tabs',
					'tabsContent' => [
						[
							'component' => 'tab',
							'tabLabel' => \__('Person', 'eightshift-forms'),
							'tabContent' => [
								[
									'component' => 'intro',
									'introTitle' => \__('Mandatory person fields', 'eightshift-forms'),
								],
								[
									'component' => 'select',
									'selectName' => $this->getSettingName(self::SETTINGS_PIPEDRIVE_PERSON_NAME_KEY),
									'selectFieldLabel' => \__('Person name', 'eightshift-forms'),
									'selectFieldHelp' => \__('When you add a new contact to your list, you can use their name to differentiate them from other contacts.', 'eightshift-forms'),
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
											...\array_filter(\array_map(
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
														'selectContent' => [
															...(\array_filter(\array_map(
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
										'selectFieldHelp' => \__('Person label is used to distinguish lead statuses in your list.', 'eightshift-forms'),
										'selectPlaceholder' => \__('Select person label', 'eightshift-forms'),
										'selectContent' => \array_filter(\array_map(
											static function ($option) use ($personLabel) {
												$id  = $option['id'] ?? '';

												if (!$id) {
													return [];
												}

												return [
													'component' => 'select-option',
													'selectOptionLabel' => \ucfirst($option['title']),
													'selectOptionValue' => $id,
													'selectOptionIsSelected' => $personLabel === $id,
												];
											},
											\array_values(\array_filter($personFields, fn($item) => $item['key'] === 'label'))[0]['fields'] ?? []
										)),
									],
								] : []),
							],
						],
						...($personName ? [
							[
								'component' => 'tab',
								'tabLabel' => \__('Lead', 'eightshift-forms'),
								'tabContent' => [
									[
										'component' => 'checkboxes',
										'checkboxesFieldLabel' => '',
										'checkboxesName' => $this->getOptionName(self::SETTINGS_PIPEDRIVE_USE_LEAD),
										'checkboxesContent' => [
											[
												'component' => 'checkbox',
												'checkboxLabel' => \__('Create new lead when creating a person', 'eightshift-forms'),
												'checkboxHelp' => \__('New lead is automatically created and assigned upon submission.', 'eightshift-forms'),
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
											'inputFieldHelp' => \__('Lead title is used to distinguish lead sources in your list.', 'eightshift-forms'),
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
											'selectFieldHelp' => \__('Make sure that you assign lead value to a field that can only have number value.', 'eightshift-forms'),
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
											'inputFieldHelp' => \__('Make sure that you only add currency added from the list in you Pipedrive admin. To find currency list go to your Pipedrive account > Settings > Company settings > Currencies.', 'eightshift-forms'),
											'inputFieldLabel' => \__('Lead currency', 'eightshift-forms'),
											'inputType' => 'text',
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
											'selectFieldHelp' => \__('Lead label is used to distinguish lead statuses in your list.', 'eightshift-forms'),
											'selectPlaceholder' => \__('Select lead label', 'eightshift-forms'),
											'selectContent' => \array_filter(\array_map(
												static function ($option) use ($leadLabel) {
													$id  = $option['id'] ?? '';

													if (!$id) {
														return [];
													}

													return [
														'component' => 'select-option',
														'selectOptionLabel' => \ucfirst($option['title']),
														'selectOptionValue' => $id,
														'selectOptionIsSelected' => $leadLabel === $id,
													];
												},
												$leadFields ?? []
											)),
										],
									] : []),
								],
							],
							[
								'component' => 'tab',
								'tabLabel' => \__('Organization', 'eightshift-forms'),
								'tabContent' => [
									[
										'component' => 'checkboxes',
										'checkboxesFieldLabel' => '',
										'checkboxesName' => $this->getOptionName(self::SETTINGS_PIPEDRIVE_USE_ORGANIZATION),
										'checkboxesContent' => [
											[
												'component' => 'checkbox',
												'checkboxLabel' => \__('Create new organization when creating a person', 'eightshift-forms'),
												'checkboxHelp' => \__('New organization is automatically created and assigned upon submission.', 'eightshift-forms'),
												'checkboxIsChecked' => $useOrganization,
												'checkboxValue' => self::SETTINGS_PIPEDRIVE_USE_ORGANIZATION,
												'checkboxSingleSubmit' => true,
												'checkboxAsToggle' => true,
											]
										]
									],
									...($useOrganization ? [
										[
											'component' => 'divider',
											'dividerExtraVSpacing' => true,
										],
										[
											'component' => 'intro',
											'introTitle' => \__('Mandatory organization fields', 'eightshift-forms'),
										],
										[
											'component' => 'select',
											'selectName' => $this->getSettingName(self::SETTINGS_PIPEDRIVE_ORGANIZATION_KEY),
											'selectFieldLabel' => \__('Organization', 'eightshift-forms'),
											'selectIsRequired' => true,
											'selectFieldHelp' => \__('Organization name is assignet to every new organization created.', 'eightshift-forms'),
											'selectPlaceholder' => \__('Select organization name field', 'eightshift-forms'),
											'selectContent' => \array_map(
												static function ($option) use ($organization) {
													return [
														'component' => 'select-option',
														'selectOptionLabel' => \ucfirst($option),
														'selectOptionValue' => $option,
														'selectOptionIsSelected' => $organization === $option,
													];
												},
												$fields
											),
										],
									] : []),
								],
							],
						] : []),
					],
				],
			] : [$this->settingDataMappedIntegrationMissingFields()],
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
		$apiKey = $this->getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyPipedrive(), self::SETTINGS_PIPEDRIVE_API_KEY_KEY)['value'];

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
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								$this->getSettingsPasswordFieldWithGlobalVariable(
									$this->getSettingsDisabledOutputWithDebugFilter(
										Variables::getApiKeyPipedrive(),
										self::SETTINGS_PIPEDRIVE_API_KEY_KEY,
										'ES_API_KEY_PIPEDRIVE'
									),
									\__('API key', 'eightshift-forms'),
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
