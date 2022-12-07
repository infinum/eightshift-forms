<?php

/**
 * Enrichment Settings class.
 *
 * @package EightshiftForms\Enrichment
 */

declare(strict_types=1);

namespace EightshiftForms\Enrichment;

use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsEnrichment class.
 */
class SettingsEnrichment implements SettingInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

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
	 * Expiration time key.
	 */
	public const SETTINGS_ENRICHMENT_EXPIRATION_TIME_KEY = 'enrichment-expiration-time';

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
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		return [];
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

		$allowedTags = \implode(\PHP_EOL, Enrichment::ENRICHMENT_DEFAULT_ALLOWED_TAGS);
		$tags = $this->getOptionValue(self::SETTINGS_ENRICHMENT_ALLOWED_TAGS_KEY);

		$tags = \str_replace(' ', \PHP_EOL, $tags);
		$tags = \str_replace(',', \PHP_EOL, $tags);

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Storage', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_ENRICHMENT_EXPIRATION_TIME_KEY),
								'inputId' => $this->getSettingsName(self::SETTINGS_ENRICHMENT_EXPIRATION_TIME_KEY),
								'inputFieldLabel' => \__('Expiration time', 'eightshift-forms'),
								// translators: %s will be replaced with expiration number default.
								'inputFieldHelp' => \sprintf(\__('Set the storage expiration time in days. Default: %s', 'eightshift-forms'), Enrichment::ENRICHMENT_EXPIRATION),
								'inputType' => 'number',
								'inputMin' => 0,
								'inputMax' => 100,
								'inputStep' => 1,
								'inputPlaceholder' => Enrichment::ENRICHMENT_EXPIRATION,
								'inputValue' => $this->getOptionValue(self::SETTINGS_ENRICHMENT_EXPIRATION_TIME_KEY),
							],
							[
								'component' => 'textarea',
								'textareaId' => $this->getSettingsName(self::SETTINGS_ENRICHMENT_ALLOWED_TAGS_KEY),
								'textareaIsMonospace' => true,
								'textareaFieldLabel' => \__('Allowed url parameters', 'eightshift-forms'),
								// translators: %s will be replaced with local validation patterns.
								'textareaFieldHelp' => \sprintf(\__("
									List all URL parameters you want to allow for enrichment. We will store these parameters in browser storage for later processing. <br />
									We provided some defaults, but if you set your parameters, the default ones will not be included in the list, so if you also want to use the default parameters, please have them in your allowed parameters list also. <br />
									Allowed parameters are provided one per line.", 'eightshift-forms')),
								'textareaValue' => $tags,
								'textareaPlaceholder' => $allowedTags,
							],
						],
					],
				]
			],
		];
	}
}
