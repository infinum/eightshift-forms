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

		if ($name === '') {
			return '';
		}

		// Check if we are on success redirect page.
		$resultOutputData = $this->getResultOutputSuccessItemPartShortcodeValue($name);

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

	/**
	 * Get result output success item part shortcode value.
	 *
	 * @param string $name Name of the item.
	 *
	 * @return array<string, mixed>
	 */
	private function getResultOutputSuccessItemPartShortcodeValue(string $name): array
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$data = isset($_GET[UtilsHelper::getStateSuccessRedirectUrlKey('data')]) ? \json_decode(\esFormsDecryptor(\sanitize_text_field(\wp_unslash($_GET[UtilsHelper::getStateSuccessRedirectUrlKey('data')]))) ?: '', true) : [];

		if (!$data) {
			return [
				'isRedirectPage' => false,
				'value' => '',
			];
		}

		$variationData = $data[UtilsHelper::getStateSuccessRedirectUrlKey('variation')] ?? [];

		if (!$variationData) {
			return [
				'isRedirectPage' => false,
				'value' => '',
			];
		}

		$output = '';

		foreach ($variationData as $key => $value) {
			if (!$key || !$value) {
				continue;
			}

			if ($name !== $key) {
				continue;
			}

			$output = $value;
			break;
		}

		if (!$output) {
			[
				'isRedirectPage' => true,
				'value' => '',
			];
		}

		return [
			'isRedirectPage' => true,
			'value' => $output,
		];
	}
}
