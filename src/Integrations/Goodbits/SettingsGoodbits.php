<?php

/**
 * Goodbits Settings class.
 *
 * @package EightshiftForms\Integrations\Goodbits
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Goodbits;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingsAll;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGoodbits class.
 */
class SettingsGoodbits implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_goodbits';

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_goodbits';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_goodbits';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_goodbits';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'goodbits';

	/**
	 * Goodbits Use key.
	 */
	public const SETTINGS_GOODBITS_USE_KEY = 'goodbits-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_GOODBITS_API_KEY_KEY = 'goodbits-api-key';

	/**
	 * List ID Key.
	 */
	public const SETTINGS_GOODBITS_LIST_KEY = 'goodbits-list';

	/**
	 * Integration fields Key.
	 */
	public const SETTINGS_GOODBITS_INTEGRATION_FIELDS_KEY = 'goodbits-integration-fields';

	/**
	 * Conditional tags key.
	 */
	public const SETTINGS_GOODBITS_CONDITIONAL_TAGS_KEY = 'goodbits-conditional-tags';

	/**
	 * Instance variable for Goodbits data.
	 *
	 * @var ClientInterface
	 */
	protected $goodbitsClient;

	/**
	 * Instance variable for Goodbits form data.
	 *
	 * @var MapperInterface
	 */
	protected $goodbits;

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $goodbitsClient Inject Goodbits which holds Goodbits connect data.
	 * @param MapperInterface $goodbits Inject Goodbits which holds Goodbits form data.
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(
		ClientInterface $goodbitsClient,
		MapperInterface $goodbits,
		SettingsFallbackDataInterface $settingsFallback
	) {
		$this->goodbitsClient = $goodbitsClient;
		$this->goodbits = $goodbits;
		$this->settingsFallback = $settingsFallback;
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

		$list = $this->getSettingsValue(SettingsGoodbits::SETTINGS_GOODBITS_LIST_KEY, $formId);

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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_GOODBITS_USE_KEY, self::SETTINGS_GOODBITS_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyGoodbits()) ? Variables::getApiKeyGoodbits() : $this->getOptionValue(self::SETTINGS_GOODBITS_API_KEY_KEY);

		if (!$isUsed || empty($apiKey)) {
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
		if(!$this->isCheckboxOptionChecked(self::SETTINGS_GOODBITS_USE_KEY, self::SETTINGS_GOODBITS_USE_KEY)) {
			return [];
		}

		return [
			'label' => \__('Goodbits', 'eightshift-forms'),
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
		$type = self::SETTINGS_TYPE_KEY;

		// Bailout if global config is not valid.
		if (!$this->isSettingsGlobalValid()) {
			return $this->getNoValidGlobalConfigOutput($type);
		}

		// Get forms from the API.
		$items = $this->goodbitsClient->getItems();

		// Bailout if integration can't fetch data.
		if (!$items) {
			return $this->getNoIntegrationFetchDataOutput($type);
		}

		// Find selected form id.
		$selectedFormId = $this->getSettingsValue(self::SETTINGS_GOODBITS_LIST_KEY, $formId);

		$output = [];

		// If the user has selected the list.
		if ($selectedFormId) {
			$formFields = $this->goodbits->getFormFields($formId);

			$output = [
				'component' => 'tabs',
				'tabsContent' => [
					$this->getOutputIntegrationFields(
						$formId,
						$formFields,
						$type,
						self::SETTINGS_GOODBITS_INTEGRATION_FIELDS_KEY,
					),
					$this->getOutputConditionalTags(
						$formId,
						$formFields,
						self::SETTINGS_GOODBITS_CONDITIONAL_TAGS_KEY
					),
				],
			];
		}

		return [
			[
				'component' => 'intro',
				'introIsFirst' => true,
				'introTitle' => \__('Goodbits', 'eightshift-forms'),
				'introSubtitle' => \__('Sends simple e-mails.', 'eightshift-forms'),
			],
			...$this->getOutputFormSelection(
				$formId,
				$items,
				$selectedFormId,
				self::SETTINGS_TYPE_KEY,
				self::SETTINGS_GOODBITS_LIST_KEY
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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_GOODBITS_USE_KEY, self::SETTINGS_GOODBITS_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$apiKey = Variables::getApiKeyGoodbits();

		return [
			[
				'component' => 'intro',
				'introIsFirst' => true,
				'introTitle' => \__('Goodbits', 'eightshift-forms'),
				'introSubtitle' => \__('In these settings, you can change all options regarding Goodbits integration.', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introIsHighlighted' => true,
				'introTitle' => \__('How to get the API key?', 'eightshift-forms'),
				'introTitleSize' => 'small',
				// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'introSubtitle' => \__('<ol>
						<li>Log in to your Goodbits Account.</li>
						<li>Go to <strong>Settings</strong>, then click <strong><a target="_blank" href="https://app.Goodbits.com/integrations/api/">API</a></strong>.</li>
						<li>Copy the API key into the field below or use the global constant</li>
					</ol>', 'eightshift-forms'),
			],
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('API', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_GOODBITS_API_KEY_KEY),
								'inputId' => $this->getSettingsName(self::SETTINGS_GOODBITS_API_KEY_KEY),
								'inputFieldLabel' => \__('API key', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'password',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_GOODBITS_API_KEY_KEY),
								'inputIsDisabled' => !empty($apiKey),
							],
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsGoodbits::SETTINGS_TYPE_KEY),
				],
			],
		];
	}
}
