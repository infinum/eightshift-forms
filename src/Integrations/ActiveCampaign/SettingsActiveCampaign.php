<?php

/**
 * ActiveCampaign Settings class.
 *
 * @package EightshiftForms\Integrations\ActiveCampaign
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\ActiveCampaign;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ActiveCampaign\ActiveCampaignClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsActiveCampaign class.
 */
class SettingsActiveCampaign implements SettingInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_active_campaign';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_active_campaign';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_active_campaign';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'active-campaign';

	/**
	 * ActiveCampaign Use key.
	 */
	public const SETTINGS_ACTIVE_CAMPAIGN_USE_KEY = 'active-campaign-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_ACTIVE_CAMPAIGN_API_KEY_KEY = 'active-campaign-api-key';

	/**
	 * API Url.
	 */
	public const SETTINGS_ACTIVE_CAMPAIGN_API_URL_KEY = 'active-campaign-api-url';

	/**
	 * List ID Key.
	 */
	public const SETTINGS_ACTIVE_CAMPAIGN_LIST_KEY = 'active-campaign-list';

	/**
	 * Integration fields Key.
	 */
	public const SETTINGS_ACTIVE_CAMPAIGN_INTEGRATION_FIELDS_KEY = 'active-campaign-integration-fields';

	/**
	 * Conditional tags key.
	 */
	public const SETTINGS_ACTIVE_CAMPAIGN_CONDITIONAL_TAGS_KEY = 'active-campaign-conditional-tags';

	/**
	 * Instance variable for ActiveCampaign data.
	 *
	 * @var ActiveCampaignClientInterface
	 */
	private $activeCampaignClient;

	/**
	 * Instance variable for ActiveCampaign form data.
	 *
	 * @var MapperInterface
	 */
	private $activeCampaign;

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param ActiveCampaignClientInterface $activeCampaignClient Inject ActiveCampaign which holds ActiveCampaign connect data.
	 * @param MapperInterface $activeCampaign Inject ActiveCampaign which holds ActiveCampaign form data.
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds fallback settings data.
	 */
	public function __construct(
		ActiveCampaignClientInterface $activeCampaignClient,
		MapperInterface $activeCampaign,
		SettingsFallbackDataInterface $settingsFallback
	) {
		$this->activeCampaignClient = $activeCampaignClient;
		$this->activeCampaign = $activeCampaign;
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

		$list = $this->getSettingsValue(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_LIST_KEY, $formId);

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
		$isUsed = $this->isCheckboxOptionChecked(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY, SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyActiveCampaign()) ? Variables::getApiKeyActiveCampaign() : $this->getOptionValue(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_API_KEY_KEY);
		$url = !empty(Variables::getApiUrlActiveCampaign()) ? Variables::getApiUrlActiveCampaign() : $this->getOptionValue(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_API_URL_KEY);

		if (!$isUsed || empty($apiKey) || empty($url)) {
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
		$items = $this->activeCampaignClient->getItems(false);

		// Bailout if integration can't fetch data.
		if (!$items) {
			return $this->getNoIntegrationFetchDataOutput($type);
		}

		// Find selected form id.
		$selectedFormId = $this->getSettingsValue(self::SETTINGS_ACTIVE_CAMPAIGN_LIST_KEY, $formId);

		$output = [];

		// If the user has selected the list.
		if ($selectedFormId) {
			$formFields = $this->activeCampaign->getFormFields($formId);

			// Output additonal tabs for config.
			$output = [
				'component' => 'tabs',
				'tabsContent' => [
					$this->getOutputIntegrationFields(
						$formId,
						$formFields,
						$type,
						self::SETTINGS_ACTIVE_CAMPAIGN_INTEGRATION_FIELDS_KEY,
					),
					$this->getOutputConditionalTags(
						$formId,
						$formFields,
						self::SETTINGS_ACTIVE_CAMPAIGN_CONDITIONAL_TAGS_KEY
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
				self::SETTINGS_ACTIVE_CAMPAIGN_LIST_KEY
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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY, self::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$apiKey = Variables::getApiKeyActiveCampaign();
		$apiUrl = Variables::getApiUrlActiveCampaign();

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
								'inputName' => $this->getSettingsName(self::SETTINGS_ACTIVE_CAMPAIGN_API_URL_KEY),
								'inputId' => $this->getSettingsName(self::SETTINGS_ACTIVE_CAMPAIGN_API_URL_KEY),
								'inputFieldLabel' => \__('API url', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiUrl) ? $apiUrl : $this->getOptionValue(self::SETTINGS_ACTIVE_CAMPAIGN_API_URL_KEY),
								'inputIsDisabled' => !empty($apiUrl),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_ACTIVE_CAMPAIGN_API_KEY_KEY),
								'inputId' => $this->getSettingsName(self::SETTINGS_ACTIVE_CAMPAIGN_API_KEY_KEY),
								'inputFieldLabel' => \__('API key', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'password',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_ACTIVE_CAMPAIGN_API_KEY_KEY),
								'inputIsDisabled' => !empty($apiKey),
							]
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsActiveCampaign::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your ActiveCampaign Account.', 'eightshift-forms'),
									\__('Navigate to your Settings page (gear icon in the bottom-left corner).', 'eightshift-forms'),
									\__('Click on <strong>Developer</strong> link.', 'eightshift-forms'),
									\__('Copy the API key and URL into the fields under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
