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
use EightshiftFormsPluginVendor\EightshiftLibs\Blocks\AbstractBlocks;

/**
 * Class Blocks
 */
class Blocks extends AbstractBlocks
{

	/**
	 * Blocks dependency filter name constant.
	 *
	 * @var string
	 */
	public const BLOCKS_DEPENDENCY_FILTER_NAME = 'es_blocks_dependency';

	/**
	 * Blocks id filter name constant.
	 *
	 * @var string
	 */
	public const BLOCKS_NAME_TO_ID_FILTER_NAME = 'es_blocks_name_to_id';

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
		\add_filter('block_categories', [$this, 'getCustomCategoryOld'], 10, 2);

		// Register blocks internal filter for props helper.
		\add_filter(static::BLOCKS_DEPENDENCY_FILTER_NAME, [$this, 'getBlocksDataFullRawItem']);
	
		// Convert string to id string.
		\add_filter(static::BLOCKS_NAME_TO_ID_FILTER_NAME, [$this, 'convertStringToId']);
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
	 * Create custom category to assign all custom blocks
	 *
	 * This category will be shown on all blocks list in "Add Block" button.
	 *
	 * @hook block_categories This is a WP 5 - WP 5.7 compatible hook callback. Will not work with WP 5.8!
	 *
	 * @param array[] $categories Array of categories for block types.
	 * @param \WP_Post $post Post being loaded.
	 *
	 * @return array[] Array of categories for block types.
	 */
	public function getCustomCategoryOld(array $categories, \WP_Post $post): array
	{
		return array_merge(
			$categories,
			[
				[
					'slug' => 'eightshift-forms',
					'title' => \esc_html__('Eightshift Forms', 'eightshift-libs'),
					'icon' => 'admin-settings',
				],
			]
		);
	}

	/**
	 * Convert string to id string.
	 * If string is empty returns a random string.
	 *
	 * @param string $string String to convert
	 *
	 * @return string
	 */
	public function convertStringToId(string $string): string
	{
		if (empty($string)) {
			return bin2hex(random_bytes(10));
		}

		$string = str_replace(' ', '-', $string);
		$string = str_replace('_', '-', $string);

		return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
	}
}
