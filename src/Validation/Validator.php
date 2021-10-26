<?php

/**
 * The class for form validator.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftFormsVendor\EightshiftLibs\Helpers\ObjectHelperTrait;

/**
 * Class Validator
 */
class Validator extends AbstractValidation
{
	use ObjectHelperTrait;

	/**
	 * Instance variable for labels data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Create a new instance.
	 *
	 * @param LabelsInterface $labels Inject documentsData which holds labels data.
	 */
	public function __construct(LabelsInterface $labels)
	{
		$this->labels = $labels;
	}

	/**
	 * Validate form and return error if it is not valid.
	 *
	 * @param array<string, mixed> $params Get params.
	 * @param array<string, mixed> $files Get files.
	 * @param string $formId Form Id.
	 *
	 * @return array<int|string, mixed>
	 */
	public function validate(array $params = [], array $files = [], string $formId = ''): array
	{
		$validationReference = $this->getValidateFields($formId);

		// Merge params and files validations.
		return array_merge(
			$this->validateParams($params, $validationReference, $formId),
			$this->validateFiles($files, $params, $formId)
		);
	}

	/**
	 * Output validation fields for form.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<string, mixed>
	 */
	private function getValidateFields($formId) {
		$output = [];

		$blocks = parse_blocks(get_the_content(null, false, $formId));

		foreach($blocks[0]['innerBlocks'][0]['innerBlocks'] as $block) {
			$name = Components::kebabToCamelCase(explode('/', $block['blockName'])[1]);

			if ($name === 'checkboxes') {
				foreach($block['innerBlocks'] as $inner) {
					$innerOptions = $this->getValidateFieldsInner($inner, 'checkbox');

					if ($innerOptions) {
						$output = array_merge($output, $innerOptions);
					}
				}
			} else {
				$innerOptions = $this->getValidateFieldsInner($block, $name);
			}

			if ($innerOptions) {
				$output = array_merge($output, $innerOptions);
			}
		}

		return $output;
	}

	/**
	 * Validate inner block
	 *
	 * @param array<string, mixed> $block Block inner content.
	 * @param string $name Block name.
	 * 
	 * @return array<string, mixed>
	 */
	private function getValidateFieldsInner($block, $name): array
	{
		$output = [];

		foreach($block['attrs'] as $attributeKey => $attributeValue) {
			switch ($name) {
				case 'senderEmail':
				case 'senderName':
					$attrName = "{$name}Input";
						break;
				default:
					$attrName = $name . ucfirst($name);
						break;
			}

			$valid = array_flip([
				"{$attrName}IsRequired",
				"{$attrName}IsEmail",
				"{$attrName}IsUrl",
				"{$attrName}Accept",
				"{$attrName}MinSize",
				"{$attrName}MaxSize",
			]);

			$id = $block['attrs']["{$attrName}Id"] ?? '';

			if (isset($valid[$attributeKey]) && !empty($id)) {
				$output[$id][lcfirst(str_replace($attrName, '', $attributeKey))] = $attributeValue;
			}
		}

		return $output;
	}

	/**
	 * Validate params.
	 *
	 * @param array<string, mixed> $params Params to check.
	 * @param array<string, mixed> $validationReference Validation reference to check against.
	 * @param string $formId Form Id.
	 *
	 * @return array<string, mixed>
	 */
	private function validateParams(array $params, array $validationReference, string $formId): array
	{
		$output = [];

		foreach ($params as $paramKey => $paramValue) {
			if (is_array($paramValue)) {
				$checked = array_filter($paramValue, function($item) {
					$inputDetails = json_decode($item, true);
					return $inputDetails['checked'];
				});

				if ($checked) {
					$inputValue = 'true';
				}
			} else {
				$inputDetails = json_decode($paramValue, true);

				$inputValue = $inputDetails['value'] ?? '';
			}

			$reference = $validationReference[$paramKey] ?? [];

			if (!$reference) {
				continue;
			}

			foreach ($reference as $dataKey => $dataValue) {
				switch ($dataKey) {
					case 'isRequired':
						error_log( print_r( ( $dataValue ), true ) );
						error_log( print_r( ( $inputValue ), true ) );
						
						if ($dataValue && $inputValue === '') {
							$output[$paramKey] = $this->labels->getLabel('validationRequired', $formId);
						}
						break;
					case 'IsEmail':
						if ($dataValue && !$this->isEmail($inputValue) && $reference['isRequired']) {
							$output[$paramKey] = $this->labels->getLabel('validationEmail', $formId);
						}
						break;
					case 'isUrl':
						if ($dataValue && !$this->isUrl($inputValue) && $reference['isRequired']) {
							$output[$paramKey] = $this->labels->getLabel('validationUrl', $formId);
						}
						break;
				}
			}
		}

		error_log( print_r( ( $output ), true ) );
		

		return $output;
	}

	/**
	 * Validate files.
	 *
	 * @param array<string, mixed> $files Files to check.
	 * @param array<string, mixed> $params Params for reference.
	 * @param string $formId Form Id.
	 *
	 * @return array<int|string, string>
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
						if (!empty($dataValue) && !$this->isFileTypeValid($fileName, $dataValue) && $inputData['validationRequired'] === '1') {
							$output[$inputName] = sprintf($this->labels->getLabel('validationAccept', $formId), $dataValue);
						}
						break;
					case 'validationMinSize':
						if (!empty($dataValue) && !$this->isFileMinSizeValid((int) $fileSize, (int) $dataValue * 1000) && $inputData['validationRequired'] === '1') {
							$output[$inputName] = sprintf($this->labels->getLabel('validationMinSize', $formId), $dataValue);
						}
						break;
					case 'validationMaxSize':
						if (!empty($dataValue) && !$this->isFileMaxSizeValid((int) $fileSize, (int) $dataValue * 1000) && $inputData['validationRequired'] === '1') {
							$output[$inputName] = sprintf($this->labels->getLabel('validationMaxSize', $formId), $dataValue);
						}
						break;
				}
			}
		}

		return $output;
	}
}
