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
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftFormsVendor\EightshiftLibs\Blocks\AbstractBlocks;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use WP_Block_Editor_Context;
use WP_Post;

/**
 * Class Blocks
 */
class Blocks extends AbstractBlocks
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		// Register all custom blocks.
		\add_filter('es_boilerplate_get_settings', [$this, 'getSettingsOverrides'], 11, 2);
		\add_action('init', [$this, 'registerBlocks'], 11);

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
		// Allow forms to be used correctly.
		if (
			$blockEditorContext->post instanceof WP_Post &&
			!empty($blockEditorContext->post->post_type) &&
			$blockEditorContext->post->post_type === Config::SLUG_POST_TYPE
		) {
			return true;
		}

		if (\is_bool($allowedBlockTypes)) {
			return $allowedBlockTypes;
		}

		// Allow forms blocks.
		foreach (Helpers::getSettings()['allowedBlocksNoneBuilderBlocksList'] as $value) {
			$allowedBlockTypes[] = $value;
		}

		// Merge addon blocks to the list.
		$filterName = HooksHelpers::getFilterName(['blocks', 'allowedBlocks']);
		if (\has_filter($filterName)) {
			$allowedBlockTypes = \array_merge($allowedBlockTypes, \apply_filters($filterName, []));
		}

		return $allowedBlockTypes;
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
					'slug' => Config::BLOCKS_MAIN_CATEGORY_SLUG,
					'title' => \esc_html__('Eightshift Forms', 'eightshift-forms'),
					'icon' => 'admin-settings',
				],
				[
					'slug' => Config::BLOCKS_ADDONS_CATEGORY_SLUG,
					'title' => \esc_html__('Eightshift Forms Addons', 'eightshift-forms'),
					'icon' => 'admin-settings',
				],
			]
		);
	}

	/**
	 * Provide overrides for the settings.
	 *
	 * @param array<mixed> $output Array of settings.
	 * @param string $name Project name.
	 *
	 * @return array<mixed>
	 */
	public function getSettingsOverrides(array $output, string $name): array
	{
		if ($name !== Config::FILTER_PREFIX) {
			return $output;
		}

		if (\is_admin()) {
			return $output;
		}

		if (!$output) {
			return $output;
		}

		// Update media breakpoints from the filter.
		$filterName = HooksHelpers::getFilterName(['blocks', 'mediaBreakpoints']);

		if (!\has_filter($filterName)) {
			return $output;
		}

		$customMediaBreakpoints = \apply_filters($filterName, []);

		if (
			\is_array($customMediaBreakpoints) &&
			isset($customMediaBreakpoints['mobile']) &&
			isset($customMediaBreakpoints['tablet']) &&
			isset($customMediaBreakpoints['desktop']) &&
			isset($customMediaBreakpoints['large'])
		) {
			$output['globalVariables']['breakpoints'] = $customMediaBreakpoints;
		}

		return $output;
	}
}
