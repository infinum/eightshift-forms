<?php

/**
 * Verifies that every embedded file in a PDF body is a C2PA (Content
 * Credentials) provenance manifest, by inspecting the payload bytes rather
 * than trusting the `/AFRelationship` or `/Subtype` labels, which are
 * attacker-controlled.
 *
 * Structure only — this makes no claim about the authenticity of the
 * manifest's signature, and structure alone cannot prove a payload is
 * harmless. A genuine manifest legitimately carries opaque binary leaves
 * (CBOR claims, thumbnails), so bytes that also parse as some other format
 * can always be nested in one. What this class does is force the payload to
 * be a complete, exactly-tiled JUMBF tree of the shape C2PA specifies, which
 * rules out the cheap attack — a short header glued in front of an otherwise
 * untouched archive or installer — and bounds the rest with the size cap in
 * Config. Treat the exemption as "this is shaped like Content Credentials",
 * not as "this is safe".
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

use EightshiftForms\Config\Config;

/**
 * Structural verifier for embedded C2PA manifests.
 */
final class C2paManifestVerifier
{
	/**
	 * JUMBF superbox type (ISO/IEC 19566-5).
	 */
	private const string JUMBF_SUPERBOX_TYPE = 'jumb';

	/**
	 * JUMBF description box type.
	 */
	private const string JUMBF_DESCRIPTION_TYPE = 'jumd';

	/**
	 * Registered C2PA content-type UUID, 63327061-0011-0010-8000-00AA00389B71.
	 */
	private const string C2PA_CONTENT_TYPE_UUID = "\x63\x32\x70\x61\x00\x11\x00\x10\x80\x00\x00\xaa\x00\x38\x9b\x71";

	/**
	 * Bytes in a box header: LBox (4) plus TBox (4).
	 */
	private const int BOX_HEADER_LENGTH = 8;

	/**
	 * Smallest legal JUMBF description box: header, type UUID, toggles byte.
	 */
	private const int JUMBF_MIN_DESCRIPTION_LENGTH = 25;

	/**
	 * Smallest payload that could hold a superbox wrapping a description box.
	 */
	private const int JUMBF_MIN_LENGTH = self::BOX_HEADER_LENGTH + self::JUMBF_MIN_DESCRIPTION_LENGTH;

	/**
	 * How deep the box walk recurses before giving up. Real manifests nest
	 * three or four levels; anything deeper is a malformed or hostile file.
	 */
	private const int JUMBF_MAX_DEPTH = 8;

	/**
	 * Does every embedded file in this body verify as a C2PA manifest?
	 *
	 * Fails closed: any parse ambiguity, unresolvable reference, filtered
	 * stream, indirect length or non-JUMBF payload returns false.
	 *
	 * Expects qpdf-expanded bytes. See mapObjectOffsets() for why a raw body
	 * cannot be resolved safely here.
	 *
	 * @param string $body qpdf-expanded PDF bytes.
	 */
	public function allEmbeddedFilesAreC2paManifests(string $body): bool
	{
		// Objects hidden in compressed object streams are invisible to this
		// pass, so their contents cannot be vouched for.
		if ($this->containsObjectStreams($body)) {
			return false;
		}

		$references = $this->collectEmbeddedFileReferences($body);

		if ($references === []) {
			return false;
		}

		$offsets = $this->mapObjectOffsets($body);

		foreach ($references as [$number, $generation]) {
			$payload = $this->resolveStream($body, $offsets, $number, $generation);

			if ($payload === null) {
				return false;
			}

			if (!$this->isC2paManifest($payload)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Does this body carry compressed object streams?
	 *
	 * Objects inside an `/ObjStm` are invisible to a raw-bytes read, so
	 * nothing this class concludes about embedded files can be trusted while
	 * one is present. Exposed so the scanner can tell "undetermined, expand
	 * and scan again" apart from "verified as not a manifest" — a C2PA
	 * signer appends its manifest as an uncompressed incremental update over
	 * a body that commonly does use object streams, which puts both shapes
	 * in the same file.
	 *
	 * @param string $body PDF bytes.
	 */
	public function containsObjectStreams(string $body): bool
	{
		return PdfTokens::contains($body, '/ObjStm');
	}

	/**
	 * Collect `[objectNumber, generation]` pairs from every `/EF` dictionary.
	 *
	 * Returns an empty array when any `/EF` dictionary contains something
	 * other than `/F` and `/UF` indirect references, so the caller rejects.
	 *
	 * @param string $body PDF bytes.
	 *
	 * @return array<int, array{0: int, 1: int}> Reference pairs.
	 */
	private function collectEmbeddedFileReferences(string $body): array
	{
		$found = \preg_match_all('/\/EF(?=[\s\/<\[(%])\s*<<(.*?)>>/s', $body, $matches);

		if ($found === false || $found === 0) {
			return [];
		}

		$references = [];

		foreach ($matches[1] as $dictionary) {
			// Every entry must be `/F n g R` or `/UF n g R`. Anything else —
			// a nested dictionary, a /DOS or /Mac alternative, a direct
			// stream — means this is a shape we cannot vouch for.
			$entries = \preg_match_all('/\/(F|UF)\s+(\d+)\s+(\d+)\s+R/', $dictionary, $entryMatches, \PREG_SET_ORDER);

			if ($entries === false || $entries === 0) {
				return [];
			}

			$consumed = \preg_replace('/\/(F|UF)\s+\d+\s+\d+\s+R/', '', $dictionary);

			if (!\is_string($consumed) || \trim($consumed) !== '') {
				return [];
			}

			foreach ($entryMatches as $entry) {
				$references[] = [(int) $entry[2], (int) $entry[3]];
			}
		}

		return $references;
	}

	/**
	 * Map every `N G obj` definition in the body to its byte offset, in one pass.
	 *
	 * Built once and threaded through the resolve calls. Scanning the whole
	 * body per reference — and again per indirect `/Length`, which is the shape
	 * `qpdf --qdf` always writes — made verification cost grow with
	 * (references x body size): a 20 MB body carrying 2000 embedded references
	 * took over 20 seconds of CPU, twice per upload, on a public form.
	 *
	 * A later definition wins, which is only sound because the scanner hands
	 * this class qpdf output and nothing else: qpdf resolves references through
	 * the xref table and writes each object exactly once, so "last in the file"
	 * and "what a PDF reader would load" cannot disagree. Read a raw body here
	 * and a file that defines an object twice shows this class one payload and
	 * a reader the other.
	 *
	 * Comments need no special handling for the same reason. qpdf's `--qdf`
	 * mode emits `%% Original object ID: N G` lines, and the pattern below
	 * cannot match one — it requires `obj` immediately after the two numbers.
	 * A heuristic that skipped commented lines instead had to guess where lines
	 * end inside binary stream data, and hid genuine definitions whenever a
	 * payload happened to carry a `%` byte.
	 *
	 * @param string $body qpdf-expanded PDF bytes.
	 *
	 * @return array<string, int> `"number generation"` key to byte offset.
	 */
	private function mapObjectOffsets(string $body): array
	{
		$count = \preg_match_all(
			'/(?<![0-9])(\d+)[ \t\r\n]+(\d+)[ \t\r\n]+obj\b/',
			$body,
			$matches,
			\PREG_SET_ORDER | \PREG_OFFSET_CAPTURE
		);

		if ($count === false || $count === 0) {
			return [];
		}

		$offsets = [];

		foreach ($matches as $match) {
			$offsets[$match[1][0] . ' ' . $match[2][0]] = (int) $match[0][1];
		}

		return $offsets;
	}

	/**
	 * Resolve an indirect reference to its raw stream payload.
	 *
	 * @param string             $body       PDF bytes.
	 * @param array<string, int> $offsets    Object offset map.
	 * @param int                $number     Object number.
	 * @param int                $generation Object generation.
	 *
	 * @return string|null Payload bytes, or null when it cannot be resolved unambiguously.
	 */
	private function resolveStream(string $body, array $offsets, int $number, int $generation): ?string
	{
		$objectOffset = $offsets[$number . ' ' . $generation] ?? null;

		if ($objectOffset === null) {
			return null;
		}

		// Bound the search to this object. `endobj` bytes can occur inside a
		// stream payload too, but never ahead of the `stream` keyword that
		// opens it, so the first one always sits past the keyword looked for
		// below. Unbounded, an `/EF` pointing at an object that carries no
		// stream at all walks forward and borrows an unrelated one.
		$objectEnd = \strpos($body, 'endobj', $objectOffset);

		if ($objectEnd === false) {
			return null;
		}

		$object = \substr($body, $objectOffset, $objectEnd - $objectOffset);

		// `stream` has to be a keyword in its own right, opening a payload on
		// the next line — not the tail of `endstream`, not part of a name.
		if (\preg_match('/(?<![A-Za-z])stream(?=[\r\n])/', $object, $keyword, \PREG_OFFSET_CAPTURE) !== 1) {
			return null;
		}

		$streamKeyword = (int) $keyword[0][1];
		$dictionary = \substr($object, 0, $streamKeyword);

		// A filtered stream is not the raw JUMBF this check expects.
		if (PdfTokens::contains($dictionary, '/Filter')) {
			return null;
		}

		// A C2PA FileSpec stream carries its own /Length inside the /F
		// sub-dictionary. Strip it so the outer, authoritative one is read.
		$outer = \preg_replace('/\/F[ \t\r\n]*<<.*?>>/s', '', $dictionary);
		$length = $this->resolveLength($body, $offsets, \is_string($outer) ? $outer : $dictionary);

		if ($length === null || $length <= 0 || $length > Config::FILE_UPLOAD_PDF_C2PA_MAX_BYTES) {
			return null;
		}

		// The lookahead above guarantees a CR, LF or CRLF sits here.
		$start = $objectOffset + $streamKeyword + \strlen('stream');
		$start += \substr($body, $start, 2) === "\r\n" ? 2 : 1;

		$payload = \substr($body, $start, $length);

		return \strlen($payload) === $length ? $payload : null;
	}

	/**
	 * Resolve a stream `/Length`, accepting a direct integer or a one-hop
	 * indirect reference.
	 *
	 * The qpdf `--qdf` mode always writes the indirect form, so rejecting it
	 * would disable the exemption on exactly the hosts where verification is
	 * most reliable. A dishonest length is harmless — isC2paManifest() requires
	 * the JUMBF tree to tile the sliced payload exactly.
	 *
	 * @param string             $body       PDF bytes, needed to resolve an indirect length.
	 * @param array<string, int> $offsets    Object offset map.
	 * @param string             $dictionary Object dictionary with any /F sub-dict removed.
	 */
	private function resolveLength(string $body, array $offsets, string $dictionary): ?int
	{
		// `(?![0-9])` is load-bearing. Without it, `/Length 10 0 R` matches this
		// "direct" pattern by backtracking the capture to just `1`, yielding a
		// 1-byte payload. It only shows up when the length object number has two
		// or more digits, which is why a single-digit sample does not catch it.
		if (\preg_match('/\/Length[ \t\r\n]+(\d+)(?![0-9])(?![ \t\r\n]+\d+[ \t\r\n]+R)/', $dictionary, $direct) === 1) {
			return (int) $direct[1];
		}

		if (\preg_match('/\/Length[ \t\r\n]+(\d+)[ \t\r\n]+(\d+)[ \t\r\n]+R/', $dictionary, $indirect) !== 1) {
			return null;
		}

		$offset = $offsets[(int) $indirect[1] . ' ' . (int) $indirect[2]] ?? null;

		if ($offset === null) {
			return null;
		}

		if (\preg_match('/obj[ \t\r\n]+(\d+)[ \t\r\n]+endobj/', \substr($body, $offset, 200), $value) !== 1) {
			return null;
		}

		return (int) $value[1];
	}

	/**
	 * Is this payload a complete C2PA manifest store?
	 *
	 * The payload must be exactly one JUMBF superbox whose declared length
	 * accounts for every byte, carrying the registered C2PA content-type UUID,
	 * whose children tile it with no slack. Trailing or interstitial bytes that
	 * no box claims are what a smuggled payload needs, so any slack rejects.
	 *
	 * @param string $payload Embedded file bytes.
	 */
	private function isC2paManifest(string $payload): bool
	{
		$length = \strlen($payload);

		if ($length < self::JUMBF_MIN_LENGTH || $length > Config::FILE_UPLOAD_PDF_C2PA_MAX_BYTES) {
			return false;
		}

		$header = $this->readBoxHeader($payload, 0, $length);

		if ($header === null || $header[0] !== $length || $header[1] !== self::JUMBF_SUPERBOX_TYPE) {
			return false;
		}

		return $this->superboxIsWellFormed($payload, self::BOX_HEADER_LENGTH, $length, self::C2PA_CONTENT_TYPE_UUID, 0);
	}

	/**
	 * Read a box header, refusing any box that does not fit the range it sits in.
	 *
	 * LBox 0 ("this box runs to the end of the file") and LBox 1 ("a 64-bit
	 * length follows") are legal ISO BMFF, but both hand a box's extent to
	 * something other than its own length field, and neither is reachable below
	 * the size cap in Config. Both fall under the minimum and are rejected.
	 *
	 * @param string $payload Embedded file bytes.
	 * @param int    $offset  Byte offset of the box.
	 * @param int    $end     Exclusive upper bound the box must fit inside.
	 *
	 * @return array{0: int, 1: string}|null Box length and type, or null when unreadable.
	 */
	private function readBoxHeader(string $payload, int $offset, int $end): ?array
	{
		if ($end - $offset < self::BOX_HEADER_LENGTH) {
			return null;
		}

		$lbox = \unpack('N', \substr($payload, $offset, 4));

		if ($lbox === false) {
			return null;
		}

		$boxLength = $lbox[1];

		if ($boxLength < self::BOX_HEADER_LENGTH || $boxLength > $end - $offset) {
			return null;
		}

		return [$boxLength, \substr($payload, $offset + 4, 4)];
	}

	/**
	 * Is this range a JUMBF superbox body — a description box followed by
	 * content boxes that tile the rest of the range exactly?
	 *
	 * @param string      $payload      Embedded file bytes.
	 * @param int         $start        First byte of the superbox body, past its own header.
	 * @param int         $end          Exclusive end of the superbox body.
	 * @param string|null $expectedUuid Content-type UUID the description must carry, or null for any.
	 * @param int         $depth        Current recursion depth.
	 */
	private function superboxIsWellFormed(string $payload, int $start, int $end, ?string $expectedUuid, int $depth): bool
	{
		if ($depth > self::JUMBF_MAX_DEPTH) {
			return false;
		}

		$description = $this->readBoxHeader($payload, $start, $end);

		if ($description === null || $description[1] !== self::JUMBF_DESCRIPTION_TYPE) {
			return false;
		}

		if ($description[0] < self::JUMBF_MIN_DESCRIPTION_LENGTH) {
			return false;
		}

		if (
			$expectedUuid !== null
			&& \substr($payload, $start + self::BOX_HEADER_LENGTH, \strlen($expectedUuid)) !== $expectedUuid
		) {
			return false;
		}

		$offset = $start + $description[0];

		// A superbox holding nothing but its own description describes nothing.
		if ($offset >= $end) {
			return false;
		}

		while ($offset < $end) {
			$box = $this->readBoxHeader($payload, $offset, $end);

			if ($box === null) {
				return false;
			}

			// The store's own children are manifest superboxes. Below that,
			// leaf boxes hold claim, signature and assertion data this class
			// does not interpret, so they only have to tile.
			if ($depth === 0 && $box[1] !== self::JUMBF_SUPERBOX_TYPE) {
				return false;
			}

			if (
				$box[1] === self::JUMBF_SUPERBOX_TYPE
				&& !$this->superboxIsWellFormed(
					$payload,
					$offset + self::BOX_HEADER_LENGTH,
					$offset + $box[0],
					null,
					$depth + 1
				)
			) {
				return false;
			}

			$offset += $box[0];
		}

		// Exact by construction: readBoxHeader caps every box at the bytes left
		// in the range, so the walk lands on $end or has already returned false.
		return true;
	}
}
