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
	public static function maybe_override_value_from_query_string(string $value, string $name)
	{

	  /**
	   * Ignoring nonce verification missing warning because of the dynamic nature of this feature
	   * ( i.e. you can set any form to post to another form and prefill any field )
	   * not sure how nonce could be implemented here.
	   */
      // phpcs:disable WordPress.Security.NonceVerification.Recommended
		if (isset($_GET["field-$name"])) {
			$unslashed = \wp_unslash($_GET["field-$name"]);
			$unslashed = is_array($unslashed) ? implode(' ', $unslashed) : $unslashed;
			$value     = \sanitize_text_field($unslashed);
		}
      // phpcs:enable WordPress.Security.NonceVerification.Recommended

		return $value;
	}

  /**
   * Build a single fast (key based) array for checking which from type(s) is/are used.
   *
   * @param  bool   $is_complex                  Is form complex? (uses multiple types).
   * @param  string $form_type                   Used form type (used if not complex).
   * @param  array  $form_types_complex          Used form types.
   * @param  array  $form_types_complex_redirect Used form types that redirect on success.
   * @return array
   */
	public static function detect_used_types(bool $is_complex, string $form_type, array $form_types_complex, array $form_types_complex_redirect): array
	{
		$used_types = [];

		if ($is_complex) {
			$all_complex_types = array_merge($form_types_complex, $form_types_complex_redirect);
			foreach ($all_complex_types as $complex_form_type) {
				$used_types[$complex_form_type] = 1;
			}
		} else {
			$used_types[$form_type] = 1;
		}

		return $used_types;
	}

  /**
   * Recursively changes theme for all blocks.
   *
   * @param array  $blocks Array of blocks.
   * @param string $theme Theme name.
   * @return array
   */
	public static function recursively_change_theme_for_all_blocks(array $blocks, string $theme)
	{
		foreach ($blocks as $key => $block) {
			$blocks[$key]['attrs']['theme'] = $theme;

			if (! empty($block['innerBlocks'])) {
				$blocks[$key]['innerBlocks'] = self::recursively_change_theme_for_all_blocks($block['innerBlocks'], $theme);
			}
		}

		return $blocks;
	}
}
