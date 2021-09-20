<?php

/**
 * The class register mailer
 *
 * @package EightshiftForms\Mailers
 */

declare(strict_types=1);

namespace EightshiftForms\Mailer;

use EightshiftForms\Exception\UnverifiedRequestException;

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
	 * @return boolean
	 */
	public function sendFormEmail(string $formId, string $to, array $files = [], array $fields = []): bool;

	/**
	 * Send email function
	 *
	 * @param string $to Email to.
	 * @param string $subject Email subject.
	 * @param string $template Email template.
	 * @param array $headers Email header.
	 * @param array $files Email files.
	 * @param array $fields Email fields.
	 *
	 * @return boolean
	 */
	public function sendEmail(string $to, string $subject, string $template = '', array $headers = [], array $files = [], array $fields = []): bool;
}
