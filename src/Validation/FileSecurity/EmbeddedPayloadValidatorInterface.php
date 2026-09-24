<?php

/**
 * Interface for embedded-file payload validators.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

/**
 * Contract for deciding whether one embedded file payload is a shape the PDF
 * scanner may exempt.
 *
 * EmbeddedFileVerifier owns the structure: it resolves every embedded stream
 * the way a reader would and hands each payload here. A validator judges only
 * the bytes and the name it is given, and must fail closed on anything it
 * does not recognise.
 */
interface EmbeddedPayloadValidatorInterface
{
	/**
	 * Largest payload, in bytes, this validator will ever accept.
	 *
	 * Lets the verifier refuse to slice a stream no enabled validator could
	 * accept, before any payload bytes are copied.
	 */
	public function maxBytes(): int;

	/**
	 * Is this payload an exemptable embedded file?
	 *
	 * @param string      $payload Decoded embedded file bytes.
	 * @param string|null $name    Name from the owning file specification's `/F` or `/UF`, or null when it names none.
	 */
	public function accepts(string $payload, ?string $name): bool;
}
