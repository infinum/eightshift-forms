<?php

/**
 * Mailchimp Settings class.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\GlobalSettings\SettingsGlobalDataInterface;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMailchimp class.
 */
class SettingsMailchimp implements SettingsDataInterface, SettingsGlobalDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_mailchimp';

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
	 * Create a new instance.
	 *
	 * @param MailchimpClientInterface $mailchimpClient Inject Mailchimp which holds Mailchimp connect data.
	 * @param MapperInterface $mailchimp Inject Mailchimp which holds Mailchimp form data.
	 */
	public function __construct(
		MailchimpClientInterface $mailchimpClient,
		MapperInterface $mailchimp
	) {
		$this->mailchimpClient = $mailchimpClient;
		$this->mailchimp = $mailchimp;
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
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => __('Mailchimp', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => Filters::ALL[self::SETTINGS_TYPE_KEY]['icon'],
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
					'highlightedContentTitle' => __('Some config required', 'eightshift-forms'),
					// translators: %s will be replaced with the global settings url.
					'highlightedContentSubtitle' => sprintf(__('Before using Mailchimp you need to configure it in  <a href="%s">global settings</a>.', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(self::SETTINGS_TYPE_KEY)),
					'highlightedContentIcon' => 'tools',
				],
			];
		}

		$items = $this->mailchimpClient->getItems(false);
		$lastUpdatedTime = $items[ClientInterface::TRANSIENT_STORED_TIME]['title'] ?? '';
		unset($items[ClientInterface::TRANSIENT_STORED_TIME]);

		if (!$items) {
			return [
				[
					'component' => 'highlighted-content',
					'highlightedContentTitle' => __('Something went wrong', 'eightshift-forms'),
					'highlightedContentSubtitle' => __('Data from Mailchimp couldn\'t be fetched. Check the API key.', 'eightshift-forms'),
					'highlightedContentIcon' => 'error',
				],
			];
		}

		$itemOptions = array_map(
			function ($option) use ($formId) {
				return [
					'component' => 'select-option',
					'selectOptionLabel' => $option['title'] ?? '',
					'selectOptionValue' => $option['id'] ?? '',
					'selectOptionIsSelected' => $this->isCheckedSettings($option['id'], self::SETTINGS_MAILCHIMP_LIST_KEY, $formId),
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

		$selectedItem = $this->getSettingsValue(self::SETTINGS_MAILCHIMP_LIST_KEY, $formId);

		$manifestForm = Components::getManifest(dirname(__DIR__, 2) . '/Blocks/components/form');

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Mailchimp', 'eightshift-forms'),
			],
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_KEY),
				'selectId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_KEY),
				'selectFieldLabel' => __('Subscription list', 'eightshift-forms'),
				// translators: %1$s will be replaced with js selector, %2$s will be replaced with the cache type, %3$s will be replaced with latest update time.
				'selectFieldHelp' => sprintf(__('If a list isn\'t showing up or is missing some items, try <a href="#" class="%1$s" data-type="%2$s">clearing the cache</a>. Last updated: %3$s.', 'eightshift-forms'), $manifestForm['componentCacheJsClass'], self::SETTINGS_TYPE_KEY, $lastUpdatedTime),
				'selectOptions' => $itemOptions,
				'selectIsRequired' => true,
				'selectValue' => $selectedItem,
				'selectSingleSubmit' => true,
			],
		];

		// If the user has selected the list.
		if ($selectedItem) {
			$tags = $this->mailchimpClient->getTags($selectedItem);

			$tagsOutput = [
				[
					'component' => 'divider',
				],
				[
					'component' => 'intro',
					'introTitle' => __('Audience tags', 'eightshift-forms'),
					'introTitleSize' => 'medium',
					'introSubtitle' => __('Control which tags wil show up on the frontend and set up how will they look and work.', 'eightshift-forms'),
				],
			];

			if ($tags) {
				$isTagsShowHidden = $this->isCheckedSettings('hidden', self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId);

				$tagsLabelsOverrides = [];

				if (!$isTagsShowHidden) {
					$tagsLabelsOverrides = [
						'component' => 'group',
						'groupHelp' => __('Provide override label that will be displayed on the frontend.', 'eightshift-forms'),
						'groupSaveOneField' => true,
						'groupContent' => array_map(
							function ($tag, $index) use ($formId) {
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
							array_keys($tags)
						),
					];
				}

				$tagsOutput = array_merge(
					$tagsOutput,
					[
						[
							'component' => 'select',
							'selectId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY),
							'selectFieldLabel' => __('Tag visibility', 'eightshift-forms'),
							'selectFieldHelp' => $isTagsShowHidden ? __('Tags you select bellow will be added to you form as a hidden field.', 'eightshift-forms') : __('Tags you select bellow will be displayed in the form.', 'eightshift-forms'),
							'selectValue' => $this->getOptionValue(self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY),
							'selectSingleSubmit' => true,
							'selectOptions' => [
								[
									'component' => 'select-option',
									'selectOptionLabel' => __('Don\'t show tags', 'eightshift-forms'),
									'selectOptionValue' => 'hidden',
									'selectOptionIsSelected' => $isTagsShowHidden,
								],
								[
									'component' => 'select-option',
									'selectOptionLabel' => __('Show as a select menu', 'eightshift-forms'),
									'selectOptionValue' => 'select',
									'selectOptionIsSelected' => $this->isCheckedSettings('select', self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId),
								],
								[
									'component' => 'select-option',
									'selectOptionLabel' => __('Show as checkboxes', 'eightshift-forms'),
									'selectOptionValue' => 'checkboxes',
									'selectOptionIsSelected' => $this->isCheckedSettings('checkboxes', self::SETTINGS_MAILCHIMP_LIST_TAGS_SHOW_KEY, $formId),
								],
							]
						],
						[
							'component' => 'group',
							'groupId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_TAGS_LABELS_KEY),
							'groupLabel' => __('Tags list', 'eightshift-forms'),
							'groupStyle' => 'tags',
							'groupContent' => [
								[
									'component' => 'group',
									'groupName' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_TAGS_KEY),
									'groupHelp' => $isTagsShowHidden ? __('Select tags that will be added to you form as a hidden field. If nothing is selected nothing will be sent.', 'eightshift-forms') : __('Select tags that will be displayed in the form field. If nothing is selected everything will be displayed.', 'eightshift-forms'),
									'groupContent' => [
										[
											'component' => 'checkboxes',
											'checkboxesFieldLabel' => '',
											'checkboxesName' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_TAGS_KEY),
											'checkboxesId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_TAGS_KEY),
											'checkboxesContent' => array_map(
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
								$tagsLabelsOverrides,
							],
						],
					]
				);
			}

			$beforeContent = '';

			$filterName = Filters::getIntegrationFilterName(self::SETTINGS_TYPE_KEY, 'adminFieldsSettings');
			if (has_filter($filterName)) {
				$beforeContent = \apply_filters($filterName, '') ?? '';
			}

			$output = array_merge(
				$output,
				$tagsOutput,
				[
					[
						'component' => 'divider',
					],
					[
						'component' => 'intro',
						'introTitle' => __('Form fields', 'eightshift-forms'),
						'introTitleSize' => 'medium',
						'introSubtitle' => __('Control which fields show up on the frontend, set up how they look and work.', 'eightshift-forms'),
					],
					[
						'component' => 'group',
						'groupId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_INTEGRATION_FIELDS_KEY),
						'groupBeforeContent' => $beforeContent,
						'groupStyle' => 'integration',
						'groupContent' => $this->getIntegrationFieldsDetails(
							self::SETTINGS_MAILCHIMP_INTEGRATION_FIELDS_KEY,
							self::SETTINGS_TYPE_KEY,
							$this->mailchimp->getFormFields($formId),
							$formId,
							[
								Mailchimp::FIELD_MAILCHIMP_TAGS_KEY
							]
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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_MAILCHIMP_USE_KEY, self::SETTINGS_MAILCHIMP_USE_KEY);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Mailchimp', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introTitle' => __('How to get the API key?', 'eightshift-forms'),
				'introTitleSize' => 'small',
				// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'introSubtitle' => __('<ol>
						<li>Log in to your Mailchimp Account.</li>
						<li>Navigate to your user profile image (bottom left corner).</li>
						<li>Click on <strong>Account</strong>.</li>
						<li>Click on <strong>Extras</strong> and <strong>API Keys</strong> in the tabs section.</li>
						<li>Click on the <strong>Create a Key</strong> button.<br/></li>
						<li>Copy the API key into the field below or use the global constant.</li>
					</ol>', 'eightshift-forms'),
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => '',
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_USE_KEY),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Use Mailchimp', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_MAILCHIMP_USE_KEY, self::SETTINGS_MAILCHIMP_USE_KEY),
						'checkboxValue' => self::SETTINGS_MAILCHIMP_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];

		if ($isUsed) {
			$apiKey = Variables::getApiKeyMailchimp();

			$output = array_merge(
				$output,
				[
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_API_KEY_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_API_KEY_KEY),
						'inputFieldLabel' => __('API key', 'eightshift-forms'),
						'inputFieldHelp' => __('Can also be provided via a global variable.', 'eightshift-forms'),
						'inputType' => 'password',
						'inputIsRequired' => true,
						'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_MAILCHIMP_API_KEY_KEY),
						'inputIsDisabled' => !empty($apiKey),
					]
				]
			);
		}

		return $output;
	}
}
