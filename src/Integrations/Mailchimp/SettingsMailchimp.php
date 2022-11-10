<?php

/**
 * Mailchimp Settings class.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMailchimp class.
 */
class SettingsMailchimp implements SettingInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_mailchimp';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_mailchimp';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_mailchimp';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'mailchimp';

	/**
	 * Mailchimp Use key.
	 */
	public const SETTINGS_MAILCHIMP_USE_KEY = 'mailchimp-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_MAILCHIMP_API_KEY_KEY = 'mailchimp-api-key';

	/**
	 * List ID Key.
	 */
	public const SETTINGS_MAILCHIMP_LIST_KEY = 'mailchimp-list';

	/**
	 * List Tags Key.
	 */
	public const SETTINGS_MAILCHIMP_LIST_TAGS_KEY = 'mailchimp-list-tags';

	/**
	 * List Tags Labels Key.
	 */
	public const SETTINGS_MAILCHIMP_LIST_TAGS_LABELS_KEY = 'mailchimp-list-tags-labels';

	/**
	 * List Tags Show Key.
	 */
	public const SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY = 'mailchimp-list-tags-show';

	/**
	 * Integration fields Key.
	 */
	public const SETTINGS_MAILCHIMP_INTEGRATION_FIELDS_KEY = 'mailchimp-integration-fields';

	/**
	 * Conditional tags key.
	 */
	public const SETTINGS_MAILCHIMP_CONDITIONAL_TAGS_KEY = 'mailchimp-conditional-tags';

	/**
	 * Instance variable for Mailchimp data.
	 *
	 * @var MailchimpClientInterface
	 */
	protected $mailchimpClient;

	/**
	 * Instance variable for Mailchimp form data.
	 *
	 * @var MapperInterface
	 */
	protected $mailchimp;

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param MailchimpClientInterface $mailchimpClient Inject Mailchimp which holds Mailchimp connect data.
	 * @param MapperInterface $mailchimp Inject Mailchimp which holds Mailchimp form data.
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(
		MailchimpClientInterface $mailchimpClient,
		MapperInterface $mailchimp,
		SettingsFallbackDataInterface $settingsFallback
	) {
		$this->mailchimpClient = $mailchimpClient;
		$this->mailchimp = $mailchimp;
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

		$list = $this->getSettingsValue(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_KEY, $formId);

		if (empty($list)) {
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
		$isUsed = $this->isCheckboxOptionChecked(SettingsMailchimp::SETTINGS_MAILCHIMP_USE_KEY, SettingsMailchimp::SETTINGS_MAILCHIMP_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyMailchimp()) ? Variables::getApiKeyMailchimp() : $this->getOptionValue(SettingsMailchimp::SETTINGS_MAILCHIMP_API_KEY_KEY);

		if (!$isUsed || empty($apiKey)) {
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
		$type = self::SETTINGS_TYPE_KEY;

		// Bailout if global config is not valid.
		if (!$this->isSettingsGlobalValid()) {
			return $this->getNoValidGlobalConfigOutput($type);
		}

		// Get forms from the API.
		$items = $this->mailchimpClient->getItems(false);

		// Bailout if integration can't fetch data.
		if (!$items) {
			return $this->getNoIntegrationFetchDataOutput($type);
		}

		// Find selected form id.
		$selectedFormId = $this->getSettingsValue(self::SETTINGS_MAILCHIMP_LIST_KEY, $formId);

		$output = [];

		// If the user has selected the form id populate additional config.
		if ($selectedFormId) {
			$formFields = $this->mailchimp->getFormFields($formId);

			// Output additonal tabs for config.
			$output = [
				'component' => 'tabs',
				'tabsContent' => [
					$this->getOutputIntegrationFields(
						$formId,
						$formFields,
						$type,
						self::SETTINGS_MAILCHIMP_INTEGRATION_FIELDS_KEY,
						[
							AbstractBaseRoute::CUSTOM_FORM_PARAMS['mailchimpTags']
						]
					),
					$this->getOutputConditionalTags(
						$formId,
						$formFields,
						self::SETTINGS_MAILCHIMP_CONDITIONAL_TAGS_KEY
					),
					$this->getOutputTags(
						$formId,
						$selectedFormId
					),
				],
			];
		}

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			...$this->getOutputFormSelection(
				$formId,
				$items,
				$selectedFormId,
				self::SETTINGS_TYPE_KEY,
				self::SETTINGS_MAILCHIMP_LIST_KEY
			),
			$output,
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		// Bailout if feature is not active.
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_MAILCHIMP_USE_KEY, self::SETTINGS_MAILCHIMP_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$apiKey = Variables::getApiKeyMailchimp();

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
								'inputName' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_API_KEY_KEY),
								'inputId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_API_KEY_KEY),
								'inputFieldLabel' => \__('API key', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'password',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_MAILCHIMP_API_KEY_KEY),
								'inputIsDisabled' => !empty($apiKey),
							],
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsMailchimp::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
										\__('Log in to your Mailchimp Account.', 'eightshift-forms'),
										\__('Navigate to your user profile image (bottom left corner).', 'eightshift-forms'),
										\__('Click on <strong>Account</strong>.', 'eightshift-forms'),
										\__('Click on <strong>Extras</strong> and <strong>API Keys</strong> in the tabs section.', 'eightshift-forms'),
										\__('Click on the <strong>Create a Key</strong> button.', 'eightshift-forms'),
										\__('Copy the API key into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Output array - tags.
	 *
	 * @param string $formId Form ID.
	 * @param string $selectedFormId Selected form id.
	 *
	 * @return array<string, array<int, array<string, array<int, array<string, array<int, array<string, mixed>>|bool|string>>|bool|string>>|string>
	 */
	private function getOutputTags(string $formId, string $selectedFormId): array
	{
		$tags = $this->mailchimpClient->getTags($selectedFormId);

		if (!$tags) {
			return [];
		}

		$isTagsShowHidden = $this->isCheckedSettings('hidden', self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId);

		return [
			'component' => 'tab',
			'tabLabel' => \__('Audience tags', 'eightshift-forms'),
			'tabContent' => [
				[
					'component' => 'intro',
					'introSubtitle' => \__('In these settings, you can control which tags will show up on the frontend and set up how will they look and work.', 'eightshift-forms'),
				],
				[
					'component' => 'select',
					'selectId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY),
					'selectFieldLabel' => \__('Tag visibility', 'eightshift-forms'),
					'selectFieldHelp' => $isTagsShowHidden ? \__('Tags you select bellow will be added to you form as a hidden field.', 'eightshift-forms') : \__('Tags you select bellow will be displayed in the form.', 'eightshift-forms'),
					'selectValue' => $this->getOptionValue(self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY),
					'selectSingleSubmit' => true,
					'selectOptions' => [
						[
							'component' => 'select-option',
							'selectOptionLabel' => \__('Don\'t show tags', 'eightshift-forms'),
							'selectOptionValue' => 'hidden',
							'selectOptionIsSelected' => $isTagsShowHidden,
						],
						[
							'component' => 'select-option',
							'selectOptionLabel' => \__('Show as a select menu', 'eightshift-forms'),
							'selectOptionValue' => 'select',
							'selectOptionIsSelected' => $this->isCheckedSettings('select', self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId),
						],
						[
							'component' => 'select-option',
							'selectOptionLabel' => \__('Show as checkboxes', 'eightshift-forms'),
							'selectOptionValue' => 'checkboxes',
							'selectOptionIsSelected' => $this->isCheckedSettings('checkboxes', self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId),
						],
					]
				],
				[
					'component' => 'group',
					'groupId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_TAGS_LABELS_KEY),
					'groupLabel' => \__('Tags list', 'eightshift-forms'),
					'groupStyle' => 'tags',
					'groupContent' => [
						[
							'component' => 'group',
							'groupName' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_TAGS_KEY),
							'groupHelp' => $isTagsShowHidden ? \__('Select tags that will be added to you form as a hidden field. If nothing is selected nothing will be sent.', 'eightshift-forms') : \__('Select tags that will be displayed in the form field. If nothing is selected everything will be displayed.', 'eightshift-forms'),
							'groupStyle' => !$isTagsShowHidden ? 'tags-inner-checkbox' : '',
							'groupContent' => [
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => '',
									'checkboxesName' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_TAGS_KEY),
									'checkboxesId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_TAGS_KEY),
									'checkboxesContent' => \array_map(
										function ($tag) use ($formId) {
											return [
												'component' => 'checkbox',
												'checkboxLabel' => $tag['name'],
												'checkboxIsChecked' => $this->isCheckboxSettingsChecked($tag['id'], self::SETTINGS_MAILCHIMP_LIST_TAGS_KEY, $formId),
												'checkboxValue' => $tag['id'],
											];
										},
										$tags
									),
								],
							],
						],
						!$isTagsShowHidden ? [
							'component' => 'group',
							'groupHelp' => \__('Provide override label that will be displayed on the frontend.', 'eightshift-forms'),
							'groupStyle' => 'tags-inner-input',
							'groupSaveOneField' => true,
							'groupContent' => \array_map(
								function ($tag) use ($formId) {
									$value = $this->getSettingsValueGroup(self::SETTINGS_MAILCHIMP_LIST_TAGS_LABELS_KEY, $formId);
									$id = $tag['id'] ?? '';

									return [
										'component' => 'input',
										'inputFieldLabel' => '',
										'inputName' => $id,
										'inputId' => $id,
										'inputPlaceholder' => $tag['name'],
										'inputValue' => $value[$id] ?? '',
									];
								},
								$tags,
							),
						] : [],
					],
				],
			],
		];
	}
}
