<?php

/**
 * Shortcode class - Result output Item show form.
 *
 * @package EightshiftForms\Shortcode
 */

declare(strict_types=1);

namespace EightshiftForms\Shortcode;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * ResultOutputItemShowForm class.
 */
class ResultOutputItemShowForm implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_shortcode('esFormsROISF', [$this, 'callback']);
	}

	/**
	 * Shortcode callback
	 *
	 * @param array<string, mixed> $atts Attributes passed to the shortcode.
	 * @param string $content Content inside the shortcode.
	 *
	 * @return string
	 */
	public function callback(array $atts, string $content): string
	{
		$selector =  UtilsHelper::getStateSelector('resultOutputShowForm');

		return "<button href='#' class={$selector}>{$content}</button>";
	}
}
