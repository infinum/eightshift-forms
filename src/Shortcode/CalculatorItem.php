<?php

/**
 * Shortcode class - Calculator Item.
 *
 * @package EightshiftForms\Shortcode
 */

declare(strict_types=1);

namespace EightshiftForms\Shortcode;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * CalculatorItem class.
 */
class CalculatorItem implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_shortcode('esFormsCalcItem', [$this, 'callback']);
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
				'id' => '',
			],
			$atts
		);

		$id = isset($params['id']) ? \esc_html($params['id']) : '';

		if (!$id || !$content) {
			return '';
		}

		$classSelector = UtilsHelper::getStateSelector('calculatorOutputItem');

		$attrName = UtilsHelper::getStateAttribute('calculatorItem');

		return "<span class='{$classSelector}' {$attrName}='{$id}'>{$content}</span>";
	}
}
