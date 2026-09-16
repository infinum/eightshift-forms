<?php

/**
 * Verifies that every embedded file in a PDF body is a C2PA (Content
 * Credentials) provenance manifest, by inspecting the payload bytes rather
 * than trusting the `/AFRelationship` or `/Subtype` labels, which are
 * attacker-controlled.
 *
 * Structure only — this makes no claim about the authenticity of the
 * manifest's signature.
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
	 * Bytes needed before the UUID check can run.
	 */
	private const int JUMBF_MIN_LENGTH = 32;

	/**
	 * Smallest sane JUMBF description box.
	 */
	private const int JUMBF_MIN_DESCRIPTION_LENGTH = 24;

	/**
	 * Does every embedded file in this body verify as a C2PA manifest?
	 *
	 * Fails closed: any parse ambiguity, unresolvable reference, filtered
	 * stream, indirect length or non-JUMBF payload returns false.
	 *
	 * @param string $body PDF bytes (raw or qpdf-expanded).
	 */
	public function allEmbeddedFilesAreC2paManifests(string $body): bool
	{
		// Objects hidden in compressed object streams are invisible to this
		// pass, so their contents cannot be vouched for.
		if ($this->containsToken($body, '/ObjStm')) {
			return false;
		}

		$references = $this->collectEmbeddedFileReferences($body);

		if ($references === []) {
			return false;
		}

		foreach ($references as [$number, $generation]) {
			$payload = $this->resolveStream($body, $number, $generation);

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
	 * Is the token present followed by a PDF name-token delimiter? Mirrors
	 * PdfScanner::containsDangerousKey so both agree on what "present" means.
	 *
	 * @param string $haystack Bytes to search.
	 * @param string $token    Token including its leading slash.
	 */
	private function containsToken(string $haystack, string $token): bool
	{
		return \preg_match('/' . \preg_quote($token, '/') . '(?=[\s\/<\[(%])/', $haystack) === 1;
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
	 * Resolve an indirect reference to its raw stream payload.
	 *
	 * Takes the last matching object definition, because an incremental
	 * update appends a newer generation of the same object number later in
	 * the file.
	 *
	 * @param string $body       PDF bytes.
	 * @param int    $number     Object number.
	 * @param int    $generation Object generation.
	 *
	 * @return string|null Payload bytes, or null when it cannot be resolved unambiguously.
	 */
	private function resolveStream(string $body, int $number, int $generation): ?string
	{
		$objectOffset = $this->findObject($body, $number, $generation);

		if ($objectOffset === null) {
			return null;
		}

		$streamKeyword = \strpos($body, 'stream', $objectOffset);

		if ($streamKeyword === false) {
			return null;
		}

		$dictionary = \substr($body, $objectOffset, $streamKeyword - $objectOffset);

		// A filtered stream is not the raw JUMBF this check expects.
		if ($this->containsToken($dictionary, '/Filter')) {
			return null;
		}

		// A C2PA FileSpec stream carries its own /Length inside the /F
		// sub-dictionary. Strip it so the outer, authoritative one is read.
		$outer = \preg_replace('/\/F[ \t\r\n]*<<.*?>>/s', '', $dictionary);
		$length = $this->resolveLength($body, \is_string($outer) ? $outer : $dictionary);

		if ($length === null || $length <= 0 || $length > Config::FILE_UPLOAD_PDF_C2PA_MAX_BYTES) {
			return null;
		}

		$start = $streamKeyword + \strlen('stream');

		if (\substr($body, $start, 2) === "\r\n") {
			$start += 2;
		} elseif (\substr($body, $start, 1) === "\n" || \substr($body, $start, 1) === "\r") {
			$start += 1;
		}

		$payload = \substr($body, $start, $length);

		return \strlen($payload) === $length ? $payload : null;
	}

	/**
	 * Byte offset of an object definition, or null when it cannot be located.
	 *
	 * Returns the *last* definition, because an incremental update appends a
	 * newer generation of the same object number later in the file. Matches
	 * on a line containing `%` are skipped: qpdf's `--qdf` mode emits
	 * `%% Original object ID: N G` comments that mention object numbers.
	 *
	 * @param string $body       PDF bytes.
	 * @param int    $number     Object number.
	 * @param int    $generation Object generation.
	 */
	private function findObject(string $body, int $number, int $generation): ?int
	{
		$pattern = '/(?<![0-9])' . $number . '[ \t\r\n]+' . $generation . '[ \t\r\n]+obj\b/';
		$count = \preg_match_all($pattern, $body, $matches, \PREG_OFFSET_CAPTURE);

		if ($count === false || $count === 0) {
			return null;
		}

		for ($index = \count($matches[0]) - 1; $index >= 0; $index--) {
			$offset = (int) $matches[0][$index][1];
			$preceding = \substr($body, 0, $offset);

			// PDFs use CR, LF or CRLF. Adobe writes bare CR, so looking back
			// for "\n" alone can stretch the "line" hundreds of bytes into
			// binary stream data and hit a stray `%`, rejecting a valid file.
			$lineFeed = \strrpos($preceding, "\n");
			$carriageReturn = \strrpos($preceding, "\r");
			$lineStart = \max($lineFeed === false ? -1 : $lineFeed, $carriageReturn === false ? -1 : $carriageReturn) + 1;

			if (!\str_contains(\substr($body, $lineStart, $offset - $lineStart), '%')) {
				return $offset;
			}
		}

		return null;
	}

	/**
	 * Resolve a stream `/Length`, accepting a direct integer or a one-hop
	 * indirect reference.
	 *
	 * The qpdf `--qdf` mode always writes the indirect form, so rejecting it
	 * would disable the exemption on exactly the hosts where verification is
	 * most reliable. A dishonest length is harmless — isC2paManifest() requires
	 * the JUMBF box to declare a length equal to the sliced payload.
	 *
	 * @param string $body       PDF bytes, needed to resolve an indirect length.
	 * @param string $dictionary Object dictionary with any /F sub-dict removed.
	 */
	private function resolveLength(string $body, string $dictionary): ?int
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

		$offset = $this->findObject($body, (int) $indirect[1], (int) $indirect[2]);

		if ($offset === null) {
			return null;
		}

		if (\preg_match('/obj[ \t\r\n]+(\d+)[ \t\r\n]+endobj/', \substr($body, $offset, 200), $value) !== 1) {
			return null;
		}

		return (int) $value[1];
	}

	/**
	 * Does this payload begin with a well-formed JUMBF superbox carrying the
	 * registered C2PA content-type UUID?
	 *
	 * @param string $payload Embedded file bytes.
	 */
	private function isC2paManifest(string $payload): bool
	{
		$length = \strlen($payload);

		if ($length < self::JUMBF_MIN_LENGTH || $length > Config::FILE_UPLOAD_PDF_C2PA_MAX_BYTES) {
			return false;
		}

		// The superbox declares its own length, and it must account for the
		// whole payload. This self-consistency is what an executable format
		// cannot satisfy while remaining executable.
		$declared = \unpack('N', \substr($payload, 0, 4));

		if ($declared === false || $declared[1] !== $length) {
			return false;
		}

		if (\substr($payload, 4, 4) !== self::JUMBF_SUPERBOX_TYPE) {
			return false;
		}

		$descriptionLength = \unpack('N', \substr($payload, 8, 4));

		if ($descriptionLength === false) {
			return false;
		}

		if ($descriptionLength[1] < self::JUMBF_MIN_DESCRIPTION_LENGTH || $descriptionLength[1] > $length) {
			return false;
		}

		if (\substr($payload, 12, 4) !== self::JUMBF_DESCRIPTION_TYPE) {
			return false;
		}

		return \substr($payload, 16, 16) === self::C2PA_CONTENT_TYPE_UUID;
	}
}
