<?php

/**
 * The class for sending emails.
 *
 * @package EightshiftForms\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailer;

use CURLFile;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use Parsedown;

/**
 * Class Mailer
 */
class Mailer implements MailerInterface
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
			$this->getTemplate('to', $fields, $formId, $to),
			$this->getTemplate('subject', $fields, $formId, $subject),
			$this->getTemplate('message', $fields, $formId, $template, $responseFields),
			$this->getHeader(
				UtilsSettingsHelper::getSettingValue(SettingsMailer::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId),
				UtilsSettingsHelper::getSettingValue(SettingsMailer::SETTINGS_MAILER_SENDER_NAME_KEY, $formId)
			),
			$this->prepareFiles($files)
		);
	}

	/**
	 * Send fallback email
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return boolean
	 */
	public function fallbackIntegrationEmail(array $formDetails): bool
	{
		$isSettingsValid = \apply_filters(SettingsFallback::FILTER_SETTINGS_IS_VALID_NAME, []);

		if (!$isSettingsValid) {
			return false;
		}

		$response = $formDetails[UtilsConfig::FD_RESPONSE_OUTPUT_DATA] ?? [];

		$type = $response[UtilsConfig::IARD_TYPE] ?? '';
		$files = $response[UtilsConfig::IARD_FILES] ?? [];
		$formId = $response[UtilsConfig::IARD_FORM_ID] ?? '';
		$isDisabled = $response[UtilsConfig::IARD_IS_DISABLED] ?? false;

		$output = [
			UtilsConfig::IARD_STATUS => $response[UtilsConfig::IARD_STATUS] ?? UtilsConfig::STATUS_ERROR,
			UtilsConfig::IARD_MSG => $response[UtilsConfig::IARD_MSG] ?? '',
			UtilsConfig::IARD_TYPE => $type,
			UtilsConfig::IARD_PARAMS => $response[UtilsConfig::IARD_PARAMS] ?? [],
			UtilsConfig::IARD_RESPONSE => $response[UtilsConfig::IARD_RESPONSE] ?? [],
			UtilsConfig::IARD_CODE => $response[UtilsConfig::IARD_CODE] ?? 0,
			UtilsConfig::IARD_BODY => $response[UtilsConfig::IARD_BODY] ?? [],
			UtilsConfig::IARD_URL => $response[UtilsConfig::IARD_URL] ?? '',
			UtilsConfig::IARD_ITEM_ID => $response[UtilsConfig::IARD_ITEM_ID] ?? '',
			UtilsConfig::IARD_FORM_ID => $formId,
			UtilsConfig::FD_POST_ID => $formDetails[UtilsConfig::FD_POST_ID] ?? '',
			'debug' => [
				'forms' => UtilsGeneralHelper::getProjectVersion(),
				'php' => \phpversion(),
				'wp' => \get_bloginfo('version'),
				'url' => \get_bloginfo('url'),
				'userAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? \sanitize_text_field(\wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
				'time' => \wp_date('Y-m-d H:i:s'),
			],
		];

		// translators: %1$s replaces the integration name and %2$s formId.
		$subject = \sprintf(\__('Failed %1$s form: %2$s', 'eightshift-forms'), $type, $formId);
		$body = '<p>' . \esc_html__('It seems like there was an issue with the user\'s form submission. Here is all the data for debugging purposes.', 'eightshift-forms') . '</p>';

		if ($isDisabled) {
			$body = '<p>' . \esc_html__('It appears that your form is currently inactive, and as a result, all the data from your form is included in this email for you to manually input.', 'eightshift-forms') . '</p>';
			// translators: %1$s replaces the integration name and %2$s formId.
			$subject = \sprintf(\__('Disabled %1$s form: %2$s', 'eightshift-forms'), $type, $formId);
		}

		// translators: %s replaces the form name.
		$body .= '<p>' . \sprintf(\wp_kses_post(\__('Form Title: <strong>%s</strong>', 'eightshift-forms')), \get_the_title($formId)) . '</p>';

		$body .= '<pre style="white-space: pre-wrap; word-wrap: break-word; font-family: monospace;">' . \htmlentities(\wp_json_encode($output, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES), \ENT_QUOTES, 'UTF-8') . '</pre>';

		$filesOutput = [];
		if ($files) {
			switch ($type) {
				case SettingsGreenhouse::SETTINGS_TYPE_KEY:
					foreach ($files as $file) {
						if ($file instanceof CURLFile) {
							$filesOutput[] = $file->name;
						}
					}
					break;
				default:
					$filesOutput = UtilsGeneralHelper::recursiveFind($files, 'path');
					break;
			}
		}

		$to = UtilsSettingsHelper::getOptionValue(SettingsFallback::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY);
		$cc = UtilsSettingsHelper::getOptionValue(SettingsFallback::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY . '-' . $type);
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
	private function getType(): string
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
	private function getFrom(string $email, string $name): string
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
	private function getHeader(string $email, string $name = ''): array
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
	 * @param string $formId FormId value.
	 * @param string $template Additional description.
	 * @param array<string, mixed> $responseFields Custom field passed from the api response data for custom tags.
	 *
	 * @return string
	 */
	private function getTemplate(string $type, array $items, string $formId, string $template = '', array $responseFields = []): string
	{
		$params = \array_merge(
			$this->prepareParams($items, $formId),
			$responseFields
		);

		foreach ($params as $name => $value) {
			if (\is_array($value)) {
				$value = \implode(', ', $value);
			}

			$template = \str_replace("{" . $name . "}", $value, $template);
		}

		if ($type === 'message') {
			$parsedown = new Parsedown();

			return $parsedown->text($template);
		}

		return $template;
	}

	/**
	 * Prepare params.
	 *
	 * @param array<string, mixed> $params Params to prepare.
	 * @param string $formId FormId value.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function prepareParams(array $params, string $formId): array
	{
		$output = [];

		// Filter params.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsMailer::SETTINGS_TYPE_KEY, 'prePostParams']);
		if (\has_filter($filterName)) {
			$params = \apply_filters($filterName, $params, $formId) ?? [];
		}

		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

		foreach ($params as $param) {
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
	private function prepareFiles(array $files): array
	{
		$output = [];

		if (!$files) {
			return $output;
		}

		foreach ($files as $file) {
			$value = $file['value'] ?? [];

			if (!$value) {
				continue;
			}

			$output = [
				...$output,
				...$value,
			];
		}

		return $output;
	}
}
