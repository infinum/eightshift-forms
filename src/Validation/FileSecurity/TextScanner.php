<?php

/**
 * Plain-text security scanner. Rejects .txt uploads that contain server-side
 * script tags — a strong signal someone is trying to smuggle PHP/ASP/JSP
 * through a "plain text" allow-list.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

/**
 * Scans .txt uploads.
 */
final class TextScanner implements FileSecurityScannerInterface
{
	/**
	 * Case-insensitive substrings that should never appear in a legitimate
	 * plain-text upload.
	 *
	 * @var array<int, string>
	 */
	private const array SCRIPT_PATTERNS = [
		'<?php',
		'<?=',
		'<%@ page',
		'<%@page',
		'<jsp:',
		'#!/bin/sh',
		'#!/bin/bash',
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
		$contents = @\file_get_contents($filepath); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if (!\is_string($contents)) {
			return 'validationFileScanFailed';
		}

		foreach (self::SCRIPT_PATTERNS as $pattern) {
			if (\stripos($contents, $pattern) !== false) {
				return 'validationFileTextUnsafe';
			}
		}

		return '';
	}
}
