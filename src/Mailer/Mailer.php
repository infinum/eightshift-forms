<?php

/**
 * The class register mailer
 *
 * @package EightshiftForms\Mailers
 */

declare(strict_types=1);

namespace EightshiftForms\Mailer;

use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Settings\FormOption;

/**
 * Class Mailer
 */
class Mailer implements MailerInterface
{
	/**
	 * Use General helper trait.
	 */
	use TraitHelper;

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
	public function sendFormEmail(string $formId, string $to, array $files = [], array $fields = []): bool
	{
		$headers = $this->getHeader(
			$this->getSettingsValue(FormOption::MAILER_SENDER_EMAIL_KEY, $formId),
			$this->getSettingsValue(FormOption::MAILER_SENDER_NAME_KEY, $formId)
		);

		$template = $this->getTemplate(
			$fields,
			$this->getSettingsValue(FormOption::MAILER_TEMPLATE_KEY, $formId)
		);

		$subject = $this->getSettingsValue(FormOption::MAILER_SUBJECT_KEY, $formId);

		return \wp_mail($to, $subject, $template, $headers, $files);
	}

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
	protected function getType(): string
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
	protected function getFrom(string $email, string $name): string
	{
		if (empty($email)) {
			return '';
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
	protected function getHeader(string $email, string $name = ''): array
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
	 * @param string $desc Additional description.
	 *
	 * @return string
	 */
	protected function getTemplate(array $items, string $desc = ''): string
	{
		$output = '';

		foreach ($this->prepareFields($items) as $item) {
			$label = $item['label'] ?? '';
			$value = $item['value'] ?? '';

			$output .= "<li><strong>{$label}</strong>: {$value}</li>";
		}

		return "
			{$desc}

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
	protected function prepareFields(array $fields): array
	{
		$output = [];

		foreach ($fields as $key => $value) {
			$value = json_decode($value, true);

			$output[] = [
				'label' => $value['label'],
				'value' => $value['value'],
			];
		}

		return $output;
	}
}
