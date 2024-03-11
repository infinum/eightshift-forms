<?php

/**
 * Shortcode class - Result output Item part.
 *
 * @package EightshiftForms\Shortcode
 */

declare(strict_types=1);

namespace EightshiftForms\Shortcode;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * ResultOutputItemPart class.
 */
class ResultOutputItemPart implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_shortcode('esFormsROIP', [$this, 'callback']);
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
		$params = \shortcode_atts(
			[
				'name' => '',
			],
			$atts
		);

		$name = isset($params['name']) ? \esc_html($params['name']) : '';

		if (!$name || !$content) {
			return '';
		}

		$classSelector = UtilsHelper::getStateSelector('resultOutputPart');

		$attrPartName = UtilsHelper::getStateAttribute('resultOutputPart');
		$attrPartDefaultName = UtilsHelper::getStateAttribute('resultOutputPartDefault');

		return "<span class='{$classSelector}' {$attrPartName}='{$name}' {$attrPartDefaultName}='{$content}'>{$content}</span>";
	}
}
