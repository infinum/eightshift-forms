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
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsUploadHelper;
use EightshiftForms\Integrations\Airtable\SettingsAirtable;
use EightshiftForms\Integrations\Calculator\SettingsCalculator;
use EightshiftForms\Integrations\Jira\SettingsJira;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Integrations\Pipedrive\SettingsPipedrive;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

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
		'validationPattern',
		'isRequiredCount',
		'isEmail',
		'isNumber',
		'isUrl',
		'accept',
		'minSize',
		'min',
		'max',
		'maxSize',
		'minCount',
		'maxCount',
		'minLength',
		'maxLength',
		'isMultiple',
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
	 * Transient cache name for validatior labels. No need to flush it because it is short live.
	 */
	public const CACHE_VALIDATOR_LABELS_TRANSIENT_NAME = 'es_validator_labels_cache';

	/**
	 * Create a new instance.
	 *
	 * @param LabelsInterface $labels Inject documentsData which holds labels data.
	 * @param ValidationPatternsInterface $validationPatterns Inject validation patterns methods.
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
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param bool $strictValidation Is validation is strict.
	 *
	 * @return array<string, mixed>
	 */
	public function validateParams(array $formDetails, bool $strictValidation = true): array
	{
		$output = [];
		$formType = $formDetails[UtilsConfig::FD_TYPE];
		$formId = $formDetails[UtilsConfig::FD_FORM_ID];
		$fieldsOnly = $formDetails[UtilsConfig::FD_FIELDS_ONLY];
		$stepFields = isset($formDetails[UtilsConfig::FD_API_STEPS]['fields']) ? \array_flip($formDetails[UtilsConfig::FD_API_STEPS]['fields']) : [];
		$params = \array_merge(
			$formDetails[UtilsConfig::FD_PARAMS],
			$formDetails[UtilsConfig::FD_FILES]
		);

		// Manualy build fields from settings components.
		if ($formType === UtilsConfig::SETTINGS_TYPE_NAME || $formType === UtilsConfig::SETTINGS_GLOBAL_TYPE_NAME) {
			$fieldsOnly = $this->getValidationReferenceManual($fieldsOnly);
		}

		// Find refference fields in admin config.
		$validationReference = $this->getValidationReference($fieldsOnly);

		// Define order of validation.
		$order = self::VALIDATION_FIELDS;

		// Check params.
		foreach ($params as $paramValue) {
			$paramType = $paramValue['type'] ?? '';

			// Skip validating hidden fields.
			if ($paramType === 'hidden') {
				continue;
			}

			$inputValue = $paramValue['value'] ?? '';
			$paramKey = $paramValue['name'] ?? '';

			// Validate only step params.
			if ($stepFields) {
				if (!isset($stepFields[$paramKey])) {
					continue;
				}
			}

			// Find validation reference by ID.
			$reference = $validationReference[$paramKey] ?? [];

			// Bailout if no validation is required.
			if (!$reference) {
				continue;
			}

			// Sort order or validation by the keys.
			\uksort($reference, function ($key1, $key2) use ($order) {
				return \array_search($key1, $order, true) <=> \array_search($key2, $order, true);
			});

			// Validate all files are uploaded to the server and not a external link.
			$isFilesError = false;
			if ($paramType === 'file') {
				if (\is_array($inputValue)) {
					// Check if single or multiple and output error.
					if (!isset($reference['isMultiple']) && \count($inputValue) > 1) {
						$output[$paramKey] = $this->getValidationLabel('validationFileMaxAmount', $formId);
						$isFilesError = true;
					}

					// Check if wrong upload path.
					foreach ($inputValue as $value) {
						if (UtilsUploadHelper::isUploadError($value)) {
							$output[$paramKey] = $this->getValidationLabel('validationFileNotLocated', $formId);
							$isFilesError = true;
							break;
						}

						// Expolode and remove empty files.
						$fileName = \array_filter(\explode(\DIRECTORY_SEPARATOR, $value));
						if (!$fileName) {
							continue;
						}

						$fileName = \array_flip($fileName);

						// Bailout if file is ok.
						if (isset($fileName[UtilsConfig::TEMP_UPLOAD_DIR])) {
							continue;
						}

						// Output error if file is not uploaded to the correct path.
						$output[$paramKey] = $this->getValidationLabel('validationFileWrongUploadPath', $formId);
						$isFilesError = true;
						break;
					}
				}
			}

			// Loop all validations from the reference.
			foreach ($reference as $dataKey => $dataValue) {
				if (!$dataValue) {
					continue;
				}

				switch ($dataKey) {
					// Check validation for required params.
					case 'isRequired':
						if (\is_string($inputValue)) {
							if (\preg_match('/^\s*$/u', $inputValue) === 1) {
								$output[$paramKey] = $this->getValidationLabel('validationRequired', $formId);
							}
						} else {
							if (empty($inputValue)) {
								$output[$paramKey] = $this->getValidationLabel('validationRequired', $formId);
							}
						}
						break;
					// Check validation for required count params.
					case 'isRequiredCount':
						if (\count(\explode(UtilsConfig::DELIMITER, $inputValue)) < $dataValue && !empty($inputValue)) {
							$output[$paramKey] = \sprintf($this->getValidationLabel('validationRequiredCount', $formId), $dataValue);
						}
						break;
					// Check validation for email params.
					case 'isEmail':
						if (!$this->isEmail($inputValue) && !empty($inputValue)) {
							$output[$paramKey] = $this->getValidationLabel('validationEmail', $formId);
						} else {
							if (UtilsSettingsHelper::isOptionCheckboxChecked(SettingsValidation::SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY, SettingsValidation::SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY)) {
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
					// Check validation for min number value.
					case 'min':
						if ((string) $dataValue > (string) $inputValue && !empty($inputValue)) {
							$output[$paramKey] = \sprintf($this->getValidationLabel('validationMin', $formId), $dataValue);
						}
						break;
					// Check validation for min number value.
					case 'max':
						if ((string) $dataValue < (string) $inputValue && !empty($inputValue)) {
							$output[$paramKey] = \sprintf($this->getValidationLabel('validationMax', $formId), $dataValue);
						}
						break;
					// Check validation for min array items length.
					case 'minCount':
						if ($dataValue > \count($inputValue)) {
							$output[$paramKey] = \sprintf($this->getValidationLabel('validationMinCount', $formId), $dataValue);
						}
						break;
					// Check validation for max array items length.
					case 'maxCount':
						if ($dataValue < \count($inputValue)) {
							$output[$paramKey] = \sprintf($this->getValidationLabel('validationMaxCount', $formId), $dataValue);
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
						if (\gettype($dataValue) !== 'string') {
							break;
						}

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
					case 'accept':
						// Check every file and detect if it has correct extension.
						if (\is_array($inputValue) && $isFilesError === false) {
							foreach ($inputValue as $value) {
								if ($this->isFileTypeValid($value, $dataValue)) {
									continue;
								}

								$output[$paramKey] = \sprintf($this->getValidationLabel('validationAcceptMimeMultiple', $formId), $dataValue);
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
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<int|string, string>
	 */
	public function validateFiles(array $formDetails): array
	{
		$output = [];
		$file = $formDetails[UtilsConfig::FD_FILES_UPLOAD];
		$formId = $formDetails[UtilsConfig::FD_FORM_ID];
		$fieldsOnly = $formDetails[UtilsConfig::FD_FIELDS_ONLY];
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
	 * Validate all manadatory fields that are passed from the `getFormDetailsApi` function.
	 * If these fields are missing it can be that the forme is not configured correctly or it could be a unauthorized request.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return boolean
	 */
	public function validateFormManadatoryProperies(array $formDetails): bool
	{
		$type = $formDetails[UtilsConfig::FD_TYPE] ?? '';
		$formId = $formDetails[UtilsConfig::FD_FORM_ID] ?? '';
		$postId = $formDetails[UtilsConfig::FD_POST_ID] ?? '';
		$itemId = $formDetails[UtilsConfig::FD_ITEM_ID] ?? '';
		$innerId = $formDetails[UtilsConfig::FD_INNER_ID] ?? '';

		if (!$type) {
			return false;
		}

		switch ($type) {
			case UtilsConfig::SETTINGS_GLOBAL_TYPE_NAME:
			case UtilsConfig::FILE_UPLOAD_ADMIN_TYPE_NAME:
				return true;
			case UtilsConfig::SETTINGS_TYPE_NAME:
				if (!$formId) {
					return false;
				}
				return true;
			case SettingsMailer::SETTINGS_TYPE_KEY:
			case SettingsJira::SETTINGS_TYPE_KEY:
			case SettingsPipedrive::SETTINGS_TYPE_KEY:
			case SettingsCalculator::SETTINGS_TYPE_KEY:
				if (!$formId || !$postId) {
					return false;
				}
				return true;
			case SettingsAirtable::SETTINGS_TYPE_KEY:
				if (!$formId || !$postId || !$itemId || !$innerId) {
					return false;
				}
				return true;
			default:
				if (!$formId || !$postId || !$itemId) {
					return false;
				}
				return true;
		}
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
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
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

		$blockDetails = UtilsGeneralHelper::getBlockNameDetails($block['blockName']);

		$name = $blockDetails['name'];
		$namespace = $blockDetails['namespace'];

		if (!$name || !$namespace) {
			return $output;
		}

		foreach ($block['attrs'] as $attributeKey => $attributeValue) {
			$attrName = Components::kebabToCamelCase($namespace === 'internal-settings' ? $name : "{$name}-{$name}");
			$id = $block['attrs']["{$attrName}Name"] ?? '';

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
