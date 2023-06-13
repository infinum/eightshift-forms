<?php

/**
 * The class for form ValidationPatterns.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Settings\SettingsHelper;

/**
 * Class ValidationPatterns
 */
class ValidationPatterns implements ValidationPatternsInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Custom validation patterns - public.
	 */
	public const VALIDATION_PATTERNS = [
		[
			'value' => '^(1[0-2]|0[1-9])\/(3[01]|[12][0-9]|0[1-9])$',
			'label' => 'MM/DD',
			'output' => 'MM/DD',
		],
		[
			'value' => '^(3[01]|[12][0-9]|0[1-9])\/(1[0-2]|0[1-9])$',
			'label' => 'DD/MM',
			'output' => 'DD/MM',
		],
		[
			'value' => '^[^@]+@[^@]{2,}\.[^@]{2,}$',
			'label' => 'simpleEmail',
			'output' => 'info@example.com',
		],
	];

	/**
	 * Custom validation patterns - private.
	 */
	public const VALIDATION_PATTERNS_PRIVATE = [
		[
			'value' => "(?:[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$",
			'label' => 'momentsEmail',
			'output' => 'info@example.com',
		],
		[
			'value' => '^0$|^[-]?[1-9]\d*$|^\.\d+$|^[-]?0\.\d*$|^[-]?[1-9]\d*\.\d*$',
			'label' => 'momentsNumber',
			'output' => '123456789',
		],
	];

	/**
	 * Prepare validation patterns for editor select output.
	 *
	 * @return array<int, array<string, string>>
	 */
	public function getValidationPatternsEditor(): array
	{
		return \array_map(
			static function ($item) {
				$label = $item['label'] ?? '';

				// Output label as value for enum.
				return [
					'label' => $label,
					'value' => $label,
				];
			},
			$this->getValidationPatterns()
		);
	}

	/**
	 * Get validation pattern - output from pattern.
	 *
	 * @param string $pattern Pattern to serach.
	 *
	 * @return array<string, string>
	 */
	public function getValidationPatternOutput(string $pattern): array
	{
		$patterns = \array_filter(
			$this->getValidationPatterns(),
			static function ($item) use ($pattern) {
				return $item['label'] === $pattern;
			}
		);

		$patterns = \reset($patterns);

		if (!$patterns) {
			return [
				'value' => $pattern,
				'label' => $pattern,
				'output' => $pattern,
			];
		}

		return $patterns;
	}

	/**
	 * Prepare validation patterns
	 *
	 * @return array<int, array<string, string>>
	 */
	private function getValidationPatterns(): array
	{
		$output = [
			...self::VALIDATION_PATTERNS,
			...self::VALIDATION_PATTERNS_PRIVATE,
		];

		$userPatterns = \preg_split("/\\r\\n|\\r|\\n/", $this->getOptionValueAsJson(SettingsValidation::SETTINGS_VALIDATION_PATTERNS_KEY));

		if ($userPatterns) {
			foreach ($userPatterns as $pattern) {
				$pattern = \explode(' : ', $pattern);

				if (!isset($pattern[0]) || !isset($pattern[1]) || !isset($pattern[2])) {
						continue;
				};

				$output[] = [
					'value' => $pattern[1],
					'label' => $pattern[0],
					'output' => $pattern[2],
				];
			}
		}

		return $output;
	}
}
