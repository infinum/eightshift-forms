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
	 * Get validation pattern - pattern from name.
	 *
	 * @param string $name Name to serach.
	 *
	 * @return string
	 */
	public function getValidationPattern(string $name): string
	{
		$patterns = \array_filter(
			$this->getValidationPatterns(),
			static function ($item) use ($name) {
				return $item['label'] === $name;
			}
		);

		if ($patterns) {
			return \reset($patterns)['value'] ?? $name;
		}

		return $name;
	}

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
				$value = $item['value'] ?? '';

				// Output label as value for enum.
				return [
					'label' => $label,
					'value' => $value === '-' ? '' : $label,
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
	 * @return string
	 */
	public function getValidationPatternOutput(string $pattern): string
	{
		$patterns = \array_filter(
			$this->getValidationPatterns(),
			static function ($item) use ($pattern) {
				return $item['value'] === $pattern;
			}
		);

		$patterns = \reset($patterns);

		if (!$patterns) {
			return $pattern;
		}

		$output = $patterns['output'] ?? '';

		if ($output) {
			return $output;
		}

		$label = $patterns['label'] ?? '';

		if ($label) {
			return $label;
		}

		return $pattern;
	}

	/**
	 * Prepare validation patterns
	 *
	 * @return array<int, array<string, string>>
	 */
	private function getValidationPatterns(): array
	{
		$output = [
			[
				'value' => '',
				'label' => '-',
				'name' => '',
			],
			...SettingsValidation::VALIDATION_PATTERNS,
			...SettingsValidation::VALIDATION_PATTERNS_PRIVATE,
		];

		$userPatterns = \preg_split("/\\r\\n|\\r|\\n/", $this->getOptionValue(SettingsValidation::SETTINGS_VALIDATION_PATTERNS_KEY));

		if ($userPatterns) {
			foreach ($userPatterns as $pattern) {
				$pattern = \explode(' : ', $pattern);

				if (!isset($pattern[0]) || !isset($pattern[1])) {
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
