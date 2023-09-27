<?php

/**
 * Transfer Settings class.
 *
 * @package EightshiftForms\Transfer
 */

declare(strict_types=1);

namespace EightshiftForms\Transfer;

use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;
use WP_Query;

/**
 * SettingsTransfer class.
 */
class SettingsTransfer implements ServiceInterface, SettingGlobalInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_transfer';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_transfer';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'transfer';

	/**
	 * Transfer use key.
	 */
	public const SETTINGS_TRANSFER_USE_KEY = 'transfer-use';

	/**
	 * Type export global settings key.
	 */
	public const TYPE_EXPORT_GLOBAL_SETTINGS = 'export-global-settings';

	/**
	 * Type export forms key.
	 */
	public const TYPE_EXPORT_FORMS = 'export-forms';

	/**
	 * Type export all key.
	 */
	public const TYPE_EXPORT_ALL = 'export-all';

	/**
	 * Type import key.
	 */
	public const TYPE_IMPORT = 'import';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = $this->isOptionCheckboxChecked(self::SETTINGS_TRANSFER_USE_KEY, self::SETTINGS_TRANSFER_USE_KEY);

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
		if (!$this->isOptionCheckboxChecked(self::SETTINGS_TRANSFER_USE_KEY, self::SETTINGS_TRANSFER_USE_KEY)) {
			return $this->getSettingOutputNoActiveFeature();
		}

		$manifestForm = Components::getComponent('form');

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Export', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'layout',
								'layoutType' => 'layout-v-stack',
								'layoutContent' => [
									[
										'component' => 'intro',
										'introTitle' => \__('Export', 'eightshift-forms'),
									],
									[
										'component' => 'card',
										'cardTitle' => \__('Global settings'),
										'cardIcon' => Helper::getProjectIcons('settings'),
										'cardContent' => [
											[
												'component' => 'submit',
												'submitValue' => \__('Export', 'eightshift-forms'),
												'submitVariant' => 'outline',
												'submitAttrs' => [
													AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['migrationType'] => self::TYPE_EXPORT_GLOBAL_SETTINGS,
												],
												'additionalClass' => $manifestForm['componentTransferJsClass'] . ' es-submit--transfer',
											],
										],
									],
									[
										'component' => 'divider',
									],
									[
										'component' => 'card',
										'cardTitle' => \__('Everything'),
										'cardIcon' => Helper::getProjectIcons('allChecked'),
										'cardContent' => [
											[
												'component' => 'submit',
												'submitValue' => \__('Export', 'eightshift-forms'),
												'submitVariant' => 'outline',
												'submitAttrs' => [
													AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['migrationType'] => self::TYPE_EXPORT_ALL,
												],
												'additionalClass' => $manifestForm['componentTransferJsClass'] . ' es-submit--transfer',
											],
										],
									],
									[
										'component' => 'divider',
									],
									[
										'component' => 'card',
										'cardTitle' => \__('Forms', 'eightshift-forms'),
										'cardIcon' => Helper::getProjectIcons('form'),
									],
									$this->getFormsList(),
									[
										'component' => 'card',
										'cardVertical' => true,
										'cardContent' => [
											[
												'component' => 'submit',
												'submitValue' => \__('Export selected', 'eightshift-forms'),
												'submitVariant' => 'outline',
												'submitAttrs' => [
													AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['migrationType'] => self::TYPE_EXPORT_FORMS,
													AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['migrationExportItems'] => '',
												],
												'additionalClass' => $manifestForm['componentTransferJsClass'] . ' es-submit--transfer',
											],
										],
									],
								]
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Import', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'layout',
								'layoutType' => 'layout-v-stack',
								'layoutContent' => [
									[
										'component' => 'intro',
										'introTitle' => \__('Import', 'eightshift-forms'),
										'introSubtitle' => \__('
										<span>Imported global settings will <strong>override</strong> all settings set in your project.</span>
										<span>By default, imported forms will <strong>not override</strong> existing forms. This can be changed with the toggle below. In case slugs are the same, a new form will be created.</span>', 'eightshift-forms'),
									],
									[
										'component' => 'intro',
										'introIsHighlighted' => true,
										'introIsHighlightedImportant' => true,
										'introTitleSize' => 'small',
										'introSubtitle' => \__('Backup your database before running any imports.<br />This process is not reversible.', 'eightshift-forms'),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'checkboxes',
										'checkboxesName' => 'override',
										'checkboxesFieldLabel' => '',
										'checkboxesContent' => [
											[
												'component' => 'checkbox',
												'checkboxValue' => 'override',
												'checkboxAsToggle' => true,
												'checkboxAsToggleSize' => 'medium',
												'checkboxLabel' => \__('Override existing forms', 'eightshift-forms'),
												'additionalClass' => "{$manifestForm['componentTransferJsClass']}-existing",
											],
										],
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'file',
										'fileName' => 'upload',
										'fileIsRequired' => true,
										'fileFieldLabel' => \__('Backup file (JSON)', 'eightshift-forms'),
										'fileAccept' => 'json',
										'additionalClass' => $manifestForm['componentTransferJsClass'] . '-upload',
									],
									[
										'component' => 'card',
										'cardVertical' => 'true',
										'cardContent' => [
											[
												'component' => 'submit',
												'submitValue' => \__('Import JSON', 'eightshift-forms'),
												'submitVariant' => 'outline',
												'submitAttrs' => [
													AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['migrationType'] => self::TYPE_IMPORT,
												],
												'additionalClass' => $manifestForm['componentTransferJsClass'] . ' es-submit--transfer',
											],
										],
									],
								],
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Manual import', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introTitle' => \__('Manual import', 'eightshift-forms'),
								'introSubtitle' => \__('
								<span>Manual import JSON data thay you get from the failed integration.</span>
								<span>Paste the JSON data from you email in the <strong>Import data field</strong>.</span>', 'eightshift-forms'),
							],
							[
								'component' => 'textarea',
								'textareaName' => 'log',
								'textareaFieldLabel' => \__('Import data', 'eightshift-forms'),
								'textareaSize' => 'big',
								'textareaLimitHeight' => true,
								'textareaIsPreventSubmit' => true,
								'additionalClass' => "{$manifestForm['componentManualImportApiJsClass']}-data",
							],
							[
								'component' => 'submit',
								'submitValue' => \__('Manual import', 'eightshift-forms'),
								'submitVariant' => 'outline',
								'additionalClass' => "{$manifestForm['componentManualImportApiJsClass']} es-submit--manual-import-api",
							],
							[
								'component' => 'textarea',
								'textareaName' => 'log',
								'textareaFieldLabel' => \__('Output log', 'eightshift-forms'),
								'textareaSize' => 'big',
								'textareaIsPreventSubmit' => true,
								'textareaLimitHeight' => true,
								'textareaIsReadOnly' => true,
								'additionalClass' => "{$manifestForm['componentManualImportApiJsClass']}-output",
							],
						],
					],
				],
			],

		];
	}

	/**
	 * Get form list.
	 *
	 * @return array<string, mixed>
	 */
	public function getFormsList(): array
	{
		$manifestForm = Components::getComponent('form');

		$args = [
			'post_type' => Forms::POST_TYPE_SLUG,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'posts_per_page' => 5000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
		];

		$theQuery = new WP_Query($args);

		$output = [];

		$isDeveloperMode = $this->isOptionCheckboxChecked(SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);

		while ($theQuery->have_posts()) {
			$theQuery->the_post();

			$id = \get_the_ID();
			$title = \get_the_title();
			$title = $isDeveloperMode ? "{$id} - {$title}" : $title;


			$output[] = [
				'component' => 'checkbox',
				'checkboxLabel' => $title,
				'checkboxValue' => $id,
				'additionalClass' => "{$manifestForm['componentTransferJsClass']}-item",
			];
		}

		\wp_reset_postdata();

		return [
			'component' => 'checkboxes',
			'checkboxesName' => 'form',
			'checkboxesContent' => $output,
			'checkboxesFieldHideLabel' => true,
		];
	}
}
