<?php

/**
 * CSV / TSV security scanner. Detects spreadsheet formula injection patterns
 * that trigger when a victim opens the file in Excel / LibreOffice / Numbers.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

/**
 * Scans CSV / TSV / text-table uploads for formula injection.
 *
 * The scanner targets only well-known exploit forms (DDE, system command
 * launchers, HYPERLINK) rather than any leading `=` / `-`, which would
 * false-positive on legitimate negative numbers and arithmetic.
 */
final class CsvScanner implements FileSecurityScannerInterface
{
	/**
	 * Patterns (case-insensitive) that have no legitimate use inside a
	 * user-uploaded CSV cell.
	 *
	 * @var array<int, string>
	 */
	private const array DANGER_PATTERNS = [
		'=cmd|',
		'=cmd /',
		'=dde(',
		'@dde(',
		'=msexcel|',
		'=hyperlink(',
		'@sum(cmd|',
	];

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
	public function scan(string $filepath, string $declaredName, string $detectedMime): string
	{
		$handle = @\fopen($filepath, 'rb'); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if (!\is_resource($handle)) {
			return 'validationFileScanFailed';
		}

		try {
			while (($line = \fgets($handle)) !== false) {
				$lower = \strtolower($line);
				foreach (self::DANGER_PATTERNS as $pattern) {
					if (\str_contains($lower, $pattern)) {
						return 'validationFileCsvUnsafe';
					}
				}
			}
		} finally {
			\fclose($handle); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		}

		return '';
	}
}
