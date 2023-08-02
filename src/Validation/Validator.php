<?php

/**
 * The class for form validator.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\Settings\Settings;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

/**
 * Class Validator
 */
class Validator extends AbstractValidation
{
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
		'validationPattern',
		'isRequiredCount',
		'isEmail',
		'isNumber',
		'isUrl',
		'accept',
		'minSize',
		'maxSize',
		'minLength',
		'maxLength',
		'isRequired',
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
	 * API validator output key.
	 *
	 * @var string
	 */
	public const VALIDATOR_OUTPUT_KEY = 'validation';

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
	 * Validate params.
	 *
	 * @param array<string, mixed> $data Date to check from reference helper.
	 *
	 * @return array<string, mixed>
	 */
	public function validateParams(array $data): array
	{
		$output = [];
		$formType = $data['type'];
		$formId = $data['formId'];
		$fieldsOnly = $data['fieldsOnly'];
		$stepFields = $data['apiSteps']['fields'] ?? [];
		$params = \array_merge(
			$data['params'],
			$data['files']
		);

		// Manualy build fields from settings components.
		if ($formType === Settings::SETTINGS_TYPE_NAME || $formType === Settings::SETTINGS_GLOBAL_TYPE_NAME) {
			$fieldsOnly = $this->getValidationReferenceManual($fieldsOnly);
		}

		$validationReference = $this->getValidationReference($fieldsOnly);

		// Output only step params to validate.
		if ($stepFields) {
			$stepParams = [];
			foreach ($stepFields as $value) {
				if (isset($params[$value])) {
					$stepParams[$value] = $params[$value];
				}
			}

			if ($stepParams) {
				$params = $stepParams;
			}
		}

		$order = self::VALIDATION_FIELDS;

		// Check params.
		foreach ($params as $paramKey => $paramValue) {
			$inputValue = $paramValue['value'] ?? '';

			// Find validation reference by ID.
			$reference = $validationReference[$paramKey] ?? [];

			// Bailout if no validation is required.
			if (!$reference) {
				continue;
			}

			// Sort order or validation by the keys.
			// @phpstan-ignore-next-line.
			\uksort($reference, function ($key1, $key2) use ($order) {
				return (\array_search($key1, $order, true) > \array_search($key2, $order, true));
			});

			// Loop all validations from the reference.
			foreach ($reference as $dataKey => $dataValue) {
				if (!$dataValue) {
					continue;
				}

				switch ($dataKey) {
					// Check validation for required params.
					case 'isRequired':
						if (empty($inputValue)) {
							$output[$paramKey] = $this->getValidationLabel('validationRequired', $formId);
						}
						break;
					// Check validation for required count params.
					case 'isRequiredCount':
						if (\count(\explode(AbstractBaseRoute::DELIMITER, $inputValue)) < $dataValue && !empty($inputValue)) {
							$output[$paramKey] = \sprintf($this->getValidationLabel('validationRequiredCount', $formId), $dataValue);
						}
						break;
					// Check validation for email params.
					case 'isEmail':
						if (!$this->isEmail($inputValue) && !empty($inputValue)) {
							$output[$paramKey] = $this->getValidationLabel('validationEmail', $formId);
						} else {
							if ($this->isCheckboxOptionChecked(SettingsValidation::SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY, SettingsValidation::SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY)) {
								$path = \dirname(__FILE__) . '/manifest.json';

								if (\file_exists($path)) {
									$data = \json_decode(\implode(' ', (array)\file($path)), true);

									if (!$this->isEmailTlValid($inputValue, $data)) {
										$output[$paramKey] = $this->getValidationLabel('validationEmailTld', $formId);
									}
								}
							}
						}
						break;
					case 'isNumber':
						if (!\is_numeric($inputValue) && !empty($inputValue)) {
							$output[$paramKey] = $this->getValidationLabel('validationNumber', $formId);
						}
						break;
					// Check validation for url params.
					case 'isUrl':
						if (!$this->isUrl($inputValue) && !empty($inputValue)) {
							$output[$paramKey] = $this->getValidationLabel('validationUrl', $formId);
						}
						break;
					// Check validation for min characters length.
					case 'minLength':
						if ($dataValue > \strlen($inputValue)) {
							$output[$paramKey] = \sprintf($this->getValidationLabel('validationMinLength', $formId), $dataValue);
						}
						break;
					// Check validation for max characters length.
					case 'maxLength':
						if ($dataValue < \strlen($inputValue)) {
							$output[$paramKey] = \sprintf($this->getValidationLabel('validationMaxLength', $formId), $dataValue);
						}
						break;
					case 'validationPattern':
						$pattern = $this->validationPatterns->getValidationPatternOutput($dataValue);

						$patternValue = $pattern['value'] ?? '';
						$patternLabel = $pattern['label'] ?? '';

						if ($patternValue) {
							$inputValue = $this->fixMomentsEmailValidationPattern($inputValue, $pattern);

							// Match pattern.
							\preg_match_all("/$patternValue/", $inputValue, $matches, \PREG_SET_ORDER, 0);

							$isMatch = isset($matches[0][0]) ? $matches[0][0] === $inputValue : false;

							if (!$isMatch && !empty($inputValue)) {
								$patternOutput = $pattern['output'] ?? '';

								if (!$patternOutput) {
									$patternOutput = $patternLabel;
								}

								$output[$paramKey] = \sprintf($this->getValidationLabel('validationPattern', $formId), $patternOutput);
							}
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
	 * @param array<string, mixed> $data Date to check from reference helper.
	 *
	 * @return array<int|string, string>
	 */
	public function validateFiles(array $data): array
	{
		$output = [];
		$file = $data['filesUpload'];
		$formId = $data['formId'];
		$fieldsOnly = $data['fieldsOnly'];
		$validationReference = $this->getValidationReference($fieldsOnly);

		$fieldName = $file['fieldName'];
		$id = $file['id'];

		$fileSize = $file['size'];
		$fileName = $file['name'];

		// Find validation reference by ID.
		$reference = $validationReference[$fieldName] ?? [];

		// Loop all validations from the reference.
		foreach ($reference as $dataKey => $dataValue) {
			if (!$dataValue) {
				continue;
			}

			switch ($dataKey) {
				case 'accept':
					if (!$this->isMimeTypeValid($file)) {
						$output[$id] = \sprintf($this->getValidationLabel('validationAcceptMime', $formId), $dataValue);
					}
					if (!$this->isFileTypeValid($fileName, $dataValue)) {
						$output[$id] = \sprintf($this->getValidationLabel('validationAccept', $formId), $dataValue);
					}
					break;
				case 'minSize':
					if (!$this->isFileMinSizeValid((int) $fileSize, (int) $dataValue * 1000)) {
						$output[$id] = \sprintf($this->getValidationLabel('validationMinSize', $formId), $dataValue / 1000);
					}
					break;
				case 'maxSize':
					if (!$this->isFileMaxSizeValid((int) $fileSize, (int) $dataValue * 1000)) {
						$output[$id] = \sprintf($this->getValidationLabel('validationMaxSize', $formId), $dataValue / 1000);
					}
					break;
			}
		}

		return $output;
	}

	/**
	 * Get validation label from cache or db on multiple items.
	 *
	 * @param array<string, string> $items Array of items to get label.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, string>
	 */
	public function getValidationLabelItems(array $items, string $formId): array
	{
		return \array_map(
			function ($item) use ($formId) {
				return $this->getValidationLabel($item, $formId);
			},
			$items
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

		// Prevent cache.
		if ($this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_CACHE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
			$output = [];
		}

		if (!$output) {
			$output = $this->labels->getValidationLabelsOutput($formId);

			\set_transient(self::CACHE_VALIDATOR_LABELS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['quick']);
		}

		return $output[$key] ?? '';
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

		foreach ($block['attrs'] as $attributeKey => $attributeValue) {
			switch ($name) {
				// TODO: test this.
				case 'custom-data':
					$type = $block['attrs']['customDataFieldType'] ?? '';
					$attrName = Components::kebabToCamelCase("{$name}-{$type}");
					$id = $block['attrs']["{$name}Name"] ?? '';
					break;
				default:
					$attrName = Components::kebabToCamelCase($namespace === 'internal-settings' ? $name : "{$name}-{$name}");
					$id = $block['attrs']["{$attrName}Name"] ?? '';
					break;
			}

			// Get all validation fields with the correct prefix.
			$valid = \array_flip(
				\array_map(
					static function ($item) use ($attrName) {
						return Components::kebabToCamelCase("{$attrName}-{$item}");
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

		$allowed = \array_flip(self::VALIDATION_MANUAL_COMPONENTS);
		$nestedKeys = \array_flip(AbstractFormBuilder::LAYOUT_KEYS);

		foreach ($blocks as $block) {
			$name = $block['component'] ?? '';

			// If nested key exists do a recursive loop.
			if (isset($nestedKeys[$name]) && isset($block["{$name}Content"])) {
				// Do recursive loop.
				$output = \array_merge($output, $this->flattenValidationReferenceManual($block["{$name}Content"]));
			} else {
				// Only output arrays of components not the actual components attribute.
				if (\is_array($block)) {
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

	/**
	 * Moments will not allow uppercase value for the email.
	 *
	 * @param mixed $inputValue Input value from the form field.
	 * @param array<string, string> $pattern Validation pattern array.
	 *
	 * @return mixed
	 */
	private function fixMomentsEmailValidationPattern($inputValue, $pattern)
	{
		$label = $pattern['label'] ?? '';

		if ($label === 'momentsEmail') {
			return \strtolower($inputValue);
		}

		return $inputValue;
	}
}
