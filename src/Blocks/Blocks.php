<?php

/**
 * Class Blocks is the base class for Gutenberg blocks registration.
 * It provides the ability to register custom blocks using manifest.json.
 *
 * @package EightshiftForms\Blocks
 */

declare(strict_types=1);

namespace EightshiftForms\Blocks;

use EightshiftFormsVendor\EightshiftFormsUtils\Blocks\UtilsBlocks;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use WP_Block_Editor_Context;
use WP_Post;

/**
 * Class Blocks
 */
class Blocks extends UtilsBlocks
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		parent::register();

		// Create new custom category for custom blocks.
		\add_filter('block_categories_all', [$this, 'getCustomCategory'], 10, 2);

		// Limits the usage of only custom project blocks.
		\add_filter('allowed_block_types_all', [$this, 'getAllBlocksList'], 99999, 2);
	}

	/**
	 * Add forms blocks to the list of all blocks.
	 * This hook allows us to override any theme/plugin configurations to allow our blocks to be displayed.
	 *
	 * @param bool|string[] $allowedBlockTypes Doesn't have any influence on what function returns.
	 * @param WP_Block_Editor_Context $blockEditorContext The current block editor context.
	 *
	 * @return bool|string[] The default list of blocks defined in the project.
	 */
	public function getAllBlocksList($allowedBlockTypes, WP_Block_Editor_Context $blockEditorContext)
	{
		if (!$blockEditorContext->post instanceof WP_Post) {
			return $allowedBlockTypes;
		}

		if (empty($blockEditorContext->post->post_type)) {
			return $allowedBlockTypes;
		}

		$settings = Helpers::getSettings()['allowedBlocksList'];

		switch ($blockEditorContext->post->post_type) {
			case UtilsConfig::SLUG_POST_TYPE:
				$out =  \array_values(
					\array_unique(
						\array_merge(
							$settings['formsCpt'],
							$settings['fieldsNoIntegration'],
							$settings['fieldsIntegration'],
							$settings['integrationsNoBuilder'],
							$settings['integrationsBuilder'],
							\apply_filters(UtilsHooksHelper::getFilterName(['blocks', 'additionalBlocks']), [])
						)
					)
				);
				dump('perica');
				return $out;
			case UtilsConfig::SLUG_RESULT_POST_TYPE:
				$out = \array_values(
					\array_unique(
						\array_merge(
							$settings['resultOutputCpt'],
							\is_array($allowedBlockTypes) ? $allowedBlockTypes : [],
							\apply_filters(UtilsHooksHelper::getFilterName(['blocks', 'allowedBlocks']), [])
						)
					)
				);
				dump('želčjko');
				dump($out);
				return $out;
			default:
				$out = \array_values(
					\array_unique(
						\array_merge(
							$settings['noFormsCpt'],
							\is_array($allowedBlockTypes) ? $allowedBlockTypes : [],
							\apply_filters(UtilsHooksHelper::getFilterName(['blocks', 'allowedBlocks']), [])
						)
					)
				);
				dump('miki');
				dump($out);
				return $out;
		}
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
					'slug' => UtilsConfig::BLOCKS_MAIN_CATEGORY_SLUG,
					'title' => \esc_html__('Eightshift Forms', 'eightshift-forms'),
					'icon' => 'admin-settings',
				],
				[
					'slug' => UtilsConfig::BLOCKS_ADDONS_CATEGORY_SLUG,
					'title' => \esc_html__('Eightshift Forms Addons', 'eightshift-forms'),
					'icon' => 'admin-settings',
				],
			]
		);
	}
}
