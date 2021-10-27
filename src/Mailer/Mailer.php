<?php

/**
 * The class for sending emails.
 *
 * @package EightshiftForms\Mailers
 */

declare(strict_types=1);

namespace EightshiftForms\Mailer;

use EightshiftForms\Form\FormHelper;
use EightshiftForms\Settings\SettingsHelper;

/**
 * Class Mailer
 */
class Mailer implements MailerInterface
{
	/**
	 * Use form helper trait.
	 */
	use FormHelper;

	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

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
	): bool {
		// Get header options from form settings.
		$headers = $this->getHeader(
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId),
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_NAME_KEY, $formId)
		);

		// Generate HTML form for sending with form fields.
		$templateHtml = $this->getTemplate(
			$fields,
			$template
		);

		// Send email.
		return \wp_mail($to, $subject, $templateHtml, $headers, $files);
	}

	/**
	 * Get Email type.
	 * We use HTML for all.
	 *
	 * @return string
	 */
	protected function getType(): string
	{
		return 'Content-Type: text/html; charset=UTF-8';
	}

	/**
	 * Get email from.
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
	 * @return array<int, string>
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
	 * @param array<string, mixed> $items All items to output.
	 * @param string $template Additional description.
	 *
	 * @return string
	 */
	protected function getTemplate(array $items, string $template): string
	{
		foreach ($this->prepareFields($items) as $item) {
			$name = $item['name'] ?? '';
			$value = $item['value'] ?? '';

			$template = str_replace("{" . $name . "}", $value, $template);
		}

		return nl2br($template);
	}

	/**
	 * Prepare email fields.
	 *
	 * @param array<string, mixed> $fields Fields to prepare.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	protected function prepareFields(array $fields): array
	{
		$output = [];

		foreach ($fields as $value) {
			// Array used for radios.
			if (is_array($value)) {
				$value = $this->getRadioFieldCheckedItem($value);
			}

			$details = json_decode($value, true);

			$output[] = [
				'name' => $details['name'],
				'value' => $details['value'],
			];
		}

		return $output;
	}
}
