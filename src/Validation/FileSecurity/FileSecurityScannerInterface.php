<?php

/**
 * Interface for file security scanners.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

/**
 * Contract for a file security scanner.
 *
 * Implementations decide whether a single file on disk is acceptable for a
 * public upload endpoint. They never sanitize or rewrite the file — they
 * either accept it or return the label key that explains the rejection.
 */
interface FileSecurityScannerInterface
{
	/**
	 * Scan a single file.
	 *
	 * @param string $filepath     Absolute path to the file on disk.
	 * @param string $declaredName Original (user-supplied) filename, used to derive the extension.
	 * @param string $detectedMime Magic-byte MIME type detected from the file contents.
	 *
	 * @return string Empty string when the file is considered safe;
	 *                otherwise the label key (from Labels) describing the rejection reason.
	 */
	public function scan(string $filepath, string $declaredName, string $detectedMime): string;
}
