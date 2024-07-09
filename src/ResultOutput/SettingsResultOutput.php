<?php

/**
 * ResultOutput settings class.
 *
 * @package EightshiftForms\ResultOutput
 */

declare(strict_types=1);

namespace EightshiftForms\ResultOutput;

use EightshiftForms\CustomPostType\Result;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsResultOutput class.
 */
class SettingsResultOutput implements UtilsSettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_result_output';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_result_output';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_result_output';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_result_output';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'result-output';

	/**
	 * Result output use redirect key.
	 */
	public const SETTINGS_RESULT_OUTPUT_USE_REDIRECT_KEY = 'result-output-use-redirect';

	/**
	 * Result output success redirect url key.
	 */
	public const SETTINGS_RESULT_OUTPUT_SUCCESS_REDIRECT_URL_KEY = 'redirection-success';

	/**
	 * Result output use key.
	 */
	public const SETTINGS_RESULT_OUTPUT_USE_KEY = 'result-output-use';

	/**
	 * Url prefix key.
	 */
	public const SETTINGS_RESULT_OUTPUT_URL_PREFIX_KEY = 'result-output-url-prefix';

	/**
	 * Hide global message on success key.
	 */
	public const SETTINGS_RESULT_OUTPUT_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY = 'hide-global-msg-on-success';


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
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
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

		$isUsed = UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_RESULT_OUTPUT_USE_REDIRECT_KEY, self::SETTINGS_RESULT_OUTPUT_USE_REDIRECT_KEY, $formId);

		if (!$isUsed) {
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
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_RESULT_OUTPUT_USE_KEY, self::SETTINGS_RESULT_OUTPUT_USE_KEY);

		if (!$isUsed) {
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
		$formDetails = UtilsGeneralHelper::getFormDetails($formId);
		$formType = $formDetails[UtilsConfig::FD_TYPE] ?? '';

		$successRedirectUrl = FiltersOuputMock::getSuccessRedirectUrlFilterValue($formType, $formId);

		$isUsed = UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_RESULT_OUTPUT_USE_REDIRECT_KEY, self::SETTINGS_RESULT_OUTPUT_USE_REDIRECT_KEY, $formId);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('After form submission', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_RESULT_OUTPUT_USE_REDIRECT_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use success redirect', 'eightshift-forms'),
										'checkboxIsChecked' => $isUsed,
										'checkboxValue' => self::SETTINGS_RESULT_OUTPUT_USE_REDIRECT_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($isUsed ? [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'input',
									'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_RESULT_OUTPUT_SUCCESS_REDIRECT_URL_KEY),
									'inputFieldLabel' => \__('Redirect to URL', 'eightshift-forms'),
									// translators: %s will be replaced with forms field name and filter output copy.
									'inputFieldHelp' => \sprintf(\__('
										After a successful submission, the user will be redirected to the provided URL and the success message will <b>not</b> be shown.<br /><br />
										If you need to include some of the submitted data, use template tags (e.g. <code>{field-name}</code>).<br />
										<details class="is-filter-applied">
											<summary>Available tags</summary>
											<ul>
												%1$s
											</ul>
	
											<br />
											Tag missing? Make sure its field has a <b>Name</b> set!
										</details>
										%2$s', 'eightshift-forms'), UtilsSettingsOutputHelper::getPartialFormFieldNames($formDetails[UtilsConfig::FD_FIELD_NAMES_TAGS]), $successRedirectUrl['settingsLocal']),
									'inputType' => 'url',
									'inputIsUrl' => true,
									'inputIsDisabled' => $successRedirectUrl['filterUsed'],
									'inputValue' => $successRedirectUrl['dataLocal'],
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => '',
									'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_RESULT_OUTPUT_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Hide global message on success', 'eightshift-forms'),
											'checkboxIsChecked' => UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_RESULT_OUTPUT_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY, self::SETTINGS_RESULT_OUTPUT_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY, $formId),
											'checkboxValue' => self::SETTINGS_RESULT_OUTPUT_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY,
											'checkboxSingleSubmit' => true,
											'checkboxAsToggle' => true,
										]
									]
								],
							] : []),
						],
					],
				],
			],
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_RESULT_OUTPUT_USE_KEY, self::SETTINGS_RESULT_OUTPUT_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Internal storage', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('If you change these options make sure you resave your permalinks under settings > permalinks.', 'eightshift-forms'),
								'introIsHighlighted' => true,
								'introIsHighlightedImportant' => true,
							],
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_RESULT_OUTPUT_URL_PREFIX_KEY),
								'inputFieldLabel' => \__('Global url prefix', 'eightshift-forms'),
								'inputFieldHelp' => \__('Define a global prefix for all the result output urls. If you set this value with "/" your result outputs will not have a prefix but be careful as the created outputs can colide with other pages.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputPlaceholder' => Result::POST_TYPE_URL_SLUG,
								'inputValue' => UtilsSettingsHelper::getOptionValue(self::SETTINGS_RESULT_OUTPUT_URL_PREFIX_KEY),
							],
						],
					],
				],
			],
		];
	}
}
