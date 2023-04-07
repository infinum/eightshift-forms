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
		\add_filter('wp_kses_allowed_html', [$this, 'setCustomWpksesPostTags'], 10, 2);
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
		return \array_merge(
			$this->setForm(),
			$this->getSvg(),
			$tags
		);
	}

	/**
	 * Add forms additional attributes to allow list.
	 *
	 * @return  array<string, array<string, bool>>
	 */
	private function setForm(): array
	{
		$form = self::FORM;

		$form['input'] = \array_merge(
			$form['input'],
			[
				'data-object-type-id' => true,
			]
		);

		return $form;
	}

	/**
	 * Get all SVG output tags.
	 *
	 * @return array<string, array<string, bool>|true>
	 */
	private function getSvg(): array
	{
		$svg = self::SVG;

		$commonSvgParams = [
			'begin' => true,
			'calcMode' => true,
			'clip-rule' => true,
			'cx' => true,
			'cy' => true,
			'd' => true,
			'dur' => true,
			'fill' => true,
			'fill-opacity' => true,
			'fill-rule' => true,
			'height' => true,
			'keySplines' => true,
			'keyTimes' => true,
			'r' => true,
			'repeatCount' => true,
			'rx' => true,
			'ry' => true,
			'stroke' => true,
			'stroke-dasharray' => true,
			'stroke-linecap' => true,
			'stroke-linejoin' => true,
			'stroke-opacity' => true,
			'stroke-width' => true,
			'transform' => true,
			'values' => true,
			'viewBox' => true,
			'width' => true,
			'xmlns' => true,
			'y' => true,
			'style' => true,
			'class' => true,
		];

		$svg['circle'] = \array_merge(
			$svg['circle'],
			$commonSvgParams,
		);

		$svg['svg'] = \array_merge(
			$svg['svg'],
			$commonSvgParams,
		);

		$svg['path'] = \array_merge(
			$svg['path'],
			$commonSvgParams,
		);

		$svg['ellipse'] = \array_merge(
			$svg['ellipse'],
			$commonSvgParams,
		);

		$svg['g'] = \array_merge(
			$svg['g'],
			$commonSvgParams,
		);

		$svg['rect'] = array_merge($commonSvgParams, [
			'x' => true,
			'y' => true,
			'width' => true,
			'height' => true,
			'rx' => true,
			'path' => true,
			'fill' => true,
			'd' => true,
		]);

		return $svg;
	}
}
