<?php

/**
 * Shortcode class - input range current.
 *
 * @package EightshiftForms\Shortcode
 */

declare(strict_types=1);

namespace EightshiftForms\Shortcode;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
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
		$params = \shortcode_atts(
			[
				'value' => '',
				'prefix' => '',
				'sufix' => '',
			],
			$atts
		);

		$value = isset($params['value']) ? \esc_html($params['value']) : '';
		$prefix = isset($params['prefix']) ? \esc_html($params['prefix']) : '';
		$sufix = isset($params['sufix']) ? \esc_html($params['sufix']) : '';

		$classSelector = UtilsHelper::getStateSelector('inputRangeCurrent');

		return "<span class='{$classSelector}'>{$prefix}{$value}{$sufix}</span>";
	}
}
