<?php

/**
 * Transfer Settings class.
 *
 * @package EightshiftForms\Transfer
 */

declare(strict_types=1);

namespace EightshiftForms\Transfer;

use EightshiftForms\CustomPostType\Forms;
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
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$manifestForm = Components::getComponent('form');

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Export global settings', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('Export all global settings.', 'eightshift-forms'),
							],
							[
								'component' => 'submit',
								'submitFieldSkip' => true,
								'submitIcon' => 'down',
								'submitValue' => \__('Export global settings', 'eightshift-forms'),
								'submitAttrs' => [
									'data-type' => self::TYPE_EXPORT_GLOBAL_SETTINGS,
								],
								'additionalClass' => $manifestForm['componentTransferJsClass'] . ' es-submit--transfer',
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Export forms', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('Export single or multiple forms with settings', 'eightshift-forms'),
							],
							$this->getFormsList(),
							[
								'component' => 'submit',
								'submitFieldSkip' => true,
								'submitValue' => \__('Export selected forms', 'eightshift-forms'),
								'submitIsDisabled' => true,
								'submitIcon' => 'down',
								'submitAttrs' => [
									'data-type' => self::TYPE_EXPORT_FORMS,
									'data-items' => '',
								],
								'additionalClass' => $manifestForm['componentTransferJsClass'] . ' es-submit--transfer',
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Export all', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('Export all forms and global settings.', 'eightshift-forms'),
							],
							[
								'component' => 'submit',
								'submitFieldSkip' => true,
								'submitValue' => \__('Export all', 'eightshift-forms'),
								'submitIcon' => 'down',
								'submitAttrs' => [
									'data-type' => self::TYPE_EXPORT_ALL,
								],
								'additionalClass' => $manifestForm['componentTransferJsClass'] . ' es-submit--transfer',
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Import', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introIsHighlighted' => true,
								'introIsHighlightedImportant' => true,
								'introSubtitle' => \__('Please backup your database before running any imports. This proces is not reversable.', 'eightshift-forms'),
							],
							[
								'component' => 'intro',
								'introSubtitle' => \__('
									Import all global settings or one of many forms with their settings. <br/>
									<ul>
									<li>Upload of global settings will <strong>override</strong> all global settings set in your project.</li>
									<li>Upload of forms will <strong>not override</strong> existing forms. If forms slug exists in the project, upload process will create a new form entry.</li>
									</ul>', 'eightshift-forms'),
							],
							[
								'component' => 'file',
								'fileIsRequired' => true,
								'fileFieldLabel' =>  \__('Upload json file', 'eightshift-forms'),
								'fileAccept' => 'json',
								'additionalClass' => $manifestForm['componentTransferJsClass'] . '-upload',
							],
							[
								'component' => 'checkboxes',
								'checkboxesName' => 'override',
								'checkboxesFieldLabel' => '',
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxAsToggle' => true,
										'checkboxAsToggleSize' => 'medium',
										'checkboxLabel' => \__('Override existing forms', 'eightshift-forms'),
										'additionalClass' => "{$manifestForm['componentTransferJsClass']}-existing",
									],
								],
							],
							[
								'component' => 'submit',
								'submitFieldSkip' => true,
								'submitValue' => \__('Upload', 'eightshift-forms'),
								'submitIcon' => 'up',
								'submitAttrs' => [
									'data-type' => self::TYPE_IMPORT,
								],
								'additionalClass' => $manifestForm['componentTransferJsClass'] . ' es-submit--transfer',
							],
						],
					],
				]
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
			'posts_per_page' => 10000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
		];

		$theQuery = new WP_Query($args);

		$output = [];

		$isDeveloperMode = $this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);

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
			'checkboxesFieldLabel' => \__('Forms', 'eightshift-forms'),
			'checkboxesContent' => $output,
		];
	}
}
