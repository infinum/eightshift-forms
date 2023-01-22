<?php

/**
 * The class for sending emails.
 *
 * @package EightshiftForms\Integrations\Mailers
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailer;

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
	 * @param string $subject Email subject.
	 * @param string $template Email template.
	 * @param array<string, mixed> $files Email files.
	 * @param array<string, mixed> $fields Email fields.
	 *
	 * @return bool
	 */
	public function sendFormEmail(
		string $formId,
		string $to,
		string $subject,
		string $template = '',
		array $files = [],
		array $fields = []
	): bool;

	/**
	 * Send fallback email.
	 *
	 * @param array<mixed> $data Data to extract data from.
	 *
	 * @return boolean
	 */
	public function fallbackEmail(array $data): bool;
}
