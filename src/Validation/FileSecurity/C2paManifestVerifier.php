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

		$offsets = $this->mapObjectOffsets($body);

		if ($offsets === []) {
			return false;
		}

		$objects = $this->mapObjects($body, $offsets);

		if ($objects === null) {
			return false;
		}

		$references = $this->collectEmbeddedFileReferences($objects);

		if ($references === []) {
			return false;
		}

		foreach ($references as [$number, $generation]) {
			$payload = $this->resolveStream($body, $objects, $offsets, $number, $generation);

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
	 * Collect `[objectNumber, generation]` pairs from every `/EF` in the body.
	 *
	 * An `/EF` value is a dictionary, and PDF lets any value be written as an
	 * indirect reference, so `/EF 7 0 R` is as ordinary as `/EF << /F 7 0 R >>`
	 * and every reader resolves both. qpdf keeps an indirect reference
	 * indirect, so both shapes are read here — and, the part that matters, an
	 * `/EF` that is neither rejects the file. Skipping a shape this class does
	 * not understand would leave the payload behind it unexamined while a
	 * reader still extracts it.
	 *
	 * Only the structural part of an object is searched: a dictionary object
	 * contributes its own parsed dictionary, and stream payload bytes are
	 * never part of a region. Without that, the binary leaves of a genuine
	 * manifest — or any image in the file — could spell `/EF` by chance and
	 * reject an upload that deserved to pass.
	 *
	 * @param array<string, array{dict: string, region: string, masked: bool, stream: int|null}> $objects Parsed objects.
	 *
	 * @return array<int, array{0: int, 1: int}> Reference pairs, empty when any `/EF` cannot be read.
	 */
	private function collectEmbeddedFileReferences(array $objects): array
	{
		$references = [];

		foreach ($objects as $object) {
			$region = $object['region'];

			// Strings are blanked before the scan so an `/EF` written inside
			// one cannot inject a reference. Only dictionaries are masked;
			// everything else is small print between objects.
			$haystack = $object['masked'] ? $this->mask($region, false) : $region;
			$position = 0;

			while (\preg_match('/\/EF(?=[\s\/<\[(%])/', $haystack, $match, \PREG_OFFSET_CAPTURE, $position) === 1) {
				$position = (int) $match[0][1] + 3;
				$offset = $position + \strspn($region, " \t\r\n\0\x0c", $position);

				$dictionary = $this->readEmbeddedFileDictionary($objects, $region, $offset);

				if ($dictionary === null) {
					return [];
				}

				$entries = $this->embeddedFileEntries($dictionary);

				if ($entries === null) {
					return [];
				}

				foreach ($entries as $entry) {
					$references[] = $entry;
				}
			}
		}

		return $references;
	}

	/**
	 * Read the dictionary an `/EF` names, direct or one indirect hop away.
	 *
	 * @param array<string, array{dict: string, region: string, masked: bool, stream: int|null}> $objects Parsed objects.
	 * @param string                                                           $region  Region the `/EF` was found in.
	 * @param int                                                              $offset  First byte of the `/EF` value.
	 *
	 * @return string|null Dictionary text, or null when the value is neither shape.
	 */
	private function readEmbeddedFileDictionary(array $objects, string $region, int $offset): ?string
	{
		if (\substr($region, $offset, 2) === '<<') {
			$read = $this->readDictionary($region, $offset);

			return $read === null ? null : $read[0];
		}

		if (\preg_match('/^(\d+)[ \t\r\n]+(\d+)[ \t\r\n]+R(?![A-Za-z0-9])/', \substr($region, $offset, 64), $indirect) !== 1) {
			return null;
		}

		$target = $objects[(int) $indirect[1] . ' ' . (int) $indirect[2]] ?? null;

		if ($target === null || $target['dict'] === '') {
			return null;
		}

		return $target['dict'];
	}

	/**
	 * Read an `/EF` dictionary, which must hold nothing but `/F` and `/UF`
	 * indirect references.
	 *
	 * Anything else — a nested dictionary, a /DOS or /Mac alternative, a
	 * direct value — is a shape we cannot vouch for.
	 *
	 * @param string $dictionary Dictionary text, brackets included.
	 *
	 * @return array<int, array{0: int, 1: int}>|null Reference pairs, or null when the dictionary holds anything else.
	 */
	private function embeddedFileEntries(string $dictionary): ?array
	{
		$inner = \substr($dictionary, 2, -2);

		$entries = \preg_match_all('/\/(F|UF)\s+(\d+)\s+(\d+)\s+R/', $inner, $matches, \PREG_SET_ORDER);

		if ($entries === false || $entries === 0) {
			return null;
		}

		$consumed = \preg_replace('/\/(F|UF)\s+\d+\s+\d+\s+R/', '', $inner);

		if (!\is_string($consumed) || \trim($consumed) !== '') {
			return null;
		}

		$references = [];

		foreach ($matches as $entry) {
			$references[] = [(int) $entry[2], (int) $entry[3]];
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
	 * A second definition of the same object rejects the whole body. qpdf
	 * resolves references through the xref table and writes each object
	 * exactly once, so a duplicate is never something qpdf produced: it is a
	 * damaged body, or the bytes `2 0 obj` planted inside some other object's
	 * stream payload, where a "later definition wins" rule would have handed
	 * this class a decoy manifest while a reader kept the real object.
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
			$key = $match[1][0] . ' ' . $match[2][0];

			if (isset($offsets[$key])) {
				return [];
			}

			$offsets[$key] = (int) $match[0][1];
		}

		return $offsets;
	}

	/**
	 * Parse every object into the parts this class reads: its dictionary, the
	 * region an `/EF` may legitimately appear in, and where its stream payload
	 * begins.
	 *
	 * The dictionary is walked with balanced brackets rather than matched with
	 * a pattern, honouring literal and hex strings, so a `>>` or a `stream`
	 * spelled inside a string value cannot end it early and hide the rest of
	 * the dictionary from every check below.
	 *
	 * A region stops at the `stream` keyword, which is what keeps payload
	 * bytes from ever being read as structure.
	 *
	 * @param string             $body    qpdf-expanded PDF bytes.
	 * @param array<string, int> $offsets Object offset map.
	 *
	 * @return array<string, array{dict: string, region: string, masked: bool, stream: int|null}>|null Parsed objects, or null when one is malformed.
	 */
	private function mapObjects(string $body, array $offsets): ?array
	{
		\asort($offsets);

		$keys = \array_keys($offsets);
		$starts = \array_values($offsets);
		$total = \strlen($body);
		$objects = [];

		foreach ($starts as $index => $offset) {
			if (\preg_match('/^\d+[ \t\r\n]+\d+[ \t\r\n]+obj/', \substr($body, $offset, 64), $header) !== 1) {
				return null;
			}

			$start = $offset + \strlen($header[0]);
			$next = $starts[$index + 1] ?? $total;
			$cursor = $start + \strspn($body, " \t\r\n\0\x0c", $start);

			$dictionary = '';
			$stream = null;
			$region = \substr($body, $start, \max(0, $next - $start));

			if (\substr($body, $cursor, 2) === '<<') {
				$read = $this->readDictionary($body, $cursor);

				if ($read === null) {
					return null;
				}

				[$dictionary, $after] = $read;

				$region = $dictionary;
				$after += \strspn($body, " \t\r\n\0\x0c", $after);

				if (\substr($body, $after, 6) === 'stream') {
					$stream = $this->streamStart($body, $after + 6);

					if ($stream === null) {
						return null;
					}
				}
			}

			$objects[$keys[$index]] = [
				'dict' => $dictionary,
				'region' => $region,
				'masked' => $dictionary !== '',
				'stream' => $stream,
			];
		}

		return $objects;
	}

	/**
	 * First payload byte after a `stream` keyword.
	 *
	 * The keyword is followed by CRLF or a single LF. A `stream` that is
	 * followed by anything else is not one.
	 *
	 * @param string $body   PDF bytes.
	 * @param int    $offset Byte just past the `stream` keyword.
	 */
	private function streamStart(string $body, int $offset): ?int
	{
		if (\substr($body, $offset, 2) === "\r\n") {
			return $offset + 2;
		}

		$eol = \substr($body, $offset, 1);

		// A bare CR opens a stream in files Adobe writes, whatever the spec says.
		return $eol === "\n" || $eol === "\r" ? $offset + 1 : null;
	}

	/**
	 * Read a `<< ... >>` dictionary with balanced brackets.
	 *
	 * @param string $body   Bytes to read from.
	 * @param int    $offset Offset of the opening `<<`.
	 *
	 * @return array{0: string, 1: int}|null Dictionary text and the offset just past it, or null when unbalanced.
	 */
	private function readDictionary(string $body, int $offset): ?array
	{
		$total = \strlen($body);
		$depth = 0;
		$cursor = $offset;

		while ($cursor < $total) {
			$byte = $body[$cursor];

			if ($byte === '(') {
				$cursor = $this->skipLiteralString($body, $cursor);
				continue;
			}

			if ($byte === '%') {
				$cursor += \strcspn($body, "\r\n", $cursor);
				continue;
			}

			if ($byte === '<') {
				if (\substr($body, $cursor, 2) === '<<') {
					$depth++;
					$cursor += 2;
					continue;
				}

				$end = \strpos($body, '>', $cursor);

				if ($end === false) {
					return null;
				}

				$cursor = $end + 1;
				continue;
			}

			if ($byte === '>' && \substr($body, $cursor, 2) === '>>') {
				$depth--;
				$cursor += 2;

				if ($depth === 0) {
					return [\substr($body, $offset, $cursor - $offset), $cursor];
				}

				continue;
			}

			$cursor++;
		}

		return null;
	}

	/**
	 * Offset just past a literal `( ... )` string, honouring escapes and
	 * balanced inner parentheses.
	 *
	 * @param string $body   Bytes to read from.
	 * @param int    $offset Offset of the opening parenthesis.
	 */
	private function skipLiteralString(string $body, int $offset): int
	{
		$total = \strlen($body);
		$depth = 0;
		$cursor = $offset;

		while ($cursor < $total) {
			$byte = $body[$cursor];

			if ($byte === '\\') {
				$cursor += 2;
				continue;
			}

			if ($byte === '(') {
				$depth++;
			} elseif ($byte === ')') {
				$depth--;

				if ($depth === 0) {
					return $cursor + 1;
				}
			}

			$cursor++;
		}

		return $total;
	}

	/**
	 * Blank out the parts of a dictionary that are not the structure being
	 * read, keeping every byte offset intact so slices still line up.
	 *
	 * Literal strings, hex strings and comments always go. With
	 * `$maskNested`, so does everything inside a sub-dictionary, which leaves
	 * the outer entries alone on the line — the only place a key that governs
	 * this object may be read from.
	 *
	 * @param string $text       Dictionary text.
	 * @param bool   $maskNested Whether to blank sub-dictionaries too.
	 */
	private function mask(string $text, bool $maskNested): string
	{
		$total = \strlen($text);
		$masked = $text;
		$depth = 0;
		$cursor = 0;

		while ($cursor < $total) {
			$byte = $text[$cursor];
			$end = null;

			if ($byte === '(') {
				$end = $this->skipLiteralString($text, $cursor);
			} elseif ($byte === '%') {
				$end = $cursor + \strcspn($text, "\r\n", $cursor);
			} elseif ($byte === '<' && \substr($text, $cursor, 2) !== '<<') {
				$closing = \strpos($text, '>', $cursor);
				$end = $closing === false ? $total : $closing + 1;
			}

			if ($end !== null) {
				while ($cursor < $end) {
					$masked[$cursor] = ' ';
					$cursor++;
				}

				continue;
			}

			if (\substr($text, $cursor, 2) === '<<') {
				$depth++;
				$nested = $maskNested && $depth > 1;
			} elseif (\substr($text, $cursor, 2) === '>>') {
				$nested = $maskNested && $depth > 1;
				$depth--;
			} else {
				if ($maskNested && $depth > 1) {
					$masked[$cursor] = ' ';
				}

				$cursor++;
				continue;
			}

			if ($nested) {
				$masked[$cursor] = ' ';
				$masked[$cursor + 1] = ' ';
			}

			$cursor += 2;
		}

		return $masked;
	}

	/**
	 * Resolve an indirect reference to its raw stream payload.
	 *
	 * @param string                                                                            $body       PDF bytes.
	 * @param array<string, array{dict: string, region: string, masked: bool, stream: int|null}> $objects    Parsed objects.
	 * @param array<string, int>                                                                $offsets    Object offset map.
	 * @param int                                                                               $number     Object number.
	 * @param int                                                                               $generation Object generation.
	 *
	 * @return string|null Payload bytes, or null when it cannot be resolved unambiguously.
	 */
	private function resolveStream(string $body, array $objects, array $offsets, int $number, int $generation): ?string
	{
		$object = $objects[$number . ' ' . $generation] ?? null;

		if ($object === null || $object['stream'] === null) {
			return null;
		}

		// A filtered stream is not the raw JUMBF this check expects. Read from
		// the whole dictionary, sub-dictionaries included, so a /Filter this
		// class cannot place is a rejection rather than a guess.
		if (PdfTokens::contains($object['dict'], '/Filter')) {
			return null;
		}

		// Only the outer entries decide this stream's extent. A C2PA FileSpec
		// carries a second /Length inside its /F sub-dictionary, and a string
		// value can spell one anywhere, so both are blanked before the read.
		$length = $this->resolveLength($body, $offsets, $this->mask($object['dict'], true));

		if ($length === null || $length <= 0 || $length > Config::FILE_UPLOAD_PDF_C2PA_MAX_BYTES) {
			return null;
		}

		$start = $object['stream'];
		$payload = \substr($body, $start, $length);

		if (\strlen($payload) !== $length) {
			return null;
		}

		// The length has to be the one that ends the stream. Without this, a
		// shorter length verifies a prefix of the payload as a complete,
		// exactly-tiled manifest while a reader extracts that prefix plus
		// whatever was appended behind it.
		$tail = $start + $length;
		$tail += \strspn($body, " \t\r\n\0\x0c", $tail);

		return \substr($body, $tail, 9) === 'endstream' ? $payload : null;
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
	 * @param string             $dictionary Object dictionary, masked down to its outer entries.
	 */
	private function resolveLength(string $body, array $offsets, string $dictionary): ?int
	{
		// Exactly one, or this is not a dictionary whose extent can be read.
		// A second /Length is how a payload gets verified as a short prefix of
		// itself: the first match wins here, the last one wins in a reader.
		if (\preg_match_all('/\/Length(?=[\s\/<\[(%])/', $dictionary) !== 1) {
			return null;
		}

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
