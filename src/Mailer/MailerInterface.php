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

	/**
	 * Get Email type
	 *
	 * @return string
	 */
	public function getType(): string;

	/**
	 * Get email from string
	 *
	 * @param string $email Email string.
	 * @param string $name Name string.
	 *
	 * @return string
	 */
	public function getFrom(string $email, string $name = ''): string;

	/**
	 * Get Email Header
	 *
	 * @param string $email Email string.
	 * @param string $name Name string.
	 * 
	 * @return array
	 */
	public function getHeader(string $email, string $name = ''): array;

	/**
	 * HTML template for email.
	 *
	 * @param array $items All items to output.
	 *
	 * @return string
	 */
	public function getTemplate(array $items): string;

	/**
	 * Prepare email fields.
	 *
	 * @param array $fields Fields to prepare.
	 *
	 * @return array
	 */
	public function prepareFields(array $fields): array;
}
