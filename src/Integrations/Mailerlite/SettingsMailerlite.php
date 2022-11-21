<?php

/**
 * Mailerlite Settings class.
 *
 * @package EightshiftForms\Integrations\Mailerlite
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailerlite;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMailerlite class.
 */
class SettingsMailerlite implements SettingInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_mailerlite';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_mailerlite';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_mailerlite';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'mailerlite';

	/**
	 * Mailerlite Use key.
	 */
	public const SETTINGS_MAILERLITE_USE_KEY = 'mailerlite-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_MAILERLITE_API_KEY_KEY = 'mailerlite-api-key';

	/**
	 * List ID Key.
	 */
	public const SETTINGS_MAILERLITE_LIST_KEY = 'mailerlite-list';

	/**
	 * Integration fields Key.
	 */
	public const SETTINGS_MAILERLITE_INTEGRATION_FIELDS_KEY = 'mailerlite-integration-fields';

	/**
	 * Conditional tags key.
	 */
	public const SETTINGS_MAILERLITE_CONDITIONAL_TAGS_KEY = 'mailerlite-conditional-tags';

	/**
	 * Instance variable for mailerlite data.
	 *
	 * @var ClientInterface
	 */
	protected $mailerliteClient;

	/**
	 * Instance variable for Mailerlite form data.
	 *
	 * @var MapperInterface
	 */
	protected $mailerlite;

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $mailerliteClient Inject Mailerlite which holds Mailerlite connect data.
	 * @param MapperInterface $mailerlite Inject Mailerlite which holds Mailerlite form data.
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(
		ClientInterface $mailerliteClient,
		MapperInterface $mailerlite,
		SettingsFallbackDataInterface $settingsFallback
	) {
		$this->mailerliteClient = $mailerliteClient;
		$this->mailerlite = $mailerlite;
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

		$list = $this->getSettingsValue(SettingsMailerlite::SETTINGS_MAILERLITE_LIST_KEY, $formId);

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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_MAILERLITE_USE_KEY, self::SETTINGS_MAILERLITE_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyMailerlite()) ? Variables::getApiKeyMailerlite() : $this->getOptionValue(self::SETTINGS_MAILERLITE_API_KEY_KEY);

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
		$items = $this->mailerliteClient->getItems(false);

		// Bailout if integration can't fetch data.
		if (!$items) {
			return $this->getNoIntegrationFetchDataOutput($type);
		}

		// Find selected form id.
		$selectedFormId = $this->getSettingsValue(self::SETTINGS_MAILERLITE_LIST_KEY, $formId);

		$output = [];

		// If the user has selected the form id populate additional config.
		if ($selectedFormId) {
			$formFields = $this->mailerlite->getFormFields($formId);

			// Output additonal tabs for config.
			$output = [
				'component' => 'tabs',
				'tabsContent' => [
					$this->getOutputIntegrationFields(
						$formId,
						$formFields,
						$type,
						self::SETTINGS_MAILERLITE_INTEGRATION_FIELDS_KEY,
					),
					$this->getOutputConditionalTags(
						$formId,
						$formFields,
						self::SETTINGS_MAILERLITE_CONDITIONAL_TAGS_KEY
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
				self::SETTINGS_MAILERLITE_LIST_KEY
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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_MAILERLITE_USE_KEY, self::SETTINGS_MAILERLITE_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$apiKey = Variables::getApiKeyMailerlite();

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
								'inputName' => $this->getSettingsName(self::SETTINGS_MAILERLITE_API_KEY_KEY),
								'inputId' => $this->getSettingsName(self::SETTINGS_MAILERLITE_API_KEY_KEY),
								'inputFieldLabel' => \__('API key', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'password',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_MAILERLITE_API_KEY_KEY),
								'inputIsDisabled' => !empty($apiKey),
							],
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsMailerlite::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your MailerLite Account.', 'eightshift-forms'),
									// translators: %s will be replaced with the link.
									\sprintf(\__('Go to <a target="_blank" rel="noopener noreferrer" href="%s">Developer API</a>.', 'eightshift-forms'), 'https://app.mailerlite.com/integrations/api/'),
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
