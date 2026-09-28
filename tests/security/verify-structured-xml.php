<?php

/**
 * Dependency-free harness for the structured-XML (Europass) exemption.
 * verify-c2pa.php covers the shared structural walk with the C2PA validator;
 * this covers the XML validator, the file-specification names it depends
 * on, and the decision PdfScanner makes with it enabled.
 *
 * Usage: php tests/security/verify-structured-xml.php
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
	require __DIR__ . '/../../src/Validation/FileSecurity/PdfStrings.php';
	require __DIR__ . '/../../src/Validation/FileSecurity/PdfVerdict.php';
	require __DIR__ . '/../../src/Validation/FileSecurity/EmbeddedPayloadValidatorInterface.php';
	require __DIR__ . '/../../src/Validation/FileSecurity/C2paPayloadValidator.php';
	require __DIR__ . '/../../src/Validation/FileSecurity/StructuredXmlPayloadValidator.php';
	require __DIR__ . '/../../src/Validation/FileSecurity/EmbeddedFileVerifier.php';
	require __DIR__ . '/../../src/Validation/FileSecurity/PdfScanner.php';
	require __DIR__ . '/c2pa-fixtures.php';
	require __DIR__ . '/structured-xml-fixtures.php';

	use EightshiftForms\Config\Config;
	use EightshiftForms\Labels\Labels;
	use EightshiftForms\Validation\FileSecurity\C2paPayloadValidator;
	use EightshiftForms\Validation\FileSecurity\EmbeddedFileVerifier;
	use EightshiftForms\Validation\FileSecurity\FileSecurityDiagnostics;
	use EightshiftForms\Validation\FileSecurity\PdfScanner;
	use EightshiftForms\Validation\FileSecurity\StructuredXmlPayloadValidator;

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

	$failures = 0;
	$xml = new EmbeddedFileVerifier([new StructuredXmlPayloadValidator()]);
	$both = new EmbeddedFileVerifier([new C2paPayloadValidator(), new StructuredXmlPayloadValidator()]);

	$check = static function (string $label, bool $actual, bool $expected) use (&$failures): void {
		if ($actual === $expected) {
			printf("PASS  %s\n", $label);
			return;
		}

		printf("FAIL  %s → expected %s, got %s\n", $label, var_export($expected, true), var_export($actual, true));
		$failures++;
	};

	$accepts = static fn(string $body): bool => $xml->allEmbeddedFilesAccepted($body);

	$europass = StructuredXmlFixtures::europass(...);
	$pdf = StructuredXmlFixtures::europassPdf(...);

	echo "--- accepted shapes ---\n";
	$check('Europass export shape (/F, indirect /EF, no labels)', $accepts($pdf($europass())), true);
	$check('legacy SkillsPassport under its own name', $accepts($pdf(
		StructuredXmlFixtures::skillsPassport(),
		'/F (Europass-XML-Attachment.xml)'
	)), true);
	$check('/F and /UF agreeing', $accepts($pdf($europass(), '/F (attachment.xml) /UF (attachment.xml)')), true);
	$check('/UF alone, UTF-16BE hex with BOM', $accepts($pdf(
		$europass(),
		'/UF <FEFF' . strtoupper(bin2hex(mb_convert_encoding('attachment.xml', 'UTF-16BE', 'UTF-8'))) . '>'
	)), true);
	$check('/F with an octal escape for the dot', $accepts($pdf($europass(), '/F (attachment\\056xml)')), true);
	$check('UTF-8 BOM and a trailing comment after the root', $accepts($pdf("\xEF\xBB\xBF" . $europass() . "<!-- exported -->\n")), true);
	$check('base64 photo inside the document', $accepts($pdf($europass(
		'<Photo>' . base64_encode(random_bytes(3000)) . '</Photo>'
	))), true);
	$check('Filespec written inline in a catalog /AF array', $accepts(StructuredXmlFixtures::pdf(
		[7 => StructuredXmlFixtures::stream($europass())],
		'',
		'/AF [<< /Type /Filespec /F (attachment.xml) /AFRelationship /Data /EF << /F 7 0 R >> >>] '
	)), true);

	// Brackets spelled inside a string are not structure. Read as structure,
	// the `>>` closes the Filespec early and loses track of which dictionary
	// owns the /EF.
	$check('>> spelled inside a string in an inline Filespec', $accepts(StructuredXmlFixtures::pdf(
		[7 => StructuredXmlFixtures::stream($europass())],
		'',
		'/AF [<< /Type /Filespec /Desc (CV >> export) /F (attachment.xml) /EF << /F 7 0 R >> >>] '
	)), true);

	echo "\n--- file specification names ---\n";
	$check('name not on the allowlist', $accepts($pdf($europass(), '/F (cv.xml)')), false);
	$check('allowlisted name in the wrong case', $accepts($pdf($europass(), '/F (Attachment.xml)')), false);
	$check('allowlisted name behind a path', $accepts($pdf($europass(), '/F (x/attachment.xml)')), false);
	$check('no name at all', $accepts($pdf($europass(), '')), false);
	$check('/F and /UF disagreeing', $accepts($pdf($europass(), '/F (attachment.xml) /UF (payload.exe)')), false);
	// Readers disagree on which duplicate wins, so the allowlisted one goes
	// first: a first-match read would verify it while a reader saves the other.
	$check('/F given twice', $accepts($pdf($europass(), '/F (attachment.xml) /F (payload.exe)')), false);
	$check('non-ASCII name', $accepts($pdf($europass(), "/F (attachm\xC3\xA9nt.xml)")), false);
	$check('name written as an indirect reference', $accepts($pdf($europass(), '/F 9 0 R')), false);

	// One stream, two Filespecs: verified as attachment.xml, saved as payload.exe.
	$check('one stream reached under two names', $accepts(StructuredXmlFixtures::pdf(
		[
			5 => '<< /Type /Filespec /F (attachment.xml) /EF << /F 7 0 R >> >>',
			6 => '<< /Type /Filespec /F (payload.exe) /EF << /F 7 0 R >> >>',
			7 => StructuredXmlFixtures::stream($europass()),
		],
		'(attachment.xml) 5 0 R (payload.exe) 6 0 R'
	)), false);

	echo "\n--- document identity ---\n";
	$check('right name, SettingContent-ms root', $accepts($pdf(
		'<?xml version="1.0" encoding="UTF-8"?><PCSettings><SearchableContent xmlns="http://schemas.microsoft.com/Search/2013/SettingContent">'
		. '<ApplicationInformation><DeepLink>%windir%\\system32\\cmd.exe</DeepLink></ApplicationInformation></SearchableContent></PCSettings>'
	)), false);
	$check('right root, wrong namespace', $accepts($pdf('<?xml version="1.0"?><Candidate xmlns="http://example.com/evil"/>')), false);
	$check('right root, no namespace', $accepts($pdf('<?xml version="1.0"?><Candidate/>')), false);
	$check('prefixed root bound to another namespace', $accepts($pdf('<?xml version="1.0"?><x:Candidate xmlns:x="http://example.com/evil" xmlns="http://www.europass.eu/1.0"/>')), false);
	$check('prefixed root bound to the right namespace', $accepts($pdf('<?xml version="1.0"?><e:Candidate xmlns:e="http://www.europass.eu/1.0"/>')), true);
	$check('legacy root under the current name', $accepts($pdf(StructuredXmlFixtures::skillsPassport())), false);

	echo "\n--- payload bytes ---\n";
	$check('DOCTYPE', $accepts($pdf("<?xml version=\"1.0\"?>\n<!DOCTYPE Candidate [<!ENTITY a \"aaaa\">]>\n<Candidate xmlns=\"http://www.europass.eu/1.0\">&a;</Candidate>")), false);
	$check('external entity (XXE)', $accepts($pdf("<?xml version=\"1.0\"?>\n<!doctype Candidate SYSTEM \"file:///etc/passwd\">\n<Candidate xmlns=\"http://www.europass.eu/1.0\"/>")), false);
	$check('trailing bytes after the root', $accepts($pdf($europass() . 'PK' . str_repeat('A', 60))), false);
	$check('second root element', $accepts($pdf($europass() . '<Candidate xmlns="http://www.europass.eu/1.0"/>')), false);
	$check('unclosed root', $accepts($pdf(substr($europass(), 0, -13))), false);
	$check('invalid UTF-8', $accepts($pdf($europass("<Note>\xC3\x28</Note>"))), false);
	$check('control byte', $accepts($pdf($europass("<Note>\x01</Note>"))), false);
	// DEL is legal XML 1.0, so this one is the byte check alone and not libxml.
	$check('DEL byte, which XML itself allows', $accepts($pdf($europass("<Note>\x7F</Note>"))), false);
	$check('PE executable under an allowlisted name', $accepts($pdf("MZ\x90\x00" . str_repeat("\x00", 60))), false);
	$check('ZIP under an allowlisted name', $accepts($pdf("PK\x03\x04" . str_repeat('B', 60))), false);
	$check('empty payload', $accepts($pdf('')), false);
	$check('stream still filtered', $accepts($pdf($europass(), '/F (attachment.xml)', '', '/Filter /FlateDecode ')), false);

	echo "\n--- scope ---\n";
	$check('valid document plus an ordinary attachment', $accepts(StructuredXmlFixtures::pdf(
		[
			5 => '<< /Type /Filespec /F (attachment.xml) /EF << /F 7 0 R >> >>',
			6 => '<< /Type /Filespec /F (notes.txt) /EF << /F 8 0 R >> >>',
			7 => StructuredXmlFixtures::stream($europass()),
			8 => StructuredXmlFixtures::stream('plain text'),
		],
		'(attachment.xml) 5 0 R (notes.txt) 6 0 R'
	)), false);
	$check('valid document plus /RF', $accepts($pdf($europass(), '/F (attachment.xml) /RF << /F [ (evil.zip) 7 0 R ] >>')), false);
	$check('C2PA manifest named attachment.xml, XML validator only', $accepts($pdf(C2paFixtures::store())), false);

	$mixed = StructuredXmlFixtures::pdf(
		[
			5 => '<< /Type /Filespec /F (attachment.xml) /EF << /F 7 0 R >> >>',
			6 => '<< /Type /Filespec /F (manifest.c2pa) /AFRelationship /C2PA_Manifest /EF << /F 8 0 R >> >>',
			7 => StructuredXmlFixtures::stream($europass()),
			8 => StructuredXmlFixtures::stream(C2paFixtures::store()),
		],
		'(attachment.xml) 5 0 R (manifest.c2pa) 6 0 R'
	);
	$check('Europass plus C2PA, XML validator only', $accepts($mixed), false);
	$check('Europass plus C2PA, both validators', $both->allEmbeddedFilesAccepted($mixed), true);
	$check('no validators accepts nothing', (new EmbeddedFileVerifier([]))->allEmbeddedFilesAccepted($pdf($europass())), false);

	echo "\n--- size cap ---\n";
	$capBase = strlen($europass('<Note></Note>'));
	$cap = Config::FILE_UPLOAD_PDF_STRUCTURED_XML_MAX_BYTES;
	$underCap = $europass('<Note>' . str_repeat('a', $cap - $capBase) . '</Note>');
	$overCap = $europass('<Note>' . str_repeat('a', $cap - $capBase + 1) . '</Note>');
	printf("      cap %d bytes, payloads %d and %d\n", $cap, strlen($underCap), strlen($overCap));
	$check('document exactly at the cap', $accepts($pdf($underCap)), true);
	$check('document one byte over the cap', $accepts($pdf($overCap)), false);

	echo "\n--- cost ---\n";

	/** Assert a verdict inside a time budget set well above what the code needs. */
	$timed = static function (string $label, string $body, bool $expected, float $budget) use ($xml, &$failures): void {
		$started = microtime(true);
		$verdict = $xml->allEmbeddedFilesAccepted($body);
		$elapsed = microtime(true) - $started;

		if ($verdict === $expected && $elapsed < $budget) {
			printf("PASS  %s (%.2fs)\n", $label, $elapsed);
			return;
		}

		printf("FAIL  %s → %s in %.2fs (want %s under %.2fs)\n", $label, var_export($verdict, true), $elapsed, var_export($expected, true), $budget);
		$failures++;
	};

	// Many inline Filespecs in one array object. Finding each one's owning
	// dictionary by walking back from its /EF would cost region x /EF count.
	$inline = str_repeat('<< /Type /Filespec /F (attachment.xml) /EF << /F 7 0 R >> >> ', 3000);
	$timed('3000 inline Filespecs in one /AF array', StructuredXmlFixtures::pdf(
		[7 => StructuredXmlFixtures::stream($europass())],
		'',
		"/AF [{$inline}] "
	), true, 0.5);

	// 100 Filespecs nested inside one another, each with its own /EF, around
	// ~1 MB of padding. Naming every owner re-read the levels inside it, so
	// cost grew with depth x region; past the cap owners go unnamed, and the
	// structured XML validator rejects an unnamed document.
	$nested = '<< /Type /Filespec /F (attachment.xml) /EF << /F 7 0 R >> /Pad (' . str_repeat('a', 1000000) . ') >>';

	for ($i = 0; $i < 100; $i++) {
		$nested = "<< /Type /Filespec /F (attachment.xml) /EF << /F 7 0 R >> /Next {$nested} >>";
	}

	$timed('100 nested Filespecs around 1 MB', StructuredXmlFixtures::pdf(
		[7 => StructuredXmlFixtures::stream($europass())],
		'',
		"/AF [{$nested}] "
	), false, 1.0);

	// One large document behind many references is parsed once.
	$large = $europass('<Note>' . str_repeat('a', 4 * 1024 * 1024) . '</Note>');
	$many = [];

	for ($i = 0; $i < 2000; $i++) {
		$many[10 + $i] = '<< /Type /Filespec /F (attachment.xml) /EF << /F 7 0 R >> >>';
	}

	$many[7] = StructuredXmlFixtures::stream($large);
	$timed('2000 references to one 4 MB document', StructuredXmlFixtures::pdf($many), true, 1.0);

	echo "\n--- PdfScanner decision ---\n";
	$hasQpdf = FileSecurityDiagnostics::getQpdfBinary() !== '';
	$allowXml = ['es_forms_validation_fileSecurityPdfAllowStructuredXml' => true];
	$allowC2pa = ['es_forms_validation_fileSecurityPdfAllowC2pa' => true];
	$noQpdf = ['es_forms_validation_fileSecurityPdfUseQpdf' => false];
	$unsafe = Labels::LABEL_VALIDATION_FILE_PDF_UNSAFE;

	/** Write a body to a temporary PDF, scan it under filter overrides, clean up. */
	$scan = static function (string $body, array $filters): string {
		$path = (string) tempnam(sys_get_temp_dir(), 'es-xml-');
		file_put_contents($path, $body);
		$GLOBALS['esFilters'] = $filters;

		try {
			return (new PdfScanner())->scan($path, 'cv.pdf', 'application/pdf');
		} finally {
			$GLOBALS['esFilters'] = [];
			@unlink($path);
		}
	};

	$checkScan = static function (string $label, string $actual, string $expected) use (&$failures): void {
		if ($actual === $expected) {
			printf("PASS  %s\n", $label);
			return;
		}

		printf("FAIL  %s → expected %s, got %s\n", $label, $expected === '' ? 'accepted' : $expected, $actual === '' ? 'accepted' : $actual);
		$failures++;
	};

	$cv = $pdf($europass());

	// The exemption must stay off until a site asks for it.
	$checkScan('Europass CV rejects by default', $scan($cv, []), $unsafe);
	$checkScan('Europass CV rejects with only the C2PA exemption on', $scan($cv, $allowC2pa), $unsafe);
	$checkScan('truthy non-boolean filter value does not enable it', $scan($cv, ['es_forms_validation_fileSecurityPdfAllowStructuredXml' => 1]), $unsafe);
	$checkScan('Europass CV rejects when qpdf is unreachable', $scan($cv, $allowXml + $noQpdf), $unsafe);

	if ($hasQpdf) {
		$checkScan('Europass CV is accepted with the exemption on', $scan($cv, $allowXml), '');
		$checkScan('Flate-compressed document is accepted after qpdf decodes it', $scan($pdf(
			(string) gzcompress($europass()),
			'/F (attachment.xml)',
			'',
			'/Filter /FlateDecode '
		), $allowXml), '');
		$checkScan('Europass plus C2PA with both exemptions on', $scan($mixed, $allowXml + $allowC2pa), '');
		$checkScan('ZIP under an allowlisted name rejects', $scan($pdf("PK\x03\x04" . str_repeat('B', 60)), $allowXml), $unsafe);
	} else {
		echo "SKIP  qpdf-dependent acceptance checks (qpdf not installed)\n";
	}

	// The exemption covers the embedded-file keys and nothing else.
	$checkScan('valid document plus JavaScript rejects', $scan(
		$pdf($europass(), '/F (attachment.xml)', '/OpenAction << /S /JavaScript /JS (app.alert\\(1\\)) >> '),
		$allowXml
	), $unsafe);
	$checkScan('valid document plus /Launch rejects', $scan(
		$pdf($europass(), '/F (attachment.xml)', '/OpenAction << /S /Launch /F (cmd.exe) >> '),
		$allowXml
	), $unsafe);

	printf("\n%s\n", $failures === 0 ? 'All checks passed.' : sprintf('%d check(s) failed.', $failures));
	exit($failures === 0 ? 0 : 1);
}
