<?php

/**
 * The Content specific functionality.
 *
 * @package EightshiftForms\Admin
 */

namespace EightshiftForms\Admin;

use EightshiftLibs\Services\ServiceInterface;

/**
 * Class Content
 */
class Content implements ServiceInterface
{

  /**
   * Register all the hooks
   */
	public function register(): void
	{
		add_action('wp_kses_allowed_html', [$this, 'set_custom_wpkses_post_tags'], 10, 2);
	}

  /**
   * Add tags to default wp_kses_post
   *
   * @param  array  $tags    Allowed tags array.
   * @param  string $context Context in which the filter is called.
   * @return array           Modified allowed tags array.
   */
	public function set_custom_wpkses_post_tags($tags, $context)
	{
		$appended_tags = [
			'form' => [
				'action'      => true,
				'method'      => true,
				'target'      => true,
				'id'          => true,
				'class'       => true,
			],
			'input' => [
				'name'        => true,
				'value'       => true,
				'type'        => true,
				'id'          => true,
				'class'       => true,
				'disabled'    => true,
				'checked'     => true,
				'readonly'    => true,
				'placeholder' => true,
			],
			'button' => [
				'name'        => true,
				'value'       => true,
				'type'        => true,
				'id'          => true,
				'class'       => true,
				'disabled'    => true,
			],
			'select' => [
				'name'        => true,
				'id'          => true,
				'class'       => true,
				'disabled'    => true,
			],
			'option' => [
				'value'       => true,
				'class'       => true,
				'selected'    => true,
				'disabled'    => true,
			],
		];

		$tags = array_merge($appended_tags, $tags);

		return $tags;
	}
}
