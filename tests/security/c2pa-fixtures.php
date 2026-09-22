<?php

/**
 * JUMBF box builders shared by the C2PA fixtures and harnesses.
 *
 * A C2PA manifest store is a JUMBF tree (ISO/IEC 19566-5). Every box is a BE32
 * length covering its own 8-byte header, a 4-byte type, then contents. The
 * store is a 'jumb' superbox whose first child is a 'jumd' description carrying
 * the registered C2PA UUID, followed by 'jumb' manifest superboxes.
 *
 * One definition, used by verify-c2pa.php, verify-pdf-scanner.php and
 * generate-test-files.sh. The verifier requires these boxes to tile a payload
 * exactly, so a second copy that drifted by one byte would not fail loudly —
 * it would keep passing while no longer standing for a manifest.
 *
 * @package EightshiftForms\Tests\Security
 */

declare(strict_types=1);

/**
 * Builders for C2PA manifest payloads.
 */
final class C2paFixtures
{
	/**
	 * Registered C2PA manifest store content-type UUID.
	 */
	public const string STORE_UUID = "\x63\x32\x70\x61\x00\x11\x00\x10\x80\x00\x00\xaa\x00\x38\x9b\x71";

	/**
	 * Registered C2PA manifest content-type UUID.
	 */
	public const string MANIFEST_UUID = "\x63\x32\x6d\x61\x00\x11\x00\x10\x80\x00\x00\xaa\x00\x38\x9b\x71";

	/**
	 * A JUMBF box: BE32 length covering the header, 4-byte type, contents.
	 *
	 * @param string $type     Four-byte box type.
	 * @param string $contents Box contents.
	 */
	public static function box(string $type, string $contents): string
	{
		return \pack('N', \strlen($contents) + 8) . $type . $contents;
	}

	/**
	 * A JUMBF description box: content-type UUID, toggles, null-terminated label.
	 *
	 * @param string $uuid  Content-type UUID.
	 * @param string $label Box label.
	 */
	public static function jumd(string $uuid, string $label): string
	{
		return self::box('jumd', $uuid . "\x03" . $label . "\x00");
	}

	/**
	 * A JUMBF superbox: its description box followed by content boxes.
	 *
	 * @param string $uuid     Content-type UUID for the description.
	 * @param string $label    Description label.
	 * @param string $contents Content boxes.
	 */
	public static function superbox(string $uuid, string $label, string $contents): string
	{
		return self::box('jumb', self::jumd($uuid, $label) . $contents);
	}

	/**
	 * One C2PA manifest superbox, holding a single opaque claim leaf.
	 *
	 * @param string $label Manifest label.
	 */
	public static function manifestBox(string $label = 'urn:uuid:test-fixture'): string
	{
		return self::superbox(self::MANIFEST_UUID, $label, self::box('c2cl', '{"claim":"structure-only-test-fixture"}'));
	}

	/**
	 * A complete C2PA manifest store, the payload a real `/EF` stream carries.
	 *
	 * @param string $extra Additional content boxes appended to the store.
	 */
	public static function store(string $extra = ''): string
	{
		return self::superbox(self::STORE_UUID, 'c2pa', self::manifestBox() . $extra);
	}

	/**
	 * A plausible JUMBF header glued in front of an unrelated payload.
	 *
	 * The shape a shallow "does this start with a C2PA superbox" check accepts
	 * and the tiling check rejects: the header's own boxes claim 30 bytes while
	 * the outer length covers everything, leaving the payload unclaimed.
	 *
	 * @param string $payload Bytes to smuggle behind the header.
	 */
	public static function prefixedBlob(string $payload): string
	{
		$blob = 'jumb' . \pack('N', 30) . 'jumd' . self::STORE_UUID . $payload;

		return \pack('N', \strlen($blob) + 4) . $blob;
	}
}
