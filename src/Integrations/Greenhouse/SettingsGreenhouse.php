<?php

/**
 * Greenhouse Settings class.
 *
 * @package EightshiftForms\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Greenhouse;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGreenhouse class.
 */
class SettingsGreenhouse implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_greenhouse';

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_greenhouse';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_greenhouse';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_greenhouse';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'greenhouse';

	/**
	 * Greenhouse Use key.
	 */
	public const SETTINGS_GREENHOUSE_USE_KEY = 'greenhouse-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_GREENHOUSE_API_KEY_KEY = 'greenhouse-api-key';

	/**
	 * Board Token Key.
	 */
	public const SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY = 'greenhouse-board-token';

	/**
	 * Job ID Key.
	 */
	public const SETTINGS_GREENHOUSE_JOB_ID_KEY = 'greenhouse-job-id';

	/**
	 * Hide resume textarea Key.
	 */
	public const SETTINGS_GREENHOUSE_HIDE_RESUME_TEXTAREA_KEY = 'greenhouse-hide-resume-textarea';

	/**
	 * Hide Cover Letter textarea Key.
	 */
	public const SETTINGS_GREENHOUSE_HIDE_COVER_LETTER_TEXTAREA_KEY = 'greenhouse-hide-cover-letter-textarea';

	/**
	 * Integration Breakpoints Key.
	 */
	public const SETTINGS_GREENHOUSE_INTEGRATION_BREAKPOINTS_KEY = 'greenhouse-integration-breakpoints';

	/**
	 * Instance variable for Greenhouse data.
	 *
	 * @var ClientInterface
	 */
	protected $greenhouseClient;

	/**
	 * Instance variable for Greenhouse form data.
	 *
	 * @var MapperInterface
	 */
	protected $greenhouse;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $greenhouseClient Inject Greenhouse which holds Greenhouse connect data.
	 * @param MapperInterface $greenhouse Inject Greenhouse which holds Greenhouse form data.
	 */
	public function __construct(
		ClientInterface $greenhouseClient,
		MapperInterface $greenhouse
	) {
		$this->greenhouseClient = $greenhouseClient;
		$this->greenhouse = $greenhouse;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsValid']);
	}

	/**
	 * Determin if settings are valid.
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

		$jobKey = $this->getSettingsValue(self::SETTINGS_GREENHOUSE_JOB_ID_KEY, $formId);

		if (empty($jobKey)) {
			return false;
		}

		return true;
	}

	/**
	 * Determin if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = (bool) $this->isCheckboxOptionChecked(self::SETTINGS_GREENHOUSE_USE_KEY, self::SETTINGS_GREENHOUSE_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyGreenhouse()) ? Variables::getApiKeyGreenhouse() : $this->getOptionValue(self::SETTINGS_GREENHOUSE_API_KEY_KEY);
		$boardToken = !empty(Variables::getBoardTokenGreenhouse()) ? Variables::getBoardTokenGreenhouse() : $this->getOptionValue(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY);

		if (!$isUsed || empty($apiKey) || empty($boardToken)) {
			return false;
		}

		return true;
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => __('Greenhouse', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg"><path d="M20.507 8.914c0 1.448-.613 2.73-1.616 3.732-1.114 1.114-2.73 1.393-2.73 2.34 0 1.281 2.062.891 4.04 2.87 1.309 1.308 2.117 3.035 2.117 5.04 0 3.956-3.176 7.104-7.16 7.104C11.176 30 8 26.852 8 22.9c0-2.009.808-3.736 2.117-5.045 1.978-1.978 4.039-1.588 4.039-2.869 0-.947-1.616-1.226-2.73-2.34-1.003-1.003-1.615-2.284-1.615-3.788 0-2.897 2.367-5.237 5.264-5.237.557 0 1.059.084 1.477.084.752 0 1.142-.335 1.142-.864 0-.306-.14-.696-.14-1.114C17.554.78 18.362 0 19.337 0c.975 0 1.755.808 1.755 1.783 0 1.03-.808 1.504-1.42 1.727-.502.167-.892.39-.892.891 0 .947 1.727 1.866 1.727 4.513zM19.95 22.9c0-2.758-2.034-4.986-4.791-4.986-2.758 0-4.791 2.228-4.791 4.986 0 2.73 2.033 4.986 4.79 4.986 2.758 0 4.792-2.26 4.792-4.986zM18.306 8.858c0-1.755-1.42-3.203-3.147-3.203-1.727 0-3.148 1.448-3.148 3.203s1.42 3.203 3.148 3.203c1.727 0 3.147-1.448 3.147-3.203z" fill="#23A47F" fill-rule="nonzero"/></svg>',
		];
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
		if (!$this->isSettingsGlobalValid()) {
			return [
				[
					'component' => 'highlighted-content',
					'highlightedContentTitle' => __('We are sorry but', 'eightshift-forms'),
					// translators: %s will be replaced with the global settings url.
					'highlightedContentSubtitle' => sprintf(__('in order to use Greenhouse integration please navigate to <a href="%s">global settings</a> and provide the missing configuration data.', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(self::SETTINGS_TYPE_KEY)),
				]
			];
		}

		$items = $this->greenhouseClient->getItems();

		if (!$items) {
			return [
				[
					'component' => 'highlighted-content',
					'highlightedContentTitle' => __('We are sorry but', 'eightshift-forms'),
					'highlightedContentSubtitle' => __('we couldn\'t get the data from the Greenhouse. Please check if you API key is valid and you provided the correct Board name.', 'eightshift-forms'),
				],
			];
		}

		$itemOptions = array_map(
			function ($option) use ($formId) {
				return [
					'component' => 'select-option',
					'selectOptionLabel' => $option['title'] ?? '',
					'selectOptionValue' => $option['id'] ?? '',
					'selectOptionIsSelected' => $this->isCheckedSettings($option['id'], self::SETTINGS_GREENHOUSE_JOB_ID_KEY, $formId),
				];
			},
			$items
		);

		array_unshift(
			$itemOptions,
			[
				'component' => 'select-option',
				'selectOptionLabel' => '',
				'selectOptionValue' => '',
			]
		);

		$selectedItem = $this->getSettingsValue(self::SETTINGS_GREENHOUSE_JOB_ID_KEY, $formId);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Greenhouse settings', 'eightshift-forms'),
				'introSubtitle' => __('Configure your greenhouse settings in one place.', 'eightshift-forms'),
			],
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_JOB_ID_KEY),
				'selectId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_JOB_ID_KEY),
				'selectFieldLabel' => __('Job ID', 'eightshift-forms'),
				'selectFieldHelp' => __('Select what Greenhouse job you want to show on this form.', 'eightshift-forms'),
				'selectOptions' => $itemOptions,
				'selectIsRequired' => true,
				'selectValue' => $selectedItem,
				'selectSingleSubmit' => true,
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => __('Field Options', 'eightshift-forms'),
				'introTitleSize' => 'medium',
				'introSubtitle' => __('Setup additional options for individual fields.', 'eightshift-forms'),
			],
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_HIDE_RESUME_TEXTAREA_KEY),
				'selectId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_HIDE_RESUME_TEXTAREA_KEY),
				'selectFieldLabel' => __('Resume Textarea', 'eightshift-forms'),
				'selectFieldHelp' => __('Show/Hide resume textarea', 'eightshift-forms'),
				'selectOptions' => [
					[
						'component' => 'select-option',
						'selectOptionLabel' => 'Show',
						'selectOptionValue' => 'show',
						'selectOptionIsSelected' => $this->isCheckedSettings('show', self::SETTINGS_GREENHOUSE_HIDE_RESUME_TEXTAREA_KEY, $formId),
					],
					[
						'component' => 'select-option',
						'selectOptionLabel' => 'Hide',
						'selectOptionValue' => 'hide',
						'selectOptionIsSelected' => $this->isCheckedSettings('hide', self::SETTINGS_GREENHOUSE_HIDE_RESUME_TEXTAREA_KEY, $formId),
					],
				],
				'selectValue' => $this->getSettingsValue(self::SETTINGS_GREENHOUSE_HIDE_RESUME_TEXTAREA_KEY, $formId),
			],
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_HIDE_COVER_LETTER_TEXTAREA_KEY),
				'selectId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_HIDE_COVER_LETTER_TEXTAREA_KEY),
				'selectFieldLabel' => __('Cover Letter Textarea', 'eightshift-forms'),
				'selectFieldHelp' => __('Show/Hide cover letter textarea', 'eightshift-forms'),
				'selectOptions' => [
					[
						'component' => 'select-option',
						'selectOptionLabel' => 'Show',
						'selectOptionValue' => 'show',
						'selectOptionIsSelected' => $this->isCheckedSettings('show', self::SETTINGS_GREENHOUSE_HIDE_COVER_LETTER_TEXTAREA_KEY, $formId),
					],
					[
						'component' => 'select-option',
						'selectOptionLabel' => 'Hide',
						'selectOptionValue' => 'hide',
						'selectOptionIsSelected' => $this->isCheckedSettings('hide', self::SETTINGS_GREENHOUSE_HIDE_COVER_LETTER_TEXTAREA_KEY, $formId),
					],
				],
				'selectValue' => $this->getSettingsValue(self::SETTINGS_GREENHOUSE_HIDE_COVER_LETTER_TEXTAREA_KEY, $formId),
			],
		];

		// If the user has selected the list.
		if ($selectedItem) {
			$output = array_merge(
				$output,
				[
					[
						'component' => 'divider',
					],
					[
						'component' => 'intro',
						'introTitle' => __('Form View Details', 'eightshift-forms'),
						'introTitleSize' => 'medium',
						'introSubtitle' => __('Configure your Mailchimp form frontend view in one place.', 'eightshift-forms'),
					],
					[
						'component' => 'group',
						'groupId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_INTEGRATION_BREAKPOINTS_KEY),
						'groupContent' => $this->getIntegrationFieldsDetails(
							self::SETTINGS_GREENHOUSE_INTEGRATION_BREAKPOINTS_KEY,
							$this->greenhouse->getFormFields($formId),
							$formId
						),
					]
				]
			);
		}

		return $output;
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$isUsed = (bool) $this->isCheckboxOptionChecked(self::SETTINGS_GREENHOUSE_USE_KEY, self::SETTINGS_GREENHOUSE_USE_KEY);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Greenhouse settings', 'eightshift-forms'),
				'introSubtitle' => __('
					Configure your Greenhouse settings in one place. <br />
					To get a Greenhouse API key you must login to your Greenhouse Account and go to <a target="_blank" href="https://app.greenhouse.io/configure/dev_center/credentials">API Credentials Settings</a>.<br />
					Then click on the <strong>Create New API Key</strong> and select <strong>Job Board</strong> as your API Type.', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introTitle' => __('How to get an API key?', 'eightshift-forms'),
				'introTitleSize' => 'medium',
				'introSubtitle' => __('
					1. Login to your Greenhouse Account. <br />
					2. Go to <a target="_blank" href="https://app.greenhouse.io/configure/dev_center/credentials">API Credentials Settings</a>. <br />
					3. Click on the <strong>Create New API Key</strong> button. <br/>
					4. Select <strong>Job Board</strong> as your API Type. <br/>
					5. Copy the API key to the provided field or use global constant.
				', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introTitle' => __('How to get an Job Board Name?', 'eightshift-forms'),
				'introTitleSize' => 'medium',
				'introSubtitle' => __('
					1. Login to your Greenhouse Account. <br />
					2. Go to <a target="_blank" href="https://app.greenhouse.io/jobboard">Job Boards Settings</a>. <br />
					3. Copy the Board Name you want to use. <br/>
					4. Convert the name to all lowercaps. <br/>
					5. Copy the Board Name to the provided field or use global constant.
				', 'eightshift-forms'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => __('Check options to use', 'eightshift-forms'),
				'checkboxesFieldHelp' => __('Select integrations you want to use in your form.', 'eightshift-forms'),
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_USE_KEY),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Use Greenhouse', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GREENHOUSE_USE_KEY, self::SETTINGS_GREENHOUSE_USE_KEY),
						'checkboxValue' => self::SETTINGS_GREENHOUSE_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];

		if ($isUsed) {
			$apiKey = Variables::getApiKeyGreenhouse();
			$boardToken = Variables::getBoardTokenGreenhouse();

			$output = array_merge(
				$output,
				[
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
						'inputFieldLabel' => __('API Key', 'eightshift-forms'),
						'inputFieldHelp' => __('You can provide API key using global variable also.', 'eightshift-forms'),
						'inputType' => 'password',
						'inputIsRequired' => true,
						'inputValue' => !empty($apiKey) ? $apiKey : $this->getOptionValue(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
						'inputIsDisabled' => !empty($apiKey),
					],
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
						'inputFieldLabel' => __('Job Board Name', 'eightshift-forms'),
						'inputFieldHelp' => __('You can provide Board name using global variable also.', 'eightshift-forms'),
						'inputType' => 'password',
						'inputIsRequired' => true,
						'inputValue' => !empty($boardToken) ? $boardToken : $this->getOptionValue(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
						'inputIsDisabled' => !empty($boardToken),
					],
				]
			);
		}

		return $output;
	}
}
