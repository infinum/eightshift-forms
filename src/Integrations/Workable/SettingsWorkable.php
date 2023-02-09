<?php

/**
 * Workable Settings class.
 *
 * @package EightshiftForms\Integrations\Workable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Workable;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingsAll;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftForms\Troubleshooting\SettingsTroubleshootingDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsWorkable class.
 */
class SettingsWorkable implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_workable';

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_workable';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_workable';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_workable';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'workable';

	/**
	 * Workable Use key.
	 */
	public const SETTINGS_WORKABLE_USE_KEY = 'workable-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_WORKABLE_API_KEY_KEY = 'workable-api-key';

	/**
	 * Board Token Key.
	 */
	public const SETTINGS_WORKABLE_SUBDOMAIN_KEY = 'workable-subdomain';

	/**
	 * Job ID Key.
	 */
	public const SETTINGS_WORKABLE_JOB_ID_KEY = 'workable-job-id';

	/**
	 * Integration fields Key.
	 */
	public const SETTINGS_WORKABLE_INTEGRATION_FIELDS_KEY = 'workable-integration-fields';

	/**
	 * File upload limit Key.
	 */
	public const SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_KEY = 'workable-file-upload-limit';

	/**
	 * File upload limit default. Defined in MB.
	 */
	public const SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_DEFAULT = 5;

	/**
	 * Instance variable for Workable data.
	 *
	 * @var ClientInterface
	 */
	protected $workableClient;

	/**
	 * Instance variable for Workable form data.
	 *
	 * @var MapperInterface
	 */
	protected $workable;

	/**
	 * Instance variable for Troubleshooting settings.
	 *
	 * @var SettingsTroubleshootingDataInterface
	 */
	protected $settingsTroubleshooting;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $workableClient Inject Workable which holds Workable connect data.
	 * @param MapperInterface $workable Inject Workable which holds Workable form data.
	 * @param SettingsTroubleshootingDataInterface $settingsTroubleshooting Inject Troubleshooting which holds Troubleshooting settings data.
	 */
	public function __construct(
		ClientInterface $workableClient,
		MapperInterface $workable,
		SettingsTroubleshootingDataInterface $settingsTroubleshooting
	) {
		$this->workableClient = $workableClient;
		$this->workable = $workable;
		$this->settingsTroubleshooting = $settingsTroubleshooting;
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

		$jobKey = $this->getSettingsValue(self::SETTINGS_WORKABLE_JOB_ID_KEY, $formId);

		if (empty($jobKey)) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_WORKABLE_USE_KEY, self::SETTINGS_WORKABLE_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyWorkable()) ? Variables::getApiKeyWorkable() : $this->getOptionValue(self::SETTINGS_WORKABLE_API_KEY_KEY);
		$subdomain = !empty(Variables::getSubdomainWorkable()) ? Variables::getSubdomainWorkable() : $this->getOptionValue(self::SETTINGS_WORKABLE_SUBDOMAIN_KEY);

		if (!$isUsed || empty($apiKey) || empty($subdomain)) {
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
			'label' => \__('Workable', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => Filters::ALL[self::SETTINGS_TYPE_KEY]['icon'],
			'type' => SettingsAll::SETTINGS_SIEDBAR_TYPE_INTEGRATION,
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
					'highlightedContentTitle' => \__('Some config required', 'eightshift-forms'),
					// translators: %s will be replaced with the global settings url.
					'highlightedContentSubtitle' => \sprintf(\__('Before using Workable you need to configure it in  <a href="%s">global settings</a>.', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(self::SETTINGS_TYPE_KEY)),
					'highlightedContentIcon' => 'tools',
				]
			];
		}

		$items = $this->workableClient->getItems(false);
		$lastUpdatedTime = $items[ClientInterface::TRANSIENT_STORED_TIME]['title'] ?? '';
		unset($items[ClientInterface::TRANSIENT_STORED_TIME]);

		if (!$items) {
			return [
				[
					'component' => 'highlighted-content',
					'highlightedContentTitle' => \__('Something went wrong', 'eightshift-forms'),
					'highlightedContentSubtitle' => \__('Data from Workable couldn\'t be fetched. Check the API key.', 'eightshift-forms'),
					'highlightedContentIcon' => 'error',
				],
			];
		}

		$itemOptions = \array_map(
			function ($option) use ($formId) {
				return [
					'component' => 'select-option',
					'selectOptionLabel' => $option['title'] ?? '',
					'selectOptionValue' => $option['id'] ?? '',
					'selectOptionIsSelected' => $this->isCheckedSettings($option['id'], self::SETTINGS_WORKABLE_JOB_ID_KEY, $formId),
				];
			},
			$items
		);

		\array_unshift(
			$itemOptions,
			[
				'component' => 'select-option',
				'selectOptionLabel' => '',
				'selectOptionValue' => '',
			]
		);

		$selectedItem = $this->getSettingsValue(self::SETTINGS_WORKABLE_JOB_ID_KEY, $formId);

		$manifestForm = Components::getManifest(\dirname(__DIR__, 2) . '/Blocks/components/form');

		$output = [
			[
				'component' => 'intro',
				'introTitle' => \__('Workable', 'eightshift-forms'),
			],
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName(self::SETTINGS_WORKABLE_JOB_ID_KEY),
				'selectId' => $this->getSettingsName(self::SETTINGS_WORKABLE_JOB_ID_KEY),
				'selectFieldLabel' => \__('Job post', 'eightshift-forms'),
				// translators: %1$s will be replaced with js selector, %2$s will be replaced with the cache type, %3$s will be replaced with latest update time.
				'selectFieldHelp' => \sprintf(\__('If a job post isn\'t showing up or is missing some jobs, try <a href="#" class="%1$s" data-type="%2$s">clearing the cache</a>. Last updated: %3$s.', 'eightshift-forms'), $manifestForm['componentCacheJsClass'], self::SETTINGS_TYPE_KEY, $lastUpdatedTime),
				'selectOptions' => $itemOptions,
				'selectIsRequired' => true,
				'selectValue' => $selectedItem,
				'selectSingleSubmit' => true,
			],
		];

		// If the user has selected the list.
		if ($selectedItem) {
			$beforeContent = '';

			$filterName = Filters::getIntegrationFilterName(self::SETTINGS_TYPE_KEY, 'adminFieldsSettings');
			if (\has_filter($filterName)) {
				$beforeContent = \apply_filters($filterName, '') ?? '';
			}

			$sortingButton = Components::render('sorting');

			$formViewDetailsIsEditableFilterName = Filters::getIntegrationFilterName(self::SETTINGS_TYPE_KEY, 'fieldsSettingsIsEditable');
			if (\has_filter($formViewDetailsIsEditableFilterName)) {
				$sortingButton = \__('This integration sorting and editing is disabled because of the active filter in your project!', 'eightshift-forms');
			}

			$output = \array_merge(
				$output,
				[
					[
						'component' => 'divider',
					],
					[
						'component' => 'intro',
						'introTitle' => \__('Form fields', 'eightshift-forms'),
						'introTitleSize' => 'medium',
						// translators: %s replaces the button or string.
						'introSubtitle' => \sprintf(\__('
							Control which fields show up on the frontend, and set up how they look and work. <br />
							To change the field order, click on the button below. To save the new order, please click on the "save settings" button at the bottom of the page. <br /><br />
							%s', 'eightshift-forms'), $sortingButton),
					],
					[
						'component' => 'group',
						'groupId' => $this->getSettingsName(self::SETTINGS_WORKABLE_INTEGRATION_FIELDS_KEY),
						'groupBeforeContent' => $beforeContent,
						'additionalGroupClass' => Components::getComponent('sorting')['componentCombinedClass'],
						'groupStyle' => 'integration',
						'groupContent' => $this->getIntegrationFieldsDetails(
							self::SETTINGS_WORKABLE_INTEGRATION_FIELDS_KEY,
							self::SETTINGS_TYPE_KEY,
							$this->workable->getFormFields($formId),
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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_WORKABLE_USE_KEY, self::SETTINGS_WORKABLE_USE_KEY);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => \__('Workable', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introTitle' => \__('How to get the API key?', 'eightshift-forms'),
				'introTitleSize' => 'small',
				// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'introSubtitle' => \__('<ol>
						<li>Log in to your Workable Account.</li>
						<li>Go to <a target="_blank" href="https://app.workable.io/configure/dev_center/credentials">API Credentials Settings</a>.</li>
						<li>Click on <strong>Create New API Key</strong>.</li>
						<li>Select <strong>Job Board</strong> as your API Type.</li>
						<li>Copy the API key into the field below or use the global constant.</li>
					</ol>', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introTitle' => \__('How to get the Subdomain?', 'eightshift-forms'),
				'introTitleSize' => 'small',
				// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'introSubtitle' => \__('<ol>
						<li>Log in to your Workable Account.</li>
						<li>Go to <a target="_blank" href="https://app.workable.io/jobboard">Job Boards Settings</a>.</li>
						<li>Copy the <strong>Board Name</strong> you want to use.</li>
						<li>Make the name all lowercase.</li>
						<li>Copy the Board Name into the field below or use the global constant.</li>
					</ol>', 'eightshift-forms'),
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => '',
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_WORKABLE_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_WORKABLE_USE_KEY),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => \__('Use Workable', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_WORKABLE_USE_KEY, self::SETTINGS_WORKABLE_USE_KEY),
						'checkboxValue' => self::SETTINGS_WORKABLE_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];

		if ($isUsed) {
			$apiKey = Variables::getApiKeyWorkable();
			$subdomain = Variables::getSubdomainWorkable();

			$output = \array_merge(
				$output,
				[
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_WORKABLE_API_KEY_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_WORKABLE_API_KEY_KEY),
						'inputFieldLabel' => \__('API key', 'eightshift-forms'),
						'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
						'inputType' => 'password',
						'inputIsRequired' => true,
						'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_WORKABLE_API_KEY_KEY),
						'inputIsDisabled' => !empty($apiKey),
					],
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_WORKABLE_SUBDOMAIN_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_WORKABLE_SUBDOMAIN_KEY),
						'inputFieldLabel' => \__('Subdomain', 'eightshift-forms'),
						'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
						'inputType' => 'text',
						'inputIsRequired' => true,
						'inputValue' => !empty($subdomain) ? $subdomain : $this->getOptionValue(self::SETTINGS_WORKABLE_SUBDOMAIN_KEY),
						'inputIsDisabled' => !empty($subdomain),
					],
					[
						'component' => 'divider',
					],
					[
						'component' => 'intro',
						'introTitle' => \__('Options', 'eightshift-forms'),
						'introSubtitle' => \__('Here you can find some of options specific to Workable integration.', 'eightshift-forms'),
						'introTitleSize' => 'medium',
					],
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_KEY),
						'inputFieldLabel' => \__('File upload limit', 'eightshift-forms'),
						'inputFieldHelp' => \__('Limit the size of files users can send via upload files. We set the default to 5MB, and limited the max file size to 25MB.', 'eightshift-forms'),
						'inputType' => 'number',
						'inputIsNumber' => true,
						'inputIsRequired' => true,
						'inputValue' => $this->getOptionValue(self::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_KEY) ?: self::SETTINGS_WORKABLE_FILE_UPLOAD_LIMIT_DEFAULT, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
						'inputMin' => 1,
						'inputMax' => 25,
						'inputStep' => 1,
					],
				]
			);
		}

		return [
			...$output,
			...$this->settingsTroubleshooting->getOutputGlobalTroubleshooting(SettingsWorkable::SETTINGS_TYPE_KEY),
		];
	}
}
