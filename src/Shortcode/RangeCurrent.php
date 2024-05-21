<?php

/**
 * Shortcode class - input range current.
 *
 * @package EightshiftForms\Shortcode
 */

declare(strict_types=1);

namespace EightshiftForms\Shortcode;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * RangeCurrent class.
 */
class RangeCurrent implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_shortcode('esFormsRangeCurrent', [$this, 'callback']);
	}

	/**
	 * Shortcode callback
	 *
	 * @param array<string, mixed> $atts Attributes passed to the shortcode.
	 *
	 * @return string
	 */
	public function callback(array $atts): string
	{
		$manifest = Helpers::getComponent('input');

		$componentClass = $manifest['componentClass'] ?? '';

		$params = \shortcode_atts(
			[
				'value' => '',
				'prefix' => '',
				'suffix' => '',
			],
			$atts
		);

		$value = isset($params['value']) ? \esc_html($params['value']) : '';
		$prefix = isset($params['prefix']) ? \esc_html($params['prefix']) : '';
		$suffix = isset($params['suffix']) ? \esc_html($params['suffix']) : '';

		$cssSelector = Helpers::selector($componentClass, $componentClass, 'range', 'current');

		$cssJsSelector = UtilsHelper::getStateSelector('inputRangeCurrent');

		return "<span class='{$cssSelector}'>{$prefix}<span class='{$cssJsSelector}'>{$value}</span>{$suffix}</span>";
	}
}
