<?php

/**
 * Jira Settings class.
 *
 * @package EightshiftForms\Integrations\Jira
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Jira;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsJira class.
 */
class SettingsJira implements ServiceInterface, SettingGlobalInterface, SettingInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

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
	 * Jira epic name key.
	 */
	public const SETTINGS_JIRA_EPIC_NAME_KEY = 'jira-epic-name';

	/**
	 * Jira description key.
	 */
	public const SETTINGS_JIRA_DESC_KEY = 'jira-desc';

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

		$selectedProject = $this->getSettingsValue(self::SETTINGS_JIRA_PROJECT_KEY, $formId);

		if (!$selectedProject) {
			return false;
		}

		$selectedIssueType = $this->getSettingsValue(self::SETTINGS_JIRA_ISSUE_TYPE_KEY, $formId);

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
			return $this->getNoActiveFeatureOutput();
		}

		$selectedProject = $this->getSettingsValue(self::SETTINGS_JIRA_PROJECT_KEY, $formId);
		$selectedIssueType = $this->getSettingsValue(self::SETTINGS_JIRA_ISSUE_TYPE_KEY, $formId);

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Settings', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'select',
								'selectName' => $this->getSettingsName(self::SETTINGS_JIRA_PROJECT_KEY),
								'selectFieldLabel' => \__('Project', 'eightshift-forms'),
								'selectSingleSubmit' => true,
								'selectContent' => \array_merge(
									[
										[
											'component' => 'select-option',
											'selectOptionLabel' => '',
											'selectOptionValue' => '',
										],
									],
									\array_map(
										static function ($option) use ($selectedProject) {
											return [
												'component' => 'select-option',
												'selectOptionLabel' => $option['title'],
												'selectOptionValue' => $option['key'],
												'selectOptionIsSelected' => $selectedProject === $option['key'],
											];
										},
										$this->jiraClient->getProjects()
									)
								),
							],
							$selectedProject ? [
								'component' => 'select',
								'selectSingleSubmit' => true,
								'selectName' => $this->getSettingsName(self::SETTINGS_JIRA_ISSUE_TYPE_KEY),
								'selectFieldLabel' => \__('Issue type', 'eightshift-forms'),
								'selectContent' => \array_merge(
									[
										[
											'component' => 'select-option',
											'selectOptionLabel' => '',
											'selectOptionValue' => '',
										],
									],
									\array_map(
										static function ($option) use ($selectedIssueType) {
											return [
												'component' => 'select-option',
												'selectOptionLabel' => $option['title'],
												'selectOptionValue' => $option['id'],
												'selectOptionIsSelected' => $selectedIssueType === $option['id'],
											];
										},
										$this->jiraClient->getIssueType($selectedProject)
									)
								),
							] : [],
						],
					],
					$selectedIssueType ? [
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_JIRA_TITLE_KEY),
								'inputFieldLabel' => \__('Issue title', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => $this->getSettingsValue(self::SETTINGS_JIRA_TITLE_KEY, $formId),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_JIRA_DESC_KEY),
								'inputFieldLabel' => \__('Additional description', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => $this->getSettingsValue(self::SETTINGS_JIRA_DESC_KEY, $formId),
							],
							// Epic type.
							$selectedIssueType === JiraClient::ISSUE_TYPE_EPIC ? [
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_JIRA_EPIC_NAME_KEY),
								'inputFieldLabel' => \__('Epic name', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => $this->getSettingsValue(self::SETTINGS_JIRA_EPIC_NAME_KEY, $formId),
							] : [],
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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_JIRA_USE_KEY, self::SETTINGS_JIRA_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyJira()) ? Variables::getApiKeyJira() : $this->getOptionValue(self::SETTINGS_JIRA_API_KEY_KEY);
		$apiBoard = !empty(Variables::getApiBoardJira()) ? Variables::getApiBoardJira() : $this->getOptionValue(self::SETTINGS_JIRA_API_BOARD_KEY);
		$apiUser = !empty(Variables::getApiUserJira()) ? Variables::getApiUserJira() : $this->getOptionValue(self::SETTINGS_JIRA_API_USER_KEY);

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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_JIRA_USE_KEY, self::SETTINGS_JIRA_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$apiKey = Variables::getApiKeyJira();
		$apiBoard = Variables::getApiBoardJira();
		$apiUser = Variables::getApiUserJira();

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
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_JIRA_API_KEY_KEY),
								'inputFieldLabel' => \__('API key', 'eightshift-forms'),
								// translators: %s will be replaced with global variable name.
								'inputFieldHelp' => \sprintf(\__('
									Provided by the Jira user token. Check the <b>Help</b> tab for instructions on how to get it.<br/><br/>
									%s', 'eightshift-forms'), $this->getGlobalVariableOutput('ES_API_KEY_JIRA', !empty($apiKey))),
								'inputType' => 'password',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_JIRA_API_KEY_KEY),
								'inputIsDisabled' => !empty($apiKey),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_JIRA_API_BOARD_KEY),
								'inputFieldLabel' => \__('Board', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								// translators: %s will be replaced with global variable name.
								'inputFieldHelp' => \sprintf(\__('
									Provided in the Jira board URL. For example, if the board URL is https://infinum-wordpress.atlassian.net, the board name is <b>infinum-wordpress</b>.<br/><br/>
									%s', 'eightshift-forms'), $this->getGlobalVariableOutput('ES_API_BOARD_JIRA', !empty($apiBoard))),
								'inputValue' => !empty($apiBoard) ? $apiBoard : $this->getOptionValue(self::SETTINGS_JIRA_API_BOARD_KEY),
								'inputIsDisabled' => !empty($apiBoard),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_JIRA_API_USER_KEY),
								'inputFieldLabel' => \__('User', 'eightshift-forms'),
								// translators: %s will be replaced with global variable name.
								'inputFieldHelp' => \sprintf(\__('
									E-mail of the user connected to the user token.<br/><br/>
									%s', 'eightshift-forms'), $this->getGlobalVariableOutput('ES_API_USER_JIRA', !empty($apiUser))),
								'inputType' => 'email',
								'inputIsRequired' => true,
								'inputIsEmail' => true,
								'inputValue' => !empty($apiUser) ? $apiUser : $this->getOptionValue(self::SETTINGS_JIRA_API_USER_KEY),
								'inputIsDisabled' => !empty($apiUser),
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
