<?php

/**
 * Read-only diagnostics for the file security scanner stack. Used by the
 * admin settings page to surface whether qpdf, PHP extensions and the
 * external AV filter are wired up correctly.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

use EightshiftForms\Helpers\HooksHelpers;

/**
 * Diagnostics for the file security stack.
 */
final class FileSecurityDiagnostics
{
	/**
	 * PHP extensions required for the structural scanners to function.
	 * `gd` or `imagick` is required — checked separately.
	 *
	 * @var array<int, string>
	 */
	private const REQUIRED_EXTENSIONS = ['fileinfo', 'zip', 'dom'];

	/**
	 * Returns the qpdf binary path resolved via filter, or empty string when
	 * the project hasn't wired one in.
	 *
	 * @return string
	 */
	public static function getQpdfBinary(): string
	{
		$binary = \apply_filters(HooksHelpers::getFilterName(['validation', 'fileSecurityPdfQpdfBinary']), ''); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase

		if (\is_string($binary) && $binary !== '' && \is_executable($binary)) {
			return $binary;
		}

		$candidates = [
			'/usr/bin/qpdf',
			'/usr/local/bin/qpdf',
			'/opt/homebrew/bin/qpdf',
		];

		foreach ($candidates as $candidate) {
			if (\is_executable($candidate)) {
				return $candidate;
			}
		}

		return '';
	}

	/**
	 * Is proc_open available? Required for qpdf invocation.
	 *
	 * @return bool
	 */
	public static function isProcOpenAvailable(): bool
	{
		if (!\function_exists('proc_open')) {
			return false;
		}

		$disabled = \explode(',', (string) \ini_get('disable_functions'));
		foreach ($disabled as $name) {
			if (\trim($name) === 'proc_open') {
				return false;
			}
		}

		return true;
	}

	/**
	 * PHP extensions that are required but missing.
	 *
	 * @return array<int, string>
	 */
	public static function getMissingExtensions(): array
	{
		$missing = [];
		foreach (self::REQUIRED_EXTENSIONS as $ext) {
			if (!\extension_loaded($ext)) {
				$missing[] = $ext;
			}
		}

		// Need at least one of gd / imagick for raster image validation.
		if (!\extension_loaded('gd') && !\extension_loaded('imagick')) {
			$missing[] = 'gd or imagick';
		}

		return $missing;
	}
}
