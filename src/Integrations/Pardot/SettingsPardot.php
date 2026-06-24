<?php

/**
 * Pardot Settings class.
 *
 * @package EightshiftForms\Integrations\Pardot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Pardot;

use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\AbstractSettingsIntegrations;
use EightshiftForms\Oauth\OauthInterface;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\SettingInterface;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsPardot class.
 */
class SettingsPardot extends AbstractSettingsIntegrations implements SettingGlobalInterface, SettingInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_pardot';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_pardot';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_pardot';

	/**
	 * Filter settings is valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_pardot';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'pardot';

	/**
	 * Pardot Use key.
	 */
	public const SETTINGS_PARDOT_USE_KEY = 'pardot-use';

	/**
	 * Client ID key.
	 */
	public const SETTINGS_PARDOT_CLIENT_ID = 'pardot-client-id';

	/**
	 * Client Secret key.
	 */
	public const SETTINGS_PARDOT_SECRET = 'pardot-client-secret';

	/**
	 * Business Unit ID key.
	 */
	public const SETTINGS_PARDOT_BUSINESS_UNIT_ID = 'pardot-business-unit-id';

	/**
	 * Environment key (production|sandbox).
	 */
	public const SETTINGS_PARDOT_ENVIRONMENT_KEY = 'pardot-environment';

	/**
	 * Skip integration key.
	 */
	public const SETTINGS_PARDOT_SKIP_INTEGRATION_KEY = 'pardot-skip-integration';

	/**
	 * OAuth allow key.
	 */
	public const SETTINGS_PARDOT_OAUTH_ALLOW_KEY = 'pardot-oauth-allow';

	/**
	 * Form handler ID key (per-form setting).
	 */
	public const SETTINGS_PARDOT_ITEM_ID_KEY = 'pardot-item-id';

	/**
	 * Field params map key (per-form setting).
	 */
	public const SETTINGS_PARDOT_PARAMS_MAP_KEY = 'pardot-params-map';

	/**
	 * Create a new instance.
	 *
	 * @param PardotClientInterface $pardotClient Inject Pardot client.
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback methods.
	 * @param OauthInterface $oauthPardot Inject Oauth methods.
	 */
	public function __construct(
		protected PardotClientInterface $pardotClient,
		protected SettingsFallbackDataInterface $settingsFallback,
		protected OauthInterface $oauthPardot
	) {} // phpcs:ignore

	/**
	 * Register all the hooks
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, $this->getSettingsData(...));
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, $this->getSettingsGlobalData(...));
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, $this->isSettingsValid(...), 10, 2);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, $this->isSettingsGlobalValid(...));
	}

	/**
	 * Determine if settings are valid.
	 *
	 * @param bool $output Output.
	 * @param string $formId Form ID.
	 */
	public function isSettingsValid(bool $output, string $formId): bool
	{
		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		$selectedHandler = SettingsHelpers::getSettingValue(self::SETTINGS_PARDOT_ITEM_ID_KEY, $formId);
		return (bool) $selectedHandler;
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
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$selectedHandler = SettingsHelpers::getSettingValue(self::SETTINGS_PARDOT_ITEM_ID_KEY, $formId);
		$mapParams = SettingsHelpers::getSettingValueGroup(self::SETTINGS_PARDOT_PARAMS_MAP_KEY, $formId);
		$handlerFields = $selectedHandler !== '' && $selectedHandler !== '0' ? $this->pardotClient->getItem($selectedHandler) : [];
		$formDetails = GeneralHelpers::getFormDetails($formId);
		$formFields = $formDetails[Config::FD_FIELD_NAMES] ?? [];

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Settings', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'select',
								'selectName' => SettingsHelpers::getSettingName(self::SETTINGS_PARDOT_ITEM_ID_KEY),
								'selectFieldLabel' => \__('Form handler', 'eightshift-forms'),
								'selectSingleSubmit' => true,
								'selectPlaceholder' => \__('Select form handler', 'eightshift-forms'),
								'selectContent' => \array_map(
									static fn(array $option): array => [
										'component' => 'select-option',
										'selectOptionLabel' => $option['title'],
										'selectOptionValue' => $option['id'],
										'selectOptionIsSelected' => $selectedHandler === $option['id'],
									],
									$this->pardotClient->getItems()
								),
							],

						],
					],
					($selectedHandler && $handlerFields && $formFields) ? [
						'component' => 'tab',
						'tabLabel' => \__('Field mapping', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('We recommend that every field in the Pardot is not required. If you want to make a field required, you need to add it to the form manually.', 'eightshift-forms'),
								'introIsHighlighted' => true,
								'introIsHighlightedImportant' => true,
							],
							[
								'component' => 'field',
								'fieldLabel' => '<b>' . \__('Form field', 'eightshift-forms') . '</b>',
								'fieldContent' => '<b>' . \__('Pardot field', 'eightshift-forms') . '</b>',
								'fieldBeforeContent' => '&emsp;',
								'fieldIsFiftyFiftyHorizontal' => true,
							],
							[
								'component' => 'group',
								'groupName' => SettingsHelpers::getSettingName(self::SETTINGS_PARDOT_PARAMS_MAP_KEY),
								'groupSaveOneField' => true,
								'groupStyle' => 'default-listing',
								'groupContent' => [
									...\array_map(
										fn($formField): array => [
											'component' => 'select',
											'selectName' => $formField,
											'selectFieldLabel' => \ucfirst((string) $formField),
											'selectFieldIsFiftyFiftyHorizontal' => true,
											'selectFieldBeforeContent' => '&rarr;',
											'selectPlaceholder' => \__('Select Pardot field', 'eightshift-forms'),
											'selectContent' => \array_filter(\array_map(
												static function (array $pardotField) use ($mapParams, $formField): array {
													$id = $pardotField['id'] ?? '';

													if (!$id) {
														return [];
													}

													return [
														'component' => 'select-option',
														'selectOptionLabel' => $pardotField['title'],
														'selectOptionValue' => $id,
														'selectOptionIsSelected' => isset($mapParams[$formField]) && $mapParams[$formField] === $id,
													];
												},
												$handlerFields
											)),
										],
										$formFields
									),
								],
							],
						],
					] : [],
				],
			],
		];
	}

	/**
	 * Determine if settings global are valid.
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_PARDOT_USE_KEY, self::SETTINGS_PARDOT_USE_KEY);
		$clientId = (bool) SettingsHelpers::getOptionWithConstant(Variables::getClientIdPardot(), self::SETTINGS_PARDOT_CLIENT_ID);
		$clientSecret = (bool) SettingsHelpers::getOptionWithConstant(Variables::getClientSecretPardot(), self::SETTINGS_PARDOT_SECRET);
		$businessUnitId = (bool) SettingsHelpers::getOptionWithConstant(Variables::getBusinessUnitIdPardot(), self::SETTINGS_PARDOT_BUSINESS_UNIT_ID);
		return !(!$isUsed || !$clientId || !$clientSecret || !$businessUnitId);
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_PARDOT_USE_KEY, self::SETTINGS_PARDOT_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$deactivateIntegration = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_PARDOT_SKIP_INTEGRATION_KEY, self::SETTINGS_PARDOT_SKIP_INTEGRATION_KEY);

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_PARDOT_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_PARDOT_SKIP_INTEGRATION_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									],
								],
							],
							...($deactivateIntegration ? [
								[
									'component' => 'intro',
									'introSubtitle' => SettingsOutputHelpers::getPartialDeactivatedIntegration('introSubtitle'),
									'introIsHighlighted' => true,
									'introIsHighlightedImportant' => true,
								],
							] : [
								[
									'component' => 'divider',
								],
								SettingsOutputHelpers::getPasswordFieldWithGlobalVariable(
									Variables::getClientIdPardot(),
									self::SETTINGS_PARDOT_CLIENT_ID,
									'ES_CLIENT_ID_PARDOT',
									\__('Consumer Key (Client ID)', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
								],
								SettingsOutputHelpers::getPasswordFieldWithGlobalVariable(
									Variables::getClientSecretPardot(),
									self::SETTINGS_PARDOT_SECRET,
									'ES_CLIENT_SECRET_PARDOT',
									\__('Consumer Secret (Client Secret)', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
								],
								SettingsOutputHelpers::getInputFieldWithGlobalVariable(
									Variables::getBusinessUnitIdPardot(),
									self::SETTINGS_PARDOT_BUSINESS_UNIT_ID,
									'ES_BUSINESS_UNIT_ID_PARDOT',
									\__('Business Unit ID', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
								],
								[
									'component' => 'select',
									'selectName' => SettingsHelpers::getOptionName(self::SETTINGS_PARDOT_ENVIRONMENT_KEY),
									'selectFieldLabel' => \__('Environment', 'eightshift-forms'),
									'selectValue' => SettingsHelpers::getOptionValue(self::SETTINGS_PARDOT_ENVIRONMENT_KEY),
									'selectContent' => [
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Production', 'eightshift-forms'),
											'selectOptionValue' => 'production',
											'selectOptionIsSelected' => SettingsHelpers::getOptionValue(self::SETTINGS_PARDOT_ENVIRONMENT_KEY) !== 'sandbox',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Sandbox', 'eightshift-forms'),
											'selectOptionValue' => 'sandbox',
											'selectOptionIsSelected' => SettingsHelpers::getOptionValue(self::SETTINGS_PARDOT_ENVIRONMENT_KEY) === 'sandbox',
										],
									],
								],
								[
									'component' => 'divider',
								],
								SettingsOutputHelpers::getOauthConnection($this->oauthPardot->getOauthAuthorizeUrl(), OauthPardot::OAUTH_PARDOT_ACCESS_TOKEN_KEY, self::SETTINGS_PARDOT_OAUTH_ALLOW_KEY),
								[
									'component' => 'divider',
								],
								SettingsOutputHelpers::getTestApiConnection(self::SETTINGS_TYPE_KEY),
							]),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							...$this->getGlobalGeneralSettings(self::SETTINGS_TYPE_KEY),
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsPardot::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to connect to Pardot?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Salesforce org.', 'eightshift-forms'),
									\__('Go to <strong>Setup → External Client App Manager</strong> and create a new App.', 'eightshift-forms'),
									\__('Basic Information: <strong>External Client App Name</strong>, <strong>Contact Email</strong>, <strong>Distribution State: Local</strong>', 'eightshift-forms'),
									\__('OAuth Settings: <strong>Manage user data via APIs (api)</strong>, <strong>Perform requests at any time (refresh_token, offline_access)</strong>, <strong>Manage Pardot services (pardot_api)</strong>', 'eightshift-forms'),
									\__('Security: <strong>Require secret for Web Server Flow</strong>, <strong>Require secret for Refresh Token Flow</strong>', 'eightshift-forms'),
									\__('App Policies: <strong>Start Page: OAuth</strong>, <strong>OAuth Start URL: https://login.salesforce.com/</strong>', 'eightshift-forms'),
									\__('Once you create the app copy the <strong>Consumer Key</strong> and <strong>Consumer Secret</strong> into the fields above.', 'eightshift-forms'),
									\__('Go to <strong>Marketing Setup → Business Unit Setup</strong> and copy the <strong>Business Unit ID</strong> (starts with <code>0Uv</code>).', 'eightshift-forms'),
									// translators: %s will be replaced with the site URL.
									\sprintf(\__('In the Connected App, set the OAuth Callback URL to <br/><code>%s/wp-json/eightshift-forms/v1/oauth/pardot</code>', 'eightshift-forms'), \get_site_url()),
									\__('Save your settings here, then click <strong>Oauth Connect</strong> to authorise.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
