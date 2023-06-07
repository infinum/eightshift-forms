<?php

/**
 * Class Blocks is the base class for Gutenberg blocks registration.
 * It provides the ability to register custom blocks using manifest.json.
 *
 * @package EightshiftForms\Blocks
 */

declare(strict_types=1);

namespace EightshiftForms\Blocks;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Blocks\AbstractBlocks;
use WP_Block_Editor_Context;

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
	 * Blocks unique string filter name constant.
	 *
	 * @var string
	 */
	public const BLOCKS_UNIQUE_STRING_FILTER_NAME = 'es_blocks_unique_string';

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
		\remove_filter('the_content', 'wpautop');

		// Create new custom category for custom blocks.
		\add_filter('block_categories_all', [$this, 'getCustomCategory'], 10, 2);
	}

	/**
	 * Create custom category to assign all custom blocks
	 *
	 * This category will be shown on all blocks list in "Add Block" button.
	 *
	 * @hook block_categories_all Available from WP 5.8.
	 *
	 * @param array<array<string, mixed>> $categories Array of categories for block types.
	 * @param WP_Block_Editor_Context $blockEditorContext The current block editor context.
	 *
	 * @return array<array<string, mixed>> Array of categories for block types.
	 */
	public function getCustomCategory(array $categories, WP_Block_Editor_Context $blockEditorContext): array
	{
		return \array_merge(
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
}
