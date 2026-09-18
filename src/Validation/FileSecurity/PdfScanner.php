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
use EightshiftForms\Labels\Labels;

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
			return Labels::LABEL_VALIDATION_FILE_SCAN_FAILED;
		}

		if (!\str_starts_with($contents, '%PDF-')) {
			return Labels::LABEL_VALIDATION_FILE_MIME_MISMATCH;
		}

		$raw = $this->assessBody($contents, false);

		if ($raw === PdfVerdict::Unsafe) {
			return Labels::LABEL_VALIDATION_FILE_PDF_UNSAFE;
		}

		// Compressed object streams hide content from the raw scan. qpdf
		// expands them so the raw scan can run again on the expanded form.
		$expanded = $this->expandWithQpdf($filepath);

		if ($expanded === null) {
			// Fail closed: an undetermined body stays rejected when there is
			// no qpdf to expand it. The exemption is a convenience, and a
			// host without qpdf can neither see inside object streams nor
			// resolve references the way a PDF reader would.
			return $raw === PdfVerdict::Undetermined ? Labels::LABEL_VALIDATION_FILE_PDF_UNSAFE : '';
		}

		// Only an outright "safe" passes. Still undetermined after expansion
		// means qpdf left object streams in place, which is the same
		// fail-closed case.
		return $this->assessBody($expanded, true) === PdfVerdict::Safe ? '' : Labels::LABEL_VALIDATION_FILE_PDF_UNSAFE;
	}

	/**
	 * Assess a body for dangerous keys not covered by the Content
	 * Credentials exemption.
	 *
	 * @param string $body     PDF bytes.
	 * @param bool   $expanded Whether these bytes came from qpdf.
	 */
	private function assessBody(string $body, bool $expanded): PdfVerdict
	{
		$matched = $this->getMatchedKeys($body);

		if ($matched === []) {
			return PdfVerdict::Safe;
		}

		// Any key outside the embedded-file pair is unsafe on sight, and
		// expanding object streams cannot make it go away.
		if (\array_diff($matched, ['/EmbeddedFile', '/EmbeddedFiles']) !== []) {
			return PdfVerdict::Unsafe;
		}

		if (!$this->c2paExemptionEnabled()) {
			return PdfVerdict::Unsafe;
		}

		// The exemption is granted on qpdf output only. A raw body resolves
		// `2 0 R` by reading the file top to bottom; a PDF reader resolves it
		// through the xref table. An attacker controls both, so a body can
		// define an object twice and show the verifier the manifest while the
		// reader extracts the other one. qpdf resolves through the xref and
		// writes each object once, which removes the gap between the two.
		if (!$expanded) {
			return PdfVerdict::Undetermined;
		}

		$verifier = new C2paManifestVerifier();

		// qpdf was asked to disable object streams, so this should not fire.
		// It stays because the exemption must never vouch for bytes it cannot
		// see, whatever qpdf did.
		if ($verifier->containsObjectStreams($body)) {
			return PdfVerdict::Undetermined;
		}

		return $verifier->allEmbeddedFilesAreC2paManifests($body) ? PdfVerdict::Safe : PdfVerdict::Unsafe;
	}

	/**
	 * Has this site opted in to the Content Credentials exemption?
	 *
	 * Off unless a site explicitly enables it. Compared with `=== true` so a
	 * truthy non-boolean filter return — `1`, `'yes'` — fails closed rather
	 * than quietly switching the exemption on.
	 */
	private function c2paExemptionEnabled(): bool
	{
		$allow = \apply_filters(HooksHelpers::getFilterName(['validation', 'fileSecurityPdfAllowC2pa']), false); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase

		return $allow === true;
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
	 * Which dangerous PDF keys does this body contain?
	 *
	 * Presence is decided by PdfTokens so this and C2paManifestVerifier cannot
	 * drift apart on what "present" means.
	 *
	 * @param string $haystack PDF bytes (raw or qpdf-expanded).
	 *
	 * @return array<int, string> Matched keys, in Config order.
	 */
	private function getMatchedKeys(string $haystack): array
	{
		$keys = \apply_filters( // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
			HooksHelpers::getFilterName(['validation', 'fileSecurityPdfDangerousKeys']),
			Config::FILE_UPLOAD_PDF_DANGEROUS_KEYS
		);

		if (!\is_array($keys)) {
			$keys = Config::FILE_UPLOAD_PDF_DANGEROUS_KEYS;
		}

		$matched = [];

		foreach ($keys as $key) {
			if (!\is_string($key) || $key === '') {
				continue;
			}

			if (PdfTokens::contains($haystack, $key)) {
				$matched[] = $key;
			}
		}

		return $matched;
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
			[$binary, '--qdf', '--object-streams=disable', $filepath, '-'],
			// qpdf exits 0 on a clean run and 3 when it produced output but
			// had something to say about the input — a missing startxref it
			// reconstructed, a repaired page tree. Real PDFs hit that
			// constantly, and the expansion is still faithful, so treating 3
			// as failure would leave object streams unexamined on a large
			// share of uploads and withhold the Content Credentials exemption
			// from files that deserve it. Exit 2 is errors, where the output
			// cannot be trusted.
			[0, 3]
		);
	}

	/**
	 * Run a child process with an explicit argv (no shell), capture stdout.
	 *
	 * @param array<int, string> $argv         Command and arguments.
	 * @param array<int, int>    $successCodes Exit codes whose stdout is usable.
	 *
	 * @return string|null Stdout contents, or null on failure.
	 */
	private function runProcess(array $argv, array $successCodes): ?string
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

		return \in_array($exitCode, $successCodes, true) && $output !== '' ? $output : null;
	}
}
