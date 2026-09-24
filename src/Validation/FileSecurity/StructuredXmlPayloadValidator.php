<?php

/**
 * Verifies that an embedded file payload is one of a short list of
 * machine-readable XML documents that legitimate generators attach to the
 * PDFs they export — the Europass CV builder being the reason this exists.
 *
 * The file name picks the one document the payload may be, and the payload
 * then has to be exactly that: well-formed UTF-8 XML, no DTD, rooted at the
 * allowlisted element in the allowlisted namespace. Any XML at all would not
 * do — XML formats exist that Windows executes on open — and neither would a
 * valid root tag with arbitrary bytes behind it.
 *
 * Structure only. Europass documents may carry base64 data (a photo; in the
 * legacy format, whole attached certificates) as text inside the XML. That is
 * inert — no reader extracts or opens it — but it is not inspected either.
 * Treat the exemption as "this is shaped like a Europass CV", not as "this is
 * safe".
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

use EightshiftForms\Config\Config;
use XMLReader;

/**
 * Validator for allowlisted embedded XML documents.
 */
final class StructuredXmlPayloadValidator implements EmbeddedPayloadValidatorInterface
{
	/**
	 * Largest document this validator accepts.
	 */
	public function maxBytes(): int
	{
		return Config::FILE_UPLOAD_PDF_STRUCTURED_XML_MAX_BYTES;
	}

	/**
	 * Is this payload the allowlisted XML document its name stands for?
	 *
	 * Cheapest checks first. Everything up to the parse is plain byte
	 * inspection, so nothing reaches libxml that could make it fetch,
	 * expand or recurse.
	 *
	 * @param string      $payload Embedded file bytes.
	 * @param string|null $name    File specification name.
	 */
	public function accepts(string $payload, ?string $name): bool
	{
		if ($name === null) {
			return false;
		}

		$expected = Config::FILE_UPLOAD_PDF_STRUCTURED_XML_ALLOWLIST[$name] ?? null;

		if ($expected === null) {
			return false;
		}

		$length = \strlen($payload);

		if ($length === 0 || $length > $this->maxBytes()) {
			return false;
		}

		if (!$this->isPlainUtf8Text($payload)) {
			return false;
		}

		// No DTD means no entities: no external fetch, no expansion bomb. The
		// search is on raw bytes rather than on parsed structure, so the parser
		// never sees one at all. A document spelling either inside CDATA or a
		// comment is rejected with them, which costs that file its exemption
		// and nothing more.
		if (\stripos($payload, '<!DOCTYPE') !== false || \stripos($payload, '<!ENTITY') !== false) {
			return false;
		}

		$root = $this->parseRoot($payload);

		return $root !== null && $root[0] === $expected[0] && $root[1] === $expected[1];
	}

	/**
	 * Is this valid UTF-8 with no control bytes XML text would not carry?
	 *
	 * Tab, line feed and carriage return are the only C0 bytes XML 1.0
	 * allows, and DEL has no business in a CV. Rejecting the rest here, before
	 * the parse, is what keeps executable and archive payloads — `MZ\x90\x00`,
	 * `PK\x03\x04` — from getting as far as libxml under an allowlisted name.
	 *
	 * @param string $payload Embedded file bytes.
	 */
	private function isPlainUtf8Text(string $payload): bool
	{
		if (\preg_match('//u', $payload) !== 1) {
			return false;
		}

		return \preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $payload) === 0;
	}

	/**
	 * Parse the whole document and return its root element's local name and
	 * namespace URI.
	 *
	 * The whole document, not just the root tag: XML allows nothing but
	 * comments, processing instructions and whitespace after the root
	 * element closes, so a complete parse is what rules out a valid root
	 * with a payload appended behind it.
	 *
	 * `LIBXML_NONET` forbids network access. `LIBXML_NOENT`, `LIBXML_DTDLOAD`,
	 * `LIBXML_DTDATTR` and `LIBXML_PARSEHUGE` are never set: entity
	 * substitution, DTD loading and lifting libxml's own size and depth
	 * limits are exactly what this must not do.
	 *
	 * @param string $payload Embedded file bytes.
	 *
	 * @return array{0: string, 1: string}|null Local name and namespace URI, or null when the document does not parse.
	 */
	private function parseRoot(string $payload): ?array
	{
		// Fail closed on a host without the extension rather than pass a
		// document nobody parsed.
		if (!\class_exists(XMLReader::class)) {
			return null;
		}

		$previous = \libxml_use_internal_errors(true);
		\libxml_clear_errors();

		$reader = new XMLReader();
		$root = null;

		try {
			if (!$reader->XML($payload, 'UTF-8', \LIBXML_NONET)) {
				return null;
			}

			while ($reader->read()) {
				if ($root === null && $reader->nodeType === XMLReader::ELEMENT) {
					$root = [(string) $reader->localName, (string) $reader->namespaceURI];
				}
			}

			// read() returns false both at the end of the document and on the
			// first error, so only an empty error list means "parsed to the end".
			if (\libxml_get_errors() !== []) {
				return null;
			}

			return $root;
		} finally {
			$reader->close();
			\libxml_clear_errors();
			\libxml_use_internal_errors($previous);
		}
	}
}
