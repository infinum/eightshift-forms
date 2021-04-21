<?php

/**
 * Class Blocks is the base class for Gutenberg blocks registration.
 * It provides the ability to register custom blocks using manifest.json.
 *
 * @package EightshiftForms\Blocks
 */

declare(strict_types=1);

namespace EightshiftForms\Blocks;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Config\Config;
use EightshiftForms\CustomPostType\Forms;
use EightshiftLibs\Blocks\AbstractBlocks;

/**
 * Class Blocks
 */
class Blocks extends AbstractBlocks implements Filters
{

	/**
	 * Reusable blocks Capability Name.
	 */
	public const REUSABLE_BLOCKS_CAPABILITY = 'edit_reusable_blocks';

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
		\add_filter('block_categories', [$this, 'getCustomCategory'], 10, 2);

		// Register custom theme support options.
		\add_action('after_setup_theme', [$this, 'addThemeSupport'], 25);

		// Register custom project color palette.
		\add_action('after_setup_theme', [$this, 'changeEditorColorPalette'], 11);

		// Register Reusable blocks side menu.
		\add_action('admin_menu', [$this, 'addReusableBlocks']);

		\add_filter('allowed_block_types', [$this, 'getAllAllowedFormBlocks'], 20, 2);
	}

	/**
	 * Limit block on forms post type to internal plugin blocks
	 *
	 * @param bool|array $allowedBlockTypes Array of block type slugs, or boolean to enable/disable all.
	 * @param \WP_Post   $post The post resource data.
	 *
	 * @return array|bool
	 */
	public function getAllAllowedFormBlocks($allowedBlockTypes, $post)
	{
		$projectName = Config::getProjectName();
		if ($post->post_type === Forms::POST_TYPE_SLUG) { /* phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps */
			$formsBlocks = $this->getAllBlocksList([], $post);

			// Remove form from the list to prevent users from adding a new form inside the form.
			if (is_array($formsBlocks)) {
				$formsBlocks = array_flip($formsBlocks);
				unset($formsBlocks["{$projectName}/form"]);
				$formsBlocks = array_values(array_flip($formsBlocks));
			}

			if (has_filter(Filters::ALLOWED_BLOCKS)) {
				return apply_filters(Filters::ALLOWED_BLOCKS, $formsBlocks);
			} else {
				return $formsBlocks;
			}
		}

		// If this filter is the first to run, $allowedBlockTypes will be === true.
		if (is_array($allowedBlockTypes)) {
			$allowedBlockTypes[] = "{$projectName}/forms";
		}

		return $allowedBlockTypes;
	}

	/**
	 * Create custom category to assign all custom blocks
	 *
	 * This category will be shown on all blocks list in "Add Block" button.
	 *
	 * @param array[]  $categories Array of all block categories.
	 * @param \WP_Post $post Post being loaded.
	 *
	 * @return array[] Array of block categories.
	 */
	public function getCustomCategory(array $categories, \WP_Post $post): array
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
	 * Get blocks absolute path
	 *
	 * Prefix path is defined by project config.
	 *
	 * @return string
	 */
	protected function getBlocksPath(): string
	{
		return Config::getProjectPath() . '/src/Blocks';
	}

	/**
	 * Add Reusable Blocks as a part of a sidebar menu.
	 *
	 * @return void
	 */
	public function addReusableBlocks(): void
	{
		\add_menu_page(
			\esc_html__('Blocks', 'eightshift-libs'),
			\esc_html__('Blocks', 'eightshift-libs'),
			self::REUSABLE_BLOCKS_CAPABILITY,
			'edit.php?post_type=wp_block',
			function () {},
			'dashicons-editor-table',
			4
		);
	}
}
