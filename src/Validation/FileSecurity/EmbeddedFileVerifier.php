<?php

/**
 * Verifies that every embedded file in a PDF body is one the enabled payload
 * validators accept, resolving each embedded stream the way a PDF reader
 * would rather than trusting the `/AFRelationship` or `/Subtype` labels,
 * which are attacker-controlled.
 *
 * This class owns the structure — which streams a reader would extract, and
 * under which name. The payload validators own the judgement of the bytes.
 * Every embedded stream must satisfy at least one of them, so enabling a
 * second validator widens what one attachment may be, never how many
 * attachments escape inspection.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

/**
 * Structural verifier for embedded files.
 */
final readonly class EmbeddedFileVerifier
{
	/**
	 * Payload validators, any one of which may accept a stream.
	 *
	 * @var array<int, EmbeddedPayloadValidatorInterface>
	 */
	private array $validators;

	/**
	 * Largest payload any validator accepts. Streams above it are rejected
	 * before they are sliced.
	 *
	 * @var int
	 */
	private int $maxBytes;

	/**
	 * Create a new instance.
	 *
	 * @param array<int, EmbeddedPayloadValidatorInterface> $validators Payload validators. Empty accepts nothing.
	 */
	public function __construct(array $validators)
	{
		$this->validators = \array_values($validators);
		$this->maxBytes = \array_reduce(
			$this->validators,
			static fn(int $carry, EmbeddedPayloadValidatorInterface $validator): int => \max($carry, $validator->maxBytes()),
			0
		);
	}

	/**
	 * Is every embedded file in this body accepted by at least one validator?
	 *
	 * Fails closed: any parse ambiguity, unresolvable reference, filtered
	 * stream, indirect length, unreadable file specification or unaccepted payload
	 * returns false.
	 *
	 * Expects qpdf-expanded bytes. See mapObjectOffsets() for why a raw body
	 * cannot be resolved safely here.
	 *
	 * @param string $body qpdf-expanded PDF bytes.
	 */
	public function allEmbeddedFilesAccepted(string $body): bool
	{
		if ($this->validators === []) {
			return false;
		}

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

		$verified = [];
		$names = [];

		foreach ($references as [$number, $generation, $name]) {
			$key = $number . ' ' . $generation;

			// One stream, one name. A validator that reads the name has to see
			// the name a reader will save the stream under, and a stream two
			// file specifications name differently is verified under one and
			// extracted under the other. Applied whichever validators are
			// enabled, so that adding a name-reading validator never changes
			// what the structure pass accepts. No signer writes one stream
			// under two names, so a name-blind validator loses nothing real.
			if (\array_key_exists($key, $names) && $names[$key] !== $name) {
				return false;
			}

			$names[$key] = $name;

			// Cost has to scale with the streams, not with the references to
			// them. A body may point thousands of `/EF` entries at one large
			// payload, and re-slicing and re-parsing it for each is work an
			// attacker gets for the price of a 12-byte reference. With the name
			// pinned above, the verdict is a property of the stream, so the
			// first answer is the only one there is.
			if (isset($verified[$key])) {
				continue;
			}

			$payload = $this->resolveStream($body, $objects, $offsets, $number, $generation);

			if ($payload === null) {
				return false;
			}

			if (!$this->isAccepted($payload, $name)) {
				return false;
			}

			$verified[$key] = true;
		}

		return $this->everyEmbeddedFileStreamWasVerified($objects, $verified);
	}

	/**
	 * Does this body carry compressed object streams?
	 *
	 * Objects inside an `/ObjStm` are invisible to a raw-bytes read, so
	 * nothing this class concludes about embedded files can be trusted while
	 * one is present. Exposed so the scanner can tell "undetermined, expand
	 * and scan again" apart from "verified as not acceptable" — a C2PA
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
	 * Does any validator accept this payload under this name?
	 *
	 * @param string      $payload Embedded file bytes.
	 * @param string|null $name    File specification name, or null.
	 */
	private function isAccepted(string $payload, ?string $name): bool
	{
		return \array_any(
			$this->validators,
			static fn(EmbeddedPayloadValidatorInterface $validator): bool => $validator->accepts($payload, $name)
		);
	}

	/**
	 * Is every stream that announces itself as an embedded file one the walk
	 * above verified?
	 *
	 * The backstop behind the `/EF` walk and the `/RF` rejection: it catches
	 * the object at the end of a path nobody here thought of. Only a
	 * backstop, because `/Type` is optional on an embedded file stream and a
	 * payload that omits it is invisible to it — which is why `/RF` is
	 * rejected on sight rather than left to this.
	 *
	 * `/EmbeddedFiles` does not match: PdfTokens requires a delimiter after
	 * the token, and `s` is not one.
	 *
	 * @param array<string, array{dict: string, region: string, masked: bool, stream: int|null}> $objects  Parsed objects.
	 * @param array<string, bool>                                                                $verified Keys of the streams already verified.
	 */
	private function everyEmbeddedFileStreamWasVerified(array $objects, array $verified): bool
	{
		foreach ($objects as $key => $object) {
			if ($object['stream'] === null || isset($verified[$key])) {
				continue;
			}

			// Whole dictionary, strings and sub-dictionaries included, as the
			// /Filter check reads it: a coincidence rejects an upload, a
			// blind spot passes one.
			if (PdfTokens::contains($object['dict'], '/EmbeddedFile')) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Collect `[objectNumber, generation, name]` triples from every `/EF` in
	 * the body.
	 *
	 * An `/EF` value is a dictionary, and PDF lets any value be written as an
	 * indirect reference, so `/EF 7 0 R` is as ordinary as `/EF << /F 7 0 R >>`
	 * and every reader resolves both. qpdf keeps an indirect reference
	 * indirect, so both shapes are read here — and, the part that matters, an
	 * `/EF` that is neither rejects the file. Skipping a shape this class does
	 * not understand would leave the payload behind it unexamined while a
	 * reader still extracts it.
	 *
	 * The name comes from the file specification that holds the `/EF`: the
	 * innermost dictionary around it, which is the object itself for the
	 * usual `N 0 obj << /Type /Filespec ... >>` and an inline dictionary for
	 * one written straight into an `/AF` array.
	 *
	 * `/EF` is not the only way a file specification names an embedded
	 * stream, so `/RF` rejects the body outright. See below.
	 *
	 * Only the structural part of an object is searched: a dictionary object
	 * contributes its own parsed dictionary, and stream payload bytes are
	 * never part of a region. Without that, the binary leaves of a genuine
	 * manifest — or any image in the file — could spell `/EF` by chance and
	 * reject an upload that deserved to pass.
	 *
	 * @param array<string, array{dict: string, region: string, masked: bool, stream: int|null}> $objects Parsed objects.
	 *
	 * @return array<int, array{0: int, 1: int, 2: string|null}> Reference triples, empty when any `/EF` or its owner cannot be read.
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

			// `/RF` (Related Files, PDF 32000-1 §7.11.4.3) sits beside `/EF`
			// in the same file specification and names embedded streams of
			// its own. qpdf follows it — an `/RF` target survives `--qdf`
			// where a genuinely unreferenced object is dropped — so walking
			// `/EF` alone let a raw payload ride along beside a real
			// manifest. Rejected rather than resolved: no exempted payload
			// type carries one, so reading the shape would buy an upload
			// nothing.
			if (PdfTokens::contains($haystack, '/RF')) {
				return [];
			}

			if (\preg_match_all('/\/EF(?=[\s\/<\[(%])/', $haystack, $matches, \PREG_OFFSET_CAPTURE) === false) {
				return [];
			}

			// A region without an `/EF` pays nothing for the owner lookup.
			if ($matches[0] === []) {
				continue;
			}

			$keyOffsets = \array_map(static fn(array $match): int => (int) $match[1], $matches[0]);
			$owners = $this->owningDictionaryOffsets($region, $keyOffsets);

			if ($owners === null) {
				return [];
			}

			$ownerNames = [];

			foreach ($keyOffsets as $keyOffset) {
				$position = $keyOffset + 3;
				$offset = $position + \strspn($region, " \t\r\n\0\x0c", $position);

				$dictionary = $this->readEmbeddedFileDictionary($objects, $region, $offset);

				if ($dictionary === null) {
					return [];
				}

				$entries = $this->embeddedFileEntries($dictionary);

				if ($entries === null) {
					return [];
				}

				$ownerStart = $owners[$keyOffset];

				// A dictionary holding many `/EF` keys is read once, not once
				// per key, so the cost stays linear in the region.
				if (!\array_key_exists($ownerStart, $ownerNames)) {
					$owner = $this->readDictionary($region, $ownerStart, \strlen($region));

					if ($owner === null) {
						return [];
					}

					$ownerNames[$ownerStart] = $this->fileSpecName($owner[0]);
				}

				foreach ($entries as [$number, $generation]) {
					$references[] = [$number, $generation, $ownerNames[$ownerStart]];
				}
			}
		}

		return $references;
	}

	/**
	 * Offset of the innermost dictionary open at each key offset — for an
	 * `/EF` key, the file specification that owns it.
	 *
	 * One forward pass over the brackets serves every key, so a region with
	 * many `/EF` keys stays linear. Strings and comments are blanked first,
	 * so a `<<` spelled inside one is not a bracket.
	 *
	 * @param string          $region     Object region.
	 * @param array<int, int> $keyOffsets Key offsets, in ascending order.
	 *
	 * @return array<int, int>|null Offset of the owning `<<` keyed by key offset, or null when a key sits in no dictionary.
	 */
	private function owningDictionaryOffsets(string $region, array $keyOffsets): ?array
	{
		$brackets = $this->bracketOffsets($this->mask($region, false));
		$next = 0;
		$open = [];
		$owners = [];

		foreach ($keyOffsets as $keyOffset) {
			while (isset($brackets[$next]) && $brackets[$next][1] < $keyOffset) {
				if ($brackets[$next][0] === '<<') {
					$open[] = $brackets[$next][1];
				} else {
					\array_pop($open);
				}

				$next++;
			}

			if ($open === []) {
				return null;
			}

			$owners[$keyOffset] = $open[\array_key_last($open)];
		}

		return $owners;
	}

	/**
	 * Offsets of every `<<` and `>>` in already-masked text, in order.
	 *
	 * @param string $masked Text with strings, hex strings and comments blanked.
	 *
	 * @return array<int, array{0: string, 1: int}> Bracket and its offset.
	 */
	private function bracketOffsets(string $masked): array
	{
		if (\preg_match_all('/<<|>>/', $masked, $matches, \PREG_OFFSET_CAPTURE) === false) {
			return [];
		}

		return \array_map(
			static fn(array $match): array => [(string) $match[0], (int) $match[1]],
			$matches[0]
		);
	}

	/**
	 * The name a file specification gives its embedded file.
	 *
	 * Read from the outer entries only, so an `/F` inside `/EF << /F 2 0 R >>`
	 * — a reference, not a name — is never mistaken for one.
	 *
	 * Null means "no name a validator can rely on": none given (both keys are
	 * optional, and a C2PA signer may write neither), a key given twice, a
	 * value that is not printable ASCII, or `/F` and `/UF` disagreeing —
	 * readers prefer `/UF` and fall back to `/F`, so which one a validator
	 * was shown must not matter. Null rather than a rejection, because only a
	 * validator knows whether it needs a name. One that does refuses null;
	 * one that ignores the name must not lose a file over it.
	 *
	 * @param string $dictionary File specification dictionary, brackets included.
	 *
	 * @return string|null The name, or null when there is none to rely on.
	 */
	private function fileSpecName(string $dictionary): ?string
	{
		$outer = $this->mask($dictionary, true);
		$found = [];

		foreach (['/F', '/UF'] as $key) {
			$count = \preg_match_all('/' . \preg_quote($key, '/') . '(?=[\s\/<\[(%])/', $outer, $matches, \PREG_OFFSET_CAPTURE);

			if ($count === false || $count > 1) {
				return null;
			}

			if ($count === 0) {
				continue;
			}

			$start = (int) $matches[0][0][1] + \strlen($key);
			$start += \strspn($dictionary, " \t\r\n\0\x0c", $start);

			$value = PdfStrings::readAsciiText($dictionary, $start);

			if ($value === null) {
				return null;
			}

			$found[] = $value;
		}

		// None found, or two that disagree.
		return \count(\array_unique($found)) === 1 ? $found[0] : null;
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
			$read = $this->readDictionary($region, $offset, \strlen($region));

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
				$read = $this->readDictionary($body, $cursor, $next);

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
	 * The walk stops at `$limit`, never at the end of the body. Callers pass
	 * the offset of the next object definition, which a dictionary cannot
	 * legally span. Without that bound the cost is quadratic: object
	 * discovery reads the raw bytes, so `N 0 obj <<` sequences planted inside
	 * an opaque stream payload each become an object whose walk runs to the
	 * end of the file. A 70 KB body shaped that way took 21 seconds of CPU.
	 * Bounded, the regions are disjoint and the total walk is one pass.
	 *
	 * A dictionary that reaches the limit unbalanced rejects the body, which
	 * also rejects the rare legitimate file spelling `N G obj` inside a
	 * string value. That costs such a file its exemption and nothing else —
	 * it is rejected exactly as it was before the exemption existed — and it
	 * matches how a planted definition is already treated in
	 * mapObjectOffsets().
	 *
	 * @param string $body   Bytes to read from.
	 * @param int    $offset Offset of the opening `<<`.
	 * @param int    $limit  Offset the walk may not read past.
	 *
	 * @return array{0: string, 1: int}|null Dictionary text and the offset just past it, or null when unbalanced.
	 */
	private function readDictionary(string $body, int $offset, int $limit): ?array
	{
		$total = \min(\strlen($body), $limit);
		$depth = 0;
		$cursor = $offset;

		while ($cursor < $total) {
			$byte = $body[$cursor];

			if ($byte === '(') {
				$cursor = PdfStrings::skipLiteral($body, $cursor, $total);
				continue;
			}

			if ($byte === '%') {
				$cursor += \strcspn($body, "\r\n", $cursor, $total - $cursor);
				continue;
			}

			if ($byte === '<') {
				if (\substr($body, $cursor, 2) === '<<') {
					$depth++;
					$cursor += 2;
					continue;
				}

				// Bounded by $total, so an unterminated hex string costs the
				// rest of this region rather than the rest of the file.
				$span = \strcspn($body, '>', $cursor, $total - $cursor);

				if ($cursor + $span >= $total) {
					return null;
				}

				$cursor += $span + 1;
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
				$end = PdfStrings::skipLiteral($text, $cursor);
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

		// qpdf --qdf decodes every stream it can, so a /Filter still present
		// is one qpdf could not undo, and the bytes here are not the bytes a
		// reader extracts. Read from the whole dictionary, sub-dictionaries
		// included, so a /Filter this class cannot place is a rejection rather
		// than a guess.
		if (PdfTokens::contains($object['dict'], '/Filter')) {
			return null;
		}

		// Only the outer entries decide this stream's extent. A C2PA FileSpec
		// carries a second /Length inside its /F sub-dictionary, and a string
		// value can spell one anywhere, so both are blanked before the read.
		$length = $this->resolveLength($body, $offsets, $this->mask($object['dict'], true));

		if ($length === null || $length <= 0 || $length > $this->maxBytes) {
			return null;
		}

		$start = $object['stream'];
		$payload = \substr($body, $start, $length);

		if (\strlen($payload) !== $length) {
			return null;
		}

		// The length has to be the one that ends the stream. Without this, a
		// shorter length verifies a prefix of the payload as a complete
		// manifest or document while a reader extracts that prefix plus
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
	 * most reliable. A dishonest length is harmless — resolveStream() requires
	 * it to end exactly at `endstream`, and each validator requires the sliced
	 * payload to be complete.
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
}
