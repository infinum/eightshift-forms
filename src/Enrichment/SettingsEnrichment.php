<?php

/**
 * Enrichment Settings class.
 *
 * @package EightshiftForms\Enrichment
 */

declare(strict_types=1);

namespace EightshiftForms\Enrichment;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsEnrichment class.
 */
class SettingsEnrichment implements UtilsSettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_enrichment';

	/**
	 * Filter settings global is valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_enrichment';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'enrichment';

	/**
	 * Enrichment Use key.
	 */
	public const SETTINGS_ENRICHMENT_USE_KEY = 'enrichment-use';

	/**
	 * Enrichment prefill use key.
	 */
	public const SETTINGS_ENRICHMENT_PREFILL_USE_KEY = 'enrichment-prefill-use';

	/**
	 * Enrichment prefill url use key.
	 */
	public const SETTINGS_ENRICHMENT_PREFILL_URL_USE_KEY = 'enrichment-prefill-url-use';

	/**
	 * Allowed tags key.
	 */
	public const SETTINGS_ENRICHMENT_ALLOWED_TAGS_KEY = 'enrichment-allowed-tags';

	/**
	 * Allowed tags map key.
	 */
	public const SETTINGS_ENRICHMENT_ALLOWED_TAGS_MAP_KEY = 'enrichment-allowed-tags-map';

	/**
	 * Expiration time key.
	 */
	public const SETTINGS_ENRICHMENT_EXPIRATION_TIME_KEY = 'enrichment-expiration-time';

	/**
	 * Expiration prefill time key.
	 */
	public const SETTINGS_ENRICHMENT_PREFILL_EXPIRATION_TIME_KEY = 'enrichment-prefill-expiration-time';


	/**
	 * Instance variable of enrichment data.
	 *
	 * @var EnrichmentInterface
	 */
	protected EnrichmentInterface $enrichment;

	/**
	 * Create a new admin instance.
	 *
	 * @param EnrichmentInterface $enrichment Inject enrichment which holds data about for storing to enrichment.
	 */
	public function __construct(EnrichmentInterface $enrichment)
	{
		$this->enrichment = $enrichment;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_ENRICHMENT_USE_KEY, self::SETTINGS_ENRICHMENT_USE_KEY)) {
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
		if (!$this->isSettingsGlobalValid()) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$enrichment = FiltersOuputMock::getEnrichmentManualMapFilterValue($this->enrichment->getEnrichmentConfig());

		$isUsedPrefill = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_ENRICHMENT_PREFILL_USE_KEY, self::SETTINGS_ENRICHMENT_PREFILL_USE_KEY);
		$isUsedPrefillUrl = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_ENRICHMENT_PREFILL_URL_USE_KEY, self::SETTINGS_ENRICHMENT_PREFILL_URL_USE_KEY);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Enrichment', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_ENRICHMENT_EXPIRATION_TIME_KEY),
								'inputFieldLabel' => \__('Clear enrichment storage after', 'eightshift-forms'),
								'inputFieldHelp' => \__('The amount of time data is stored on the user\'s computer.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputMin' => 0,
								'inputMax' => 100,
								'inputStep' => 1,
								'inputPlaceholder' => Enrichment::ENRICHMENT_EXPIRATION,
								'inputFieldAfterContent' => \__('days', 'eightshift-forms'),
								'inputFieldInlineBeforeAfterContent' => true,
								'inputValue' => UtilsSettingsHelper::getOptionValue(self::SETTINGS_ENRICHMENT_EXPIRATION_TIME_KEY),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'textarea',
								'textareaName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_ENRICHMENT_ALLOWED_TAGS_KEY),
								'textareaFieldLabel' => \__('Add custom enrichment parameters', 'eightshift-forms'),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								// translators: %s will be replaced with local validation patterns.
								'textareaFieldHelp' => \sprintf(\__('
									Enter one URL parameter per line.
									<br/><br />
									Parameters are stored in browser storage for optional additional processing later.<br />
									Some commonly used parameters are included by default.%s', 'eightshift-forms'), $enrichment['settings']),
								'textareaValue' => UtilsSettingsHelper::getOptionValueAsJson(self::SETTINGS_ENRICHMENT_ALLOWED_TAGS_KEY, 1),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'intro',
								'introSubtitle' => \__('Map the URL parameters and cookies to field names. When the form is submitted, the selected fields will be populated by the chosen URL parameters or cookies.<br /><br />You can map to multiple fields by separating their names with a comma.', 'eightshift-forms'),
							],
							[
								'component' => 'field',
								'fieldLabel' => '<b>' . \__('URL parameter', 'eightshift-forms') . '</b>',
								'fieldContent' => '<b>' . \__('Field name', 'eightshift-forms') . '</b>',
								'fieldBeforeContent' => '&emsp;', // "Em space" to pad it out a bit.
								'fieldIsFiftyFiftyHorizontal' => true,
							],
							...\array_map(
								function ($item) {
									return [
										'component' => 'input',
										'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_ENRICHMENT_ALLOWED_TAGS_MAP_KEY . '-' . $item),
										'inputFieldLabel' => $item,
										'inputValue' => UtilsSettingsHelper::getOptionValue(self::SETTINGS_ENRICHMENT_ALLOWED_TAGS_MAP_KEY . '-' . $item),
										'inputFieldIsFiftyFiftyHorizontal' => true,
										'inputFieldBeforeContent' => '&rarr;',
									];
								},
								$enrichment['settingsFields'] ?? []
							),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Prefill from storage', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_ENRICHMENT_PREFILL_USE_KEY),
								'checkboxesFieldHelp' => \__("
									If a user doesn't finish submitting a form, the enrichment prefill feature remembers their inputs in localStorage.
									When they visit the form again, the prefill feature will automatically input the previous data.
									However, if the form is successfully submitted, this data will be erased.
									<br/><br/>
									It is important to note that this feature needs to be disclosed in your privacy policy page.", 'eightshift-forms'),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use enrichment prefill', 'eightshift-forms'),
										'checkboxIsChecked' => $isUsedPrefill,
										'checkboxValue' => self::SETTINGS_ENRICHMENT_PREFILL_USE_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($isUsedPrefill ? [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'input',
									'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_ENRICHMENT_PREFILL_EXPIRATION_TIME_KEY),
									'inputFieldLabel' => \__('Clear form prefill storage after', 'eightshift-forms'),
									'inputFieldHelp' => \__('The amount of time data is stored on the user\'s computer.', 'eightshift-forms'),
									'inputType' => 'number',
									'inputMin' => 0,
									'inputMax' => 100,
									'inputStep' => 1,
									'inputPlaceholder' => Enrichment::ENRICHMENT_PREFILL_EXPIRATION,
									'inputFieldAfterContent' => \__('days', 'eightshift-forms'),
									'inputFieldInlineBeforeAfterContent' => true,
									'inputValue' => UtilsSettingsHelper::getOptionValue(self::SETTINGS_ENRICHMENT_PREFILL_EXPIRATION_TIME_KEY),
								],
							] : []),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Prefill from URL', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_ENRICHMENT_PREFILL_URL_USE_KEY),
								'checkboxesFieldHelp' => \__("Allow all your forms to be prefilled using URL params.", 'eightshift-forms'),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use enrichment prefill from URL', 'eightshift-forms'),
										'checkboxIsChecked' => $isUsedPrefillUrl,
										'checkboxValue' => self::SETTINGS_ENRICHMENT_PREFILL_URL_USE_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
						],
					],
				]
			],
		];
	}
}
