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
			self::FORM,
			$this->getSvg(),
			$tags
		);
	}

	/**
	 * Get all SVG output tags.
	 *
	 * @return array<string, array<string, bool>|true>
	 */
	private function getSvg(): array
	{
		$svg = self::SVG;

		$svg['circle'] = \array_merge(
			$svg['circle'],
			[
				'stroke-width' => true,
				'fill-opacity' => true,
			]
		);

		$svg['path'] = \array_merge(
			$svg['path'],
			[
				'opacity' => true,
			]
		);

		$svg['ellipse'] = \array_merge(
			$svg['ellipse'],
			[
				'fill-opacity' => true,
			]
		);

		$svg['g'] = \array_merge(
			$svg['g'],
			[
				'stroke-width' => true,
			]
		);

		$svg['rect'] = [
			'x' => true,
			'y' => true,
			'width' => true,
			'height' => true,
			'rx' => true,
			'path' => true,
			'fill' => true,
			'd' => true,
			'stroke' => true,
			'stroke-width' => true,
			'stroke-linecap' => true,
			'stroke-linejoin' => true,
			'stroke-fill' => true,
		];

		return $svg;
	}
}
