<?php

/**
 * Troubleshooting Settings class.
 *
 * @package EightshiftForms\Troubleshooting
 */

declare(strict_types=1);

namespace EightshiftForms\Troubleshooting;

use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Settings\SettingInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsFallback class.
 */
class SettingsFallback implements ServiceInterface, SettingsFallbackDataInterface, SettingInterface, SettingGlobalInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_fallback';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_fallback';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_fallback';

	/**
	 * Filter settings should log activity key.
	 */
	public const FILTER_SETTINGS_SHOULD_LOG_ACTIVITY_NAME = 'es_forms_settings_should_log_activity_fallback';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'fallback';

	/**
	 * Fallback Use key.
	 */
	public const SETTINGS_FALLBACK_USE_KEY = 'fallback-use';

	/**
	 * Fallback Email key.
	 */
	public const SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY = 'fallback-email';

	/**
	 * Fallback Activity Log Use key.
	 */
	public const SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY = 'fallback-activity-log-use';

	/**
	 * Fallback Log Level key.
	 */
	public const SETTINGS_FALLBACK_LOG_LEVEL_KEY = 'fallback-log-level';

	/**
	 * Fallback Auto Delete key.
	 */
	public const SETTINGS_FALLBACK_AUTO_DELETE_KEY = 'fallback-auto-delete';

	/**
	 * Fallback Auto Delete Retention key.
	 */
	public const SETTINGS_FALLBACK_AUTO_DELETE_RETENTION_KEY = 'fallback-auto-delete-retention';

	/**
	 * Fallback Auto Delete Retention Default value.
	 */
	public const SETTINGS_FALLBACK_AUTO_DELETE_RETENTION_DEFAULT_VALUE = 30;

	/**
	 * Fallback Keys key.
	 */
	public const SETTINGS_FALLBACK_FLAGS_KEY = 'fallback-flags';

	/**
	 * Register all the hooks
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, $this->getSettingsData(...));
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, $this->getSettingsGlobalData(...));
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, $this->isSettingsGlobalValid(...));
		\add_filter(self::FILTER_SETTINGS_SHOULD_LOG_ACTIVITY_NAME, $this->shouldLogActivity(...), 10, 2);
	}

	/**
	 * Determine if settings global are valid.
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_FALLBACK_USE_KEY, self::SETTINGS_FALLBACK_USE_KEY);
		$isActivityLogUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY, self::SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY);
		return $isUsed && $isActivityLogUsed;
	}

	/**
	 * Log activity.
	 *
	 * @param bool $isSettingsValid Is settings valid.
	 * @param string $key Key to check.
	 */
	public function shouldLogActivity(bool $isSettingsValid, string $key): bool
	{
		if ($key === '' || $key === '0') {
			return false;
		}

		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		return SettingsHelpers::isOptionCheckboxChecked($key, SettingsFallback::SETTINGS_FALLBACK_FLAGS_KEY);
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
		// Bailout if feature is not active.
		if (!$this->isSettingsGlobalValid()) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'layout',
				'layoutContent' => [
					[
						'component' => 'card-inline',
						'cardInlineTitle' => \__('View all activity logs in database', 'eightshift-forms'),
						'cardInlineRightContent' => [
							[
								'component' => 'button',
								'buttonVariant' => 'primaryGhost',
								'buttonUrl' => GeneralHelpers::getListingPageUrl(Config::SLUG_ADMIN_LISTING_ACTIVITY_LOGS, $formId),
								'buttonLabel' => \__('View', 'eightshift-forms'),
							],
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
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_FALLBACK_USE_KEY, self::SETTINGS_FALLBACK_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$activityLogUse = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY, self::SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY);
		$logLevel = SettingsHelpers::getOptionValue(self::SETTINGS_FALLBACK_LOG_LEVEL_KEY);
		$autoDeleteIsUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_FALLBACK_AUTO_DELETE_KEY, self::SETTINGS_FALLBACK_AUTO_DELETE_KEY);

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('E-mail', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Enable activity log', 'eightshift-forms'),
										'checkboxIsChecked' => $activityLogUse,
										'checkboxValue' => self::SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									],
								],
							],
							...($activityLogUse ? [
								[
									'component' => 'divider',
									'dividerSeparator' => true,
								],
								[
									'component' => 'layout',
									'layoutContent' => [
										[
											'component' => 'card-inline',
											'cardInlineTitle' => \__('View all activity logs in database', 'eightshift-forms'),
											'cardInlineRightContent' => [
												[
													'component' => 'button',
													'buttonVariant' => 'primaryGhost',
													'buttonUrl' => GeneralHelpers::getListingPageUrl(Config::SLUG_ADMIN_LISTING_ACTIVITY_LOGS),
													'buttonLabel' => \__('View all activity logs', 'eightshift-forms'),
												],
											],
										],
									],
								],
								[
									'component' => 'checkboxes',
									'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_FALLBACK_AUTO_DELETE_KEY),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Auto-delete old activity logs', 'eightshift-forms'),
											'checkboxHelp' => \__('Activity logs older than the retention interval will be automatically deleted.', 'eightshift-forms'),
											'checkboxIsChecked' => $autoDeleteIsUsed,
											'checkboxValue' => self::SETTINGS_FALLBACK_AUTO_DELETE_KEY,
											'checkboxSingleSubmit' => true,
											'checkboxAsToggle' => true,
										],
									],
								],
								...($autoDeleteIsUsed ? [
									[
										'component' => 'input',
										'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_FALLBACK_AUTO_DELETE_RETENTION_KEY),
										'inputFieldLabel' => \__('Retention interval', 'eightshift-forms'),
										'inputFieldHelp' => \__('Duration of time in days an activity log should be retained in the database.', 'eightshift-forms'),
										'inputType' => 'number',
										'inputMin' => 1,
										'inputMax' => 365,
										'inputStep' => 1,
										'inputIsNumber' => true,
										'inputPlaceholder' => self::SETTINGS_FALLBACK_AUTO_DELETE_RETENTION_DEFAULT_VALUE,
										'inputFieldAfterContent' => \__('days', 'eightshift-forms'),
										'additionalFieldClass' => 'esf-input-with-suffix',
										'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_FALLBACK_AUTO_DELETE_RETENTION_KEY),
									],
								] : []),
								[
									'component' => 'divider',
									'dividerSeparator' => true,
								],
								[
									'component' => 'input',
									'inputName' => SettingsHelpers::getOptionName(self::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY),
									'inputFieldLabel' => \__('Fallback e-mail', 'eightshift-forms'),
									'inputFieldHelp' => \__('E-mail will be added to the "CC" field; the "From" field will be read from global settings.<br />Use commas to separate multiple e-mails.', 'eightshift-forms'),
									'inputType' => 'text',
									'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY),
								],
								[
									'component' => 'divider',
									'dividerSeparator' => true,
								],
								[
									'component' => 'select',
									'selectName' => SettingsHelpers::getOptionName(self::SETTINGS_FALLBACK_LOG_LEVEL_KEY),
									'selectValue' => $logLevel,
									'selectSingleSubmit' => true,
									'selectIsRequired' => true,
									'selectFieldLabel' => \__('Log level', 'eightshift-forms'),
									'selectFieldHelp' => \__('The log level to use for the activity log.', 'eightshift-forms'),
									'selectContent' => [
										[
											'component' => 'select-option',
											'selectOptionValue' => 'minimal',
											'selectOptionLabel' => \__('Minimal', 'eightshift-forms'),
											'selectOptionIsSelected' => $logLevel === 'minimal',
										],
										[
											'component' => 'select-option',
											'selectOptionValue' => 'default',
											'selectOptionLabel' => \__('Default', 'eightshift-forms'),
											'selectOptionIsSelected' => $logLevel === 'default' || $logLevel === '',
										],
										[
											'component' => 'select-option',
											'selectOptionValue' => 'fullMax',
											'selectOptionLabel' => \__('FULL MAX', 'eightshift-forms'),
											'selectOptionIsSelected' => $logLevel === 'fullMax',
										],
									],
								],
								$this->getFlagsOutput(),
							] : []),
						],
					],
				],
			],
		];
	}

	/**
	 * Output array settings for form.
	 *
	 * @param string $integration Integration name used for fallback.
	 *
	 * @return array<string, array<int, array<string, bool|string>>|string>
	 */
	public function getOutputGlobalFallback(string $integration): array
	{
		return $this->isSettingsGlobalValid() ? [
			'component' => 'tab',
			'tabLabel' => \__('Fallback e-mail', 'eightshift-forms'),
			'tabContent' => [
				[
					'component' => 'intro',
					'introSubtitle' => \__('In case a form submission fails, Eightshift Forms can send a plain-text e-mail with all the submitted data as a fallback. The data can then be used for debugging and manual processing.', 'eightshift-forms'),
				],
				[
					'component' => 'divider',
					'dividerSeparator' => true,
				],
				[
					'component' => 'input',
					'inputName' => SettingsHelpers::getOptionName(self::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY . '-' . $integration),
					'inputFieldLabel' => \__('Fallback e-mail', 'eightshift-forms'),
					'inputFieldHelp' => \__('E-mail will be added to the "CC" field; the "From" field will be read from global settings.<br />Use commas to separate multiple e-mails.', 'eightshift-forms'),
					'inputType' => 'text',
					'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY . '-' . $integration),
				],
			],
		] : [];
	}

	/**
	 * Get flags output.
	 *
	 * @return array<string, mixed>
	 */
	private function getFlagsOutput(): array
	{
		$output = [];
		$outputTypes = [];

		foreach (Labels::getFlagsList() as $key => $value) {
			$desc = $value['description'] ?? '';
			$type = $value['type'] ?? '';
			$isRecommended = $value['isActivityLogRecommended'] ?? false;

			if (!$desc) {
				continue;
			}

			if (!$type) {
				continue;
			}

			if (!SettingsHelpers::isOptionTypeActive($type) && !isset(Labels::EXCLUDE_TYPE_CHECK[$type])) {
				continue;
			}

			if (!\in_array($type, $outputTypes, true)) {
				$output[] = [
					'component' => 'divider',
					'dividerSeparator' => true,
				];

				$output[] = [
					'component' => 'intro',
					'introTitle' => \ucfirst((string) $type),
				];

				$outputTypes[] = $type;
			}

			$output[] = [
				'component' => 'checkbox',
				'checkboxLabel' => $key,
				// translators: %1$s will be replaced with the flag label. %2$s will be replaced with the recommended text.
				'checkboxHelp' => \sprintf(\__('%1$s %2$s', 'eightshift-forms'), $desc, ($isRecommended ? \__('<br/><strong class="esf:text-mauve-600 esf:font-medium">Recommended.</strong>', 'eightshift-forms') : '')),
				'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked($key, self::SETTINGS_FALLBACK_FLAGS_KEY),
				'checkboxValue' => $key,
				'checkboxAsToggle' => true,
			];
		}

		return [
			'component' => 'checkboxes',
			'checkboxesFieldLabel' => '',
			'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_FALLBACK_FLAGS_KEY),
			'checkboxesContent' => $output,
		];
	}
}
