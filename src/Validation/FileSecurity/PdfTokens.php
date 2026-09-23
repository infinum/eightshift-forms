<?php

/**
 * Shared notion of "this PDF name token is present in these bytes", used by
 * every scanner that reads a PDF body as raw bytes.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

/**
 * PDF name-token matching.
 */
final class PdfTokens
{
	/**
	 * Is a PDF name token present in these bytes?
	 *
	 * Matches the token only when it is followed by a PDF name-token delimiter
	 * (whitespace, `/`, `<`, `[`, `(`, `%`). This avoids substring-style false
	 * positives where the token appears inside a longer PDF name — most commonly
	 * a font subset prefix like `/AAAAAA+GentiumPlus` which would otherwise
	 * match `/AA`, or coincidental bytes inside ASCII85 stream data.
	 *
	 * @param string $haystack Bytes to search.
	 * @param string $token    Token including its leading slash.
	 */
	public static function contains(string $haystack, string $token): bool
	{
		return \preg_match('/' . \preg_quote($token, '/') . '(?=[\s\/<\[(%])/', $haystack) === 1;
	}
}
