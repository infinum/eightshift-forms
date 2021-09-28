<?php

/**
 * The class for form validator.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Labels\LabelsInterface;

/**
 * Class Validator
 */
class Validator extends AbstractValidation
{
	/**
	 * Instance variable of form labels data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Create a new instance.
	 *
	 * @param LabelsInterface $labels Inject documentsData which holds form labels data.
	 */
	public function __construct(LabelsInterface $labels)
	{
		$this->labels = $labels;
	}

	/**
	 * Validate form and return error if it is not valid.
	 *
	 * @param array $params Get params.
	 * @param array $files Get files.
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	public function validate(array $params = [], array $files = [], string $formId = ''): array
	{
		// Merge params and files validations.
		return array_merge(
			$this->validateParams($params, $formId),
			$this->validateFiles($files, $params, $formId)
		);
	}

	/**
	 * Validate params.
	 *
	 * @param array $params Params to check.
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	private function validateParams(array $params, string $formId): array
	{
		$output = [];

		foreach ($params as $paramKey => $paramValue) {
			$inputDetails = json_decode($paramValue, true);

			$inputData = $inputDetails['data'] ?? [];
			$inputValue = $inputDetails['value'] ?? '';

			foreach ($inputData as $dataKey => $dataValue) {
				switch ($dataKey) {
					case 'validationRequired':
						if ($dataValue === '1' && $inputValue === '') {
							$output[$paramKey] = $this->labels->getLabel('validationRequired', $formId);
						}
						break;
					case 'validationEmail':
						if ($dataValue === '1' && !$this->isEmail($inputValue) && $dataKey['validationRequired'] === '1') {
							$output[$paramKey] = $this->labels->getLabel('validationEmail', $formId);
						}
						break;
					case 'validationUrl':
						if ($dataValue === '1' && !$this->isUrl($inputValue) && $dataKey['validationRequired'] === '1') {
							$output[$paramKey] = $this->labels->getLabel('validationUrl', $formId);
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
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	private function validateFiles(array $files, array $params, string $formId = ''): array
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

			foreach ($inputData as $dataKey => $dataValue) {
				switch ($dataKey) {
					case 'validationAccept':
						if (!empty($dataValue) && !$this->isFileTypeValid($fileName, $dataValue) && $dataKey['validationRequired'] === '1') {
							$output[$inputName] = sprintf($this->labels->getLabel('validationAccept', $formId), $dataValue);
						}
						break;
					case 'validationMinSize':
						if (!empty($dataValue) && !$this->isFileMinSizeValid((int) $fileSize, (int) $dataValue * 1000) && $dataKey['validationRequired'] === '1') {
							$output[$inputName] = sprintf($this->labels->getLabel('validationMinSize', $formId), $dataValue);
						}
						break;
					case 'validationMaxSize':
						if (!empty($dataValue) && !$this->isFileMaxSizeValid((int) $fileSize, (int) $dataValue * 1000) && $dataKey['validationRequired'] === '1') {
							$output[$inputName] = sprintf($this->labels->getLabel('validationMaxSize', $formId), $dataValue);
						}
						break;
				}
			}
		}

		return $output;
	}
}
