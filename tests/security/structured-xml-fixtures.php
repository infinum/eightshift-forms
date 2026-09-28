<?php

/**
 * Builders for PDFs carrying embedded XML documents, shared by
 * verify-structured-xml.php.
 *
 * The default shape mirrors a real Europass CV export (cairo 1.15.12): a
 * Filespec with `/F (attachment.xml)` and an indirect `/EF`, reached from the
 * catalog's `/EmbeddedFiles` name tree, with no `/UF`, `/Subtype`,
 * `/AFRelationship` or `/AF`. Built in code so no real CV — which is personal
 * data — ever has to be committed.
 *
 * @package EightshiftForms\Tests\Security
 */

declare(strict_types=1);

/**
 * Builders for structured-XML PDF fixtures.
 */
final class StructuredXmlFixtures
{
	/**
	 * A minimal Europass (2020+) `Candidate` document.
	 *
	 * @param string $inner Extra markup inside the root element.
	 */
	public static function europass(string $inner = ''): string
	{
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
			. '<Candidate xsi:schemaLocation="http://www.europass.eu/1.0 Candidate.xsd" xmlns="http://www.europass.eu/1.0"'
			. ' xmlns:oa="http://www.openapplications.org/oagis/9" xmlns:hr="http://www.hr-xml.org/3"'
			. ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . "\n"
			. '    <hr:DocumentID schemeID="Test-0001" schemeName="DocumentIdentifier" schemeAgencyName="EUROPASS" schemeVersionID="4.0"/>' . "\n"
			. '    <CandidatePerson><PersonName><oa:GivenName>Test</oa:GivenName><hr:FamilyName>Fixture</hr:FamilyName></PersonName></CandidatePerson>' . "\n"
			. $inner
			. "</Candidate>\n";
	}

	/**
	 * A minimal legacy Europass (2013–2020) `SkillsPassport` document.
	 */
	public static function skillsPassport(): string
	{
		return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
			. '<SkillsPassport xmlns="http://europass.cedefop.europa.eu/Europass" locale="en">' . "\n"
			. "  <LearnerInfo><Identification><PersonName><FirstName>Test</FirstName><Surname>Fixture</Surname></PersonName></Identification></LearnerInfo>\n"
			. "</SkillsPassport>\n";
	}

	/**
	 * A stream object body: dictionary plus payload.
	 *
	 * @param string $payload    Stream bytes.
	 * @param string $dictExtra  Extra dictionary entries.
	 */
	public static function stream(string $payload, string $dictExtra = ''): string
	{
		return \sprintf("<< /Type /EmbeddedFile /Length %d %s>>\nstream\n%s\nendstream", \strlen($payload), $dictExtra, $payload);
	}

	/**
	 * A complete PDF with a cross-reference table, from object bodies.
	 *
	 * Objects 1–3 are the catalog, page tree and page. The catalog carries an
	 * `/EmbeddedFiles` name tree at object 4 when `$treeEntries` is given.
	 *
	 * @param array<int, string> $objects       Object number to body (between `obj` and `endobj`), from 4 up.
	 * @param string             $treeEntries   Name-tree `/Names` array contents, e.g. `(attachment.xml) 5 0 R`.
	 * @param string             $catalogExtra  Extra catalog entries.
	 */
	public static function pdf(array $objects, string $treeEntries = '', string $catalogExtra = ''): string
	{
		$names = $treeEntries === '' ? '' : '/Names << /EmbeddedFiles 4 0 R >> ';

		$all = [
			1 => "<< /Type /Catalog /Pages 2 0 R {$names}{$catalogExtra}>>",
			2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
			3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] >>',
		];

		if ($treeEntries !== '') {
			$all[4] = "<< /Names [{$treeEntries}] >>";
		}

		$all += $objects;
		\ksort($all);

		$body = "%PDF-1.6\n%\xE2\xE3\xCF\xD3\n";
		$offsets = [];

		foreach ($all as $number => $object) {
			$offsets[$number] = \strlen($body);
			$body .= "{$number} 0 obj\n{$object}\nendobj\n";
		}

		$size = \max(\array_keys($all)) + 1;
		$startxref = \strlen($body);
		$body .= "xref\n0 {$size}\n0000000000 65535 f \n";

		for ($i = 1; $i < $size; $i++) {
			$body .= isset($offsets[$i]) ? \sprintf("%010d 00000 n \n", $offsets[$i]) : "0000000000 00000 f \n";
		}

		return $body . "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$startxref}\n%%EOF\n";
	}

	/**
	 * The shape a Europass export writes: one Filespec, indirect `/EF`.
	 *
	 * @param string $payload      Embedded XML bytes.
	 * @param string $nameEntries  File specification name entries.
	 * @param string $catalogExtra Extra catalog entries.
	 * @param string $streamExtra  Extra stream dictionary entries.
	 */
	public static function europassPdf(
		string $payload,
		string $nameEntries = '/F (attachment.xml)',
		string $catalogExtra = '',
		string $streamExtra = ''
	): string {
		return self::pdf(
			[
				5 => "<< {$nameEntries} /EF 6 0 R /Type /Filespec >>",
				6 => '<< /F 7 0 R >>',
				7 => self::stream($payload, $streamExtra),
			],
			'(attachment.xml) 5 0 R',
			$catalogExtra
		);
	}
}
