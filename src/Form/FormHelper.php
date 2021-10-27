<?php

/**
 * Trait that holds all generic helpers used in classes of forms.
 *
 * @package EightshiftLibs\Form
 */

declare(strict_types=1);

namespace EightshiftForms\Form;

/**
 * FormHelper trait.
 */
trait FormHelper
{
	/**
	 * Get one checked radio.
	 *
	 * @param array<string, mixed> $radios Array of radio values.
	 *
	 * @return string
	 */
	public function getRadioFieldCheckedItem(array $radios): string
	{
		// Filter item that has checked value.
		$output = array_filter($radios, function ($item) {
			$inputDetails = json_decode($item, true);
			return $inputDetails['value'] !== '';
		});

		// Output only item that is checked or empty.
		return $output ? reset($output) : '';
	}
}
