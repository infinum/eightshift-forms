<?php

/**
 * Enrichment Settings class.
 *
 * @package EightshiftForms\Enrichment
 */

declare(strict_types=1);

namespace EightshiftForms\Enrichment;

use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsEnrichment class.
 */
class SettingsEnrichment implements SettingGlobalInterface, ServiceInterface
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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_ENRICHMENT_USE_KEY, self::SETTINGS_ENRICHMENT_USE_KEY)) {
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
			return $this->getNoActiveFeatureOutput();
		}

		$enrichment = $this->getEnrichmentManualMapFilterValue($this->enrichment->getEnrichmentConfig());

		$expiration = $enrichment['expiration'] ?? '';
		$allowed = $enrichment['data']['original']['allowed'] ?? '';

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Internal storage', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_ENRICHMENT_EXPIRATION_TIME_KEY),
								'inputFieldLabel' => \__('Clear after', 'eightshift-forms'),
								'inputType' => 'number',
								'inputMin' => 0,
								'inputMax' => 100,
								'inputStep' => 1,
								'inputPlaceholder' => Enrichment::ENRICHMENT_EXPIRATION,
								'inputFieldAfterContent' => \__('days', 'eightshift-forms'),
								'inputFieldInlineBeforeAfterContent' => true,
								'inputValue' => $this->getOptionValue(self::SETTINGS_ENRICHMENT_EXPIRATION_TIME_KEY),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Custom parameters', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'textarea',
								'textareaName' => $this->getSettingsName(self::SETTINGS_ENRICHMENT_ALLOWED_TAGS_KEY),
								'textareaFieldLabel' => \__('Custom parameters', 'eightshift-forms'),
								'textareaIsMonospace' => true,
								'textareaSingleSubmit' => true,
								'textareaSaveAsJson' => true,
								// translators: %s will be replaced with local validation patterns.
								'textareaFieldHelp' => \sprintf(\__('
									Enter one URL parameter per line.
									<br/><br />
									Parameters are stored in browser storage for optional additional processing later.<br />
									Some commonly used parameters are included by default.%s', 'eightshift-forms'), $enrichment['settings']),
								'textareaValue' => $this->getOptionValueAsJson(self::SETTINGS_ENRICHMENT_ALLOWED_TAGS_KEY, 1),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Parameter mapping', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('Map the URL parameters to field names. When the form is submitted, the selected fields will be populated by the chosen URL parameters.<br /><br />You can map to multiple fields by separating their names with a comma.', 'eightshift-forms'),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
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
										'inputName' => $this->getSettingsName(self::SETTINGS_ENRICHMENT_ALLOWED_TAGS_MAP_KEY . '-' . $item),
										'inputFieldLabel' => $item,
										'inputValue' => $this->getOptionValue(self::SETTINGS_ENRICHMENT_ALLOWED_TAGS_MAP_KEY . '-' . $item),
										'inputFieldIsFiftyFiftyHorizontal' => true,
										'inputFieldBeforeContent' => '&rarr;',
									];
								},
								$allowed
							),
						],
					],
				]
			],
		];
	}
}
