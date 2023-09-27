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
use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMailchimp class.
 */
class SettingsMailchimp implements SettingInterface, SettingGlobalInterface, ServiceInterface
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
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_mailchimp';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_mailchimp';

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
	 * List Tags Show Key.
	 */
	public const SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY = 'mailchimp-list-tags-show';

	/**
	 * Skip integration.
	 */
	public const SETTINGS_MAILCHIMP_SKIP_INTEGRATION_KEY = 'mailchimp-skip-integration';

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(SettingsFallbackDataInterface $settingsFallback)
	{
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
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = $this->isOptionCheckboxChecked(SettingsMailchimp::SETTINGS_MAILCHIMP_USE_KEY, SettingsMailchimp::SETTINGS_MAILCHIMP_USE_KEY);
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
		// Bailout if global config is not valid.
		if (!$this->isSettingsGlobalValid()) {
			return $this->getSettingOutputNoValidGlobalConfig(self::SETTINGS_TYPE_KEY);
		}

		// Output additonal tabs for config.
		$output = [
			'component' => 'tabs',
			'tabsContent' => [
				[
					'component' => 'tab',
					'tabLabel' => \__('Audience tags', 'eightshift-forms'),
					'tabContent' => [
						[
							'component' => 'intro',
							'introSubtitle' => \__('In these settings, you can control the work and feel of audience tags.', 'eightshift-forms'),
						],
						[
							'component' => 'select',
							'selectName' => $this->getSettingName(self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY),
							'selectFieldLabel' => \__('Tag visibility', 'eightshift-forms'),
							'selectFieldHelp' => \__('Select the way you want to show/use tags in your form.', 'eightshift-forms'),
							'selectValue' => $this->getSettingValue(self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId),
							'selectSingleSubmit' => true,
							'selectPlaceholder' => \__('Select tag visibility', 'eightshift-forms'),
							'selectContent' => [
								[
									'component' => 'select-option',
									'selectOptionLabel' => \__('Don\'t use tags', 'eightshift-forms'),
									'selectOptionValue' => 'none',
									'selectOptionIsSelected' => $this->isSettingChecked('none', self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId),
								],
								[
									'component' => 'select-option',
									'selectOptionLabel' => \__('Use as hidden field', 'eightshift-forms'),
									'selectOptionValue' => 'hidden',
									'selectOptionIsSelected' => $this->isSettingChecked('hidden', self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId),
								],
								[
									'component' => 'select-option',
									'selectOptionLabel' => \__('Use as a select field', 'eightshift-forms'),
									'selectOptionValue' => 'select',
									'selectOptionIsSelected' => $this->isSettingChecked('select', self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId),
								],
								[
									'component' => 'select-option',
									'selectOptionLabel' => \__('Use as checkbox field', 'eightshift-forms'),
									'selectOptionValue' => 'checkboxes',
									'selectOptionIsSelected' => $this->isSettingChecked('checkboxes', self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId),
								],
								[
									'component' => 'select-option',
									'selectOptionLabel' => \__('Use as radio field', 'eightshift-forms'),
									'selectOptionValue' => 'radios',
									'selectOptionIsSelected' => $this->isSettingChecked('radios', self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId),
								],
							]
						],
					],
				],
			],
		];

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
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
		if (!$this->isOptionCheckboxChecked(self::SETTINGS_MAILCHIMP_USE_KEY, self::SETTINGS_MAILCHIMP_USE_KEY)) {
			return $this->getSettingOutputNoActiveFeature();
		}

		$apiKey = Variables::getApiKeyMailchimp();
		$successRedirectUrl = $this->getSuccessRedirectUrlFilterValue(self::SETTINGS_TYPE_KEY, '');
		$deactivateIntegration = $this->isOptionCheckboxChecked(self::SETTINGS_MAILCHIMP_SKIP_INTEGRATION_KEY, self::SETTINGS_MAILCHIMP_SKIP_INTEGRATION_KEY);

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('General', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => $this->getOptionName(self::SETTINGS_MAILCHIMP_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => $this->settingDataDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => $this->settingDataDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_MAILCHIMP_SKIP_INTEGRATION_KEY,
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
									$this->getOptionName(self::SETTINGS_MAILCHIMP_API_KEY_KEY),
									\__('API key', 'eightshift-forms'),
									!empty($apiKey) ? $apiKey : $this->getOptionValue(self::SETTINGS_MAILCHIMP_API_KEY_KEY),
									'ES_API_KEY_MAILCHIMP',
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
								'inputFieldLabel' => \__('Redirect to URL after form submit', 'eightshift-forms'),
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
}
