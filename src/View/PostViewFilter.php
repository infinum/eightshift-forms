<?php

/**
 * File for modifying allowed tags for kses.
 *
 * @package EightshiftForms\View
 */

declare(strict_types=1);

namespace EightshiftForms\View;

use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;
use EightshiftForms\View\FormView;

/**
 * The project config class.
 */
class PostViewFilter implements ServiceInterface
{

  /**
   * Registers class filters / actions.
   *
   * @return void
   */
	public function register(): void
	{
		add_filter('wp_kses_allowed_html', [ $this, 'modify_kses_post_tags' ], 30, 1);
	}

  /**
   * Modifies allowed tags in wp_kses_post()
   *
   * @param  array $allowed_tags Array of allowed tags.
   * @return array
   */
	public function modify_kses_post_tags(array $allowed_tags): array
	{
		$allowed_tags = array_merge($allowed_tags, FormView::extra_allowed_tags($allowed_tags));
		return $allowed_tags;
	}
}
