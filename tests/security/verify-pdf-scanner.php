<?php

/**
 * Dependency-free harness for PdfScanner. verify-c2pa.php covers the payload
 * verifier in isolation; this covers the decision the scanner actually makes —
 * which keys reject, when the Content Credentials exemption applies, and what
 * happens when qpdf cannot be reached.
 *
 * Usage: php tests/security/verify-pdf-scanner.php [fixture_dir]
 *        defaults to tests/security/test-files
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers {

	/**
	 * Stand-in for the real helper, which reads the plugin's public filter map
	 * out of a global WordPress constant. Only the name has to be stable and
	 * unique per filter for this harness.
	 */
	class HooksHelpers
	{
		/**
		 * Build a filter name from its parts.
		 *
		 * @param array<int, string> $names        Filter name parts.
		 * @param array<mixed>       $data         Unused here.
		 * @param string             $filterPrefix Unused here.
		 */
		public static function getFilterName(array $names, array $data = [], string $filterPrefix = ''): string
		{
			return 'es_forms_' . \implode('_', $names);
		}
	}
}

namespace {

	// Loaded directly rather than through Composer's autoloader so this runs on
	// a checkout with no vendor/ present. HooksHelpers above is the only stub;
	// everything below is the shipping code.
	require __DIR__ . '/../../src/Config/Config.php';
	require __DIR__ . '/../../src/Labels/Labels.php';
	require __DIR__ . '/../../src/Validation/FileSecurity/FileSecurityScannerInterface.php';
	require __DIR__ . '/../../src/Validation/FileSecurity/FileSecurityDiagnostics.php';
	require __DIR__ . '/../../src/Validation/FileSecurity/PdfTokens.php';
	require __DIR__ . '/../../src/Validation/FileSecurity/C2paManifestVerifier.php';
	require __DIR__ . '/../../src/Validation/FileSecurity/PdfScanner.php';

	use EightshiftForms\Labels\Labels;
	use EightshiftForms\Validation\FileSecurity\FileSecurityDiagnostics;
	use EightshiftForms\Validation\FileSecurity\PdfScanner;

	$GLOBALS['esFilters'] = [];

	/**
	 * Minimal apply_filters: returns whatever the current scenario registered
	 * for this filter name, otherwise the default the caller passed.
	 *
	 * @param string $name  Filter name.
	 * @param mixed  $value Default value.
	 * @param mixed  ...$args Unused here.
	 *
	 * @return mixed Filtered value.
	 */
	function apply_filters(string $name, $value, ...$args) // phpcs:ignore
	{
		return \array_key_exists($name, $GLOBALS['esFilters']) ? $GLOBALS['esFilters'][$name] : $value;
	}

	$dir = $argv[1] ?? __DIR__ . '/test-files';
	$failures = 0;

	$allowC2pa = ['es_forms_validation_fileSecurityPdfAllowC2pa' => true];
	$noQpdf = ['es_forms_validation_fileSecurityPdfUseQpdf' => false];
	$hasQpdf = FileSecurityDiagnostics::getQpdfBinary() !== '';

	/** Run a scan under a given set of filter overrides. */
	$scan = static function (string $path, array $filters): string {
		$GLOBALS['esFilters'] = $filters;

		try {
			return (new PdfScanner())->scan($path, \basename($path), 'application/pdf');
		} finally {
			$GLOBALS['esFilters'] = [];
		}
	};

	$check = static function (string $label, string $actual, string $expected) use (&$failures): void {
		if ($actual === $expected) {
			printf("PASS  %s\n", $label);
			return;
		}

		printf(
			"FAIL  %s → expected %s, got %s\n",
			$label,
			$expected === '' ? 'accepted' : $expected,
			$actual === '' ? 'accepted' : $actual
		);
		$failures++;
	};

	/** Scan a fixture, or report a skip when it has not been generated. */
	$checkFixture = static function (string $label, string $file, array $filters, string $expected) use ($dir, $scan, $check): void {
		$path = $dir . '/' . $file;

		if (!is_readable($path)) {
			printf("SKIP  %s (%s not generated — run ./generate-test-files.sh first)\n", $label, $file);
			return;
		}

		$check($label, $scan($path, $filters), $expected);
	};

	$unsafe = Labels::LABEL_VALIDATION_FILE_PDF_UNSAFE;

	echo "--- dangerous keys, exemption off (the default) ---\n";
	$checkFixture('clean PDF is accepted', 'clean.pdf', [], '');
	$checkFixture('/JavaScript rejects', 'pdf-javascript.pdf', [], $unsafe);
	$checkFixture('/Launch rejects', 'pdf-launch.pdf', [], $unsafe);
	$checkFixture('/EmbeddedFile rejects', 'pdf-embedded.pdf', [], $unsafe);

	// The exemption must stay off until a site asks for it. Every other check in
	// this file is worth less than this one.
	$checkFixture('genuine C2PA manifest still rejects by default', 'pdf-c2pa-valid.pdf', [], $unsafe);

	echo "\n--- exemption on ---\n";

	if ($hasQpdf) {
		$checkFixture('genuine C2PA manifest is accepted', 'pdf-c2pa-valid.pdf', $allowC2pa, '');
	} else {
		echo "SKIP  genuine C2PA manifest is accepted (qpdf not installed)\n";
	}

	$checkFixture('clean PDF is still accepted', 'clean.pdf', $allowC2pa, '');

	// Correct /AFRelationship and /Subtype labels, executable bytes behind them.
	$checkFixture('mislabelled payload rejects', 'pdf-c2pa-mislabelled.pdf', $allowC2pa, $unsafe);
	$checkFixture('JUMBF header glued onto a real ZIP rejects', 'pdf-c2pa-prefixed-zip.pdf', $allowC2pa, $unsafe);
	$checkFixture('manifest plus an ordinary attachment rejects', 'pdf-c2pa-mixed.pdf', $allowC2pa, $unsafe);

	// The exemption covers the embedded-file keys and nothing else. A manifest
	// sitting next to /OpenAction and /JavaScript is still an unsafe PDF.
	$checkFixture('manifest plus JavaScript rejects', 'pdf-c2pa-plus-js.pdf', $allowC2pa, $unsafe);

	echo "\n--- exemption needs qpdf ---\n";
	// The verifier resolves `2 0 R` by reading the body top to bottom; a PDF
	// reader resolves it through the xref. Only qpdf output closes that gap, so
	// a body the scanner cannot expand never earns the exemption.
	$checkFixture('genuine manifest rejects when qpdf is unreachable', 'pdf-c2pa-valid.pdf', $allowC2pa + $noQpdf, $unsafe);

	echo "\n--- shadow objects ---\n";
	// Object 2 is defined twice. The xref points at the ZIP, which is what a
	// reader extracts; a top-to-bottom read finds the manifest appended after
	// it. Accepting this file was the bug the qpdf requirement above fixes.
	$shadow = (static function (): string {
		$box = static fn(string $type, string $contents): string => pack('N', strlen($contents) + 8) . $type . $contents;
		$storeUuid = "\x63\x32\x70\x61\x00\x11\x00\x10\x80\x00\x00\xaa\x00\x38\x9b\x71";
		$manifestUuid = "\x63\x32\x6d\x61\x00\x11\x00\x10\x80\x00\x00\xaa\x00\x38\x9b\x71";
		$jumd = static fn(string $uuid, string $label): string => $box('jumd', $uuid . "\x03" . $label . "\x00");
		$superbox = static fn(string $uuid, string $label, string $contents): string
			=> $box('jumb', $jumd($uuid, $label) . $contents);
		$manifest = $superbox($storeUuid, 'c2pa', $superbox($manifestUuid, 'urn:uuid:test', $box('c2cl', '{"claim":"x"}')));
		$zip = "PK\x03\x04" . str_repeat("\x41", 200);

		$body = "%PDF-1.4\n";
		$offsets = [];

		$offsets[1] = strlen($body);
		$body .= "1 0 obj << /Type /Catalog /Pages 5 0 R /Names << /EmbeddedFiles << /Names [(cc) 4 0 R] >> >> >> endobj\n";

		// The definition the xref points at.
		$offsets[2] = strlen($body);
		$body .= sprintf("2 0 obj << /Length %d >>\nstream\n%s\nendstream\nendobj\n", strlen($zip), $zip);

		// The definition a top-to-bottom read lands on instead.
		$body .= sprintf("2 0 obj << /Length %d >>\nstream\n%s\nendstream\nendobj\n", strlen($manifest), $manifest);

		$offsets[4] = strlen($body);
		$body .= "4 0 obj << /Type /FileSpec /F (cc) /EF << /F 2 0 R >> >> endobj\n";
		$offsets[5] = strlen($body);
		$body .= "5 0 obj << /Type /Pages /Kids [6 0 R] /Count 1 >> endobj\n";
		$offsets[6] = strlen($body);
		$body .= "6 0 obj << /Type /Page /Parent 5 0 R /MediaBox [0 0 612 792] >> endobj\n";

		$startxref = strlen($body);
		$body .= "xref\n0 7\n0000000000 65535 f \n";

		for ($i = 1; $i <= 6; $i++) {
			$body .= sprintf("%010d 00000 %s \n", $offsets[$i] ?? 0, isset($offsets[$i]) ? 'n' : 'f');
		}

		return $body . "trailer << /Size 7 /Root 1 0 R >>\nstartxref\n{$startxref}\n%%EOF\n";
	})();

	$shadowPath = (string) tempnam(sys_get_temp_dir(), 'es-shadow-') . '.pdf';
	file_put_contents($shadowPath, $shadow);
	$check('object defined twice, xref pointing at the ZIP', $scan($shadowPath, $allowC2pa), $unsafe);
	@unlink($shadowPath);

	echo "\n--- non-PDF input ---\n";
	$checkFixture('bytes that are not a PDF', 'mime-mismatch.pdf', [], Labels::LABEL_VALIDATION_FILE_MIME_MISMATCH);
	$check('unreadable file', $scan($dir . '/does-not-exist.pdf', []), Labels::LABEL_VALIDATION_FILE_SCAN_FAILED);

	printf("\n%s\n", $failures === 0 ? 'All checks passed.' : sprintf('%d check(s) failed.', $failures));
	exit($failures === 0 ? 0 : 1);
}
