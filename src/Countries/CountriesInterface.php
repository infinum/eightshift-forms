<?php

/**
 * File containing the CountriesInterface interface.
 *
 * @package EightshiftForms\Countries
 */

declare(strict_types=1);

namespace EightshiftForms\Countries;

/**
 * Interface for a Countries data provider.
 */
interface CountriesInterface
{
	/**
	 * Get countries data set depending on the provided filter and default set.
	 *
	 * @param bool $useFullOutput Used to output limited output used for settings and output.
	 *
	 * @return array<string, mixed>
	 */
	public function getCountriesDataSet(bool $useFullOutput = true): array;
}
