<?php

/**
 * NationBuilder Settings class.
 *
 * @package EightshiftForms\Integrations\Nationbuilder
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Nationbuilder;

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
 * SettingsNationbuilder class.
 */
class SettingsNationbuilder extends AbstractSettingsIntegrations implements UtilsSettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_nationbuilder';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_nationbuilder';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_nationbuilder';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'nationbuilder';

	/**
	 * NationBuilder Use key.
	 */
	public const SETTINGS_NATIONBUILDER_USE_KEY = 'nationbuilder-use';

	/**
	 * Client ID Key.
	 */
	public const SETTINGS_NATIONBUILDER_CLIENT_ID = 'nationbuilder-client-id';

	/**
	 * Client Secret Key.
	 */
	public const SETTINGS_NATIONBUILDER_CLIENT_SECRET = 'nationbuilder-client-secret';

	/**
	 * Client Slug Key.
	 */
	public const SETTINGS_NATIONBUILDER_CLIENT_SLUG = 'nationbuilder-client-slug';

	/**
	 * Skip integration.
	 */
	public const SETTINGS_NATIONBUILDER_SKIP_INTEGRATION_KEY = 'nationbuilder-skip-integration';

	/**
	 * Params map key.
	 */
	public const SETTINGS_NATIONBUILDER_PARAMS_MAP_KEY = 'nationbuilder-params-map';

	/**
	 * List key.
	 */
	public const SETTINGS_NATIONBUILDER_LIST_KEY = 'nationbuilder-list';

	/**
	 * Tags key.
	 */
	public const SETTINGS_NATIONBUILDER_TAGS_KEY = 'nationbuilder-tags';

	/**
	 * Cron key.
	 */
	public const SETTINGS_NATIONBUILDER_CRON_KEY = 'nationbuilder-cron';

	/**
	 * Oauth allow key.
	 */
	public const SETTINGS_NATIONBUILDER_OAUTH_ALLOW_KEY = 'nationbuilder-oauth-allow';

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
	protected $oauthNationbuilder;

	/**
	 * Instance variable for Jira data.
	 *
	 * @var NationbuilderClientInterface
	 */
	protected $nationbuilderClient;

	/**
	 * Create a new instance.
	 *
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback methods.
	 * @param OauthInterface $oauthNationbuilder Inject Oauth methods.
	 * @param NationbuilderClientInterface $nationbuilderClient Inject Jira which holds Jira connect data.
	 */
	public function __construct(
		SettingsFallbackDataInterface $settingsFallback,
		OauthInterface $oauthNationbuilder,
		NationbuilderClientInterface $nationbuilderClient,
	) {
		$this->settingsFallback = $settingsFallback;
		$this->oauthNationbuilder = $oauthNationbuilder;
		$this->nationbuilderClient = $nationbuilderClient;
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
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
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
		$mapParams = UtilsSettingsHelper::getSettingValueGroup(self::SETTINGS_NATIONBUILDER_PARAMS_MAP_KEY, $formId);
		$list = UtilsSettingsHelper::getSettingValue(self::SETTINGS_NATIONBUILDER_LIST_KEY, $formId);
		$tags = \array_flip(\explode(UtilsConfig::DELIMITER, UtilsSettingsHelper::getSettingValue(self::SETTINGS_NATIONBUILDER_TAGS_KEY, $formId)));

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
								'groupName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_NATIONBUILDER_PARAMS_MAP_KEY),
								'groupSaveOneField' => true,
								'groupStyle' => 'default-listing',
								'groupContent' => [
									[
										'component' => 'field',
										'fieldLabel' => '<b>' . \__('Form field', 'eightshift-forms') . '</b>',
										'fieldContent' => '<b>' . \__('NationBuilder fields', 'eightshift-forms') . '</b>',
										'fieldBeforeContent' => '&emsp;', // "Em space" to pad it out a bit.
										'fieldIsFiftyFiftyHorizontal' => true,
									],
									...\array_map(
										function ($item) use ($mapParams) {
											return [
												'component' => 'select',
												'selectName' => $item,
												'selectFieldLabel' => \ucfirst($item),
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
					[
						'component' => 'tab',
						'tabLabel' => \__('Lists & Tags', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'select',
								'selectName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_NATIONBUILDER_LIST_KEY),
								'selectFieldLabel' => \__('Select list', 'eightshift-forms'),
								'selectValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_NATIONBUILDER_LIST_KEY, $formId),
								'selectContent' => \array_map(
									static function ($option) use ($list) {
										return [
											'component' => 'select-option',
											'selectOptionLabel' => $option['title'],
											'selectOptionValue' => $option['id'],
											'selectOptionIsSelected' => $option['id'] === $list,
										];
									},
									$this->nationbuilderClient->getLists()
								),
							],
							[
								'component' => 'select',
								'selectName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_NATIONBUILDER_TAGS_KEY),
								'selectFieldLabel' => \__('Select tags', 'eightshift-forms'),
								'selectIsMultiple' => true,
								'selectValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_NATIONBUILDER_TAGS_KEY, $formId),
								'selectContent' => \array_map(
									static function ($option) use ($tags) {
										return [
											'component' => 'select-option',
											'selectOptionLabel' => $option['title'],
											'selectOptionValue' => $option['id'],
											'selectOptionIsSelected' => isset($tags[$option['id']]),
										];
									},
									$this->nationbuilderClient->getTags()
								),
							]
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
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_NATIONBUILDER_USE_KEY, self::SETTINGS_NATIONBUILDER_USE_KEY);
		$clientId = (bool) UtilsSettingsHelper::getOptionWithConstant(Variables::getClientIdNationBuilder(), self::SETTINGS_NATIONBUILDER_CLIENT_ID);
		$clientSecret = (bool) UtilsSettingsHelper::getOptionWithConstant(Variables::getClientSecretNationBuilder(), self::SETTINGS_NATIONBUILDER_CLIENT_SECRET);
		$clientSlug = UtilsSettingsHelper::getOptionWithConstant(Variables::getClientSlugNationBuilder(), self::SETTINGS_NATIONBUILDER_CLIENT_SLUG);

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
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_NATIONBUILDER_USE_KEY, self::SETTINGS_NATIONBUILDER_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$deactivateIntegration = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_NATIONBUILDER_SKIP_INTEGRATION_KEY, self::SETTINGS_NATIONBUILDER_SKIP_INTEGRATION_KEY);

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
								'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_NATIONBUILDER_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_NATIONBUILDER_SKIP_INTEGRATION_KEY,
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
									Variables::getClientIdNationBuilder(),
									self::SETTINGS_NATIONBUILDER_CLIENT_ID,
									'ES_CLIENT_ID_NATIONBUILDER',
									\__('Client ID', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getPasswordFieldWithGlobalVariable(
									Variables::getClientSecretNationBuilder(),
									self::SETTINGS_NATIONBUILDER_CLIENT_SECRET,
									'ES_CLIENT_SECRET_NATIONBUILDER',
									\__('Client Secret', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getInputFieldWithGlobalVariable(
									Variables::getClientSlugNationBuilder(),
									self::SETTINGS_NATIONBUILDER_CLIENT_SLUG,
									'ES_CLIENT_SLUG_NATIONBUILDER',
									\__('Client slug', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getOauthConnection($this->oauthNationbuilder->getOauthAuthorizeUrl(), OauthNationbuilder::OAUTH_NATIONBUILDER_ACCESS_TOKEN_KEY, self::SETTINGS_NATIONBUILDER_OAUTH_ALLOW_KEY),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getTestApiConnection(self::SETTINGS_TYPE_KEY),
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
					[
						'component' => 'tab',
						'tabLabel' => \__('Queue jobs', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'textarea',
								'textareaFieldLabel' => \__('Queue jobs', 'eightshift-forms'),
								'textareaFieldHelp' => \__('Subscriptions in queue that are still not processed.', 'eightshift-forms'),
								'textareaIsReadOnly' => true,
								'textareaIsPreventSubmit' => true,
								'textareaName' => 'queue',
								'textareaValue' => \wp_json_encode(UtilsSettingsHelper::getOptionValueGroup(self::SETTINGS_NATIONBUILDER_CRON_KEY), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE),
								'textareaSize' => 'huge',
								'textareaLimitHeight' => true,
							],
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsNationbuilder::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									// translators: %s will be replaced with the link.
									\sprintf(\__('Log in to your <a target="_blank" rel="noopener noreferrer" href="%s">NationBuilder Account</a>.', 'eightshift-forms'), 'https://nationbuilder.com/nation_login/'),
									\__('Click on <strong>Institutions</strong> in the sidebar.', 'eightshift-forms'),
									\__('Click on <strong>Apps</strong> in the top bar.', 'eightshift-forms'),
									\__('Create a new application and populate the OAuth Callback-url with <br/>`https://<server-url>/wp-json/eightshift-forms/v1/oauth/nationbuilder`', 'eightshift-forms'),
									\__('Copy the Client Secret, Id and Slug keys into the fields under the General tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Get fields for NationBuilder.
	 *
	 * @return array<mixed>
	 */
	private function getFields(): array
	{
		$customFields = \array_values(\array_map(
			static function ($field) {
				return [
					'id' => $field['id'],
					'title' => $field['title'],
				];
			},
			$this->nationbuilderClient->getCustomFields()
		));

		return \array_merge([
			[
				'id' => 'first_name',
				'title' => \__('First name', 'eightshift-forms'),
			],
			[
				'id' => 'middle_name',
				'title' => \__('Middle name', 'eightshift-forms'),
			],
			[
				'id' => 'last_name',
				'title' => \__('Last name', 'eightshift-forms'),
			],
			[
				'id' => 'legal_name',
				'title' => \__('Legal name', 'eightshift-forms'),
			],
			[
				'id' => 'mobile_number',
				'title' => \__('Mobile number', 'eightshift-forms'),
			],
			[
				'id' => 'phone_number',
				'title' => \__('Phone number', 'eightshift-forms'),
			],
			[
				'id' => 'work_phone_number',
				'title' => \__('Work phone number', 'eightshift-forms'),
			],
			[
				'id' => 'email1',
				'title' => \__('Email1', 'eightshift-forms'),
			],
			[
				'id' => 'email2',
				'title' => \__('Email2', 'eightshift-forms'),
			],
			[
				'id' => 'email3',
				'title' => \__('Email3', 'eightshift-forms'),
			],
			[
				'id' => 'email4',
				'title' => \__('Email4', 'eightshift-forms'),
			],
			[
				'id' => 'occupation',
				'title' => \__('Occupation', 'eightshift-forms'),
			],
			[
				'id' => 'born_at',
				'title' => \__('Born at', 'eightshift-forms'),
			],
			[
				'id' => 'note',
				'title' => \__('Note', 'eightshift-forms'),
			],
			[
				'id' => 'city_district',
				'title' => \__('City', 'eightshift-forms'),
			],
			[
				'id' => 'county_district',
				'title' => \__('Country', 'eightshift-forms'),
			],
			[
				'id' => 'employer',
				'title' => \__('Employer', 'eightshift-forms'),
			],
			[
				'id' => 'sex',
				'title' => \__('Sex', 'eightshift-forms'),
			],
			[
				'id' => 'federal_district',
				'title' => \__('Federal district', 'eightshift-forms'),
			],
			[
				'id' => 'fire_district',
				'title' => \__('Fire district', 'eightshift-forms'),
			],
			[
				'id' => 'state_lower_district',
				'title' => \__('State lower district', 'eightshift-forms'),
			],
			[
				'id' => 'state_upper_district',
				'title' => \__('State upper district', 'eightshift-forms'),
			],
			[
				'id' => 'supranational_district',
				'title' => \__('Supranational district', 'eightshift-forms'),
			],
			[
				'id' => 'city_sub_district',
				'title' => \__('City sub district', 'eightshift-forms'),
			],
			[
				'id' => 'availability',
				'title' => \__('Availability', 'eightshift-forms'),
			],
			[
				'id' => 'church',
				'title' => \__('Church', 'eightshift-forms'),
			],
			[
				'id' => 'ethnicity',
				'title' => \__('Ethnicity', 'eightshift-forms'),
			],
			[
				'id' => 'fax_number',
				'title' => \__('Fax number', 'eightshift-forms'),
			],
		], $customFields);
	}
}
