<?php

/**
 * The class for form validator.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Cache\ManifestCache;
use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\UploadHelpers;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

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
	 * User submit once meta key.
	 */
	public const USER_SUBMIT_ONCE_META_KEY = 'es_validator_submit_once';

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
	 * Validate params.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	public function validateParams(array $formDetails): array
	{
		$output = [];
		$formType = $formDetails[Config::FD_TYPE];
		$formId = $formDetails[Config::FD_FORM_ID];
		$fieldsOnly = $formDetails[Config::FD_FIELDS_ONLY];
		$stepFields = isset($formDetails[Config::FD_API_STEPS]['fields']) ? \array_flip($formDetails[Config::FD_API_STEPS]['fields']) : [];
		$params = \array_merge(
			$formDetails[Config::FD_PARAMS],
			$formDetails[Config::FD_FILES]
		);

		// Manually build fields from settings components.
		if ($formType === Config::SETTINGS_TYPE_NAME || $formType === Config::SETTINGS_GLOBAL_TYPE_NAME) {
			$fieldsOnly = $this->getValidationReferenceManual($fieldsOnly);
		}

		// Find reference fields in admin config.
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
						$output[$paramKey] = $this->labels->getLabel('validationFileMaxAmount', $formId);
						$isFilesError = true;
					}

					// Check if wrong upload path.
					foreach ($inputValue as $value) {
						if (UploadHelpers::isUploadError($value)) {
							$output[$paramKey] = $this->labels->getLabel('validationFileNotLocated', $formId);
							$isFilesError = true;
							break;
						}

						// Explode and remove empty files.
						$fileName = \array_filter(\explode(\DIRECTORY_SEPARATOR, $value));
						if (!$fileName) {
							continue;
						}

						$fileName = \array_flip($fileName);

						// Bailout if file is ok.
						if (isset($fileName[Config::TEMP_UPLOAD_DIR])) {
							continue;
						}

						// Output error if file is not uploaded to the correct path.
						$output[$paramKey] = $this->labels->getLabel('validationFileWrongUploadPath', $formId);
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
								$output[$paramKey] = $this->labels->getLabel('validationRequired', $formId);
							}
						} else {
							if (empty($inputValue)) {
								$output[$paramKey] = $this->labels->getLabel('validationRequired', $formId);
							}
						}
						break;
					// Check validation for required count params.
					case 'isRequiredCount':
						if (\is_array($inputValue) && \count($inputValue) < $dataValue && !empty($inputValue)) {
							$output[$paramKey] = \sprintf($this->labels->getLabel('validationRequiredCount', $formId), $dataValue);
						}
						break;
					// Check validation for email params.
					case 'isEmail':
						if (!$this->isEmail($inputValue)) {
							if (!empty($inputValue)) {
								$output[$paramKey] = $this->labels->getLabel('validationEmail', $formId);
							}
						} else {
							if (!empty($inputValue) && SettingsHelpers::isOptionCheckboxChecked(SettingsValidation::SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY, SettingsValidation::SETTINGS_VALIDATION_USE_EMAIL_TLD_KEY)) {
								$tldList = Helpers::getCache()[ManifestCache::TYPE_FORMS][ManifestCache::TLD_KEY];

								if (!$this->isEmailTldValid($inputValue, \array_values($tldList))) {
									$output[$paramKey] = $this->labels->getLabel('validationEmailTld', $formId);
								}
							}
						}
						break;
					case 'isNumber':
						if (!\is_numeric($inputValue) && !empty($inputValue)) {
							$output[$paramKey] = $this->labels->getLabel('validationNumber', $formId);
						}
						break;
					// Check validation for url params.
					case 'isUrl':
						if (!$this->isUrl($inputValue) && !empty($inputValue)) {
							$output[$paramKey] = $this->labels->getLabel('validationUrl', $formId);
						}
						break;
					// Check validation for min number value.
					case 'min':
						if ((string) $dataValue > (string) $inputValue && !empty($inputValue)) {
							$output[$paramKey] = \sprintf($this->labels->getLabel('validationMin', $formId), $dataValue);
						}
						break;
					// Check validation for min number value.
					case 'max':
						if ((string) $dataValue < (string) $inputValue && !empty($inputValue)) {
							$output[$paramKey] = \sprintf($this->labels->getLabel('validationMax', $formId), $dataValue);
						}
						break;
					// Check validation for min array items length.
					case 'minCount':
						if (\is_array($inputValue) && $dataValue > \count($inputValue)) {
							$output[$paramKey] = \sprintf($this->labels->getLabel('validationMinCount', $formId), $dataValue);
						}
						break;
					// Check validation for max array items length.
					case 'maxCount':
						if (\is_array($inputValue) && $dataValue < \count($inputValue)) {
							$output[$paramKey] = \sprintf($this->labels->getLabel('validationMaxCount', $formId), $dataValue);
						}
						break;
					// Check validation for min characters length.
					case 'minLength':
						if ($dataValue > \strlen($inputValue)) {
							$output[$paramKey] = \sprintf($this->labels->getLabel('validationMinLength', $formId), $dataValue);
						}
						break;
					// Check validation for max characters length.
					case 'maxLength':
						if ($dataValue < \strlen($inputValue)) {
							$output[$paramKey] = \sprintf($this->labels->getLabel('validationMaxLength', $formId), $dataValue);
						}
						break;
					case 'validationPattern':
						if (\gettype($dataValue) !== 'string') {
							break;
						}

						$pattern = ValidationPatterns::getValidationPatternOutput($dataValue);

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

								$output[$paramKey] = \sprintf($this->labels->getLabel('validationPattern', $formId), $patternOutput);
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

								$output[$paramKey] = \sprintf($this->labels->getLabel('validationAcceptMimeMultiple', $formId), $dataValue);
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
		$file = $formDetails[Config::FD_FILES_UPLOAD];
		$formId = $formDetails[Config::FD_FORM_ID];
		$fieldsOnly = $formDetails[Config::FD_FIELDS_ONLY];
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
						$output[$id] = \sprintf($this->labels->getLabel('validationAcceptMime', $formId), $dataValue);
					}
					if (!$this->isFileTypeValid($fileName, $dataValue)) {
						$output[$id] = \sprintf($this->labels->getLabel('validationAccept', $formId), $dataValue);
					}
					break;
				case 'minSize':
					if (!$this->isFileMinSizeValid((int) $fileSize, (int) $dataValue * 1000)) {
						$output[$id] = \sprintf($this->labels->getLabel('validationMinSize', $formId), $dataValue / 1000);
					}
					break;
				case 'maxSize':
					if (!$this->isFileMaxSizeValid((int) $fileSize, (int) $dataValue * 1000)) {
						$output[$id] = \sprintf($this->labels->getLabel('validationMaxSize', $formId), $dataValue / 1000);
					}
					break;
			}
		}

		return $output;
	}

	/**
	 * Validate mandatory params or FormDetails.
	 *
	 * @param array<string, mixed> $params Params to validate or FormDetails.
	 * @param array<string, mixed> $mandatoryParams Mandatory params to validate.
	 *
	 * @return boolean
	 */
	public function validateMandatoryParams(array $params, array $mandatoryParams): bool
	{
		if (!$params) {
			return true;
		}

		foreach ($mandatoryParams as $paramName => $paramType) {
			if (!isset($params[$paramName])) {
				return false;
			}

			if (empty($params[$paramName])) {
				return false;
			}

			if (\gettype($params[$paramName]) !== $paramType) {
				return false;
			}
		}

		return true;
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
				return $this->labels->getLabel($item, $formId);
			},
			$items
		);
	}

	/**
	 * Set validation submit once.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return bool
	 */
	public function setValidationSubmitOnce(string $formId): bool
	{
		$onlyLoggedIn = SettingsHelpers::isSettingCheckboxChecked(SettingsValidation::SETTINGS_VALIDATION_USE_SUBMIT_ONLY_LOGGED_IN_KEY, SettingsValidation::SETTINGS_VALIDATION_USE_SUBMIT_ONLY_LOGGED_IN_KEY, $formId);
		$submitOnce = SettingsHelpers::isSettingCheckboxChecked(SettingsValidation::SETTINGS_VALIDATION_USE_SUBMIT_ONCE_KEY, SettingsValidation::SETTINGS_VALIDATION_USE_SUBMIT_ONCE_KEY, $formId);
		if (!$onlyLoggedIn || !$submitOnce) {
			return false;
		}

		$currentUser = \get_current_user_id();

		if ($currentUser === 0) {
			return false;
		}

		$output = \get_user_meta($currentUser, self::USER_SUBMIT_ONCE_META_KEY, true) ?: [];

		$output[$formId] = true;

		\update_user_meta($currentUser, self::USER_SUBMIT_ONCE_META_KEY, $output);

		return true;
	}

	/**
	 * Check if user is logged in.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return bool
	 */
	public function validateSubmitOnlyLoggedIn(string $formId): bool
	{
		$onlyLoggedIn = SettingsHelpers::isSettingCheckboxChecked(SettingsValidation::SETTINGS_VALIDATION_USE_SUBMIT_ONLY_LOGGED_IN_KEY, SettingsValidation::SETTINGS_VALIDATION_USE_SUBMIT_ONLY_LOGGED_IN_KEY, $formId);
		if (!$onlyLoggedIn) {
			return false;
		}

		return \get_current_user_id() === 0;
	}

	/**
	 * Check if user has already submitted the form.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return bool
	 */
	public function validateSubmitOnlyOnce(string $formId): bool
	{
		$onlyLoggedIn = SettingsHelpers::isSettingCheckboxChecked(SettingsValidation::SETTINGS_VALIDATION_USE_SUBMIT_ONLY_LOGGED_IN_KEY, SettingsValidation::SETTINGS_VALIDATION_USE_SUBMIT_ONLY_LOGGED_IN_KEY, $formId);
		$submitOnce = SettingsHelpers::isSettingCheckboxChecked(SettingsValidation::SETTINGS_VALIDATION_USE_SUBMIT_ONCE_KEY, SettingsValidation::SETTINGS_VALIDATION_USE_SUBMIT_ONCE_KEY, $formId);

		if (!$onlyLoggedIn || !$submitOnce) {
			return false;
		}

		$output = \get_user_meta(\get_current_user_id(), self::USER_SUBMIT_ONCE_META_KEY, true) ?: [];

		return isset($output[$formId]) && $output[$formId] === true;
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

		$blockDetails = GeneralHelpers::getBlockNameDetails($block['blockName']);

		$name = $blockDetails['name'];
		$namespace = $blockDetails['namespace'];

		if (!$name || !$namespace) {
			return $output;
		}

		foreach ($block['attrs'] as $attributeKey => $attributeValue) {
			$attrName = Helpers::kebabToCamelCase($namespace === 'internal-settings' ? $name : "{$name}-{$name}");
			$id = $block['attrs']["{$attrName}Name"] ?? '';

			// Get all validation fields with the correct prefix.
			$valid = \array_flip(
				\array_map(
					static function ($item) use ($attrName) {
						return Helpers::kebabToCamelCase("{$attrName}-{$item}");
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
