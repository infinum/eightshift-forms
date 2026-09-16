<?php

/**
 * Dependency-free harness for C2paManifestVerifier. The project has no PHP
 * test framework, so this asserts the verifier's behaviour directly.
 *
 * Usage: php tests/security/verify-c2pa.php [fixture_dir]
 *        defaults to tests/security/test-files
 */

declare(strict_types=1);

// Loaded directly rather than through Composer's autoloader so this runs on a
// checkout with no vendor/ present. Config has no dependencies of its own.
require __DIR__ . '/../../src/Config/Config.php';
require __DIR__ . '/../../src/Validation/FileSecurity/C2paManifestVerifier.php';

use EightshiftForms\Validation\FileSecurity\C2paManifestVerifier;

$dir = $argv[1] ?? __DIR__ . '/test-files';
$verifier = new C2paManifestVerifier();
$failures = 0;

$check = static function (string $label, string $body, bool $expected) use ($verifier, &$failures): void {
	$actual = $verifier->allEmbeddedFilesAreC2paManifests($body);

	if ($actual === $expected) {
		printf("PASS  %s\n", $label);
		return;
	}

	printf("FAIL  %s → expected %s, got %s\n", $label, var_export($expected, true), var_export($actual, true));
	$failures++;
};

// Registered C2PA content-type UUID.
$uuid = "\x63\x32\x70\x61\x00\x11\x00\x10\x80\x00\x00\xaa\x00\x38\x9b\x71";

/** Build a well-formed JUMBF superbox with optional padding. */
$manifest = static function (string $padding) use ($uuid): string {
	$inner = 'jumb' . pack('N', 30) . 'jumd' . $uuid . $padding;
	return pack('N', strlen($inner) + 4) . $inner;
};

/** Wrap a payload in a minimal PDF with a direct stream /Length. */
$wrapDirect = static fn(string $payload): string => sprintf(
	"%%PDF-1.4\n1 0 obj << /Type /FileSpec /EF << /F 2 0 R >> >> endobj\n2 0 obj << /Length %d >>\nstream\n%s\nendstream endobj\n",
	strlen($payload),
	$payload
);

/** Wrap a payload the way `qpdf --qdf` writes it: indirect /Length, /F sub-dict. */
$wrapIndirect = static fn(string $payload): string => sprintf(
	"%%PDF-1.4\n1 0 obj <<\n  /Type /FileSpec\n  /EF <<\n    /F 2 0 R\n  >>\n>> endobj\n"
	. "%%%% Original object ID: 18 0\n2 0 obj <<\n  /F <<\n    /Length %d\n    /Subtype (application/c2pa)\n  >>\n  /Length 3 0 R\n>>\nstream\n%s\nendstream endobj\n"
	. "3 0 obj\n  %d\nendobj\n",
	strlen($payload),
	$payload,
	strlen($payload)
);

echo "--- fixtures ---\n";

$fixtures = [
	'pdf-c2pa-valid.pdf' => true,
	'pdf-c2pa-mislabelled.pdf' => false,
	'pdf-c2pa-mixed.pdf' => false,
	'pdf-embedded.pdf' => false,
];

foreach ($fixtures as $file => $expected) {
	$path = $dir . '/' . $file;

	if (!is_readable($path)) {
		printf("SKIP  %s (not generated — run ./generate-test-files.sh first)\n", $file);
		continue;
	}

	$check($file, (string) file_get_contents($path), $expected);
}

echo "\n--- payload structure ---\n";
$check('minimal 32-byte manifest', $wrapDirect($manifest('')), true);
$check('manifest with trailing padding', $wrapDirect($manifest(str_repeat('P', 100))), true);
$check('PE header (MZ) in a C2PA FileSpec', $wrapDirect("MZ\x90\x00" . str_repeat('A', 60)), false);
$check('ZIP header (PK) in a C2PA FileSpec', $wrapDirect("PK\x03\x04" . str_repeat('B', 60)), false);
$check('box length field lies about payload size', $wrapDirect("\x00\x00\x00\xFFjumb" . pack('N', 30) . 'jumd' . $uuid), false);
$check('well-formed jumb/jumd but wrong UUID', $wrapDirect(pack('N', 36) . 'jumb' . pack('N', 30) . 'jumd' . str_repeat("\x41", 16)), false);
$check('payload truncated below 32 bytes', $wrapDirect(pack('N', 12) . 'jumb'), false);

echo "\n--- qpdf --qdf output shape ---\n";
// Regression guard: qpdf writes /Length as an indirect reference and puts a
// second /Length inside the /F sub-dictionary. Rejecting either shape silently
// disables the exemption on every qpdf host.
$check('indirect /Length with /F sub-dict', $wrapIndirect($manifest('')), true);
$check('indirect /Length pointing at a missing object', str_replace("3 0 obj\n  32\nendobj\n", '', $wrapIndirect($manifest(''))), false);

echo "\n--- real-world producer shapes ---\n";
// Adobe writes bare CR line endings. Looking back for "\n" alone to find the
// start of a line can stretch hundreds of bytes into binary stream data and hit
// a stray `%`, which made findObject() reject a valid Adobe file.
$crBody = str_replace("\n", "\r", $wrapDirect($manifest('')));
$check('bare CR line endings (Adobe style)', $crBody, true);

// A length object number with two or more digits. `/Length 10 0 R` matches a
// naive "direct integer" pattern by backtracking the capture to `1`, yielding a
// 1-byte payload. A single-digit sample never catches this.
$multiDigit = sprintf(
	"%%PDF-1.4\n1 0 obj << /EF << /F 2 0 R >> >> endobj\n"
	. "2 0 obj <<\n  /DL %d\n  /Params << /Size %d >>\n  /Length 10 0 R\n>>\nstream\n%s\nendstream endobj\n"
	. "10 0 obj\n  %d\nendobj\n",
	strlen($manifest('')), strlen($manifest('')), $manifest(''), strlen($manifest(''))
);
$check('indirect /Length with a 2-digit object number', $multiDigit, true);

echo "\n--- scope ---\n";
$manifestBody = $manifest('');
$mixed = "%PDF-1.4\n1 0 obj << /EF << /F 2 0 R >> >> endobj\n"
	. '2 0 obj << /Length ' . strlen($manifestBody) . " >>\nstream\n" . $manifestBody . "\nendstream endobj\n"
	. "3 0 obj << /EF << /F 4 0 R >> >> endobj\n4 0 obj << /Length 10 >>\nstream\nplain text\nendstream endobj\n";
$check('genuine manifest plus an ordinary attachment', $mixed, false);
$check('/EF holding a direct value, not a reference', "%PDF-1.4\n1 0 obj << /EF << /F (inline.txt) >> >> endobj\n", false);
$check('/EF present but object stream in document', "%PDF-1.4\n/ObjStm\n1 0 obj << /EF << /F 2 0 R >> >> endobj\n", false);

echo "\n--- live qpdf round trip ---\n";
$qpdf = trim((string) @shell_exec('command -v qpdf 2>/dev/null'));
$sample = $dir . '/pdf-c2pa-valid.pdf';

if ($qpdf === '' || !is_readable($sample)) {
	echo "SKIP  qpdf not installed or fixture missing\n";
} else {
	$expanded = (string) @shell_exec(escapeshellarg($qpdf) . ' --qdf --object-streams=disable ' . escapeshellarg($sample) . ' - 2>/dev/null');
	if ($expanded === '') {
		echo "SKIP  qpdf produced no output\n";
	} else {
		$check('fixture survives qpdf --qdf and still verifies', $expanded, true);
	}
}

printf("\n%s\n", $failures === 0 ? 'All checks passed.' : sprintf('%d check(s) failed.', $failures));
exit($failures === 0 ? 0 : 1);