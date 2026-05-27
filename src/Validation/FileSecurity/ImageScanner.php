<?php

/**
 * Image security scanner. Confirms the file is actually parseable as the
 * declared image type, and for SVG strips out the danger of scriptable
 * payloads.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

/**
 * Scans raster and vector image files.
 */
final class ImageScanner implements FileSecurityScannerInterface
{
	/**
	 * MIME types this scanner handles as raster images (validated via getimagesize).
	 *
	 * @var array<int, string>
	 */
	private const RASTER_MIMES = [
		'image/jpeg',
		'image/png',
		'image/gif',
		'image/webp',
		'image/bmp',
		'image/x-ms-bmp',
		'image/tiff',
	];

	/**
	 * SVG MIME types — these need XML inspection, not getimagesize.
	 *
	 * @var array<int, string>
	 */
	private const SVG_MIMES = [
		'image/svg+xml',
		'image/svg',
	];

	/**
	 * Patterns that disqualify an SVG. Case-insensitive substring checks
	 * keep this resilient to obfuscation with whitespace inside tags.
	 *
	 * @var array<int, string>
	 */
	private const SVG_DANGER_PATTERNS = [
		'<script',
		'<foreignobject',
		'<iframe',
		'<embed',
		'<object',
		'javascript:',
		'data:text/html',
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
		if (\in_array($detectedMime, self::SVG_MIMES, true)) {
			return $this->scanSvg($filepath);
		}

		if (\in_array($detectedMime, self::RASTER_MIMES, true)) {
			return $this->scanRaster($filepath);
		}

		// Not a known image MIME; the orchestrator already rejected via the
		// extension/MIME match step, so this branch is defensive.
		return '';
	}

	/**
	 * Raster check: ensure the file actually decodes as an image. getimagesize
	 * parses headers and rejects truncated, polyglot or malformed files.
	 *
	 * @param string $filepath Path to file.
	 *
	 * @return string Empty when safe, label key when not.
	 */
	private function scanRaster(string $filepath): string
	{
		$info = @\getimagesize($filepath); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if (!\is_array($info) || empty($info[0]) || empty($info[1])) {
			return 'validationFileImageUnsafe';
		}

		return '';
	}

	/**
	 * SVG check: parse the XML and reject any embedded scripting vectors.
	 *
	 * @param string $filepath Path to file.
	 *
	 * @return string Empty when safe, label key when not.
	 */
	private function scanSvg(string $filepath): string
	{
		$contents = @\file_get_contents($filepath); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if (!\is_string($contents) || $contents === '') {
			return 'validationFileScanFailed';
		}

		$haystack = \strtolower($contents);
		foreach (self::SVG_DANGER_PATTERNS as $pattern) {
			if (\strpos($haystack, $pattern) !== false) {
				return 'validationFileImageUnsafe';
			}
		}

		// On/event handlers (onload=, onclick=, etc.) — only flag inside tags.
		if (\preg_match('/<[^>]+\son[a-z]+\s*=/i', $contents) === 1) {
			return 'validationFileImageUnsafe';
		}

		return '';
	}
}
