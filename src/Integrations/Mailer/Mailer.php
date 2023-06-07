<?php

/**
 * The class for sending emails.
 *
 * @package EightshiftForms\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailer;

use CURLFile;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use Parsedown;

/**
 * Class Mailer
 */
class Mailer implements MailerInterface
{
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
	 * @param array<string, mixed> $responseFields Custom field passed from the api response data for custom tags.
	 *
	 * @return bool
	 */
	public function sendFormEmail(
		string $formId,
		string $to,
		string $subject,
		string $template = '',
		array $files = [],
		array $fields = [],
		array $responseFields = []
	): bool {

		// Send email.
		return \wp_mail(
			$this->getTemplate('to', $fields, $to),
			$this->getTemplate('subject', $fields, $subject),
			$this->getTemplate('message', $fields, $template, $responseFields),
			$this->getHeader(
				$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId),
				$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_NAME_KEY, $formId)
			),
			$this->prepareFiles($files)
		);
	}

	/**
	 * Send fallback email
	 *
	 * @param array<mixed> $data Data to extract data from.
	 *
	 * @return boolean
	 */
	public function fallbackEmail(array $data): bool
	{
		$isSettingsValid = \apply_filters(SettingsFallback::FILTER_SETTINGS_IS_VALID_NAME, []);

		if (!$isSettingsValid) {
			return false;
		}

		$integration = $data['integration'] ?? '';
		$files = $data['files'] ?? [];
		$formId = $data['formId'] ?? '';
		$isDisabled = $data['isDisabled'] ?? false;

		// translators: %1$s replaces the integration name and %2$s formId.
		$subject = \sprintf(\__('Failed %1$s integration: %2$s', 'eightshift-forms'), $integration, $formId);
		$body = '<p>' . \esc_html__('It seems like there was an issue with the user\'s form submission. Here is all the data for debugging purposes.', 'eightshift-forms') . '</p>';

		if ($isDisabled) {
			$body = '<p>' . \esc_html__('It appears that your integration is currently inactive, and as a result, all the data from your form is included in this email for you to manually input.', 'eightshift-forms') . '</p>';
			// translators: %1$s replaces the integration name and %2$s formId.
			$subject = \sprintf(\__('Disabled %1$s integration: %2$s', 'eightshift-forms'), $integration, $formId);
		}

		// translators: %s replaces the form name.
		$body .= '<p>' . \sprintf(\wp_kses_post(\__('Form Title: <strong>%s</strong>', 'eightshift-forms')), \get_the_title($formId)) . '</p>';

		if (isset($data['files'])) {
			unset($data['files']);
		}
		if (isset($data['subject'])) {
			unset($data['subject']);
		}
		if (isset($data['isDisabled'])) {
			unset($data['isDisabled']);
		}

		$body .= '<pre style="white-space: pre-wrap; word-wrap: break-word; font-family: monospace;">' . \htmlentities(\wp_json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES), \ENT_QUOTES, 'UTF-8') . '</pre>';

		$filesOutput = [];
		if ($files) {
			switch ($integration) {
				case SettingsGreenhouse::SETTINGS_TYPE_KEY:
					foreach ($files as $file) {
						if ($file instanceof CURLFile) {
							$filesOutput[] = $file->name;
						}
					}
					break;
				default:
					$filesOutput = Helper::recursiveFind($files, 'path');
					break;
			}
		}

		$to = $this->getOptionValue(SettingsFallback::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY);
		$cc = $this->getOptionValue(SettingsFallback::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY . '-' . $integration);
		$headers = [
			$this->getType()
		];

		if ($cc) {
			$headers[] = "Cc: {$cc}";
		}

		// Send email.
		return \wp_mail($to, $subject, $body, $headers, $filesOutput);
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
	 * @param string $type Type for the template. Available: to, subject, message.
	 * @param array<string, mixed> $items All items to output.
	 * @param string $template Additional description.
	 * @param array<string, mixed> $responseFields Custom field passed from the api response data for custom tags.
	 *
	 * @return string
	 */
	protected function getTemplate(string $type, array $items, string $template = '', array $responseFields = []): string
	{
		$params = \array_merge(
			$this->prepareFields($items),
			$responseFields
		);

		foreach ($params as $name => $value) {
			$template = \str_replace("{" . $name . "}", $value, $template);
		}

		if ($type === 'message') {
			$parsedown = new Parsedown();

			return $parsedown->text($template);
		}

		return $template;
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

		$customFields = \array_flip(Components::flattenArray(AbstractBaseRoute::CUSTOM_FORM_PARAMS));

		foreach ($fields as $key => $param) {
			// Remove unnecessary fields.
			if (isset($customFields[$key])) {
				continue;
			}

			$name = $param['name'] ?? '';
			$value = $param['value'] ?? '';

			if (!$name) {
				continue;
			}

			$output[$name] = $value;
		}

		return $output;
	}

	/**
	 * Prepare files.
	 *
	 * @param array<string, mixed> $files Files to prepare.
	 *
	 * @return array<string>
	 */
	protected function prepareFiles(array $files): array
	{
		$output = [];

		if (!$files) {
			return $output;
		}

		foreach ($files as $items) {
			if (!$items) {
				continue;
			}

			foreach ($items as $file) {
				$path = $file['path'] ?? '';

				if (!$path) {
					continue;
				}

				$output[] = $path;
			}
		}

		return $output;
	}
}
