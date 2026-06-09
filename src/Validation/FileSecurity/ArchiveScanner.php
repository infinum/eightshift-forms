<?php

/**
 * Archive security scanner. Inspects ZIP archives for disallowed contents
 * (executables / scripts in the deny-list), path traversal in member names,
 * and zip-bomb compression ratios.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\HooksHelpers;
use ZipArchive;

/**
 * Scans .zip uploads.
 */
final class ArchiveScanner implements FileSecurityScannerInterface
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
	public function scan(string $filepath, string $declaredName, string $detectedMime): string
	{
		if (!\class_exists(ZipArchive::class)) {
			return 'validationFileScanFailed';
		}

		$zip = new ZipArchive();
		if ($zip->open($filepath, ZipArchive::RDONLY) !== true) {
			return 'validationFileArchiveUnsafe';
		}

		$denyList = $this->getDenyList();
		$totalUncompressed = 0;

		try {
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$stat = $zip->statIndex($i);
				if (!\is_array($stat)) {
					return 'validationFileArchiveUnsafe';
				}

				$name = (string) ($stat['name'] ?? '');

				if ($this->hasPathTraversal($name)) {
					return 'validationFileArchiveUnsafe';
				}

				$ext = $this->getExtension($name);
				if ($ext !== '' && isset($denyList[$ext])) {
					return 'validationFileArchiveUnsafe';
				}

				$size = (int) ($stat['size'] ?? 0);
				$compressed = (int) ($stat['comp_size'] ?? 0);

				$totalUncompressed += $size;
				if ($totalUncompressed > Config::FILE_UPLOAD_ARCHIVE_MAX_UNCOMPRESSED) {
					return 'validationFileArchiveUnsafe';
				}

				if ($compressed > 0 && ($size / $compressed) > Config::FILE_UPLOAD_ARCHIVE_MAX_RATIO) {
					return 'validationFileArchiveUnsafe';
				}
			}
		} finally {
			$zip->close();
		}

		return '';
	}

	/**
	 * Member name contains path traversal segments or absolute paths.
	 *
	 * @param string $name Member name.
	 */
	private function hasPathTraversal(string $name): bool
	{
		if ($name === '') {
			return true;
		}

		if ($name[0] === '/' || $name[0] === '\\') {
			return true;
		}

		// Drive letter on Windows-style paths.
		if (\strlen($name) >= 2 && $name[1] === ':') {
			return true;
		}

		$normalized = \str_replace('\\', '/', $name);
					return \array_any(\explode('/', $normalized), fn($segment): bool => $segment === '..');
	}

	/**
	 * Lowercase extension extracted from an archive member name.
	 *
	 * @param string $name Member name.
	 */
	private function getExtension(string $name): string
	{
		$dot = \strrpos($name, '.');
		if ($dot === false) {
			return '';
		}

		return \strtolower(\substr($name, $dot + 1));
	}

	/**
	 * Deny-listed extensions, flipped to ['ext' => true] for O(1) lookup.
	 *
	 * @return array<string, bool>
	 */
	private function getDenyList(): array
	{
		$denyList = \apply_filters( // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
			HooksHelpers::getFilterName(['validation', 'fileSecurityDenyExtensions']),
			Config::FILE_UPLOAD_DENY_EXTENSIONS
		);

		if (!\is_array($denyList)) {
			$denyList = Config::FILE_UPLOAD_DENY_EXTENSIONS;
		}

		$normalized = [];
		foreach ($denyList as $ext) {
			if (!\is_string($ext)) {
				continue;
			}
			$normalized[\strtolower(\ltrim($ext, '.'))] = true;
		}

		return $normalized;
	}
}
