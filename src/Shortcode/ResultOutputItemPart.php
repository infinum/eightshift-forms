<?php

/**
 * Shortcode class - Result output Item part.
 *
 * @package EightshiftForms\Shortcode
 */

declare(strict_types=1);

namespace EightshiftForms\Shortcode;

use EightshiftForms\Helpers\FormsHelper;
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

		if ($name === '') {
			return '';
		}

		// Check if we are on success redirect page.
		$resultOutputData = FormsHelper::getResultOutputSuccessItemPartShortcodeValue($name);

		// Used only on success redirect page.
		if ($resultOutputData['isRedirectPage']) {
			$class = UtilsHelper::getStateSelector('resultOutputPart');
			$outputValue = $resultOutputData['value'] ?? $content;
			return "<span class='{$class}'>{$outputValue}</span>";
		}

		// Used on the same page as the form and changed via JS.
		$attrs = [
			UtilsHelper::getStateAttribute('resultOutputPart') => $name,
			UtilsHelper::getStateAttribute('resultOutputPartDefault') => $content,
			'class' => UtilsHelper::getStateSelector('resultOutputPart'),
		];

		$attrsOutput = '';
		foreach ($attrs as $key => $value) {
			$attrsOutput .= \wp_kses_post(" {$key}='" . $value . "'");
		}

		return "<span {$attrsOutput}>{$content}</span>";
	}
}
