<?php

/**
 * Endpoint for sending an email.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/send-email
 *
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Captcha\BasicCaptcha;
use EightshiftForms\Exception\Unverified_Request_Exception;

/**
 * Class SendEmailRoute
 */
class SendEmailRoute extends BaseRoute
{

  /**
   * Route slug
   *
   * @var string
   */
	public const ENDPOINT_SLUG = '/send-email';

	public const TO_PARAM                          = 'email_to';
	public const SUBJECT_PARAM                     = 'email_subject';
	public const MESSAGE_PARAM                     = 'email_message';
	public const ADDITIONAL_HEADERS_PARAM          = 'email_additional_headers';
	public const SEND_CONFIRMATION_TO_SENDER_PARAM = 'email_send_copy_to_sender';
	public const CONFIRMATION_SUBJECT_PARAM        = 'email_confirmation_subject';
	public const CONFIRMATION_MESSAGE_PARAM        = 'email_confirmation_message';
	public const EMAIL_PARAM                       = 'email';

  /**
   * Basic Captcha object.
   *
   * @var BasicCaptcha
   */
	protected $basicCaptcha;

  /**
   * Construct object.
   *
   * @param BasicCaptcha $basicCaptcha Basic captcha object.
   */
	public function __construct(BasicCaptcha $basicCaptcha)
	{
		$this->basicCaptcha = $basicCaptcha;
	}

  /**
   * Method that returns rest response.
   *
   * @param  \WP_REST_Request $request Data got from endpoint url.
   *
   * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
   *                                is already an instance, WP_HTTP_Response, otherwise
   *                                returns a new WP_REST_Response instance.
   */
	public function routeCallback(\WP_REST_Request $request)
	{

		try {
			$params = $this->verifyRequest($request);
		} catch (Unverified_Request_Exception $e) {
			return rest_ensure_response($e->get_data());
		}

	  // If email was sent (and sending a copy back to sender is enabled) we need to validate this email is correct.
		if (
			$this->shouldSendEmailCopyToUser($params) &&
			! $this->isEmailSetAndValid($params)
		) {
			return $this->restResponseHandler('invalid-email-error');
		}

		$emailInfo            = $this->buildEmailInfoFromParams($params);
		$emailInfo['headers'] = $this->addDefaultHeaders($emailInfo['headers']);
		$response              = wp_mail($emailInfo['to'], $emailInfo['subject'], $emailInfo['message'], $emailInfo['headers']);

	  // If we need to send copy to sender.
		if ($this->shouldSendEmailCopyToUser($params)) {
			$emailConfirmationInfo = $this->buildEmailInfoFromParams($params, true);
			$responseConfirmation   = wp_mail($params[self::EMAIL_PARAM], $emailConfirmationInfo['subject'], $emailConfirmationInfo['message']);
		}

		if (
			! $response ||
			( $this->shouldSendEmailCopyToUser($params) && empty($responseConfirmation) )
		) {
			return $this->restResponseHandler('send-email-error');
		}

		return \rest_ensure_response([
			'code' => 200,
			'message' => esc_html__('Email sent', 'eightshift-forms'),
			'data' => [],
		]);
	}

  /**
   * Adds default email headers so email is interpreted as HTML.
   *
   * @param  string $headers Existing headers.
   * @return string
   */
	protected function addDefaultHeaders(string $headers): string
	{
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		return $headers;
	}

  /**
   * Check if we received a parameter to send an email confirmation to user.
   *
   * @param  array $params Query parameters sent to route.
   * @return bool
   */
	protected function shouldSendEmailCopyToUser(array $params): bool
	{
		return isset($params[self::SEND_CONFIRMATION_TO_SENDER_PARAM]) && filter_var($params[self::SEND_CONFIRMATION_TO_SENDER_PARAM], FILTER_VALIDATE_BOOL);
	}

  /**
   * Check if email param is set and valid.
   *
   * @param  array $params Query parameters sent to route.
   * @return boolean
   */
	protected function isEmailSetAndValid(array $params): bool
	{
		return isset($params[self::EMAIL_PARAM]) && filter_var($params[self::EMAIL_PARAM], FILTER_VALIDATE_EMAIL);
	}

  /**
   * Takes all parameters received in request and builds all subject / message info needed to send the email.
   * Must return array with the following keys:
   * - to
   * - subject
   * - message
   * - headers
   *
   * @param  array $params              Params received in request.
   * @param  bool  $isForConfirmation (Optional) If true, we build info for confirmation email sent to user rather than for the admin email.
   * @return array
   */
	protected function buildEmailInfoFromParams(array $params, bool $isForConfirmation = false): array
	{
		$subjectParam = $isForConfirmation ? self::CONFIRMATION_SUBJECT_PARAM : self::SUBJECT_PARAM;
		$messageParam = $isForConfirmation ? self::CONFIRMATION_MESSAGE_PARAM : self::MESSAGE_PARAM;

		return [
			'to' => ! empty($params[self::TO_PARAM]) ? wp_unslash(sanitize_text_field(strtolower($params[self::TO_PARAM]))) : '',
			'subject' => $this->replacePlaceholdersWithContent($params[$subjectParam], $params),
			'message' => $this->replacePlaceholdersWithContent($params[$messageParam], $params),
			'headers' => $params[self::ADDITIONAL_HEADERS_PARAM] ?? '',
		];
	}

  /**
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
	protected function getRequiredParams(): array
	{
		return [
			self::TO_PARAM,
			self::SUBJECT_PARAM,
			self::MESSAGE_PARAM,
		];
	}

  /**
   * Toggle if this route requires nonce verification
   *
   * @return bool
   */
	protected function requiresNonceVerification(): bool
	{
		return true;
	}

  /**
   * Returns allowed methods for this route.
   *
   * @return string|array
   */
	protected function getMethods()
	{
		return static::CREATABLE;
	}
}
