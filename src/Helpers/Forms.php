<?php

/**
 * Helpers for forms
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

/**
 * Helpers for forms
 */
class Forms
{

  /**
   * Overrides the value from $_GET if it's set.
   *
   * @param  string $value Input field's value.
   * @param  string $name  Field's key / name.
   * @return string
   */
	public static function maybeOverrideValueFromPost(string $value, string $name)
	{

	  /**
	   * Ignoring nonce verification missing warning because of the dynamic nature of this feature
	   * ( i.e. you can set any form to post to another form and prefill any field )
	   * not sure how nonce could be implemented here.
	   */
    // phpcs:disable WordPress.Security.NonceVerification.Missing
		if (isset($_POST["field-$name"])) {
			$unslashed = sanitize_text_field((string) \wp_unslash($_POST["field-$name"])); /* phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, Sanitized 2 lines below */
		}
    // phpcs:enable WordPress.Security.NonceVerification.Missing

		return $value;
	}

  /**
   * Build a single fast (key based) array for checking which from type(s) is/are used.
   *
   * @param  bool   $isComplex                  Is form complex? (uses multiple types).
   * @param  string $formType                   Used form type (used if not complex).
   * @param  array  $formTypesComplex          Used form types.
   * @param  array  $formTypesComplexRedirect Used form types that redirect on success.
   * @return array
   */
	public static function detectUsedTypes(bool $isComplex, string $formType, array $formTypesComplex, array $formTypesComplexRedirect): array
	{
		$usedTypes = [];

		if ($isComplex) {
			$allComplexTypes = array_merge($formTypesComplex, $formTypesComplexRedirect);
			foreach ($allComplexTypes as $complexFormType) {
				$usedTypes[$complexFormType] = 1;
			}
		} else {
			$usedTypes[$formType] = 1;
		}

		return $usedTypes;
	}

  /**
   * Recursively changes theme for all blocks.
   *
   * @param array  $blocks Array of blocks.
   * @param string $theme Theme name.
   * @return array
   */
	public static function recursivelyChangeThemeForAllBlocks(array $blocks, string $theme)
	{
		foreach ($blocks as $key => $block) {
			$blocks[$key]['attrs']['theme'] = $theme;

			if (! empty($block['innerBlocks'])) {
				$blocks[$key]['innerBlocks'] = self::recursivelyChangeThemeForAllBlocks($block['innerBlocks'], $theme);
			}
		}

		return $blocks;
	}
}
