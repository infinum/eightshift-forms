<?php

/**
 * Office (OOXML) security scanner. Office documents are ZIP archives — this
 * scanner enumerates internal members and rejects macros, embedded OLE
 * objects and externally-targeted relationship references.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

use ZipArchive;

/**
 * Scans .docx / .xlsx / .pptx archives.
 */
final class OfficeScanner implements FileSecurityScannerInterface
{
	/**
	 * Suffix patterns that disqualify the document outright (macro bodies,
	 * embedded OLE binaries). Compared case-insensitively against entry names.
	 *
	 * @var array<int, string>
	 */
	private const FORBIDDEN_ENTRY_SUFFIXES = [
		'vbaproject.bin',
		'/oleobject',
		'oleobject.bin',
		'activex',
		'/embeddings/',
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
					if (\strpos($lower, $needle) !== false) {
						return 'validationFileOfficeUnsafe';
					}
				}

				// Relationship files describe links between document parts.
				// External TargetMode is the documented vector for
				// auto-loaded remote payloads (DDE, template injection).
				if (\substr($lower, -5) === '.rels') {
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
}
