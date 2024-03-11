<?php

/**
 * Shortcode class - link.
 *
 * @package EightshiftForms\Shortcode
 */

declare(strict_types=1);

namespace EightshiftForms\Shortcode;

use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Link class.
 */
class Link implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_shortcode('esFormsLink', [$this, 'callback']);
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
				'url' => '',
				'label' => '',
			],
			$atts
		);

		$url = isset($params['url']) ? \esc_url($params['url']) : '';
		$label = isset($params['label']) ? \esc_html($params['label']) : '';

		if (!$url || !$label) {
			return '';
		}

		return "<a href='{$url}' target='__blank' rel='noopener noreferrer'>{$label}</a>";
	}
}
