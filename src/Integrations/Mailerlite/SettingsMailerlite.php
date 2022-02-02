<?php

/**
 * Mailerlite Settings class.
 *
 * @package EightshiftForms\Integrations\Mailerlite
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailerlite;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMailerlite class.
 */
class SettingsMailerlite implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_mailerlite';

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
	 * Create a new instance.
	 *
	 * @param ClientInterface $mailerliteClient Inject Mailerlite which holds Mailerlite connect data.
	 * @param MapperInterface $mailerlite Inject Mailerlite which holds Mailerlite form data.
	 */
	public function __construct(
		ClientInterface $mailerliteClient,
		MapperInterface $mailerlite
	) {
		$this->mailerliteClient = $mailerliteClient;
		$this->mailerlite = $mailerlite;
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
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => __('Mailerlite', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg"><g fill="#21C16C" fill-rule="nonzero"><path d="M26.681 3H3.297A3.292 3.292 0 0 0 0 6.297v20.945l4.55-4.506h22.153A3.292 3.292 0 0 0 30 19.44V6.297A3.325 3.325 0 0 0 26.681 3ZM7.011 17.77c0 .483-.396.878-.88.878a.882.882 0 0 1-.878-.879V8.43c0-.484.395-.88.879-.88.483 0 .879.396.879.88v9.34Zm4.088 0c0 .483-.396.878-.88.878a.882.882 0 0 1-.878-.879v-6.176c0-.483.395-.879.879-.879.483 0 .879.396.879.88v6.175Zm.11-8.99a.938.938 0 0 1-.945.945h-.088a.938.938 0 0 1-.945-.945v-.066c0-.527.417-.945.945-.945h.088c.527 0 .945.418.945.945v.066Zm6.198 9.649a2.873 2.873 0 0 1-1.297.285c-1.517 0-2.33-.725-2.33-2.11v-4.241h-.791a.47.47 0 0 1-.484-.462v-.022c0-.154.088-.308.22-.417l1.956-1.913a.682.682 0 0 1 .396-.197c.264 0 .505.22.505.483V10.78h1.407c.44 0 .791.352.791.791 0 .44-.351.792-.791.792h-1.407v4.132c0 .593.308.637.726.637.176 0 .351-.022.505-.066.11-.044.242-.044.352-.066.395 0 .725.33.747.747-.044.286-.242.572-.505.682Zm5.384-1.363a3.854 3.854 0 0 0 1.846-.418.696.696 0 0 1 .352-.088c.44 0 .791.33.791.77v.022a.858.858 0 0 1-.483.725c-.616.352-1.275.66-2.638.66-2.461 0-3.956-1.517-3.956-4.045 0-2.967 1.978-4.044 3.649-4.044 2.505 0 3.648 2 3.648 3.847a.854.854 0 0 1-.813.879H20.505c.242 1.099 1.033 1.692 2.286 1.692Z"/><path d="M22.396 12.209a1.83 1.83 0 0 0-1.869 1.67h3.759a1.866 1.866 0 0 0-1.89-1.67Z"/></g></svg>',
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
					'highlightedContentSubtitle' => sprintf(__('in order to use mailerlite integration please navigate to <a href="%s">global settings</a> and provide the missing configuration data.', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(self::SETTINGS_TYPE_KEY)),
				],
			];
		}

		$items = $this->mailerliteClient->getItems();

		if (!$items) {
			return [
				[
					'component' => 'highlighted-content',
					'highlightedContentTitle' => __('We are sorry but', 'eightshift-forms'),
					'highlightedContentSubtitle' => __('we couldn\'t get the data from the Mailerlite. Please check if your API key is valid.', 'eightshift-forms'),
				],
			];
		}

		$itemOptions = array_map(
			function ($option) use ($formId) {
				return [
					'component' => 'select-option',
					'selectOptionLabel' => $option['title'] ?? '',
					'selectOptionValue' => $option['id'] ?? '',
					'selectOptionIsSelected' => $this->isCheckedSettings($option['id'], self::SETTINGS_MAILERLITE_LIST_KEY, $formId),
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

		$selectedItem = $this->getSettingsValue(self::SETTINGS_MAILERLITE_LIST_KEY, $formId);

		$manifestForm = Components::getManifest(dirname(__DIR__, 2) . '/Blocks/components/form');

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Mailerlite settings', 'eightshift-forms'),
				'introSubtitle' => __('Configure your Mailerlite settings in one place.', 'eightshift-forms'),
			],
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName(self::SETTINGS_MAILERLITE_LIST_KEY),
				'selectId' => $this->getSettingsName(self::SETTINGS_MAILERLITE_LIST_KEY),
				'selectFieldLabel' => __('List', 'eightshift-forms'),
				// translators: %1$s will be replaced with js selector, %2$s will be replaced with the cache type.
				'selectFieldHelp' => sprintf(__('Select list for subscription. If you don\'t see your lists correctly try clearing cache on this <a href="#" class="%1$s" data-type="%2$s">link</a>.', 'eightshift-forms'), $manifestForm['componentCacheJsClass'], self::SETTINGS_TYPE_KEY),
				'selectOptions' => $itemOptions,
				'selectIsRequired' => true,
				'selectValue' => $selectedItem,
				'selectSingleSubmit' => true,
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
						'introSubtitle' => __('Configure your Mailerlite form frontend view in one place.', 'eightshift-forms'),
					],
					[
						'component' => 'group',
						'groupId' => $this->getSettingsName(self::SETTINGS_MAILERLITE_INTEGRATION_FIELDS_KEY),
						'groupContent' => $this->getIntegrationFieldsDetails(
							self::SETTINGS_MAILERLITE_INTEGRATION_FIELDS_KEY,
							self::SETTINGS_TYPE_KEY,
							$this->mailerlite->getFormFields($formId),
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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_MAILERLITE_USE_KEY, self::SETTINGS_MAILERLITE_USE_KEY);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Mailerlite settings', 'eightshift-forms'),
				'introSubtitle' => __('
					Configure your Mailerlite settings in one place. <br />
					To get a Mailerlite API key you must login to your <a target="_blank" href="https://app.mailerlite.com/integrations/api/">Mailerlite Account</a> and copy the provided API Key.', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introTitle' => __('How to get an API key?', 'eightshift-forms'),
				'introTitleSize' => 'medium',
				'introSubtitle' => __('
					1. Login to your Mailerlite Account. <br />
					2. Go to <a target="_blank" href="https://app.mailerlite.com/integrations/api/">Developer API</a>. <br />
					3. Copy the API key to the provided field or use global constant.
				', 'eightshift-forms'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => __('Check options to use', 'eightshift-forms'),
				'checkboxesFieldHelp' => __('Select integrations you want to use in your form.', 'eightshift-forms'),
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_MAILERLITE_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_MAILERLITE_USE_KEY),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Use Mailerlite', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_MAILERLITE_USE_KEY, self::SETTINGS_MAILERLITE_USE_KEY),
						'checkboxValue' => self::SETTINGS_MAILERLITE_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];

		if ($isUsed) {
			$apiKey = Variables::getApiKeyMailerlite();

			$output = array_merge(
				$output,
				[
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_MAILERLITE_API_KEY_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_MAILERLITE_API_KEY_KEY),
						'inputFieldLabel' => __('API Key', 'eightshift-forms'),
						'inputFieldHelp' => __('You can provide API key using global variable also.', 'eightshift-forms'),
						'inputType' => 'password',
						'inputIsRequired' => true,
						'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_MAILERLITE_API_KEY_KEY),
						'inputIsDisabled' => !empty($apiKey),
					],
				]
			);
		}

		return $output;
	}
}
