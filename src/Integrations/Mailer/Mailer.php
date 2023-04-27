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
			$this->getTemplate($fields, $to),
			$this->getTemplate($fields, $subject),
			$this->getTemplate($fields, $template, $responseFields),
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
		$url = $data['url'] ?? '';
		$files = $data['files'] ?? [];
		$response = $data['response'] ? \wp_json_encode($data['response']) : '';
		$formId = $data['formId'] ?? '';
		$itemId = $data['itemId'] ?? '';
		$params = $data['params'] ?? [];
		$code = $data['code'] ?? 400;
		$body = $data['body'] ? \wp_json_encode($data['body']) : '';

		if (\is_array($itemId)) {
			$itemId = \implode(AbstractBaseRoute::DELIMITER, $itemId);
		}

		$paramsOutput = "
			<p><strong>Form Details:</strong></p>
			<ul>
				<li>formId: {$formId}</li>
				<li>itemId: {$itemId}</li>
				<li>integration: {$integration}</li>
				<li>response code: {$code}</li>
				<li>url: {$url}</li>
			</ul>
		";

		if ($params) {
			$paramsOutput .= "<p><strong>Data sent to integration:</strong></p>";
			$paramsOutput .= $this->fallbackEmailPrepareParams($params);
		}

		if ($response) {
			$paramsOutput .= "
				<p><strong>Data got from integration response:</strong></p>
				{$response}
			";
		}

		if ($body) {
			$paramsOutput .= "
				<p><strong>Data got from integration response body:</strong></p>
				{$body}
			";
		}

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
		// translators: %1$s replaces the integration name and %2$s formId.
		$subject = \sprintf(\__('Your %1$s form failed: %2$s', 'eightshift-forms'), $integration, $formId);
		// translators: %s replaces the parameters list html.
		$templateHtml = \sprintf(\__("<p>It looks like something went wrong with the users form submition, here is all the data to debug.</p>%s", 'eightshift-forms'), $paramsOutput);
		$headers = [
			$this->getType()
		];

		if ($cc) {
			$headers[] = "Cc: {$cc}";
		}

		// Send email.
		return \wp_mail($to, $subject, $templateHtml, $headers, $filesOutput);
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
	 * @param array<string, mixed> $responseFields Custom field passed from the api response data for custom tags.
	 *
	 * @return string
	 */
	protected function getTemplate(array $items, string $template = '', array $responseFields = []): string
	{
		$params = \array_merge(
			$this->prepareFields($items),
			$responseFields
		);

		foreach ($params as $name => $value) {
			$template = \str_replace("{" . $name . "}", $value, $template);
		}

		return \str_replace("\n", '<br />', $template);
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

			if (!$name || !$value) {
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

	/**
	 * Prepare recursive params for fallback email.
	 *
	 * @param array<mixed> $params Params to check.
	 *
	 * @return string
	 */
	private function fallbackEmailPrepareParams(array $params): string
	{
		$output = '';

		foreach ($params as $paramKey => $paramValue) {
			if (\is_array($paramValue)) {
				$paramValueOutput = '<ul>';
				$paramValueOutput .= $this->fallbackEmailPrepareParams($paramValue);
				$paramValueOutput .= '</ul>';

				$paramValue = $paramValueOutput;
			}

			$output .= "<li>{$paramKey}: {$paramValue}</li>";
		}

		return $output;
	}
}
