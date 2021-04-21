<?php

/**
 * File for modifying allowed tags for kses.
 *
 * @package EightshiftForms\View
 */

declare(strict_types=1);

namespace EightshiftForms\View;

use EightshiftLibs\Services\ServiceInterface;
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
		add_filter('wp_kses_allowed_html', [$this, 'modifyKsesPostTags'], 30, 1);
	}

  /**
   * Modifies allowed tags in wp_kses_post()
   *
   * @param  array $allowedTags Array of allowed tags.
   * @return array
   */
	public function modifyKsesPostTags(array $allowedTags): array
	{
		$allowedTags = array_merge($allowedTags, FormView::extraAllowedTags($allowedTags));
		return $allowedTags;
	}
}
