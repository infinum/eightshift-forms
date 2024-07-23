<?php

/**
 * Shortcode class - Result output Item show form.
 *
 * @package EightshiftForms\Shortcode
 */

declare(strict_types=1);

namespace EightshiftForms\Shortcode;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * ResultOutputItemShowForm class.
 */
class ResultOutputItemShowForm implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_shortcode('esFormsROISF', [$this, 'callback']);
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
		$buttonComponent = '';

		$filterNameComponentNext = UtilsHooksHelper::getFilterName(['block', 'form', 'componentShowForm']);

		if (\has_filter($filterNameComponentNext)) {
			$buttonComponent = \apply_filters($filterNameComponentNext, [
				'value' => $content,
				'jsSelector' => UtilsHelper::getStateSelector('resultOutputShowForm'),
			]);
		}

		return Helpers::render(
			'submit',
			\array_merge(
				Helpers::props('submit', [], [
					'submitButtonComponent' => $buttonComponent,
				]),
			),
			'components',
			true
		);
	}
}
