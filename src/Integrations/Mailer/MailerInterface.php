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
	 * Send emails method.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param array<string, mixed> $responseTags Response tags.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	public function sendEmails(array $formDetails, array $responseTags = []): array;

	/**
	 * Send troubleshooting email.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param array<string, mixed> $data Data to send in the email.
	 * @param string $debugKey Debug key.
	 *
	 * @return boolean
	 */
	public function sendTroubleshootingEmail(
		array $formDetails,
		array $data,
		string $debugKey = ''
	): bool;

	/**
	 * Get debug key.
	 *
	 * @param array<string, mixed> $data Data to use.
	 *
	 * @return string
	 */
	public function getDebugKey(array $data): string;
}
