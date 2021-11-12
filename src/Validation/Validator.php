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
use EightshiftForms\Settings\SettingsHelper;
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
	use SettingsHelper;

	/**
	 * Instance variable for labels data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Validation Fields to check.
	 * If adding a new validation type put it here.
	 *
	 * @var array<int, string>
	 */
	private const VALIDATION_FIELDS = [
		"IsRequired",
		"IsRequiredCount",
		"IsEmail",
		"IsUrl",
		"Accept",
		"MinSize",
		"MaxSize",
		"ValidationPattern",
	];

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
	 * @param array<int|string, mixed> $params Get params.
	 * @param array<string, mixed> $files Get files.
	 * @param string $formId Form Id.
	 * @param array<string, mixed> $formData Form data to validate.
	 *
	 * @return array<int|string, mixed>
	 */
	public function validate(array $params = [], array $files = [], string $formId = '', array $formData = []): array
	{
		// If single submit skip all validations.
		if (isset($params['es-form-single-submit'])) {
			return [];
		}

		// Find out forms original data nad check for valition options.
		if ($formData) {
			$validationReference = $this->getValidationReferenceManual($formData);
		} else {
			$blocks = parse_blocks(get_the_content(null, false, (int) $formId));

			$validationReference = $this->getValidationReference($blocks[0]['innerBlocks'][0]['innerBlocks']);
		}

		// Merge params and files validations.
		return array_merge(
			$this->validateParams($params, $validationReference, $formId),
			$this->validateFiles($files, $validationReference, $formId)
		);
	}

	/**
	 * Prepare validation patterns
	 *
	 * @return array<int, array<string, string>>
	 */
	public function getValidationPatterns(): array
	{
		$localPatterns = SettingsValidation::VALIDATION_PATTERNS;

		$userPatterns = preg_split("/\\r\\n|\\r|\\n/", $this->getOptionsValue(SettingsValidation::SETTINGS_VALIDATION_PATTERNS_KEY));

		if ($userPatterns) {
			foreach ($userPatterns as $pattern) {
				$pattern = explode(' : ', $pattern);

				if (empty($pattern) || !isset($pattern[0]) || !isset($pattern[1])) {
						continue;
				};

				$localPatterns[$pattern[0]] = $pattern[1];
			}
		}

		$output = [
			[
				'value' => '',
				'label' => '---'
			]
		];
		foreach ($localPatterns as $key => $value) {
			$output[] = [
				'value' => $value,
				'label' => $key
			];
		};

		return $output;
	}

	/**
	 * Get validation pattern - pattern from name.
	 *
	 * @param string $name Name to serach.
	 *
	 * @return string
	 */
	public function getValidationPattern(string $name): string
	{
		$patterns = array_filter(
			$this->getValidationPatterns(),
			function ($item) use ($name) {
				return $item['label'] === $name;
			}
		);

		if ($patterns) {
			return reset($patterns)['value'] ?? $name;
		}

		return $name;
	}

	/**
	 * Get validation pattern - name from pattern.
	 *
	 * @param string $pattern Pattern to serach.
	 *
	 * @return string
	 */
	public function getValidationPatternName(string $pattern): string
	{
		$patterns = array_filter(
			$this->getValidationPatterns(),
			function ($item) use ($pattern) {
				return $item['value'] === $pattern;
			}
		);

		if ($patterns) {
			return reset($patterns)['label'] ?? $pattern;
		}

		return $pattern;
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
							$output[$paramKey] = $this->labels->getLabel('validationRequired', $formId);
						}
						break;
					// Check validation for required count params.
					case 'isRequiredCount':
						if ($dataValue && count(explode(", ", $inputValue)) < $dataValue && !empty($inputValue)) {
							$output[$paramKey] = sprintf($this->labels->getLabel('validationRequiredCount', $formId), $dataValue);
						}
						break;
					// Check validation for email params.
					case 'isEmail':
						if ($dataValue && !$this->isEmail($inputValue) && !empty($inputValue)) {
							$output[$paramKey] = $this->labels->getLabel('validationEmail', $formId);
						}
						break;
					// Check validation for url params.
					case 'isUrl':
						if ($dataValue && !$this->isUrl($inputValue) && !empty($inputValue)) {
							$output[$paramKey] = $this->labels->getLabel('validationUrl', $formId);
						}
						break;
					case 'validationPattern':
						preg_match("/$dataValue/", $inputValue, $matches, PREG_OFFSET_CAPTURE, 0);

						$key = $matches[0] ?? '';

						if ($dataValue && empty($key) && !empty($inputValue)) {
							$output[$paramKey] = sprintf($this->labels->getLabel('validationPattern', $formId), $this->getValidationPatternName($dataValue));
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
				// Check validation for accept file types.
				if ($dataKey === 'accept') {
					foreach ($fileValue['name'] as $file) {
						if (!empty($dataValue) && !$this->isFileTypeValid($file, $dataValue)) {
							$output[$fileKey] = sprintf($this->labels->getLabel('validationAccept', $formId), $dataValue);
							continue;
						}
					}
				}

				// Check validation for size min/max.
				foreach ($fileValue['size'] as $fileSize) {
					// Check validation for min size.
					if ($dataKey === 'minSize') {
						if (!empty($dataValue) && !$this->isFileMinSizeValid((int) $fileSize, (int) $dataValue * 1000)) {
							$output[$fileKey] = sprintf($this->labels->getLabel('validationMinSize', $formId), $dataValue);
							continue;
						}
					}

					// Check validation for max size.
					if ($dataKey === 'maxSize') {
						if (!empty($dataValue) && !$this->isFileMaxSizeValid((int) $fileSize, (int) $dataValue * 1000)) {
							$output[$fileKey] = sprintf($this->labels->getLabel('validationMaxSize', $formId), $dataValue);
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
			$name = Components::kebabToCamelCase(explode('/', $block['blockName'])[1]);

			if (!$name) {
				continue;
			}

			$innerOptions = $this->getValidationReferenceInner($block, $name);

			if ($innerOptions) {
				$output = array_merge($output, $innerOptions);
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
					break;
				default:
					$attrName = $name . ucfirst($name);
					break;
			}

			// Get all validation fields with the correct prefix.
			$valid = array_flip(
				array_map(
					function ($item) use ($attrName) {
						return "{$attrName}{$item}";
					},
					self::VALIDATION_FIELDS
				)
			);

			// Get Block Id.
			$id = $block['attrs']["{$attrName}Id"] ?? '';

			// Output validation items with correct value for the matching ID.
			if (isset($valid[$attributeKey]) && !empty($id)) {
				$output[$id][lcfirst(str_replace($attrName, '', $attributeKey))] = $attributeValue;
			}
		}

		return $output;
	}

	/**
	 * Output validation reference fields for manual form (integrations, settings).
	 *
	 * @param array<int|string, mixed> $blocks Blocks array of data.
	 *
	 * @return array<int|string, array<string, mixed>>
	 */
	private function getValidationReferenceManual(array $blocks): array
	{
		$output = [];

		// Loop multiple levels form-selector > form.
		foreach ($blocks as $block) {
			if (!$block) {
				continue;
			}

			$name = $block['component'];

			if (!$name) {
				continue;
			}

			$innerOptions = $this->getValidationReferenceManualInner($block, $name);

			if ($innerOptions) {
				$output = array_merge($output, $innerOptions);
			}
		}

		return $output;
	}

	/**
	 * Output validation reference inner blocks fields for manual form (integrations, settings).
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
			$valid = array_flip(
				array_map(
					function ($item) use ($name) {
						return "{$name}{$item}";
					},
					self::VALIDATION_FIELDS
				)
			);

			// Get Block Id.
			$id = $attributes["{$name}Id"] ?? '';

			// Output validation items with correct value for the matching ID.
			if (isset($valid[$attributeKey]) && !empty($id)) {
				$output[$id][lcfirst(str_replace($name, '', $attributeKey))] = $attributeValue;
			}
		}

		return $output;
	}
}
