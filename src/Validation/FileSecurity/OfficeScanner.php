<?php

/**
 * Office security scanner.
 *
 * Office files come in two structurally different containers and we have to
 * inspect each on its own terms:
 *
 *   - Office Open XML (`.docx/.xlsx/.pptx`) is a ZIP. We enumerate members
 *     and reject macro bodies, embedded OLE binaries and externally-targeted
 *     relationship references.
 *   - Legacy Office (`.doc/.xls/.ppt`) is OLE Compound File Binary Format
 *     (CFBF). It is not a ZIP, so we read the raw bytes and look for the
 *     canonical UTF-16LE stream names that indicate macros or embedded OLE
 *     payloads.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

use ZipArchive;

/**
 * Scans .docx / .xlsx / .pptx archives and legacy .doc / .xls / .ppt files.
 */
final class OfficeScanner implements FileSecurityScannerInterface
{
	/**
	 * OOXML member-name suffixes that disqualify the document outright
	 * (macro bodies, embedded OLE binaries). Compared case-insensitively.
	 *
	 * @var array<int, string>
	 */
	private const array FORBIDDEN_ENTRY_SUFFIXES = [
		'vbaproject.bin',
		'/oleobject',
		'oleobject.bin',
		'activex',
		'/embeddings/',
	];

	/**
	 * CFBF (OLE Compound File) magic. Identifies legacy `.doc/.xls/.ppt`.
	 */
	private const string CFBF_MAGIC = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";

	/**
	 * UTF-8 stream names (encoded to UTF-16LE at runtime) whose presence in a
	 * CFBF container indicates VBA macros or embedded OLE payloads. The
	 * leading `\x01` for system streams is preserved as part of the needle.
	 *
	 * @var array<int, string>
	 */
	private const array CFBF_DANGEROUS_STREAMS = [
		'Macros',
		'_VBA_PROJECT',
		'_VBA_PROJECT_CUR',
		'ObjectPool',
		"\x01Ole10Native",
		"\x01CompObjOle",
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
		$header = $this->readHeader($filepath, 8);
		if ($header === '') {
			return 'validationFileScanFailed';
		}

		if (\strncmp($header, self::CFBF_MAGIC, 8) === 0) {
			return $this->scanCfbf($filepath);
		}

		return $this->scanOoxml($filepath);
	}

	/**
	 * Read the first $bytes of the file without loading the whole thing.
	 *
	 * @param string $filepath Path to file.
	 * @param int    $bytes    Number of bytes to read.
	 *
	 * @return string Bytes read, or empty string on failure.
	 */
	private function readHeader(string $filepath, int $bytes): string
	{
		$handle = @\fopen($filepath, 'rb'); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ($handle === false) {
			return '';
		}

		$header = (string) \fread($handle, $bytes); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
		\fclose($handle); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		return $header;
	}

	/**
	 * Scan a ZIP-based OOXML document.
	 *
	 * @param string $filepath Path to file.
	 *
	 * @return string Empty string when safe, label key on rejection.
	 */
	private function scanOoxml(string $filepath): string
	{
		if (!\class_exists(ZipArchive::class)) {
			return 'validationFileScanFailed';
		}

		$zip = new ZipArchive();
		$opened = $zip->open($filepath, ZipArchive::RDONLY);
		if ($opened !== true) {
			return 'validationFileOfficeUnsafe';
		}

		try {
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$entryName = (string) $zip->getNameIndex($i);
				$lower = \strtolower($entryName);

				foreach (self::FORBIDDEN_ENTRY_SUFFIXES as $needle) {
					if (\str_contains($lower, $needle)) {
						return 'validationFileOfficeUnsafe';
					}
				}

				// Relationship files describe links between document parts.
				// External TargetMode is the documented vector for
				// auto-loaded remote payloads (DDE, template injection).
				if (\str_ends_with($lower, '.rels')) {
					$body = (string) $zip->getFromIndex($i);
					if ($body !== '' && \stripos($body, 'targetmode="external"') !== false) {
						return 'validationFileOfficeUnsafe';
					}
				}
			}
		} finally {
			$zip->close();
		}

		return '';
	}

	/**
	 * Scan a legacy CFBF (OLE Compound File) document by raw-byte substring
	 * match on the UTF-16LE encoding of known macro / embedded-object stream
	 * names. Avoids depending on a full OLE parser while still catching the
	 * canonical attack vectors.
	 *
	 * @param string $filepath Path to file.
	 *
	 * @return string Empty string when safe, label key on rejection.
	 */
	private function scanCfbf(string $filepath): string
	{
		$contents = @\file_get_contents($filepath); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if (!\is_string($contents) || $contents === '') {
			return 'validationFileScanFailed';
		}

		foreach (self::CFBF_DANGEROUS_STREAMS as $name) {
			$needle = $this->toUtf16Le($name);
			if ($needle !== '' && \str_contains($contents, $needle)) {
				return 'validationFileOfficeUnsafe';
			}
		}

		return '';
	}

	/**
	 * Encode a UTF-8 string to UTF-16LE without a BOM. CFBF directory entries
	 * store stream names in this encoding.
	 *
	 * @param string $value UTF-8 input.
	 *
	 * @return string UTF-16LE bytes, or empty string when conversion fails.
	 */
	private function toUtf16Le(string $value): string
	{
		if (!\function_exists('mb_convert_encoding')) {
			return '';
		}

		$encoded = \mb_convert_encoding($value, 'UTF-16LE', 'UTF-8');
		return \is_string($encoded) ? $encoded : '';
	}
}
