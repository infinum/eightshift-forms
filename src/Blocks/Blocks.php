<?php

/**
 * Class Blocks is the base class for Gutenberg blocks registration.
 * It provides the ability to register custom blocks using manifest.json.
 *
 * @package EightshiftForms\Blocks
 */

declare(strict_types=1);

namespace EightshiftForms\Blocks;

use EightshiftForms\Config\Config;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Blocks\AbstractBlocks;

/**
 * Class Blocks
 */
class Blocks extends AbstractBlocks
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Blocks dependency filter name constant.
	 *
	 * @var string
	 */
	public const BLOCKS_DEPENDENCY_FILTER_NAME = 'es_blocks_dependency';

	/**
	 * Blocks unique string filter name constant.
	 *
	 * @var string
	 */
	public const BLOCKS_UNIQUE_STRING_FILTER_NAME = 'es_blocks_unique_string';

	/**
	 * Blocks string to value filter name constant.
	 *
	 * @var string
	 */
	public const BLOCKS_STRING_TO_VALUE_FILTER_NAME = 'es_blocks_string_to_filter';

	/**
	 * Blocks option checkbox is checked name constant.
	 *
	 * @var string
	 */
	public const BLOCKS_OPTION_CHECKBOX_IS_CHECKED_FILTER_NAME = 'es_blocks_options_checkbox_is_checked_filter';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		// Register all custom blocks.
		\add_action('init', [$this, 'getBlocksDataFullRaw'], 10);
		\add_action('init', [$this, 'registerBlocks'], 11);

		// Remove P tags from content.
		remove_filter('the_content', 'wpautop');

		// Create new custom category for custom blocks.
		if (\is_wp_version_compatible('5.8')) {
			\add_filter('block_categories_all', [$this, 'getCustomCategory'], 10, 2);
		} else {
			\add_filter('block_categories', [$this, 'getCustomCategoryOld'], 10, 2);
		}

		// Register blocks internal filter for props helper.
		\add_filter(static::BLOCKS_DEPENDENCY_FILTER_NAME, [$this, 'getBlocksDataFullRawItem']);

		// Blocks string to value filter name constant.
		\add_filter(static::BLOCKS_STRING_TO_VALUE_FILTER_NAME, [$this, 'getStringToValue']);

		// Blocks option checkbox is checked name constant.
		\add_filter(static::BLOCKS_OPTION_CHECKBOX_IS_CHECKED_FILTER_NAME, [$this, 'isCheckboxOptionChecked'], 10, 2);
	}

	/**
	 * Create custom category to assign all custom blocks
	 *
	 * This category will be shown on all blocks list in "Add Block" button.
	 *
	 * @hook block_categories This is a WP 5 - WP 5.7 compatible hook callback. Will not work with WP 5.8!
	 *
	 * @param array<array<string, mixed>> $categories Array of categories for block types.
	 * @param \WP_Post $post Post being loaded.
	 *
	 * @return array<array<string, mixed>> Array of categories for block types.
	 */
	public function getCustomCategoryOld(array $categories, \WP_Post $post): array
	{
		return array_merge(
			$categories,
			[
				[
					'slug' => 'eightshift-forms',
					'title' => \esc_html__('Eightshift Forms', 'eightshift-forms'),
					'icon' => 'admin-settings',
				],
			]
		);
	}

	/**
	 * Create custom category to assign all custom blocks
	 *
	 * This category will be shown on all blocks list in "Add Block" button.
	 *
	 * @hook block_categories_all Available from WP 5.8.
	 *
	 * @param array<array<string, mixed>> $categories Array of categories for block types.
	 * @param \WP_Block_Editor_Context $blockEditorContext The current block editor context.
	 *
	 * @return array<array<string, mixed>> Array of categories for block types.
	 */
	public function getCustomCategory(array $categories, \WP_Block_Editor_Context $blockEditorContext): array
	{
		return array_merge(
			$categories,
			[
				[
					'slug' => 'eightshift-forms',
					'title' => \esc_html__('Eightshift Forms', 'eightshift-forms'),
					'icon' => 'admin-settings',
				],
			]
		);
	}

	/**
	 * Convert string to value.
	 * Remove unecesery spaces, underscores or special chars.
	 *
	 * @param string $string String to convert.
	 *
	 * @return string
	 */
	public function getStringToValue(string $string): string
	{
		$string = strtolower($string);
		$string = str_replace(' ', '-', $string);
		$string = str_replace('_', '-', $string);

		return preg_replace('/[^A-Za-z0-9\-]/', '', $string) ?? '';
	}
}
