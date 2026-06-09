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
	 * Returns the qpdf binary path resolved via filter, or empty string when
	 * the project hasn't wired one in.
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
	 */
	public static function isProcOpenAvailable(): bool
	{
		if (!\function_exists('proc_open')) {
			return false;
		}

		$disabled = \explode(',', (string) \ini_get('disable_functions'));
					return \array_all($disabled, fn($name): bool => \trim((string) $name) !== 'proc_open');
	}

	/**
	 * PHP extensions statuses.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function getExtensionStatuses(): array
	{
		return [
			[
				'title' => 'fileinfo',
				'subtitle' => \__('Fileinfo extension is required for file type detection.', 'eightshift-forms'),
				'status' => \extension_loaded('fileinfo'),
			],
			[
				'title' => 'zip',
				'subtitle' => \__('Zip extension is required for handling zip archives.', 'eightshift-forms'),
				'status' => \extension_loaded('zip'),
			],
			[
				'title' => 'dom',
				'subtitle' => \__('DOM extension is required for parsing file structure.', 'eightshift-forms'),
				'status' => \extension_loaded('dom'),
			],
			[
				'title' => 'gd or imagick',
				'subtitle' => \__('GD or Imagick extension is required for image processing.', 'eightshift-forms'),
				'status' => \extension_loaded('gd') || \extension_loaded('imagick'),
			],
			[
				'title' => 'proc_open()',
				'subtitle' => \__('proc_open() must be available for qpdf integration to function.', 'eightshift-forms'),
				'status' => self::isProcOpenAvailable(),
			],
			[
				'title' => 'qpdf binary',
				'subtitle' => \__('qpdf optional binary for PDF processing.', 'eightshift-forms'),
				'status' => self::getQpdfBinary() !== '',
			],
		];
	}
}
