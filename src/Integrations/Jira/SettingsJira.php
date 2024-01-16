<?php

/**
 * Jira Settings class.
 *
 * @package EightshiftForms\Integrations\Jira
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Jira;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingInterface;
use EightshiftForms\General\SettingsGeneral;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsJira class.
 */
class SettingsJira implements UtilsSettingGlobalInterface, UtilsSettingInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_jira';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_jira';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_jira';

	/**
	 * Filter settings integration use key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_jira';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'jira';

	/**
	 * Jira Use key.
	 */
	public const SETTINGS_JIRA_USE_KEY = 'jira-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_JIRA_API_KEY_KEY = 'jira-api-key';

	/**
	 * API Board.
	 */
	public const SETTINGS_JIRA_API_BOARD_KEY = 'jira-api-board';

	/**
	 * API Board url.
	 */
	public const SETTINGS_JIRA_API_BOARD_URL_KEY = 'jira-api-board-url';

	/**
	 * API User.
	 */
	public const SETTINGS_JIRA_API_USER_KEY = 'jira-api-user';

	/**
	 * Jira project key.
	 */
	public const SETTINGS_JIRA_PROJECT_KEY = 'jira-project';

	/**
	 * Jira issue type key.
	 */
	public const SETTINGS_JIRA_ISSUE_TYPE_KEY = 'jira-issue-type';

	/**
	 * Jira title key.
	 */
	public const SETTINGS_JIRA_TITLE_KEY = 'jira-title';

	/**
	 * Jira description key.
	 */
	public const SETTINGS_JIRA_DESC_KEY = 'jira-desc';

	/**
	 * Jira params map key.
	 */
	public const SETTINGS_JIRA_PARAMS_MAP_KEY = 'jira-params-map';

	/**
	 * Jira params manual map key.
	 */
	public const SETTINGS_JIRA_PARAMS_MANUAL_MAP_KEY = 'jira-params-manual-map';

	/**
	 * Jira api is self hosted version.
	 */
	public const SETTINGS_JIRA_SELF_HOSTED_KEY = 'jira-self-hosted';

	/**
	 * Skip integration.
	 */
	public const SETTINGS_JIRA_SKIP_INTEGRATION_KEY = 'jira-skip-integration';

	/**
	 * Instance variable for Jira data.
	 *
	 * @var JiraClientInterface
	 */
	protected $jiraClient;

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param JiraClientInterface $jiraClient Inject Jira which holds Jira connect data.
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(
		JiraClientInterface $jiraClient,
		SettingsFallbackDataInterface $settingsFallback
	) {
		$this->jiraClient = $jiraClient;
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
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsValid'], 10, 2);
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
	public function isSettingsValid(string $formId, string $type): bool
	{
		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		$selectedProject = UtilsSettingsHelper::getSettingValue(self::SETTINGS_JIRA_PROJECT_KEY, $formId);

		if (!$selectedProject) {
			return false;
		}

		$selectedIssueType = UtilsSettingsHelper::getSettingValue(self::SETTINGS_JIRA_ISSUE_TYPE_KEY, $formId);

		if (!$selectedIssueType) {
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
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$formDetails = UtilsGeneralHelper::getFormDetails($formId);

		$selectedProject = UtilsSettingsHelper::getSettingValue(self::SETTINGS_JIRA_PROJECT_KEY, $formId);
		$selectedIssueType = UtilsSettingsHelper::getSettingValue(self::SETTINGS_JIRA_ISSUE_TYPE_KEY, $formId);
		$manualMapParams = UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_JIRA_PARAMS_MANUAL_MAP_KEY, self::SETTINGS_JIRA_PARAMS_MANUAL_MAP_KEY, $formId);
		$mapParams = UtilsSettingsHelper::getSettingValueGroup(self::SETTINGS_JIRA_PARAMS_MAP_KEY, $formId);
		$customFields = $this->jiraClient->getProjectsCustomFields($selectedProject);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Settings', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'select',
								'selectName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_JIRA_PROJECT_KEY),
								'selectFieldLabel' => \__('Project', 'eightshift-forms'),
								'selectSingleSubmit' => true,
								'selectPlaceholder' => \__('Select project', 'eightshift-forms'),
								'selectContent' => \array_map(
									static function ($option) use ($selectedProject) {
										return [
											'component' => 'select-option',
											'selectOptionLabel' => $option['title'],
											'selectOptionValue' => $option['key'],
											'selectOptionIsSelected' => $selectedProject === $option['key'],
										];
									},
									$this->jiraClient->getProjects()
								),
							],
							$selectedProject ? [
								'component' => 'select',
								'selectSingleSubmit' => true,
								'selectName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_JIRA_ISSUE_TYPE_KEY),
								'selectFieldLabel' => \__('Issue type', 'eightshift-forms'),
								'selectPlaceholder' => \__('Select issue type', 'eightshift-forms'),
								'selectContent' => \array_map(
									static function ($option) use ($selectedIssueType) {
										return [
											'component' => 'select-option',
											'selectOptionLabel' => $option['title'],
											'selectOptionValue' => $option['id'],
											'selectOptionIsSelected' => $selectedIssueType === $option['id'],
										];
									},
									$this->jiraClient->getIssueType($selectedProject)
								),
							] : [],
						],
					],
					$selectedIssueType ? [
						'component' => 'tab',
						'tabLabel' => \__('Parameter mapping', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_JIRA_TITLE_KEY),
								'inputFieldLabel' => \__('Issue title', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_JIRA_TITLE_KEY, $formId),
							],
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_JIRA_DESC_KEY),
								'inputFieldLabel' => \__('Additional description', 'eightshift-forms'),
								'inputType' => 'text',
								'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_JIRA_DESC_KEY, $formId),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'intro',
								'introSubtitle' => \__('All fields will be outputed in the Jira issue description field using table layout but you can also map individual custom field.', 'eightshift-forms'),
								'introHelp' => UtilsSettingsOutputHelper::getPartialFieldTags(UtilsSettingsOutputHelper::getPartialFormFieldNames($formDetails[UtilsConfig::FD_FIELD_NAMES_TAGS])),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_JIRA_PARAMS_MANUAL_MAP_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Disable auto output to description table.', 'eightshift-forms'),
										'checkboxIsChecked' => $manualMapParams,
										'checkboxValue' => self::SETTINGS_JIRA_PARAMS_MANUAL_MAP_KEY,
										'checkboxAsToggle' => true,
										'checkboxAsToggleSize' => 'medium',
									],
								],
							],
							...(($customFields && !$this->jiraClient->isSelfHosted()) ? [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'field',
									'fieldLabel' => '<b>' . \__('Jira field', 'eightshift-forms') . '</b>',
									'fieldContent' => '<b>' . \__('Value', 'eightshift-forms') . '</b>',
									'fieldBeforeContent' => '&emsp;', // "Em space" to pad it out a bit.
									'fieldIsFiftyFiftyHorizontal' => true,
								],
								[
									'component' => 'group',
									'groupName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_JIRA_PARAMS_MAP_KEY),
									'groupSaveOneField' => true,
									'groupStyle' => 'default-listing',
									'groupContent' => [
										...\array_map(
											function ($item) use ($mapParams) {
												$id  = $item['id'] ?? '';

												if ($id) {
													return [
														'component' => 'input',
														'inputName' => $id,
														'inputFieldLabel' => $item['title'],
														'inputValue' => $mapParams[$id] ?? '',
														'inputFieldIsFiftyFiftyHorizontal' => true,
														'inputFieldBeforeContent' => '&rarr;',
													];
												}
											},
											$this->jiraClient->getProjectsCustomFields($selectedProject)
										),
									],
								],
							] : []),
						],
					] : [],
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
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_JIRA_USE_KEY, self::SETTINGS_JIRA_USE_KEY);
		$apiKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyJira(), self::SETTINGS_JIRA_API_KEY_KEY)['value'];
		$apiBoard = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiBoardJira(), self::SETTINGS_JIRA_API_BOARD_KEY)['value'];
		$apiUser = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiUserJira(), self::SETTINGS_JIRA_API_USER_KEY)['value'];

		if (!$isUsed || empty($apiKey) || empty($apiBoard) || empty($apiUser)) {
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
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_JIRA_USE_KEY, self::SETTINGS_JIRA_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$successRedirectUrl = FiltersOuputMock::getSuccessRedirectUrlFilterValue(self::SETTINGS_TYPE_KEY, '');
		$deactivateIntegration = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_JIRA_SKIP_INTEGRATION_KEY, self::SETTINGS_JIRA_SKIP_INTEGRATION_KEY);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_JIRA_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_JIRA_SKIP_INTEGRATION_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($deactivateIntegration ? [
								[
									'component' => 'intro',
									'introSubtitle' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('introSubtitle'),
									'introIsHighlighted' => true,
									'introIsHighlightedImportant' => true,
								],
							] : [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getPasswordFieldWithGlobalVariable(
									UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(
										Variables::getApiKeyJira(),
										self::SETTINGS_JIRA_API_KEY_KEY,
										'ES_API_KEY_JIRA'
									),
									\__('API key', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getInputFieldWithGlobalVariable(
									UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(
										Variables::getApiBoardJira(),
										self::SETTINGS_JIRA_API_BOARD_KEY,
										'ES_API_BOARD_JIRA'
									),
									\__('Board', 'eightshift-forms'),
									\__('Provided the Jira board URL. For example, if the board URL is https://infinum-wordpress.atlassian.net, the board name is <b>infinum-wordpress.atlassian.net</b>.', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getInputFieldWithGlobalVariable(
									UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(
										Variables::getApiUserJira(),
										self::SETTINGS_JIRA_API_USER_KEY,
										'ES_API_USER_JIRA'
									),
									\__('User', 'eightshift-forms'),
									\__('E-mail or user name of the user connected to the user token.', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => '',
									'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_JIRA_SELF_HOSTED_KEY),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Use self-hosted version', 'eightshift-forms'),
											'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_JIRA_SELF_HOSTED_KEY, self::SETTINGS_JIRA_SELF_HOSTED_KEY),
											'checkboxValue' => self::SETTINGS_JIRA_SELF_HOSTED_KEY,
											'checkboxAsToggle' => true,
											'checkboxAsToggleSize' => 'medium',
										],
									],
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getTestAliConnection(self::SETTINGS_TYPE_KEY),
							]),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_TYPE_KEY . '-' . SettingsGeneral::SETTINGS_GLOBAL_REDIRECT_SUCCESS_KEY),
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
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_JIRA_API_BOARD_URL_KEY),
								'inputFieldLabel' => \__('Alternative board url', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => false,
								'inputFieldHelp' => \__('Provided the Jira alternative board URL if there is a defference. For example, if the board URL is https://infinum-wordpress.atlassian.net, the board name is <b>infinum-wordpress.atlassian.net</b>.', 'eightshift-forms'),
								'inputValue' => UtilsSettingsHelper::getOptionValue(self::SETTINGS_JIRA_API_BOARD_URL_KEY),
							],
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsJira::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Jira Account.', 'eightshift-forms'),
									// translators: %s will be replaced with the api externa link.
									\sprintf(\__('Click on the <strong><a target="_blank" rel="noopener noreferrer" href="%s">API</a></strong>.', 'eightshift-forms'), 'https://id.atlassian.com/manage-profile/security/api-tokens'),
									\__('Copy the secret API key into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
