<?php

/**
 * PDF string reading shared by the scanners that read a PDF body as raw
 * bytes: where a literal string ends, and what a string value decodes to.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

/**
 * PDF literal and hex string reading.
 */
final class PdfStrings
{
	/**
	 * Offset just past a literal `( ... )` string, honouring escapes and
	 * balanced inner parentheses.
	 *
	 * @param string   $body   Bytes to read from.
	 * @param int      $offset Offset of the opening parenthesis.
	 * @param int|null $limit  Offset the scan may not read past, or null for the whole string.
	 */
	public static function skipLiteral(string $body, int $offset, ?int $limit = null): int
	{
		$total = $limit === null ? \strlen($body) : \min(\strlen($body), $limit);
		$depth = 0;
		$cursor = $offset;

		while ($cursor < $total) {
			$byte = $body[$cursor];

			if ($byte === '\\') {
				$cursor += 2;
				continue;
			}

			if ($byte === '(') {
				$depth++;
			} elseif ($byte === ')') {
				$depth--;

				if ($depth === 0) {
					return $cursor + 1;
				}
			}

			$cursor++;
		}

		return $total;
	}

	/**
	 * Read a literal or hex string at an offset and decode it to printable
	 * ASCII text.
	 *
	 * A UTF-16BE (`FE FF`) or UTF-8 (`EF BB BF`) byte-order mark is honoured,
	 * since a text string such as `/UF` is written either way. Every
	 * character must still be printable ASCII: callers compare the result
	 * byte for byte, and anything else is refused rather than normalised.
	 *
	 * @param string $text   Text holding the value.
	 * @param int    $offset First byte of the value.
	 *
	 * @return string|null Decoded text, or null when the value is not a readable printable-ASCII string.
	 */
	public static function readAsciiText(string $text, int $offset): ?string
	{
		$first = \substr($text, $offset, 1);

		if ($first === '(') {
			$end = self::skipLiteral($text, $offset);

			if (\substr($text, $end - 1, 1) !== ')') {
				return null;
			}

			$bytes = self::decodeLiteral(\substr($text, $offset + 1, $end - $offset - 2));
		} elseif ($first === '<' && \substr($text, $offset, 2) !== '<<') {
			$close = \strpos($text, '>', $offset);

			if ($close === false) {
				return null;
			}

			$hex = (string) \preg_replace('/[\s\0]+/', '', \substr($text, $offset + 1, $close - $offset - 1));

			if ($hex === '' || \preg_match('/^[0-9A-Fa-f]+$/', $hex) !== 1) {
				return null;
			}

			// PDF 32000-1 §7.3.4.3: an odd final digit is followed by an implied 0.
			$bytes = (string) \hex2bin(\strlen($hex) % 2 === 1 ? $hex . '0' : $hex);
		} else {
			return null;
		}

		if (\str_starts_with($bytes, "\xFE\xFF")) {
			$bytes = \substr($bytes, 2);

			if (\strlen($bytes) % 2 !== 0 || \preg_match('/^(?:\x00[\x20-\x7E])+$/', $bytes) !== 1) {
				return null;
			}

			$bytes = (string) \preg_replace('/\x00(.)/s', '$1', $bytes);
		} elseif (\str_starts_with($bytes, "\xEF\xBB\xBF")) {
			$bytes = \substr($bytes, 3);
		}

		return \preg_match('/^[\x20-\x7E]+$/', $bytes) === 1 ? $bytes : null;
	}

	/**
	 * Decode the escapes in a literal string's contents (PDF 32000-1 §7.3.4.2).
	 *
	 * @param string $contents Bytes between the outer parentheses.
	 */
	private static function decodeLiteral(string $contents): string
	{
		$escapes = ['n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\x08", 'f' => "\x0c", '(' => '(', ')' => ')', '\\' => '\\'];
		$total = \strlen($contents);
		$decoded = '';
		$cursor = 0;

		while ($cursor < $total) {
			$byte = $contents[$cursor];

			if ($byte !== '\\') {
				$decoded .= $byte;
				$cursor++;
				continue;
			}

			$next = $contents[$cursor + 1] ?? '';

			if (isset($escapes[$next])) {
				$decoded .= $escapes[$next];
				$cursor += 2;
				continue;
			}

			if (\preg_match('/^[0-7]{1,3}/', \substr($contents, $cursor + 1, 3), $octal) === 1) {
				$decoded .= \chr(((int) \octdec($octal[0])) & 0xFF);
				$cursor += 1 + \strlen($octal[0]);
				continue;
			}

			// A backslash before an end of line continues the string onto the
			// next line and contributes nothing.
			if ($next === "\r" || $next === "\n") {
				$cursor += \substr($contents, $cursor + 1, 2) === "\r\n" ? 3 : 2;
				continue;
			}

			// Any other escaped byte stands for itself; a lone trailing
			// backslash is dropped.
			$decoded .= $next;
			$cursor += 2;
		}

		return $decoded;
	}
}
