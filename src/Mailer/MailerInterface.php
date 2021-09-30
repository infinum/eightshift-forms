<?php

/**
 * The class for sending emails.
 *
 * @package EightshiftForms\Mailers
 */

declare(strict_types=1);

namespace EightshiftForms\Mailer;

/**
 * Class Mailer
 */
interface MailerInterface
{

	/**
	 * Send email function for form ID.
	 *
	 * @param string $formId Form Id.
	 * @param string $to Email to.
	 * @param array $files Email files.
	 * @param array $fields Email fields.
	 *
	 * @return bool
	 */
	public function sendFormEmail(string $formId, string $to, array $files = [], array $fields = []): bool;
}