<?php

/**
 * Jira Settings class.
 *
 * @package EightshiftForms\Integrations\Jira
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Jira;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsJira class.
 */
class SettingsJira implements SettingsJiraDataInterface, ServiceInterface, SettingGlobalInterface
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
	 * Create a new instance.
	 *
	 * @param JiraClientInterface $jiraClient Inject Jira which holds Jira connect data.
	 */
	public function __construct(JiraClientInterface $jiraClient)
	{
		$this->jiraClient = $jiraClient;
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

		$typeItems = Filters::ALL[self::SETTINGS_TYPE_KEY]['integration'];

		if (!isset($typeItems[$type])) {
			return false;
		}

		$useJira = $this->getSettingsValue($typeItems[$type]['use'], $formId);

		if (empty($useJira)) {
			return false;
		}

		$mapSet = $this->getOptionValueGroup($typeItems[$type]['map']);

		if (empty($mapSet)) {
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
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName(self::SETTINGS_JIRA_PROJECT_KEY),
				'selectFieldLabel' => \__('Select a project', 'eightshift-forms'),
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
				'selectFieldLabel' => \__('Select a issue type', 'eightshift-forms'),
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
			...($selectedIssueType ? [
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
			] : []),
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
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'password',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_JIRA_API_KEY_KEY),
								'inputIsDisabled' => !empty($apiKey),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_JIRA_API_BOARD_KEY),
								'inputFieldLabel' => \__('API Board', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiBoard) ? $apiBoard : $this->getOptionValue(self::SETTINGS_JIRA_API_BOARD_KEY),
								'inputIsDisabled' => !empty($apiBoard),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_JIRA_API_USER_KEY),
								'inputFieldLabel' => \__('API User', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiUser) ? $apiUser : $this->getOptionValue(self::SETTINGS_JIRA_API_USER_KEY),
								'inputIsDisabled' => !empty($apiUser),
							],
						],
					],
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

	/**
	 * Output array settings for form.
	 *
	 * @param string $formId Form ID.
	 * @param string $key Key for use toggle.
	 *
	 * @return array<string, array<int, array<string, array<int, array<string, mixed>>|bool|string>>|string>
	 */
	public function getOutputJira(string $formId, string $key): array
	{
		$useJira = \apply_filters(SettingsJira::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, $formId);

		if (!$useJira) {
			return [];
		}

		$isUsed = $this->isCheckboxSettingsChecked($key, $key, $formId);

		$output = [
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => '',
				'checkboxesName' => $this->getSettingsName($key),
				'checkboxesIsRequired' => false,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => \__('Use Jira integration', 'eightshift-forms'),
						'checkboxIsChecked' => $isUsed,
						'checkboxValue' => $key,
						'checkboxSingleSubmit' => true,
						'checkboxAsToggle' => true,
						'checkboxAsToggleSize' => 'medium',
					]
				]
			],
		];

		return [
			'component' => 'tab',
			'tabLabel' => \__('Jira', 'eightshift-forms'),
			'tabContent' => [
				...$output,
			],
		];
	}

	/**
	 * Output array settings for form.
	 *
	 * @param array<string, string> $properties Array of properties from integration.
	 * @param array<string, string> $keys Array of keys to get data from.
	 *
	 * @return array<string, array<int, array<string, array<int, array<string, array<int, array<string, array<int|string, array<string, bool|string>>|string>>|bool|string>>|string>>|string>
	 */
	public function getOutputGlobalJira(array $properties, array $keys): array
	{
		$mapKey = $keys['map'] ?? '';

		$isValid = $this->isSettingsGlobalValid();

		if (!$isValid) {
			return [];
		}

		$jiraAvailableKeys = $this->getOptionCheckboxValues(SettingsJira::SETTINGS_JIRA_AVAILABLE_KEYS_KEY);

		$jiraMapValue = $this->getOptionValueGroup($mapKey);

		return [
			'component' => 'tab',
				'tabLabel' => \__('Jira', 'eightshift-forms'),
				'tabContent' => [
					[
						'component' => 'intro',
						'introSubtitle' => \__('
							Control which fields from Jira are connected to the HubSpot properties.<br/>
							Label is a Jira field, and the input is a HubSpot field to map to.', 'eightshift-forms'),
					],
					$jiraAvailableKeys ? [
						'component' => 'group',
						'groupName' => $this->getSettingsName($mapKey),
						'groupSaveOneField' => true,
						'groupStyle' => 'default-listing',
						'groupContent' => \array_map(
							static function ($item) use ($jiraMapValue, $properties) {
								$selectedValue = $jiraMapValue[$item] ?? '';
								return [
									'component' => 'select',
									'selectName' => $item,
									'selectFieldLabel' => $item,
									'selectContent' => \array_merge(
										[
											[
												'component' => 'select-option',
												'selectOptionLabel' => '',
												'selectOptionValue' => '',
											],
										],
										\array_map(
											static function ($option) use ($selectedValue) {
												return [
													'component' => 'select-option',
													'selectOptionLabel' => $option,
													'selectOptionValue' => $option,
													'selectOptionIsSelected' => $selectedValue === $option,
												];
											},
											$properties
										)
									),
								];
							},
							$jiraAvailableKeys
						),
					] : [],
				],
		];
	}
}
