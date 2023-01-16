<?php

/**
 * The class for form validator.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftFormsVendor\EightshiftLibs\Helpers\ObjectHelperTrait;

/**
 * Class Validator
 */
class Validator extends AbstractValidation
{
	/**
	 * Use Object Helper
	 */
	use ObjectHelperTrait;

	/**
	 * Use general helper trait.
	 */
	// use SettingsHelper;

	/**
	 * Instance variable for labels data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Instance variable of ValidationPatternsInterface data.
	 *
	 * @var ValidationPatternsInterface
	 */
	protected $validationPatterns;

	/**
	 * Validation Fields to check.
	 * If adding a new validation type put it here.
	 *
	 * @var array<int, string>
	 */
	private const VALIDATION_FIELDS = [
		'IsRequired',
		'IsRequiredCount',
		'IsEmail',
		'IsNumber',
		'IsUrl',
		'Accept',
		'MinSize',
		'MaxSize',
		'ValidationPattern',
		'MinLength',
		'MaxLength'
	];

	/**
	 * Validation components to check.
	 *
	 * @var array<int, string>
	 */
	private const VALIDATION_MANUAL_COMPONENTS = [
		'input',
		'textarea',
		'select',
		'checkboxes',
		'radios',
		'file',
	];

	/**
	 * Transient cache name for validatior labels.
	 */
	public const CACHE_VALIDATOR_LABELS_TRANSIENT_NAME = 'es_validator_labels_cache';

	/**
	 * Create a new instance.
	 *
	 * @param LabelsInterface $labels Inject documentsData which holds labels data.
	 * @param ValidationPatternsInterface $validationPatterns Inject ValidationPatternsInterface which holds validation methods.
	 */
	public function __construct(
		LabelsInterface $labels,
		ValidationPatternsInterface $validationPatterns
	) {
		$this->labels = $labels;
		$this->validationPatterns = $validationPatterns;
	}

	/**
	 * Validate form and return error if it is not valid.
	 *
	 * @param array<string, mixed> $validationReference Reference of form data to check by.
	 *
	 * @return array<int|string, mixed>
	 */
	public function validate(array $data): array
	{
		if ($data['type'] === 'settings' || $data['type'] === 'globalSettings') {
			$validationReference = $this->getValidationReferenceManual($data['fieldsOnly']);
			error_log( print_r( ( $validationReference ), true ) );
			
		} else {
			$validationReference = $this->getValidationReference($data['fieldsOnly']);
		}

		return \array_merge(
			$this->validateParams($data['params'], $validationReference, $data['formId']),
			$this->validateFiles($data['files'], $validationReference, $data['formId']),
		);
	}

	/**
	 * Get validation label from cache or db.
	 *
	 * @param string $key Key to get data from.
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	private function getValidationLabel(string $key, string $formId): string
	{
		$output = \get_transient(self::CACHE_VALIDATOR_LABELS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		if (!$output) {
			$output = $this->labels->getValidationLabelsOutput($formId);

			\set_transient(self::CACHE_VALIDATOR_LABELS_TRANSIENT_NAME, $output, 180); // 3 min.
		}

		return $output[$key] ?? '';
	}

	/**
	 * Validate params.
	 *
	 * @param array<int|string, mixed> $params Params to check.
	 * @param array<int|string, mixed> $validationReference Validation reference to check against.
	 * @param string $formId Form Id.
	 *
	 * @return array<int|string, string>
	 */
	private function validateParams(array $params, array $validationReference, string $formId): array
	{
		$output = [];

		// Check params.
		foreach ($params as $paramKey => $paramValue) {
			$inputValue = $paramValue['value'] ?? '';
			$inputType = $paramValue['type'] ?? '';

			// No need to validate hidden fields.
			if ($inputType === 'hidden') {
				continue;
			}

			// Find validation reference by ID.
			$reference = $validationReference[$paramKey] ?? [];

			// Bailout if no validation is required.
			if (!$reference) {
				continue;
			}

			// Loop all validations from the reference.
			foreach ($reference as $dataKey => $dataValue) {
				switch ($dataKey) {
					// Check validation for required params.
					case 'isRequired':
						if ($dataValue && empty($inputValue)) {
							$output[$paramKey] = $this->getValidationLabel('validationRequired', $formId);
						}
						break;
					// Check validation for required count params.
					case 'isRequiredCount':
						if ($dataValue && \count(\explode(", ", $inputValue)) < $dataValue && !empty($inputValue)) {
							$output[$paramKey] = \sprintf($this->getValidationLabel('validationRequiredCount', $formId), $dataValue);
						}
						break;
					// Check validation for email params.
					case 'isEmail':
						if ($dataValue && !$this->isEmail($inputValue) && !empty($inputValue)) {
							$output[$paramKey] = $this->getValidationLabel('validationEmail', $formId);
						}
						break;
					case 'isNumber':
						if ($dataValue && !\is_numeric($inputValue) && !empty($inputValue)) {
							$output[$paramKey] = $this->getValidationLabel('validationNumber', $formId);
						}
						break;
					// Check validation for url params.
					case 'isUrl':
						if ($dataValue && !$this->isUrl($inputValue) && !empty($inputValue)) {
							$output[$paramKey] = $this->getValidationLabel('validationUrl', $formId);
						}
						break;
					// Check validation for min characters length.
					case 'minLength':
						if ($dataValue && $dataValue > \strlen($inputValue)) {
							$output[$paramKey] = \sprintf($this->getValidationLabel('validationMinLength', $formId), $dataValue);
						}
						break;
					// Check validation for max characters length.
					case 'maxLength':
						if ($dataValue && $dataValue < \strlen($inputValue)) {
							$output[$paramKey] = \sprintf($this->getValidationLabel('validationMaxLength', $formId), $dataValue);
						}
						break;
					case 'validationPattern':
						\preg_match("/$dataValue/", $inputValue, $matches, \PREG_OFFSET_CAPTURE, 0);

						$key = $matches[0] ?? '';

						if ($dataValue && (empty($key) || $key[0] !== $inputValue) && !empty($inputValue)) {
							$output[$paramKey] = \sprintf($this->getValidationLabel('validationPattern', $formId), $this->validationPatterns->getValidationPatternOutput($dataValue));
						}
						break;
				}
			}
		}

		return $output;
	}

	/**
	 * Validate files from the validation reference.
	 *
	 * @param array<string, mixed> $files Files to check.
	 * @param array<int|string, mixed> $validationReference Validation reference to check against.
	 * @param string $formId Form Id.
	 *
	 * @return array<int|string, string>
	 */
	private function validateFiles(array $files, array $validationReference, string $formId = ''): array
	{
		$output = [];

		// Check files.
		foreach ($files as $fileKey => $fileValue) {
			// Find validation reference by ID.
			$reference = $validationReference[$fileKey] ?? [];

			// Bailout if no validation is required.
			if (!$reference) {
				continue;
			}

			// Loop all validations from the reference.
			foreach ($reference as $dataKey => $dataValue) {
				// Check validation for accepted file types.
				if ($dataKey === 'accept') {
					$individualFiles = [];
					for ($i = 0; $i < \count($fileValue['name']); $i++) {
						$file = [
							'name' => $fileValue['name'][$i],
							'type' => $fileValue['type'][$i],
							'tmp_name' => $fileValue['tmp_name'][$i],
						];
						$individualFiles[] = $file;
					}

					foreach ($individualFiles as $file) {
						if (!$this->isMimeTypeValid($file)) {
							$output[$fileKey] = \sprintf($this->getValidationLabel('validationAcceptMime', $formId), $dataValue);
						}
					}

					foreach ($fileValue['name'] as $file) {
						if (!empty($dataValue) && !$this->isFileTypeValid($file, $dataValue)) {
							$output[$fileKey] = \sprintf($this->getValidationLabel('validationAccept', $formId), $dataValue);
							continue;
						}
					}
				}

				// Check validation for size min/max.
				foreach ($fileValue['size'] as $fileSize) {
					// Check validation for min size. Calculations are in kB but outputted to MB.
					if ($dataKey === 'minSize') {
						if (!empty($dataValue) && !$this->isFileMinSizeValid((int) $fileSize, (int) $dataValue * 1000)) {
							$output[$fileKey] = \sprintf($this->getValidationLabel('validationMinSize', $formId), $dataValue / 1000);
							continue;
						}
					}

					// Check validation for max size. Calculations are in kB but outputted to MB.
					if ($dataKey === 'maxSize') {
						if (!empty($dataValue) && !$this->isFileMaxSizeValid((int) $fileSize, (int) $dataValue * 1000)) {
							$output[$fileKey] = \sprintf($this->getValidationLabel('validationMaxSize', $formId), $dataValue / 1000);
							continue;
						}
					}
				}
			}
		}

		return $output;
	}

	/**
	 * Output validation reference fields for block form.
	 *
	 * @param array<string, mixed> $blocks Blocks array of data.
	 *
	 * @return array<int|string, array<string, mixed>>
	 */
	private function getValidationReference(array $blocks): array
	{
		$output = [];

		// Loop multiple levels form-selector > form.
		foreach ($blocks as $block) {
			$name = Components::kebabToCamelCase(\explode('/', $block['blockName'])[1]);

			if (!$name) {
				continue;
			}

			$innerOptions = $this->getValidationReferenceInner($block, $name);

			if ($innerOptions) {
				$output = \array_merge($output, $innerOptions);
			}
		}

		return $output;
	}

	/**
	 * Output validation reference inner blocks fields for block form.
	 *
	 * @param array<string, mixed> $block Block inner content.
	 * @param string $name Block name.
	 *
	 * @return array<int|string, array<string, mixed>>
	 */
	private function getValidationReferenceInner($block, $name): array
	{
		$output = [];

		// Append attributes defined in the manifest as defaults.
		if ($name === 'senderEmail') {
			$block['attrs']['senderEmailInputIsRequired'] = true;
			$block['attrs']['senderEmailInputIsEmail'] = true;
		}

		// Check all attributes.
		foreach ($block['attrs'] as $attributeKey => $attributeValue) {
			switch ($name) {
				// If something custom add corrections.
				case 'senderEmail':
					$attrName = "{$name}Input";
					$id = $block['attrs']["{$attrName}Id"] ?? '';
					break;
				case 'customData':
					$type = $block['attrs']['customDataFieldType'] ?? '';
					$attrName = $name . \ucfirst($type);
					$id = $block['attrs']["{$name}Id"] ?? '';
					break;
				default:
					$attrName = $name . \ucfirst($name);
					$id = $block['attrs']["{$attrName}Id"] ?? '';
					break;
			}

			// Get all validation fields with the correct prefix.
			$valid = \array_flip(
				\array_map(
					static function ($item) use ($attrName) {
						return "{$attrName}{$item}";
					},
					self::VALIDATION_FIELDS
				)
			);

			// Output validation items with correct value for the matching ID.
			if (isset($valid[$attributeKey]) && !empty($id)) {
				$output[$id][\lcfirst(\str_replace($attrName, '', $attributeKey))] = $attributeValue;
			}
		}

		return $output;
	}

	/**
	 * Output validation reference fields for manual form settings.
	 *
	 * @param array<int|string, mixed> $blocks Blocks array of data.
	 *
	 * @return array<int|string, array<string, mixed>>
	 */
	private function getValidationReferenceManual(array $blocks): array
	{
		$output = [];
		
		$blocksPrepare = [];
		$allowed = array_flip(self::VALIDATION_MANUAL_COMPONENTS);

		\array_walk_recursive(
			$blocks,
			function ($a) use (&$blocksPrepare, $allowed) {
				error_log( print_r( ( $a ), true ) );
				
				if (isset($allowed)) {
					$blocksPrepare[] = $a;
				}
			}
		);

		// error_log( print_r( ( $blocksPrepare ), true ) );

		// // Loop multiple levels form-selector > form.
		// foreach ($blocks as $block) {
		// 	if (!$block) {
		// 		continue;
		// 	}

		// 	$name = $block['component'];

		// 	if (!$name) {
		// 		continue;
		// 	}

		// 	$innerOptions = $this->getValidationReferenceManualInner($block, $name);

		// 	if ($innerOptions) {
		// 		$output = \array_merge($output, $innerOptions);
		// 	}
		// }

		return $output;
	}

	/**
	 * Output validation reference inner blocks fields for manual form settings.
	 *
	 * @param array<string, mixed> $attributes Component attributes.
	 * @param string $name Component name.
	 *
	 * @return array<int|string, array<string, mixed>>
	 */
	private function getValidationReferenceManualInner($attributes, $name): array
	{
		$output = [];

		// Check all attributes.
		foreach ($attributes as $attributeKey => $attributeValue) {
			// Get all validation fields with the correct prefix.
			$valid = \array_flip(
				\array_map(
					static function ($item) use ($name) {
						return "{$name}{$item}";
					},
					self::VALIDATION_FIELDS
				)
			);

			// Get Block Id.
			$id = $attributes["{$name}Name"] ?? '';

			// error_log( print_r( ( $name ), true ) );
			// error_log( print_r( ( $id ), true ) );
			// error_log( print_r( ( $attributeKey ), true ) );
			// error_log( print_r( ( $attributes ), true ) );
			// error_log( print_r( ( '-------------------------' ), true ) );
			
			

			// Output validation items with correct value for the matching ID.
			if (isset($valid[$attributeKey]) && !empty($id)) {
				$output[$id][\lcfirst(\str_replace($name, '', $attributeKey))] = $attributeValue;
			}
		}

		return $output;
	}
}
