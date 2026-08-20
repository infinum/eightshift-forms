<?php

/**
 * Shortcode class - entry progress.
 *
 * @package EightshiftForms\Shortcode
 */

declare(strict_types=1);

namespace EightshiftForms\Shortcode;

use EightshiftForms\Entries\EntriesHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * EntryProgress class.
 */
class EntryProgress implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_shortcode('esFormsEntryProgress', [$this, 'callback']);
	}

	/**
	 * Shortcode callback
	 *
	 * @param array<string, mixed> $atts Attributes passed to the shortcode.
	 *
	 * @return string
	 */
	public function callback(array $atts): string
	{
		$params = \shortcode_atts(
			[
				'form_id' => '', // form ID.
				'key' => '', // entries key.
				'prefix' => '', // prefix text.
				'suffix' => '', // suffix text.
				'limit' => 0, // number.
				'output' => 'percentage', // number|percentage.
				'show_bar' => 'no', // yes|no.
				'condition_key' => '',
				'condition_value' => '',
			],
			$atts
		);

		$formId = isset($params['form_id']) ? \esc_html($params['form_id']) : '';
		$key = isset($params['key']) ? \esc_html($params['key']) : '';
		$prefix = isset($params['prefix']) ? \esc_html($params['prefix']) : '';
		$suffix = isset($params['suffix']) ? \esc_html($params['suffix']) : '';
		$limit = isset($params['limit']) ? (int) $params['limit'] : 0;
		$output = isset($params['output']) ? \esc_html($params['output']) : 'percentage';
		$showBar = isset($params['show_bar']) ? \esc_html($params['show_bar']) : 'no';
		$conditionKey = isset($params['condition_key']) ? \esc_html($params['condition_key']) : '';
		$conditionValue = isset($params['condition_value']) ? \esc_html($params['condition_value']) : '';

		if (!$formId || !$key || $limit === 0) {
			return '';
		}

		$entries = EntriesHelper::getEntries($formId, 1, 10000)['items'] ?? [];

		$current = 0;

		foreach ($entries as $entry) {
			$data = $entry['entryValue'] ?? [];

			if (!$data) {
				continue;
			}

			if (!empty($conditionKey) && !empty($conditionValue)) {
				$status = $data[$conditionKey] ?? '';

				if ($status !== $conditionValue) {
					continue;
				}
			}

			$number = $data[$key] ?? '';

			if (!\is_numeric($number)) {
				continue;
			}

			$current += $number;
		}

		$percentage = 0;

		if ($current > 0 && $limit > 0) {
			$percentage = \number_format(($current / $limit) * 100, 2) . '%';
		}

		$bar = "<div class='es-entry-progress__bar'><div class='es-entry-progress__status' style='width: {$percentage};'></div></div>";

		if ($showBar === 'no') {
			$bar = '';
		}

		if ($output === 'number') {
			$outoutNumberValue = \round($current);

			return "<div class='es-entry-progress'>{$bar}<div class='es-entry-progress__text'>{$prefix}{$outoutNumberValue}{$suffix}</div>";
		}

		return "<div class='es-entry-progress'>{$bar}<div class='es-entry-progress__text'>{$prefix}{$percentage}{$suffix}</div>";
	}
}
