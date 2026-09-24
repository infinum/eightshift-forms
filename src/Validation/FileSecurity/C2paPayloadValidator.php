<?php

/**
 * Verifies that an embedded file payload is a C2PA (Content Credentials)
 * provenance manifest, by inspecting the payload bytes rather than trusting
 * the `/AFRelationship` or `/Subtype` labels, which are attacker-controlled.
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
 * Structural validator for embedded C2PA manifests.
 */
final class C2paPayloadValidator implements EmbeddedPayloadValidatorInterface
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
	 * Largest manifest this validator accepts.
	 */
	public function maxBytes(): int
	{
		return Config::FILE_UPLOAD_PDF_C2PA_MAX_BYTES;
	}

	/**
	 * Is this payload a complete C2PA manifest store?
	 *
	 * The name is not consulted. C2PA signers do not agree on one, and the
	 * payload shape is the whole test.
	 *
	 * The payload must be exactly one JUMBF superbox whose declared length
	 * accounts for every byte, carrying the registered C2PA content-type UUID,
	 * whose children tile it with no slack. Trailing or interstitial bytes that
	 * no box claims are what a smuggled payload needs, so any slack rejects.
	 *
	 * @param string      $payload Embedded file bytes.
	 * @param string|null $name    Unused.
	 */
	public function accepts(string $payload, ?string $name): bool
	{
		$length = \strlen($payload);

		if ($length < self::JUMBF_MIN_LENGTH || $length > $this->maxBytes()) {
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
