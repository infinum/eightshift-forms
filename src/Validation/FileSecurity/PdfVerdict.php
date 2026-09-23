<?php

/**
 * Outcome of assessing a PDF body for dangerous keys.
 *
 * @package EightshiftForms\Validation\FileSecurity
 */

declare(strict_types=1);

namespace EightshiftForms\Validation\FileSecurity;

/**
 * Three outcomes, because a raw body cannot settle the question alone.
 */
enum PdfVerdict
{
	/**
	 * A dangerous key the Content Credentials exemption does not cover. Reject.
	 */
	case Unsafe;

	/**
	 * No dangerous key matched, or every match verified as a C2PA manifest.
	 */
	case Safe;

	/**
	 * Only the qpdf-expanded form can decide. The caller rejects when that
	 * form is unavailable.
	 */
	case Undetermined;
}
