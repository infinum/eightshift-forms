<?php

/**
 * NotionBuilder Settings class.
 *
 * @package EightshiftForms\Integrations\Notionbuilder
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Notionbuilder;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftForms\Integrations\AbstractSettingsIntegrations;
use EightshiftForms\Oauth\OauthInterface;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsNotionbuilder class.
 */
class SettingsNotionbuilder extends AbstractSettingsIntegrations implements UtilsSettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_notionbuilder';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_notionbuilder';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'notionbuilder';

	/**
	 * NotionBuilder Use key.
	 */
	public const SETTINGS_NOTIONBUILDER_USE_KEY = 'notionbuilder-use';

	/**
	 * Client ID Key.
	 */
	public const SETTINGS_NOTIONBUILDER_CLIENT_ID = 'notionbuilder-client-id';

	/**
	 * Client Secret Key.
	 */
	public const SETTINGS_NOTIONBUILDER_CLIENT_SECRET = 'notionbuilder-client-secret';

	/**
	 * Client Slug Key.
	 */
	public const SETTINGS_NOTIONBUILDER_CLIENT_SLUG = 'notionbuilder-client-slug';

	/**
	 * Skip integration.
	 */
	public const SETTINGS_NOTIONBUILDER_SKIP_INTEGRATION_KEY = 'notionbuilder-skip-integration';

	/**
	 * Params map key.
	 */
	public const SETTINGS_NOTIONBUILDER_PARAMS_MAP_KEY = 'notionbuilder-params-map';

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Instance variable for Oauth.
	 *
	 * @var OauthInterface
	 */
	protected $oauthNotionbuilder;

	/**
	 * Create a new instance.
	 *
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback methods.
	 * @param OauthInterface $oauthNotionbuilder Inject Oauth methods.
	 */
	public function __construct(
		SettingsFallbackDataInterface $settingsFallback,
		OauthInterface $oauthNotionbuilder,
	) {
		$this->settingsFallback = $settingsFallback;
		$this->oauthNotionbuilder = $oauthNotionbuilder;
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
		$params = $formDetails[UtilsConfig::FD_FIELD_NAMES] ?? [];
		$mapParams = UtilsSettingsHelper::getSettingValueGroup(self::SETTINGS_NOTIONBUILDER_PARAMS_MAP_KEY, $formId);

		dump($formDetails);

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
								'component' => 'group',
								'groupName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_NOTIONBUILDER_PARAMS_MAP_KEY),
								'groupSaveOneField' => true,
								'groupStyle' => 'default-listing',
								'groupContent' => [
									[
										'component' => 'field',
										'fieldLabel' => '<b>' . \__('Form field', 'eightshift-forms') . '</b>',
										'fieldContent' => '<b>' . \__('NotionBuilder fields', 'eightshift-forms') . '</b>',
										'fieldBeforeContent' => '&emsp;', // "Em space" to pad it out a bit.
										'fieldIsFiftyFiftyHorizontal' => true,
									],
									...\array_map(
										function ($item) use ($mapParams) {
											return [
												'component' => 'select',
												'selectName' => $item,
												'selectFieldLabel' => ucfirst($item),
												'selectValue' => $mapParams[$item] ?? '',
												'selectFieldIsFiftyFiftyHorizontal' => true,
												'selectFieldBeforeContent' => '&rarr;',
												'selectContent' => \array_map(
													static function ($option) use ($mapParams, $item) {
														return [
															'component' => 'select-option',
															'selectOptionLabel' => $option['title'],
															'selectOptionValue' => $option['id'],
															'selectOptionIsSelected' => $option['id'] === ($mapParams[$item] ?? ''),
														];
													},
													$this->getFields()
												),
											];
										},
										$params
									),
								],
							],
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
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_NOTIONBUILDER_USE_KEY, self::SETTINGS_NOTIONBUILDER_USE_KEY);
		$clientId = (bool) UtilsSettingsHelper::getOptionWithConstant(Variables::getClientIdNotionBuilder(), self::SETTINGS_NOTIONBUILDER_CLIENT_ID);
		$clientSecret = (bool) UtilsSettingsHelper::getOptionWithConstant(Variables::getClientSecretNotionBuilder(), self::SETTINGS_NOTIONBUILDER_CLIENT_SECRET);
		$clientSlug = UtilsSettingsHelper::getOptionWithConstant(Variables::getClientSlugNotionBuilder(), self::SETTINGS_NOTIONBUILDER_CLIENT_SLUG);

		if (!$isUsed || !$clientId || !$clientSecret || !$clientSlug) {
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
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_NOTIONBUILDER_USE_KEY, self::SETTINGS_NOTIONBUILDER_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$deactivateIntegration = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_NOTIONBUILDER_SKIP_INTEGRATION_KEY, self::SETTINGS_NOTIONBUILDER_SKIP_INTEGRATION_KEY);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_NOTIONBUILDER_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_NOTIONBUILDER_SKIP_INTEGRATION_KEY,
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
									Variables::getClientIdNotionBuilder(),
									self::SETTINGS_NOTIONBUILDER_CLIENT_ID,
									'ES_CLIENT_ID_NOTIONBUILDER',
									\__('Client ID', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getPasswordFieldWithGlobalVariable(
									Variables::getClientSecretNotionBuilder(),
									self::SETTINGS_NOTIONBUILDER_CLIENT_SECRET,
									'ES_CLIENT_SECRET_NOTIONBUILDER',
									\__('Client Secret', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getInputFieldWithGlobalVariable(
									Variables::getClientSlugNotionBuilder(),
									self::SETTINGS_NOTIONBUILDER_CLIENT_SLUG,
									'ES_CLIENT_SLUG_NOTIONBUILDER',
									\__('Client slug', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getOauthConnection($this->oauthNotionbuilder->getOauthAuthorizeUrl(), OauthNotionbuilder::OAUTH_NOTIONBUILDER_ACCESS_TOKEN_KEY),
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
					$this->settingsFallback->getOutputGlobalFallback(SettingsNotionbuilder::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									// translators: %s will be replaced with the link.
									\sprintf(\__('Log in to your <a target="_blank" rel="noopener noreferrer" href="%s">NotionBuilder Account</a>.', 'eightshift-forms'), 'https://app.notionbuilder.io/'),
									// translators: %s will be replaced with the link.
									\sprintf(\__('Go to <a target="_blank" rel="noopener noreferrer" href="%s">API Credentials Settings</a>.', 'eightshift-forms'), 'https://app.notionbuilder.io/configure/dev_center/credentials'),
									\__('Click on <strong>Create New API Key</strong>.', 'eightshift-forms'),
									\__('Select <strong>Job Board</strong> as your API Type.', 'eightshift-forms'),
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
	 * Get fields for NotionBuilder.
	 *
	 * @return array<string, string>
	 */
	private function getFields(): array
	{
		return [
			[
				'id' => 'email1',
				'title' => \__('Email1', 'eightshift-forms'),
			]
		];
	}
}
