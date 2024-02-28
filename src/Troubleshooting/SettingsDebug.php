<?php

/**
 * Debug Settings class.
 *
 * @package EightshiftForms\Troubleshooting
 */

declare(strict_types=1);

namespace EightshiftForms\Troubleshooting;

use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsDebug class.
 */
class SettingsDebug implements ServiceInterface, UtilsSettingGlobalInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_debug';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_debug';

	/**
	 * Filter settings is debug active key.
	 */
	public const FILTER_SETTINGS_IS_DEBUG_ACTIVE = UtilsConfig::FILTER_SETTINGS_IS_DEBUG_ACTIVE;

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'debug';

	/**
	 * Troubleshooting Use key.
	 */
	public const SETTINGS_DEBUG_USE_KEY = 'debug-use';


	/**
	 * Troubleshooting debugging key.
	 */
	public const SETTINGS_DEBUG_DEBUGGING_KEY = UtilsConfig::SETTINGS_DEBUG_DEBUGGING_KEY;
	public const SETTINGS_DEBUG_SKIP_VALIDATION_KEY = UtilsConfig::SETTINGS_DEBUG_SKIP_VALIDATION_KEY;
	public const SETTINGS_DEBUG_SKIP_RESET_KEY = UtilsConfig::SETTINGS_DEBUG_SKIP_RESET_KEY;
	public const SETTINGS_DEBUG_SKIP_CAPTCHA_KEY = UtilsConfig::SETTINGS_DEBUG_SKIP_CAPTCHA_KEY;
	public const SETTINGS_DEBUG_SKIP_FORMS_SYNC_KEY = UtilsConfig::SETTINGS_DEBUG_SKIP_FORMS_SYNC_KEY;
	public const SETTINGS_DEBUG_SKIP_CACHE_KEY = UtilsConfig::SETTINGS_DEBUG_SKIP_CACHE_KEY;
	public const SETTINGS_DEBUG_DEVELOPER_MODE_KEY = UtilsConfig::SETTINGS_DEBUG_DEVELOPER_MODE_KEY;
	public const SETTINGS_DEBUG_QM_LOG = UtilsConfig::SETTINGS_DEBUG_QM_LOG;
	public const SETTINGS_DEBUG_FORCE_DISABLED_FIELDS = UtilsConfig::SETTINGS_DEBUG_FORCE_DISABLED_FIELDS;

	/**
	 * Troubleshooting debug encryption key.
	 */
	public const SETTINGS_DEBUG_ENCRYPTION = 'debug-encryption';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
		\add_filter(self::FILTER_SETTINGS_IS_DEBUG_ACTIVE, [$this, 'isDebugActive']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_USE_KEY, self::SETTINGS_DEBUG_USE_KEY);

		if (!$isUsed) {
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
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_USE_KEY, self::SETTINGS_DEBUG_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Debug', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('These options can break your forms.<br /> Use with caution!', 'eightshift-forms'),
								'introIsHighlighted' => true,
								'introIsHighlightedImportant' => true,
							],
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_DEBUG_DEBUGGING_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Bypass validation', 'eightshift-forms'),
										'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_VALIDATION_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
										'checkboxValue' => self::SETTINGS_DEBUG_SKIP_VALIDATION_KEY,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
										'checkboxHelp' => \__('Disable form validation and go directly to the integrations form submission. This way, you can debug the validation errors from the integration.', 'eightshift-forms'),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Bypass captcha', 'eightshift-forms'),
										'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_CAPTCHA_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
										'checkboxValue' => self::SETTINGS_DEBUG_SKIP_CAPTCHA_KEY,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
										'checkboxHelp' => \__('Allows sending the form without CAPTCHA validation, with the feature still enabled.', 'eightshift-forms'),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__("Don't clear form after submission", 'eightshift-forms'),
										'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_RESET_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
										'checkboxValue' => self::SETTINGS_DEBUG_SKIP_RESET_KEY,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
										'checkboxHelp' => \__('Disable form reset after successful submission for easier debugging.', 'eightshift-forms'),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Developer mode', 'eightshift-forms'),
										'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
										'checkboxValue' => self::SETTINGS_DEBUG_DEVELOPER_MODE_KEY,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
										'checkboxHelp' => \__('
											Outputs multiple developers options in forms. Available outputs:<br/><br/>
											<ul>
												<li>Every listing will have ID prepended to the label.</li>
												<li>Integration API response will have a `debug` key with all the details. You can check it out using inspector network tab.</li>
												<li>On the frontend, when hovering over a form field a debug tooltip will be shown with some helpful information.</li>
											</ul>', 'eightshift-forms'),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Stop form syncing', 'eightshift-forms'),
										'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_FORMS_SYNC_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
										'checkboxValue' => self::SETTINGS_DEBUG_SKIP_FORMS_SYNC_KEY,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
										'checkboxHelp' => \__('Prevents syncing with integrations when a form is opened in edit mode.', 'eightshift-forms'),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Skip internal cache', 'eightshift-forms'),
										'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_CACHE_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
										'checkboxValue' => self::SETTINGS_DEBUG_SKIP_CACHE_KEY,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
										'checkboxHelp' => \__('Prevents storing integration data to the temporary internal cache to optimize load time and API calls. Turning on this option can cause many API calls in a short time, which may cause a temporary ban from the external integration service. Use with caution.', 'eightshift-forms'),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Output Query Monitor log', 'eightshift-forms'),
										'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_QM_LOG, self::SETTINGS_DEBUG_DEBUGGING_KEY),
										'checkboxValue' => self::SETTINGS_DEBUG_QM_LOG,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
										'checkboxHelp' => \__('You can preview the output logs for internal API responses not handled by JavaScript. To use this feature, the Query Monitor plugin must be installed and active in your project.', 'eightshift-forms'),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Enable disabled fields admin overrides', 'eightshift-forms'),
										'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_FORCE_DISABLED_FIELDS, self::SETTINGS_DEBUG_DEBUGGING_KEY),
										'checkboxValue' => self::SETTINGS_DEBUG_FORCE_DISABLED_FIELDS,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
										'checkboxHelp' => \__('You can use this toggle to turn off all disabled fields in the global settings. This is used to debug API keys that are stored in the global variables.', 'eightshift-forms'),
									],
								]
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Encryption', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'select',
								'selectFieldLabel' => \__('Type', 'eightshift-forms'),
								'selectName' => 'debug-encrypt-type',
								'selectFieldHelp' => \__('Choose if you want to encrypt or decrypt the string.', 'eightshift-forms'),
								'additionalClass' => UtilsHelper::getStateSelectorAdmin('debugEncryptionType'),
								'selectContent' => [
									[
										'component' => 'select-option',
										'selectOptionLabel' => \__('Encrypt', 'eightshift-forms'),
										'selectOptionValue' => 'encrypt',
										'selectOptionIsSelected' => true,
									],
									[
										'component' => 'select-option',
										'selectOptionLabel' => \__('Decrypt', 'eightshift-forms'),
										'selectOptionValue' => 'decrypt',
									],
								],
							],
							[
								'component' => 'textarea',
								'textareaFieldLabel' => \__('String to encrypt or decrypt', 'eightshift-forms'),
								'textareaName' => 'debug-encrypt-data',
								'additionalClass' => UtilsHelper::getStateSelectorAdmin('debugEncryption'),
							],
							[
								'component' => 'textarea',
								'textareaName' => 'debug-encrypt-output',
								'textareaFieldLabel' => \__('Output log', 'eightshift-forms'),
								'textareaSize' => 'big',
								'textareaIsPreventSubmit' => true,
								'textareaLimitHeight' => true,
								'textareaIsReadOnly' => true,
								'additionalClass' => UtilsHelper::getStateSelectorAdmin('debugEncryptionOutput'),
							],
							[
								'component' => 'submit',
								'submitValue' => \__('Test Computed', 'eightshift-forms'),
								'submitVariant' => 'outline',
								'additionalClass' => UtilsHelper::getStateSelectorAdmin('debugEncryptionRun'),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Support', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introTitle' => \__('Diagnostics', 'eightshift-forms'),
								'introSubtitle' => '<p>' . \__("Check these settings to make sure your forms work correctly. For more details check <a href='/wp-admin/site-health.php' target='_blank' rel='noopener noreferrer'>Site Health</a>.", 'eightshift-forms') . '</p>',
							],
							[
								'component' => 'card-inline',
								'cardInlineTitle' => '<a href="https://www.php.net/supported-versions.php" target="_blank" rel="noopener noreferrer">PHP version</a>',
								'cardInlineSubTitle' => 'Version of PHP running on the server.',
								'cardInlineRightContent' => \phpversion(),
							],
							[
								'component' => 'card-inline',
								'cardInlineTitle' => '<a href="https://wordpress.org/documentation/article/wordpress-versions/" target="_blank" rel="noopener noreferrer">WordPress version</a>',
								'cardInlineSubTitle' => 'Version of WordPress running on the server.',
								'cardInlineRightContent' => \get_bloginfo('version'),
							],
							[
								'component' => 'card-inline',
								'cardInlineTitle' => '<a href="https://www.php.net/manual/en/ini.core.php#ini.memory-limit" target="_blank" rel="noopener noreferrer">Memory limit</a>',
								'cardInlineSubTitle' => 'Maximum amount of memory (in bytes) that a script is allowed to allocate.',
								'cardInlineRightContent' => (int)\ini_get('memory_limit') <= 64 ? '<span class="error-text">' . \ini_get('memory_limit') . '</span>' : '<span class="success-text">' . \ini_get('memory_limit') . '</span>',
							],
							[
								'component' => 'card-inline',
								'cardInlineTitle' => '<a href="https://www.php.net/manual/en/info.configuration.php#ini.max-execution-time" target="_blank" rel="noopener noreferrer">Max execution time</a>',
								'cardInlineSubTitle' => 'Maximum time (in seconds) a script is allowed to run.',
								'cardInlineRightContent' => (int)\ini_get('max_execution_time') <= 30 ? '<span class="error-text">' . \ini_get('max_execution_time') . '</span>' : '<span class="success-text">' . \ini_get('max_execution_time') . '</span>',
							],
							[
								'component' => 'card-inline',
								'cardInlineTitle' => '<a href="https://www.php.net/manual/en/info.configuration.php#ini.max-input-vars" target="_blank" rel="noopener noreferrer">Max input vars</a>',
								'cardInlineSubTitle' => 'Maximum number of input variables that can be used in a single function.',
								'cardInlineRightContent' => (int)\ini_get('max_input_vars') <= 50 ? '<span class="error-text">' . \ini_get('max_input_vars') . '</span>' : '<span class="success-text">' . \ini_get('max_input_vars') . '</span>',
							],
							[
								'component' => 'card-inline',
								'cardInlineTitle' => '<a href="https://www.php.net/manual/en/features.file-upload.common-pitfalls.php" target="_blank" rel="noopener noreferrer">Max POST size</a>',
								'cardInlineSubTitle' => 'Maximum size of POST data that is accepted.',
								'cardInlineRightContent' => (int)\ini_get('post_max_size') <= 64 ? '<span class="error-text">' . \ini_get('post_max_size') . '</span>' : '<span class="success-text">' . \ini_get('post_max_size') . '</span>',
							],
							[
								'component' => 'card-inline',
								'cardInlineTitle' => '<a href="https://www.php.net/manual/en/features.file-upload.common-pitfalls.php" target="_blank" rel="noopener noreferrer">Max upload filesize</a>',
								'cardInlineSubTitle' => 'Maximum allowed file size for file uploads.',
								'cardInlineRightContent' => (int)\ini_get('upload_max_filesize') <= 64 ? '<span class="error-text">' . \ini_get('upload_max_filesize') . '</span>' : '<span class="success-text">' . \ini_get('upload_max_filesize') . '</span>',
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Determine if settings global is valid and debug item is active.
	 *
	 * @param string $settingKey Setting key to check.
	 *
	 * @return boolean
	 */
	public function isDebugActive(string $settingKey): bool
	{
		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		return UtilsSettingsHelper::isOptionCheckboxChecked($settingKey, self::SETTINGS_DEBUG_DEBUGGING_KEY);
	}
}
