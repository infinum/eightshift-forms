<?php

/**
 * The class register mailer
 *
 * @package EightshiftForms\Mailers
 */

declare(strict_types=1);

namespace EightshiftForms\Mailer;

use EightshiftForms\Config\Config;

/**
 * Class Mailer
 */
class Mailer implements MailerInterface
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
	public function sendEmail(string $to, string $subject, string $template = '', array $headers = [], array $files = [], array $fields = []): bool
	{
		if (!$headers) {
			$headers = $this->getHeader(
				Config::getMailerSenderEmail(),
				Config::getMailerSenderName()
			);
		}

		if (!$template) {
			$template = $this->getTemplate($fields);
		}

		return \wp_mail($to, $subject, $template, $headers, $files);
	}

	/**
	 * Get Email type
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return 'Content-Type: text/html; charset=UTF-8';
	}

	/**
	 * Get email from string
	 *
	 * @param string $email Email string.
	 * @param string $name Name string.
	 *
	 * @return string
	 */
	public function getFrom(string $email, string $name = ''): string
	{
		if (empty($email)) {
			return [];
		}

		if (empty($name)) {
			return "From: {$email}";
		}

		return "From: {$name} <{$email}>";
	}

	/**
	 * Get Email Header
	 *
	 * @param string $email Email string.
	 * @param string $name Name string.
	 * 
	 * @return array
	 */
	public function getHeader(string $email, string $name = ''): array
	{
		return [
			$this->getType(),
			$this->getFrom($email, $name),
		];
	}

	/**
	 * HTML template for email.
	 *
	 * @param array $items All items to output.
	 *
	 * @return string
	 */
	public function getTemplate(array $items): string
	{
		$output = '';

		foreach ($this->prepareFields($items) as $item) {
			$label = $item['label'] ?? '';
			$value = $item['value'] ?? '';

			$output .= "<li><strong>{$label}</strong>: {$value}</li>";
		}

		return "
			<ul>
				{$output}
			</ul>
		";
	}

	/**
	 * Prepare email fields.
	 *
	 * @param array $fields Fields to prepare.
	 *
	 * @return array
	 */
	public function prepareFields(array $fields): array
	{
		$output = [];

		foreach ($fields as $key => $value) {
			$value = json_decode($value, true);

			$output[] = [
				'label' => $key,
				'value' => $value['value'],
			];
		}

		return $output;
	}
}
