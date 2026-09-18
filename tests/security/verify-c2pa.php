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
require __DIR__ . '/../../src/Validation/FileSecurity/PdfTokens.php';
require __DIR__ . '/../../src/Validation/FileSecurity/C2paManifestVerifier.php';
require __DIR__ . '/c2pa-fixtures.php';

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

// Box builders live in c2pa-fixtures.php so this harness, verify-pdf-scanner.php
// and generate-test-files.sh cannot drift on what a manifest is shaped like.
$storeUuid = C2paFixtures::STORE_UUID;
$manifestUuid = C2paFixtures::MANIFEST_UUID;
$box = C2paFixtures::box(...);
$jumd = C2paFixtures::jumd(...);
$superbox = C2paFixtures::superbox(...);
$manifestBox = C2paFixtures::manifestBox(...);
$manifest = C2paFixtures::store(...);

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
	'pdf-c2pa-prefixed-zip.pdf' => false,
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
$check('complete manifest store', $wrapDirect($manifest()), true);
$check('store holding two manifest superboxes', $wrapDirect($manifest($manifestBox('urn:uuid:second'))), true);
$check('PE header (MZ) in a C2PA FileSpec', $wrapDirect("MZ\x90\x00" . str_repeat('A', 60)), false);
$check('ZIP header (PK) in a C2PA FileSpec', $wrapDirect("PK\x03\x04" . str_repeat('B', 60)), false);
$check('box length field lies about payload size', $wrapDirect("\x00\x00\x00\xFF" . substr($manifest(), 4)), false);
$check('well-formed store shape but wrong UUID', $wrapDirect($superbox(str_repeat("\x41", 16), 'c2pa', $manifestBox())), false);
$check('payload truncated below the minimum', $wrapDirect(pack('N', 12) . 'jumb'), false);

echo "\n--- box tiling ---\n";
// The check must cover every byte of the payload. Anything a box does not
// claim is where a smuggled payload lives.
$check('trailing bytes no box claims', $wrapDirect($manifest() . str_repeat('P', 100)), false);
$check('trailing bytes with the outer length widened to cover them', $wrapDirect(
	pack('N', strlen($manifest()) + 100) . substr($manifest(), 4) . str_repeat('P', 100)
), false);
$check('store with a description box but no content box', $wrapDirect($superbox($storeUuid, 'c2pa', '')), false);
$check('store child that is a leaf box, not a manifest superbox', $wrapDirect(
	$superbox($storeUuid, 'c2pa', $box('c2cl', str_repeat('C', 40)))
), false);
$check('child box overruns its parent', $wrapDirect(
	$superbox($storeUuid, 'c2pa', pack('N', 4096) . 'jumb' . str_repeat('D', 40))
), false);
$check('LBox 0 (runs to end of file)', $wrapDirect(
	$superbox($storeUuid, 'c2pa', pack('N', 0) . 'jumb' . str_repeat('E', 40))
), false);
$check('description box is not the first child', $wrapDirect(
	$box('jumb', $box('c2cl', str_repeat('F', 40)) . $jumd($storeUuid, 'c2pa'))
), false);

echo "\n--- prefixed-payload smuggling ---\n";
// The attack the tiling check exists to stop: a real archive with a plausible
// JUMBF header glued in front. unzip tolerates a prefix and extracts the member
// regardless, so "starts with a valid header" is not evidence of anything.
$zipPath = $dir . '/archive-with-exe.zip';

if (!is_readable($zipPath)) {
	echo "SKIP  archive-with-exe.zip (not generated — run ./generate-test-files.sh first)\n";
} else {
	$zip = (string) file_get_contents($zipPath);
	$check('JUMBF header glued in front of a real ZIP', $wrapDirect(C2paFixtures::prefixedBlob($zip)), false);
	$check('real ZIP parked inside a leaf box at store level', $wrapDirect($superbox($storeUuid, 'c2pa', $box('c2cl', $zip))), false);
}

echo "\n--- qpdf --qdf output shape ---\n";
// Regression guard: qpdf writes /Length as an indirect reference and puts a
// second /Length inside the /F sub-dictionary. Rejecting either shape silently
// disables the exemption on every qpdf host.
$check('indirect /Length with /F sub-dict', $wrapIndirect($manifest()), true);
$check('indirect /Length pointing at a missing object', preg_replace('/3 0 obj\n  \d+\nendobj\n/', '', $wrapIndirect($manifest())) ?? '', false);

echo "\n--- real-world producer shapes ---\n";
// Adobe writes bare CR line endings. Looking back for "\n" alone to find the
// start of a line can stretch hundreds of bytes into binary stream data and hit
// a stray `%`, which made object lookup reject a valid Adobe file.
$check('bare CR line endings (Adobe style)', str_replace("\n", "\r", $wrapDirect($manifest())), true);

// A length object number with two or more digits. `/Length 10 0 R` matches a
// naive "direct integer" pattern by backtracking the capture to `1`, yielding a
// 1-byte payload. A single-digit sample never catches this.
$multiDigit = sprintf(
	"%%PDF-1.4\n1 0 obj << /EF << /F 2 0 R >> >> endobj\n"
	. "2 0 obj <<\n  /DL %d\n  /Params << /Size %d >>\n  /Length 10 0 R\n>>\nstream\n%s\nendstream endobj\n"
	. "10 0 obj\n  %d\nendobj\n",
	strlen($manifest()),
	strlen($manifest()),
	$manifest(),
	strlen($manifest())
);
$check('indirect /Length with a 2-digit object number', $multiDigit, true);

// A `%` byte inside binary stream data is not a comment. Treating it as one
// hid every object definition between it and the next line break, which
// rejected genuine manifests purely on what their other streams contained.
$binary = str_repeat("\x41", 30) . '%' . str_repeat("\x42", 30);
$strayPercent = sprintf(
	"%%PDF-1.4\n1 0 obj << /EF << /F 2 0 R >> >> endobj\n"
	. "8 0 obj << /Length %d >> stream\n%s endstream endobj "
	. "2 0 obj << /Length %d >> stream\n%s\nendstream endobj\n",
	strlen($binary),
	$binary,
	strlen($manifest()),
	$manifest()
);
$check('stray % inside a preceding binary stream', $strayPercent, true);

echo "\n--- scope ---\n";
$mixed = "%PDF-1.4\n1 0 obj << /EF << /F 2 0 R >> >> endobj\n"
	. '2 0 obj << /Length ' . strlen($manifest()) . " >>\nstream\n" . $manifest() . "\nendstream endobj\n"
	. "3 0 obj << /EF << /F 4 0 R >> >> endobj\n4 0 obj << /Length 10 >>\nstream\nplain text\nendstream endobj\n";
$check('genuine manifest plus an ordinary attachment', $mixed, false);
$check('/EF holding a direct value, not a reference', "%PDF-1.4\n1 0 obj << /EF << /F (inline.txt) >> >> endobj\n", false);

// The stream lookup is bounded to the referenced object. Unbounded, an /EF
// pointing at an object that carries no stream walks forward and verifies
// against an unrelated object's payload.
$check('/EF pointing at an object that carries no stream', sprintf(
	"%%PDF-1.4\n1 0 obj << /EF << /F 2 0 R >> >> endobj\n"
	. "2 0 obj << /Type /Metadata >> endobj\n"
	. "9 0 obj << /Length %d >>\nstream\n%s\nendstream endobj\n",
	strlen($manifest()),
	$manifest()
), false);
$check('/EF present but object stream in document', "%PDF-1.4\n/ObjStm\n1 0 obj << /EF << /F 2 0 R >> >> endobj\n", false);

echo "\n--- reference resolution ---\n";
// PDF lets any value be written as an indirect reference, and qpdf keeps one
// indirect. Reading only the direct `/EF << ... >>` shape left whatever the
// other shape pointed at unverified while a reader still extracted it.
$smuggled = "PK\x03\x04" . str_repeat('S', 60);

$indirectEf = static fn(string $payload): string => sprintf(
	"%%PDF-1.4\n1 0 obj << /Type /Filespec /EF << /F 2 0 R >> >> endobj\n"
	. "2 0 obj << /Length %d >>\nstream\n%s\nendstream endobj\n"
	. "3 0 obj << /Type /Filespec /EF 4 0 R >> endobj\n"
	. "4 0 obj << /F 5 0 R >> endobj\n"
	. "5 0 obj << /Length %d >>\nstream\n%s\nendstream endobj\n",
	strlen($manifest()),
	$manifest(),
	strlen($payload),
	$payload
);

$check('indirect /EF beside an inline manifest', $indirectEf($smuggled), false);
$check('indirect /EF pointing at a genuine manifest', $indirectEf($manifest()), true);
$check('/EF value that is neither a dictionary nor a reference', "%PDF-1.4\n1 0 obj << /EF true >> endobj\n", false);

echo "\n--- stream extent ---\n";
// The verified slice has to be the bytes a reader extracts. Each shape below
// puts a second /Length where a first-match read finds it, leaving the real
// stream longer than the tree that was checked.
$check('decoy /Length inside a string value', sprintf(
	"%%PDF-1.4\n1 0 obj << /EF << /F 2 0 R >> >> endobj\n"
	. "2 0 obj << /Desc (/Length %d ) /Length %d >>\nstream\n%s\nendstream endobj\n",
	strlen($manifest()),
	strlen($manifest()) + strlen($smuggled),
	$manifest() . $smuggled
), false);

$check('/Length left behind by a nested dict in /F', sprintf(
	"%%PDF-1.4\n1 0 obj << /EF << /F 2 0 R >> >> endobj\n"
	. "2 0 obj << /F << /Params << /Size 1 >> /Length %d >> /Length %d >>\nstream\n%s\nendstream endobj\n",
	strlen($manifest()),
	strlen($manifest()) + strlen($smuggled),
	$manifest() . $smuggled
), false);

// The dictionary is walked with balanced brackets, so neither of these ends
// it early and hides the /Length that follows.
$check('>> spelled inside a string value', sprintf(
	"%%PDF-1.4\n1 0 obj << /EF << /F 2 0 R >> >> endobj\n"
	. "2 0 obj << /Desc (>> stream) /Length %d >>\nstream\n%s\nendstream endobj\n",
	strlen($manifest()),
	$manifest()
), true);

$check('name value spelled /stream', sprintf(
	"%%PDF-1.4\n1 0 obj << /EF << /F 2 0 R >> >> endobj\n"
	. "2 0 obj << /Desc /stream\n  /Length %d >>\nstream\n%s\nendstream endobj\n",
	strlen($manifest()),
	$manifest()
), true);

echo "\n--- shadow objects ---\n";
// qpdf writes every object exactly once, so a second definition is either a
// damaged body or a decoy planted inside another object's payload — which a
// "later definition wins" lookup would have verified in place of the object a
// reader resolves through the xref.
$shadow = sprintf("2 0 obj << /Length %d >>\nstream\n%s\nendstream endobj\n", strlen($manifest()), $manifest());
$check('second definition planted in another stream payload', sprintf(
	"%%PDF-1.4\n1 0 obj << /EF << /F 2 0 R >> >> endobj\n"
	. "2 0 obj << /Length %d >>\nstream\n%s\nendstream endobj\n"
	. "9 0 obj << /Length %d >>\nstream\n%s\nendstream endobj\n",
	strlen($smuggled),
	$smuggled,
	strlen($shadow),
	$shadow
), false);

echo "\n--- cost ---\n";
// Guard against the object lookup going quadratic again. Resolving each
// reference used to rescan the whole body, so cost grew with
// (references x body size) and a large upload burned CPU for tens of seconds.
$many = "%PDF-1.4\n";
$payload = $manifest();

for ($i = 1; $i <= 500; $i++) {
	$many .= sprintf("%d 0 obj << /Type /FileSpec /EF << /F %d 0 R >> >> endobj\n", $i, 500 + $i);
}

$many .= '% ' . str_repeat('X', 4 * 1024 * 1024) . "\n";

// Indirect /Length, the shape qpdf --qdf writes, so this covers both the
// reference lookup and the length lookup.
for ($i = 1; $i <= 500; $i++) {
	$many .= sprintf("%d 0 obj <<\n  /Length %d 0 R\n>>\nstream\n%s\nendstream endobj\n", 500 + $i, 2000 + $i, $payload);
	$many .= sprintf("%d 0 obj\n  %d\nendobj\n", 2000 + $i, strlen($payload));
}

$started = microtime(true);
$verdict = $verifier->allEmbeddedFilesAreC2paManifests($many);
$elapsed = microtime(true) - $started;

if ($verdict === true && $elapsed < 0.5) {
	printf("PASS  500 references over a 4 MB body in %.2fs\n", $elapsed);
} else {
	printf("FAIL  500 references over a 4 MB body → %s in %.2fs (want true under 0.50s)\n", var_export($verdict, true), $elapsed);
	$failures++;
}

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
