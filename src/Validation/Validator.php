<?php

/**
 * The class for form validator.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Exception\UnverifiedRequestException;

/**
 * Class Validator
 */
class Validator extends AbstractValidation
{
	/**
	 * Validate form and return error if it is not valid.
	 *
	 * @param array $params Get params.
	 *
	 * @return array
	 */
	public function validate(array $params = []): array
	{
		$output = [];
		
		foreach($params as $value) {
			$value = json_decode($value, true);

			$id = $value['id'] ?? [];
			$data = $value['data'] ?? [];
			$value = $value['value'] ?? '';

			foreach($data as $dataKey => $dataValue) {
				if ($dataKey === 'validationRequired' && $dataValue === '1' && $value === '') {
					$output[$id] = esc_html__('This field is required!', 'eightshift-forms');
				}
			}
		}

		return $output;
	}
}
