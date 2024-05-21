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
				'type' => '',
			],
			$atts
		);

		$name = isset($params['name']) ? \esc_html($params['name']) : '';

		if ($name === '') {
			return '';
		}

		$attrs = [
			UtilsHelper::getStateAttribute('resultOutputPart') => $name,
			UtilsHelper::getStateAttribute('resultOutputPartDefault') => $content,
			'class' => UtilsHelper::getStateSelector('resultOutputPart'),
		];

		$type = isset($params['type']) ? \esc_html($params['type']) : '';

		if ($type) {
			$attrs['data-type'] = $type;
		}

		$attrsOutput = '';
		foreach ($attrs as $key => $value) {
			$attrsOutput .= \wp_kses_post(" {$key}='" . $value . "'");
		}

		return "<span {$attrsOutput}>{$content}</span>";
	}
}
