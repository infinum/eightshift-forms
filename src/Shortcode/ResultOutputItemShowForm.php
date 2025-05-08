<?php

/**
 * Shortcode class - Result output Item show form.
 *
 * @package EightshiftForms\Shortcode
 */

declare(strict_types=1);

namespace EightshiftForms\Shortcode;

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Helpers\HooksHelpers;
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
		$attrsOutput = [];

		$filterName = HooksHelpers::getFilterName(['block', 'form', 'componentShowForm']);

		if (\has_filter($filterName)) {
			$buttonComponent = \apply_filters($filterName, [
				'value' => $content,
				'jsSelector' => UtilsHelper::getStateSelector('resultOutputShowForm'),
			]);

			$attrsOutput = [
				'submitButtonComponent' => $buttonComponent,
			];
		} else {
			$attrsOutput = [
				'submitValue' => $content,
				'additionalClass' => UtilsHelper::getStateSelector('resultOutputShowForm'),
			];
		}

		return Helpers::render(
			'submit',
			Helpers::props('submit', [], $attrsOutput),
			'components',
			true
		);
	}
}
