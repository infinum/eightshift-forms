<?php

/**
 * PDF security scanner. Detects scriptable / networked / executable PDF
 * dictionary keys in both uncompressed bodies and (when qpdf is available)
 * compressed object streams.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\HooksHelpers;

/**
 * Scans PDF files for dangerous structures.
 */
final class PdfScanner implements FileSecurityScannerInterface
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
		$contents = $this->readFile($filepath);
		if ($contents === '') {
			return 'validationFileScanFailed';
		}

		if (!\str_starts_with($contents, '%PDF-')) {
			return 'validationFileMimeMismatch';
		}

		if ($this->containsDangerousKey($contents)) {
			return 'validationFilePdfUnsafe';
		}

		// Compressed object streams hide content from the raw scan. qpdf
		// expands them so the raw scan can run again on the expanded form.
		$expanded = $this->expandWithQpdf($filepath);
		if ($expanded !== null && $this->containsDangerousKey($expanded)) {
			return 'validationFilePdfUnsafe';
		}

		return '';
	}

	/**
	 * Read the whole file. PDFs that exceed PHP's memory limit are a problem
	 * regardless of content — let the caller's existing maxSize gate that.
	 *
	 * @param string $filepath Path to file.
	 *
	 * @return string Contents, or empty string on failure.
	 */
	private function readFile(string $filepath): string
	{
		$contents = @\file_get_contents($filepath); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return \is_string($contents) ? $contents : '';
	}

	/**
	 * Does the haystack contain any of the documented dangerous PDF keys?
	 *
	 * Matches the key only when it is followed by a PDF name-token delimiter
	 * (whitespace, `/`, `<`, `[`, `(`, `%`). This avoids substring-style false
	 * positives where the key appears inside a longer PDF name — most commonly
	 * a font subset prefix like `/AAAAAA+GentiumPlus` which would otherwise
	 * match `/AA`.
	 *
	 * @param string $haystack PDF bytes (raw or qpdf-expanded).
	 */
	private function containsDangerousKey(string $haystack): bool
	{
		foreach (Config::FILE_UPLOAD_PDF_DANGEROUS_KEYS as $key) {
			$pattern = '/' . \preg_quote($key, '/') . '(?=[\s\/<\[(%])/';
			if (\preg_match($pattern, $haystack) === 1) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Run qpdf to produce an uncompressed copy on stdout, so dangerous keys
	 * that live inside Flate-compressed object streams are visible to the
	 * raw scan. Returns null when qpdf is unavailable or fails.
	 *
	 * @param string $filepath Path to file.
	 *
	 * @return string|null Uncompressed PDF bytes, or null when qpdf is unavailable.
	 */
	private function expandWithQpdf(string $filepath): ?string
	{
		$useQpdf = \apply_filters(HooksHelpers::getFilterName(['validation', 'fileSecurityPdfUseQpdf']), true); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
		if ($useQpdf === false) {
			return null;
		}

		if (!\function_exists('proc_open')) {
			return null;
		}

		$binary = FileSecurityDiagnostics::getQpdfBinary();

		if ($binary === '') {
			return null;
		}

		return $this->runProcess(
			[$binary, '--qdf', '--object-streams=disable', $filepath, '-']
		);
	}

	/**
	 * Run a child process with an explicit argv (no shell), capture stdout.
	 *
	 * @param array<int, string> $argv Command and arguments.
	 *
	 * @return string|null Stdout contents, or null on failure.
	 */
	private function runProcess(array $argv): ?string
	{
		$descriptors = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];

		$pipes = [];
		$process = \proc_open($argv, $descriptors, $pipes); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_proc_open

		if (!\is_resource($process)) {
			return null;
		}

		\fclose($pipes[0]); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		\stream_set_blocking($pipes[1], false);
		\stream_set_blocking($pipes[2], false);

		$output = '';
		$errorOutput = '';

		while (!\feof($pipes[1]) || !\feof($pipes[2])) {
			$read = [];

			if (!\feof($pipes[1])) {
				$read[] = $pipes[1];
			}

			if (!\feof($pipes[2])) {
				$read[] = $pipes[2];
			}

			if ($read === []) {
				break;
			}

			$write = null;
			$except = null;
			$ready = \stream_select($read, $write, $except, null);

			if ($ready === false) {
				break;
			}

			foreach ($read as $stream) {
				$chunk = \stream_get_contents($stream);
				if (!\is_string($chunk)) {
					continue;
				}
				if ($chunk === '') {
					continue;
				}

				if ($stream === $pipes[1]) {
					$output .= $chunk;
				} else {
					$errorOutput .= $chunk;
				}
			}
		}

		\fclose($pipes[1]); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		\fclose($pipes[2]); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		$exitCode = \proc_close($process);

		return $exitCode === 0 && $output !== '' ? $output : null;
	}
}
