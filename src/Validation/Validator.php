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
	public function validate(array $params = [], array $files = []): array
	{
		return array_merge(
			$this->validateParams($params), $this->validateFiles($files, $params)
		);
	}

	/**
	 * Validate params.
	 *
	 * @param array $params Params to check.
	 *
	 * @return array
	 */
	private function validateParams(array $params): array
	{
		$output = [];

		foreach($params as $paramKey => $paramValue) {
			$inputDetails = json_decode($paramValue, true);

			$inputData = $inputDetails['data'] ?? [];
			$inputValue = $inputDetails['value'] ?? '';

			foreach($inputData as $dataKey => $dataValue) {
				switch ($dataKey) {
					case 'validationRequired':
						if($dataValue === '1' && $inputValue === '') {
							$output[$paramKey] = esc_html__('This field is required!', 'eightshift-forms');
						}
						break;
				}
			}
		}

		return $output;
	}

	/**
	 * Validate files.
	 *
	 * @param array $files Files to check.
	 * @param array $params Params for reference.
	 *
	 * @return array
	 */
	private function validateFiles(array $files, array $params): array
	{
		$output = [];

		foreach ($files as $fileKey => $fileValue) {
			$input = $params[$fileKey] ?? [];

			if (!$input) {
				continue;
			}

			$fileName = $fileValue['name'] ?? '';
			$fileSize = $fileValue['size'] ?? '';

			$inputDetails = json_decode($input, true);
			$inputData = $inputDetails['data'] ?? [];
			$inputName = $inputDetails['name'] ?? '';

			foreach($inputData as $dataKey => $dataValue) {
				switch ($dataKey) {
					case 'validationAccept':
						if (!empty($dataValue) && !$this->isFileTypeValid($fileName, $dataValue)) {
							$output[$inputName] = sprintf(esc_html__('Your file type is not supported. Please use only %s file type.', 'eightshift-forms'), $dataValue);
						}
						break;
					case 'validationMinSize':
						if (!empty($dataValue) && !$this->isFileMinSizeValid((int) $fileSize, (int) $dataValue * 1000)) {
							$output[$inputName] = sprintf(esc_html__('Your file is smaller than allowed. Minimum file size is %s kb.', 'eightshift-forms'), $dataValue);
						}
						break;
					case 'validationMaxSize':
						if (!empty($dataValue) && !$this->isFileMaxSizeValid((int) $fileSize, (int) $dataValue * 1000)) {
							$output[$inputName] = sprintf(esc_html__('Your file is larget than allowed. Maximum file size is %s kb.', 'eightshift-forms'), $dataValue);
						}
						break;
				}
			}
		}

		return $output;
	}
}
