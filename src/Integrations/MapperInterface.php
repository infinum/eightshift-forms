<?php

/**
 * The class for Mapper interface.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations;

/**
 * Class Mapper
 */
interface MapperInterface
{
	/**
	 * Map Greenhouse form to our components.
	 *
	 * @param array $formAdditionalProps Additional props to pass to form.
	 *
	 * @return string
	 */
	public function getForm(array $formAdditionalProps): string;

	/**
	 * Map Greenhouse fields to our components.
	 *
	 * @param array $data Fields.
	 *
	 * @return array
	 */
	public function getFields(array $data): array;
}
