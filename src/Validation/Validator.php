<?php

/**
 * The class for form validator.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\Settings\Settings;

/**
 * Class Validator
 */
class Validator extends AbstractValidation
{
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
	 * Transient cache name for validatior labels. No need to flush it because it is short live.
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
		// Manualy build fields from settings components.
		if ($data['type'] === Settings::SETTINGS_TYPE_NAME || $data['type'] === Settings::SETTINGS_GLOBAL_TYPE_NAME) {
			$data['fieldsOnly'] = $this->getValidationReferenceManual($data['fieldsOnly']);
		}

		$validationReference = $this->getValidationReference($data['fieldsOnly']);

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
						if ($dataValue && \count(\explode(AbstractBaseRoute::DELIMITER, $inputValue)) < $dataValue && !empty($inputValue)) {
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
			$innerOptions = $this->getValidationReferenceInner($block);

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
	 *
	 * @return array<int|string, array<string, mixed>>
	 */
	private function getValidationReferenceInner($block): array
	{
		$output = [];

		$blockDetails = Helper::getBlockNameDetails($block['blockName']);

		$name = $blockDetails['name'];
		$namespace = $blockDetails['namespace'];

		if (!$name || !$namespace) {
			return $output;
		}

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
					$id = $block['attrs']["{$attrName}Name"] ?? '';
					break;
				case 'customData':
					$type = $block['attrs']['customDataFieldType'] ?? '';
					$attrName = $name . \ucfirst($type);
					$id = $block['attrs']["{$name}Name"] ?? '';
					break;
				default:
					$attrName = $namespace === 'internal-settings' ? $name : $name . \ucfirst($name);
					$id = $block['attrs']["{$attrName}Name"] ?? '';
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
	 * Flatten all settings components to only relevant ones for the validation.
	 *
	 * @param array<string, mixed> $blocks Block to flatten.
	 *
	 * @return array<string, mixed>
	 */
	private function flattenValidationReferenceManual(array $blocks): array
	{
		$output = [];

		$allowed = array_flip(self::VALIDATION_MANUAL_COMPONENTS);
		$nestedKeys = \array_flip(AbstractFormBuilder::LAYOUT_KEYS);

		foreach ($blocks as $block) {
			$name = $block['component'];

			// If nested key exists do a recursive loop.
			if (isset($nestedKeys[$name]) && isset($block["{$name}Content"])) {
				// Do recursive loop.
				$output = array_merge($output, $this->flattenValidationReferenceManual($block["{$name}Content"]));
			} else {
				// Only output arrays of components not the actual components attribute.
				if (is_array($block)) {
					// Output only allowed fields that are relevant for the validation.
					if (isset($allowed[$name])) {
						$output[] = $block;
					}
				}
			}
		}

		return $output;
	}

	/**
	 * Output validation reference fields for manual form settings.
	 * Create block style array for settings.
	 *
	 * @param array<int|string, mixed> $blocks Blocks array of data.
	 *
	 * @return array<int|string, array<string, mixed>>
	 */
	private function getValidationReferenceManual(array $blocks): array
	{
		$output = [];

		$items = $this->flattenValidationReferenceManual($blocks);
		if (!$items) {
			return $output;
		}

		$namespace = 'internal-settings';

		$nestedKeys = \array_flip(AbstractFormBuilder::NESTED_KEYS_NEW);

		foreach ($items as $block) {
			$name = $block['component'];
			$innerBlocks = [];

			if (isset($nestedKeys[$name]) && isset($block["{$name}Content"])) {
				foreach ($block["{$name}Content"] as $inner) {
					$innerBlocks[] = [
						'blockName' => "{$namespace}/{$inner['component']}",
						'attrs' => $inner,
						'innerBlocks' => [],
					];
				}
				unset($block["{$name}Content"]);
			}

			unset($block['component']);

			$output[] = [
				'blockName' => "{$namespace}/{$name}",
				'attrs' => $block,
				'innerBlocks' => $innerBlocks,
			];
		}

		return $output;
	}
}
