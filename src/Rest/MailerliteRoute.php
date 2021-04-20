<?php

/**
 * Endpoint for adding / updating contacts in Mailerlite.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/mailerlite
 *
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Captcha\BasicCaptcha;
use EightshiftForms\Exception\MissingFilterInfoException;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Integrations\Mailerlite\Mailerlite;
use Http\Client\Exception\HttpException;

/**
 * Class MailerliteRoute
 */
class MailerliteRoute extends BaseRoute implements Filters
{

  /**
   * Route slug
   *
   * @var string
   */
	public const ENDPOINT_SLUG = '/mailerlite';

  /**
   * Parameter for email.
   *
   * @var string
   */
	public const EMAIL_PARAM = 'email';

  /**
   * Parameter for group id.
   *
   * @var string
   */
	public const GROUP_ID_PARAM = 'groupId';

  /**
   * Mailerlite object.
   *
   * @var Mailerlite
   */
	protected $mailerlite;

  /**
   * Basic Captcha object.
   *
   * @var BasicCaptcha
   */
	protected $basicCaptcha;

  /**
   * Construct object
   *
   * @param Mailerlite   $mailerlite    Mailerlite object.
   * @param BasicCaptcha $basicCaptcha BasicCaptcha object.
   */
	public function __construct(Mailerlite $mailerlite, BasicCaptcha $basicCaptcha)
	{
		$this->mailerlite = $mailerlite;
		$this->basicCaptcha = $basicCaptcha;
	}

  /**
   * Method that returns rest response
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
		} catch (UnverifiedRequestException $e) {
			return rest_ensure_response($e->get_data());
		}

		$email = ! empty($params[self::EMAIL_PARAM]) ? strtolower($params[self::EMAIL_PARAM]) : '';
		$groupId = ! empty($params[self::GROUP_ID_PARAM]) ? (int) $params[self::GROUP_ID_PARAM] : 0;
		$mergeFieldParams = $this->unsetIrrelevantParams($params);
		$response = '';
		$message = '';

	  // Make sure we have the group ID.
		if (empty($groupId)) {
			return $this->restResponseHandler('mailerlite-missing-group-id');
		}

	  // Make sure we have an email.
		if (empty($email)) {
			return $this->restResponseHandler('mailerlite-missing-email');
		}

	  // Retrieve all entities from the "leads" Entity Set.
		try {
			$response = $this->mailerlite->addSubscriber($groupId, $email, $mergeFieldParams);
		} catch (MissingFilterInfoException $e) {
			return $this->restResponseHandler('mailerlite-missing-keys', [ 'message' => $e->getMessage() ]);
		} catch (HttpException $e) {
			$msg     = $e->getResponse()->getBody()->getContents();
			$message = json_decode($msg, true)['error']['message'];

			return $this->restResponseHandler('mailerlite-blocked-email', [ 'message' => $message ]);
		} catch (\Exception $e) {
			return $this->restResponseHandlerUnknownError([ 'error' => $e->getMessage() ]);
		}

		return \rest_ensure_response([
			'code' => 200,
			'data' => $response,
			'message' => ! empty($message) ? $message : \esc_html__('Successfully added', 'eightshift-forms'),
		]);
	}

  /**
   * Defines a list of required parameters which must be present in the request as GET parameters or it will error out.
   *
   * @return array
   */
	protected function getRequiredParams(): array
	{
		return [
			self::EMAIL_PARAM,
			self::GROUP_ID_PARAM,
		];
	}

  /**
   * Returns keys of irrelevant params which we don't want to send to CRM (even tho they're in form).
   *
   * @return array
   */
	protected function getIrrelevantParams(): array
	{
		return [
			BasicCaptcha::FIRST_NUMBER_KEY,
			BasicCaptcha::SECOND_NUMBER_KEY,
			BasicCaptcha::RESULT_KEY,
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
