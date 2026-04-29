<?php

/**
 * The EscapedView specific functionality.
 *
 * @package EightshiftForms\View
 */

namespace EightshiftForms\View;

use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;
use EightshiftFormsVendor\EightshiftLibs\View\AbstractEscapedView;

/**
 * Class EscapedView
 */
class EscapedView extends AbstractEscapedView implements ServiceInterface
{
	/**
	 * Register all the hooks.
	 */
	public function register(): void
	{
		\add_filter('wp_kses_allowed_html', [$this, 'setCustomWpksesPostTags'], 99999, 2);
	}

	/**
	 * Add tags to default wp_kses_post.
	 *
	 * @param array<string, array<string, bool>|true> $tags Allowed tags array.
	 * @param string $context Context in which the filter is called.
	 *
	 * @return array<string, array<string, bool>|true> Modified allowed tags array.
	 */
	public function setCustomWpksesPostTags(array $tags, string $context)  // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceAfterLastUsed
	{
		return \array_merge($tags, self::FORM, $this->getSvg());
	}

	/**
	 * Get all SVG output tags.
	 *
	 * wp_kses lowercases all tag and attribute names before lookup (see wp_kses_split2, wp_kses_attr_check),
	 * so every key here must be lowercase — even camelCase SVG names like viewBox or linearGradient.
	 *
	 * @return array<string, array<string, bool>|true>
	 */
	private function getSvg(): array
	{
		static $result = null;

		if ($result !== null) {
			return $result;
		}

		$commonSvgParams = [
			'attributename' => true,
			'begin' => true,
			'calcmode' => true,
			'class' => true,
			'clip-rule' => true,
			'cx' => true,
			'cy' => true,
			'd' => true,
			'dur' => true,
			'fill' => true,
			'fill-opacity' => true,
			'fill-rule' => true,
			'height' => true,
			'id' => true,
			'keysplines' => true,
			'keytimes' => true,
			'opacity' => true,
			'r' => true,
			'repeatcount' => true,
			'rx' => true,
			'ry' => true,
			'stroke' => true,
			'stroke-dasharray' => true,
			'stroke-linecap' => true,
			'stroke-linejoin' => true,
			'stroke-opacity' => true,
			'stroke-width' => true,
			'style' => true,
			'transform' => true,
			'values' => true,
			'viewbox' => true,
			'width' => true,
			'x' => true,
			'xmlns' => true,
			'y' => true,
		];

		$gradientParams = [
			'id' => true,
			'gradienttransform' => true,
			'gradientunits' => true,
		];

		$svg = self::SVG;

		foreach (['circle', 'svg', 'path', 'ellipse', 'g'] as $tag) {
			$svg[$tag] = \array_merge($svg[$tag], $commonSvgParams);
		}

		$svg['rect'] = \array_merge($commonSvgParams, ['path' => true]);
		$svg['animate'] = $commonSvgParams;
		$svg['lineargradient'] = \array_merge($gradientParams, ['x1' => true, 'x2' => true, 'y1' => true, 'y2' => true]);
		$svg['radialgradient'] = \array_merge($gradientParams, ['cx' => true, 'cy' => true, 'r' => true]);
		$svg['stop'] = ['id' => true, 'offset' => true, 'stop-color' => true, 'stop-opacity' => true];

		$result = $svg;
		return $result;
	}
}
