<?php

/**
 * The class for sending emails.
 *
 * @package EightshiftForms\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailer;

use CURLFile;
use EightshiftForms\ActivityLog\ActivityLogHelper;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftForms_Parsedown as Parsedown;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

/**
 * Class Mailer
 */
class Mailer implements MailerInterface
{
	/**
	 * Instance variable of SecurityInterface data.
	 *
	 * @var SecurityInterface
	 */
	protected $security;

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Instance variable of SettingsFallbackDataInterface data.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance that injects classes.
	 *
	 * @param SecurityInterface $security Security interface.
	 * @param LabelsInterface $labels Labels interface.
	 * @param SettingsFallbackDataInterface $settingsFallback Settings fallback data interface.
	 */
	public function __construct(
		SecurityInterface $security,
		LabelsInterface $labels,
		SettingsFallbackDataInterface $settingsFallback
	) {
		$this->security = $security;
		$this->labels = $labels;
		$this->settingsFallback = $settingsFallback;
	}

	/**
	 * Send emails method.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param array<string, mixed> $responseTags Response tags.
	 *
	 * @throws BadRequestException If mailer is missing config.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	public function sendEmails(array $formDetails, array $responseTags = []): array
	{
		$formId = $formDetails[Config::FD_FORM_ID];

		// Bailout if settings are not ok.
		if (!\apply_filters(SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME, false, $formId)) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->labels->getLabel('mailerMissingConfig'),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_MAILER_MISSING_CONFIG,
				],
			);
			// phpcs:enable
		}

		// This data is set here because $formDetails can me modified in the previous filters.
		$params = $formDetails[Config::FD_PARAMS];
		$files = $formDetails[Config::FD_FILES];

		// Send email.
		$response = $this->internalSendEmail(
			$formId,
			SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_TO_KEY, $formId),
			SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_SUBJECT_KEY, $formId),
			SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_TEMPLATE_KEY, $formId),
			$files,
			$params,
			$responseTags,
			[
				'settings' => SettingsHelpers::getSettingValueGroup(SettingsMailer::SETTINGS_MAILER_TO_ADVANCED_KEY, $formId),
				'shouldAppend' => SettingsHelpers::isSettingCheckboxChecked(SettingsMailer::SETTINGS_MAILER_TO_ADVANCED_APPEND_KEY, SettingsMailer::SETTINGS_MAILER_TO_ADVANCED_APPEND_KEY, $formId),
			]
		);

		// If email fails.
		if (!$response) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->labels->getLabel('mailerErrorEmailSend'),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_MAILER_ERROR_EMAIL_SEND,
				],
			);
			// phpcs:enable
		}

		$this->sendConfirmationEmail($formId, $params, $files, $responseTags);

		return [
			AbstractBaseRoute::R_MSG => $this->labels->getLabel('mailerSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG => $formDetails,
				AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_MAILER_SUCCESS,
			],
		];
	}

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
	): bool {
		$formId = $formDetails[Config::FD_FORM_ID] ?? '';
		$type = $formDetails[Config::FD_TYPE] ?? '';
		$files = $formDetails[Config::FD_FILES] ?? [];

		$debugKeyValue = $debugKey ?: $this->getDebugKey($data);

		$activityLogId = ActivityLogHelper::setActivityLog(
			$this->security->getIpAddress('hash'),
			$debugKeyValue,
			$formId,
			$debugKey ? $data : $this->getDebugOutputActivityLog($data)
		);


		$to = SettingsHelpers::getOptionValue(SettingsFallback::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY);
		$cc = SettingsHelpers::getOptionValue(SettingsFallback::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY . '-' . $type);
		$headers = [
			$this->getType()
		];

		if (!$to && !$cc) {
			return false;
		}

		if (!$to && $cc) {
			$to = $cc;
			$cc = '';
		}

		if ($cc) {
			$headers[] = "Cc: {$cc}";
		}

		$data = $debugKey ? $data : $this->getDebugOutputLevel($data);

		// translators: %1$s replaces the form title, %2$s replaces the form id, %3$s replaces the debug key.
		$subject = \sprintf(\__('Troubleshooting form: %1$s (%2$s)(%3$s)', 'eightshift-forms'), \get_the_title($formId), \esc_html($formId), \esc_html($debugKeyValue));

		$body = '<p style="font-family: monospace;">' . \esc_html__('It seems like there was an issue with form on your website. Here is all the data for debugging purposes.', 'eightshift-forms') . '</p>';

		// translators: %s replaces the form title.
		$body .= '<p style="font-family: monospace;">' . \sprintf(\wp_kses_post(\__('Form Title: <strong>%s</strong>', 'eightshift-forms')), \get_the_title($formId)) . '</p>';
		// translators: %s replaces the form id.
		$body .= '<p style="font-family: monospace;">' . \sprintf(\wp_kses_post(\__('Form ID: <strong>%s</strong>', 'eightshift-forms')), \esc_html($formId)) . '</p>';

		if ($activityLogId) {
			// translators: %s replaces the activity log id.
			$body .= '<p style="font-family: monospace;">' . \sprintf(\wp_kses_post(\__('Activity Log ID: <strong>%s</strong>', 'eightshift-forms')), \esc_html((string) $activityLogId)) . '</p>';
		}

		if ($debugKeyValue) {
			// translators: %s replaces the debug key.
			$body .= '<p style="font-family: monospace;">' . \sprintf(\wp_kses_post(\__('Debug Key: <strong>%s</strong>', 'eightshift-forms')), \esc_html($debugKeyValue)) . '</p>';
			// translators: %s replaces the debug key description.
			$body .= '<p style="font-family: monospace;">' . \sprintf(\wp_kses_post(\__('Debug Key description: <strong>%s</strong>', 'eightshift-forms')), \esc_html($this->settingsFallback->getFlagLabel($debugKeyValue))) . '</p>';
		}

		// translators: %s replaces the website url.
		$body .= '<p style="font-family: monospace;">' . \sprintf(\wp_kses_post(\__('Website url: <strong>%s</strong>', 'eightshift-forms')), \esc_html(\get_bloginfo('url'))) . '</p>';

		// translators: %s replaces the debug data.
		$body .= '<p style="font-family: monospace;">' . \esc_html__('Debug data:', 'eightshift-forms') . '</p>';

		$body .= '<pre style="white-space: pre-wrap; word-wrap: break-word; font-family: monospace;">' . \htmlentities(\wp_json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES), \ENT_QUOTES, 'UTF-8') . '</pre>';

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
					$filesOutput = Helpers::recursiveArrayFind($files, 'path');
					break;
			}
		}

		return \wp_mail($to, $subject, $body, $headers, $filesOutput);
	}

	/**
	 * Get debug key.
	 *
	 * @param array<string, mixed> $data Data to use.
	 *
	 * @return string
	 */
	public function getDebugKey(array $data): string
	{
		return $data[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG_KEY] ?? '';
	}

	/**
	 * Send confirmation email.
	 *
	 * @param string $formId Form ID.
	 * @param array<mixed> $params Params array.
	 * @param array<mixed> $files Files array.
	 * @param array<string, mixed> $responseTags Response tags.
	 *
	 * @return boolean
	 */
	private function sendConfirmationEmail(string $formId, array $params, array $files, array $responseTags = []): bool
	{
		// Check if Mailer data is set and valid.
		$isConfirmationValid = \apply_filters(SettingsMailer::FILTER_SETTINGS_IS_VALID_CONFIRMATION_NAME, false, $formId);

		if (!$isConfirmationValid) {
			return false;
		}

		$senderEmail = FormsHelper::getParamValue(SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_EMAIL_FIELD_KEY, $formId), $params);

		if (!$senderEmail) {
			return false;
		}

		// Send email.
		return $this->internalSendEmail(
			$formId,
			$senderEmail,
			SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId),
			SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId),
			$files,
			$params,
			$responseTags
		);
	}

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
	 * @param array<string, mixed> $toAdvanced Advanced conditions for the email to.
	 *
	 * @return bool
	 */
	private function internalSendEmail(
		string $formId,
		string $to,
		string $subject,
		string $template = '',
		array $files = [],
		array $fields = [],
		array $responseFields = [],
		array $toAdvanced = []
	): bool {

		$params = \array_merge(
			$this->prepareParams(\array_merge($fields, $files), $formId),
			$responseFields
		);

		$body = '<html><body>' . $this->getTemplate($params, true, $template) . '</body></html>';

		$filterName = HooksHelpers::getFilterName(['integrations', SettingsMailer::SETTINGS_TYPE_KEY, 'bodyTemplate']);
		if (\has_filter($filterName)) {
			$body = \apply_filters($filterName, $body, $formId, $template, $params);
		}

		// Send email.
		return \wp_mail(
			$this->getTemplate($params, false, $this->getAdvancedConditions($to, $toAdvanced, $params)),
			$this->getTemplate($params, false, $subject),
			$body,
			$this->getHeader(
				SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId),
				SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_SENDER_NAME_KEY, $formId)
			),
			$this->prepareFiles($files)
		);
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
	 * @param array<string, mixed> $params Params to replace in the template.
	 * @param boolean $shouldParse Should the template be parsed.
	 * @param string $template Additional description.
	 *
	 * @return string
	 */
	private function getTemplate(array $params, bool $shouldParse = false, string $template = ''): string
	{
		foreach ($params as $name => $value) {
			if (\is_array($value)) {
				$value = \implode(', ', $value);
			}

			$template = \str_replace("{" . $name . "}", (string) $value, $template);
		}

		if ($shouldParse) {
			$parsedown = new Parsedown();

			return $parsedown->text($template);
		}

		return $template;
	}

	/**
	 * Prepare params.
	 *
	 * @param array<string, mixed> $params Params to prepare.
	 * @param string $formId Form ID.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function prepareParams(array $params, string $formId): array
	{
		$output = [];

		// Remove unnecessary params.
		$params = GeneralHelpers::removeUnnecessaryParamFields($params);

		$shouldSendEmptyFields = SettingsHelpers::isSettingCheckboxChecked(SettingsMailer::SETTINGS_MAILER_SEND_EMPTY_FIELDS_KEY, SettingsMailer::SETTINGS_MAILER_SEND_EMPTY_FIELDS_KEY, $formId);

		foreach ($params as $param) {
			$name = $param['name'] ?? '';
			$value = $param['value'] ?? '';
			$type = $param['type'] ?? '';

			if (!$name || !$type) {
				continue;
			}

			if (!$shouldSendEmptyFields && !$value) {
				continue;
			}

			if ($type === 'file') {
				$value = \array_map(
					static function (string $file) {
						$filename = \pathinfo($file, \PATHINFO_FILENAME);
						$extension = \pathinfo($file, \PATHINFO_EXTENSION);
						return "{$filename}.{$extension}";
					},
					$value
				);
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

	/**
	 * Get advanced conditions.
	 *
	 * @param string $default Default email.
	 * @param array<string, mixed> $advanced Advanced conditions.
	 * @param array<string, mixed> $params Params.
	 *
	 * @return string
	 */
	private function getAdvancedConditions(string $default, array $advanced, array $params): string
	{
		if (!$advanced) {
			return $default;
		}

		$settings = $advanced['settings'] ?? [];

		if (!$settings) {
			return $default;
		}

		$output = [];

		foreach ($settings as $item) {
			$email = $item[0] ?? '';
			$conditions = $item[1] ?? '';

			if (!$email || !$conditions) {
				continue;
			}

			if (!$this->evaluateAdvancedConditionLogic($conditions, $params)) {
				continue;
			}

			$output[] = $email;
		}

		if (!$output) {
			return $default;
		}

		if ($advanced['shouldAppend'] ?? false) {
			$output[] = $default;
		}

		return \implode(',', $output);
	}

	/**
	 * Evaluate logic.
	 *
	 * Condition logic examples:
	 * rating=3&checkboxes=check-1---check-2
	 *
	 * Operators between conditions can be "&" (AND) or "|" (OR).
	 * Operators between values can be "---" and that means OR.
	 * Negation is supported by adding "!=" to the condition.
	 *
	 * @param string $logic Logic string.
	 * @param array<string, mixed> $params Params.
	 *
	 * @return bool
	 */
	private function evaluateAdvancedConditionLogic(string $logic, array $params): bool
	{
		// Tokenize the logic string.
		$tokens = \preg_split('/([&|])/', $logic, -1, \PREG_SPLIT_DELIM_CAPTURE);

		// Convert conditions into boolean values.
		$processedTokens = [];

		foreach ($tokens as $token) {
			$token = \trim($token);
			if ($token === '&' || $token === '|') {
				$processedTokens[] = $token;
				continue;
			}

			// Check if the condition is a negation.
			$isNegation = \strpos($token, '!=') !== false;
			$operator = $isNegation ? '!=' : '=';

			[$key, $value] = \explode($operator, $token, 2);
			$key = \trim($key);
			$values = \explode('---', \trim($value)); // Split values by ---.

			$conditionResult = isset($params[$key]) && \array_intersect((array)$params[$key], $values);

			// If it's a negation, invert the result.
			if ($isNegation) {
				$conditionResult = !$conditionResult;
			}

			$processedTokens[] = !empty($conditionResult);
		}

		// Evaluate with correct precedence (AND before OR).
		$stack = [];
		$currentOp = null;

		foreach ($processedTokens as $token) {
			if ($token === '&' || $token === '|') {
				$currentOp = $token;
			} else {
				if ($currentOp === '&') {
					$last = \array_pop($stack);
					$stack[] = $last && $token;
				} else {
					$stack[] = $token;
				}
			}
		}

		return \in_array(true, $stack, true);
	}

	/**
	 * Get debug output details.
	 *
	 * @param array<string, mixed> $data Data to use.
	 *
	 * @return array<string, mixed>
	 */
	private function getDebugOutputLevel(array $data): array
	{
		$logLevel = SettingsHelpers::getOptionValue(SettingsFallback::SETTINGS_FALLBACK_LOG_LEVEL_KEY);

		$debugData = $data[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG] ?? [];

		$output = [
			AbstractBaseRoute::R_MSG => $data[AbstractBaseRoute::R_MSG] ?? '',
			AbstractBaseRoute::R_CODE => $data[AbstractBaseRoute::R_CODE] ?? '',
			AbstractBaseRoute::R_STATUS => $data[AbstractBaseRoute::R_STATUS] ?? '',
			AbstractBaseRoute::R_DATA => $data[AbstractBaseRoute::R_DATA] ?? [],
		];

		if (isset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG])) {
			unset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG]);
		}

		switch ($logLevel) {
			case 'minimal':
				if (isset($debugData[AbstractBaseRoute::R_DEBUG_KEY])) {
					$output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG_KEY] = $debugData[AbstractBaseRoute::R_DEBUG_KEY];
				}

				return $output;
			case 'fullMax':
				if (isset($debugData[AbstractBaseRoute::R_DEBUG_KEY])) {
					$output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG_KEY] = $debugData[AbstractBaseRoute::R_DEBUG_KEY];
				}
				if (isset($debugData[AbstractBaseRoute::R_DEBUG])) {
					$output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG] = $debugData[AbstractBaseRoute::R_DEBUG];
				}
				if (isset($debugData[AbstractBaseRoute::R_DEBUG_USER])) {
					$output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG_USER] = $debugData[AbstractBaseRoute::R_DEBUG_USER];
				}
				if (isset($debugData[AbstractBaseRoute::R_DEBUG_SUCCESS_ADDITIONAL_DATA])) {
					$output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG_SUCCESS_ADDITIONAL_DATA] = $debugData[AbstractBaseRoute::R_DEBUG_SUCCESS_ADDITIONAL_DATA];
				}
				if (isset($debugData[AbstractBaseRoute::R_DEBUG_REQUEST])) {
					$output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG_REQUEST] = $debugData[AbstractBaseRoute::R_DEBUG_REQUEST];
				}

				return $output;
			default:
				if (isset($debugData[AbstractBaseRoute::R_DEBUG_KEY])) {
					$output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG_KEY] = $debugData[AbstractBaseRoute::R_DEBUG_KEY];
				}
				if (isset($debugData[AbstractBaseRoute::R_DEBUG])) {
					$output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG] = $debugData[AbstractBaseRoute::R_DEBUG];

					if (isset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_ICON])) {
						unset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_ICON]);
					}

					if (isset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_FIELDS])) {
						unset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_FIELDS]);
					}
					if (isset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_FIELDS_ONLY])) {
						unset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_FIELDS_ONLY]);
					}
					if (isset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_FIELD_NAMES])) {
						unset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_FIELD_NAMES]);
					}
					if (isset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_FIELD_NAMES_FULL])) {
						unset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_FIELD_NAMES_FULL]);
					}
					if (isset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_STEPS_SETUP])) {
						unset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_STEPS_SETUP]);
					}
					if (isset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_FILES_UPLOAD])) {
						unset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_FILES_UPLOAD]);
					}
					if (isset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_FILES])) {
						unset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_FILES]);
					}
					if (isset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_API_STEPS])) {
						unset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_API_STEPS]);
					}
					if (isset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_ACTION])) {
						unset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_ACTION]);
					}
					if (isset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_ACTION_EXTERNAL])) {
						unset($output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG][Config::FD_ACTION_EXTERNAL]);
					}
				}
				if (isset($debugData[AbstractBaseRoute::R_DEBUG_USER])) {
					$output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG_USER] = $debugData[AbstractBaseRoute::R_DEBUG_USER];
				}
				if (isset($debugData[AbstractBaseRoute::R_DEBUG_SUCCESS_ADDITIONAL_DATA])) {
					$output[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG][AbstractBaseRoute::R_DEBUG_SUCCESS_ADDITIONAL_DATA] = $debugData[AbstractBaseRoute::R_DEBUG_SUCCESS_ADDITIONAL_DATA];
				}

				return $output;
		}
	}

	/**
	 * Get debug output for activity log.
	 *
	 * @param array<mixed> $data Data to use.
	 *
	 * @return array<string, mixed>
	 */
	private function getDebugOutputActivityLog(array $data): array
	{
		$debugData = $data[AbstractBaseRoute::R_DATA][AbstractBaseRoute::R_DEBUG] ?? [];

		$output = [];

		if (isset($debugData[AbstractBaseRoute::R_DEBUG_KEY])) {
			$output[AbstractBaseRoute::R_DEBUG_KEY] = $debugData[AbstractBaseRoute::R_DEBUG_KEY];
		}

		if (isset($debugData[AbstractBaseRoute::R_DEBUG][Config::FD_PARAMS_ORIGINAL])) {
			$output['params'] = $debugData[AbstractBaseRoute::R_DEBUG][Config::FD_PARAMS_ORIGINAL];
		}

		if (isset($debugData[AbstractBaseRoute::R_DEBUG][Config::FD_RESPONSE_OUTPUT_DATA][Config::IARD_RESPONSE])) {
			$output['integrationResponse'] = $debugData[AbstractBaseRoute::R_DEBUG][Config::FD_RESPONSE_OUTPUT_DATA][Config::IARD_RESPONSE];
		}

		if (isset($debugData[AbstractBaseRoute::R_DEBUG][Config::FD_RESPONSE_OUTPUT_DATA][Config::IARD_BODY])) {
			$output['integrationBody'] = $debugData[AbstractBaseRoute::R_DEBUG][Config::FD_RESPONSE_OUTPUT_DATA][Config::IARD_BODY];
		}

		return $output;
	}
}
