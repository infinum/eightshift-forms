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
		
		foreach($params as $key => $value) {
			$value = json_decode($value, true);
			error_log( print_r( ( $value ), true ) );

			$id = $value['id'] ?? [];
			$data = $value['data'] ?? [];
			$value = $value['value'] ?? '';
			foreach($data as $dataKey => $dataValue) {

				
				

				// error_log( print_r( ( $dataKey ), true ) );
				// error_log( print_r( ( $dataValue ), true ) );
				// error_log( print_r( ( $value ), true ) );
				// error_log( print_r( ( "----------------" ), true ) );

				switch ($dataKey) {
					case 'validationRequired':
						if($dataValue === '1' && ($value === '' || $value === 'off')) {
							$output[$key] = esc_html__('This field is required!', 'eightshift-forms');
						}
						break;
				}
			}
		}

		return $output;
	}
}
