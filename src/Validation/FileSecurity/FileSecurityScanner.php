<?php

/**
 * Orchestrator for file security scanning. Owns the always-on checks
 * (magic-byte MIME, extension deny-list, MIME ↔ extension agreement) and
 * dispatches to type-specific scanners when applicable.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\HooksHelpers;
use finfo;

/**
 * Single entry point for inspecting an uploaded file before it is persisted.
 */
final class FileSecurityScanner
{
	/**
	 * Map of MIME prefix / exact MIME → type-specific scanner.
	 *
	 * @var array<string, class-string<FileSecurityScannerInterface>>
	 */
	private const TYPE_SCANNERS = [
		'application/pdf' => PdfScanner::class,
		'image/' => ImageScanner::class,
		'application/vnd.openxmlformats-officedocument' => OfficeScanner::class,
		'application/vnd.ms-excel' => OfficeScanner::class,
		'application/msword' => OfficeScanner::class,
		'application/vnd.ms-powerpoint' => OfficeScanner::class,
		'text/csv' => CsvScanner::class,
		'application/csv' => CsvScanner::class,
		'application/zip' => ArchiveScanner::class,
		'application/x-zip-compressed' => ArchiveScanner::class,
		'text/plain' => TextScanner::class,
	];

	/**
	 * Inspect a single file. Returns an empty string when the file is safe,
	 * or a label key (resolvable via Labels::getLabel) describing the
	 * rejection reason.
	 *
	 * @param string $filepath     Absolute path on disk (typically the PHP $_FILES tmp_name).
	 * @param string $declaredName Original user-supplied filename.
	 *
	 * @return string Empty string = safe; otherwise label key.
	 */
	public function scan(string $filepath, string $declaredName): string
	{
		if ($filepath === '' || !\is_readable($filepath)) {
			return 'validationFileScanFailed';
		}

		$extension = $this->getExtension($declaredName);
		if ($this->isDenyListed($extension)) {
			return 'validationFileExtensionDenied';
		}

		$detectedMime = $this->detectMime($filepath);
		if ($detectedMime === '') {
			return 'validationFileScanFailed';
		}

		if (!$this->extensionMatchesMime($extension, $detectedMime)) {
			return 'validationFileMimeMismatch';
		}

		$scannerError = $this->runTypeScanner($filepath, $declaredName, $detectedMime);
		if ($scannerError !== '') {
			return $scannerError;
		}

		return '';
	}

	/**
	 * Lowercase extension extracted from the declared filename.
	 *
	 * @param string $name Filename.
	 *
	 * @return string
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
	 * Is the extension in the deny-list (built-in plus filter overrides)?
	 *
	 * @param string $extension Lowercase extension.
	 *
	 * @return bool
	 */
	private function isDenyListed(string $extension): bool
	{
		if ($extension === '') {
			return false;
		}

		$denyList = \apply_filters( // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
			HooksHelpers::getFilterName(['validation', 'fileSecurityDenyExtensions']),
			Config::FILE_UPLOAD_DENY_EXTENSIONS
		);

		if (!\is_array($denyList)) {
			$denyList = Config::FILE_UPLOAD_DENY_EXTENSIONS;
		}

		foreach ($denyList as $denied) {
			if (\is_string($denied) && \strtolower(\ltrim($denied, '.')) === $extension) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Detect MIME via libmagic (finfo). Falls back to mime_content_type, then
	 * to an empty string when neither is available.
	 *
	 * @param string $filepath Path to file.
	 *
	 * @return string MIME type, or empty string when detection fails.
	 */
	private function detectMime(string $filepath): string
	{
		if (\class_exists(finfo::class)) {
			$finfo = new finfo(\FILEINFO_MIME_TYPE);
			$mime = @$finfo->file($filepath); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if (\is_string($mime) && $mime !== '') {
				return \strtolower($mime);
			}
		}

		if (\function_exists('mime_content_type')) {
			$mime = @\mime_content_type($filepath); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if (\is_string($mime) && $mime !== '') {
				return \strtolower($mime);
			}
		}

		return '';
	}

	/**
	 * Does the declared extension agree with the detected MIME? Cross-checks
	 * against the WordPress core MIME → extension map so that what the file
	 * says it is and what the bytes say it is have to line up.
	 *
	 * @param string $extension    Lowercase extension from the declared filename.
	 * @param string $detectedMime MIME detected from file bytes.
	 *
	 * @return bool
	 */
	private function extensionMatchesMime(string $extension, string $detectedMime): bool
	{
		if ($extension === '' || $detectedMime === '') {
			return false;
		}

		$mimeMap = \wp_get_mime_types();
		foreach ($mimeMap as $extPattern => $mime) {
			if (\strtolower($mime) !== $detectedMime) {
				continue;
			}

			foreach (\explode('|', $extPattern) as $allowedExt) {
				if (\strtolower($allowedExt) === $extension) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Dispatch to the per-type scanner whose registered MIME matches.
	 *
	 * @param string $filepath     Path to file.
	 * @param string $declaredName Original filename.
	 * @param string $detectedMime Detected MIME.
	 *
	 * @return string Empty when safe, label key on rejection.
	 */
	private function runTypeScanner(string $filepath, string $declaredName, string $detectedMime): string
	{
		foreach (self::TYPE_SCANNERS as $needle => $scannerClass) {
			if ($needle === $detectedMime || \strpos($detectedMime, $needle) === 0) {
				/**
				 * Scan with the type-specific scanner. It must return an empty string when the file is safe,
				 * or a label key describing the rejection reason.
				 *
				 * @var FileSecurityScannerInterface $scanner
				 */
				$scanner = new $scannerClass();
				return $scanner->scan($filepath, $declaredName, $detectedMime);
			}
		}

		return '';
	}
}
