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
	 * @param array<string, mixed> $formAdditionalProps Additional props to pass to form.
	 *
	 * @return string
	 */
	public function getForm(array $formAdditionalProps): string;

	/**
	 * Map Greenhouse fields to our components.
	 *
	 * @param array<string, mixed> $data Fields.
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFields(array $data, string $formId): array;
}
