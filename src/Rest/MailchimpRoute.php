<?php

/**
 * Endpoint for adding / updating contacts in Mailchimp.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/mailchimp
 *
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Captcha\BasicCaptcha;
use EightshiftForms\Exception\MissingFilterInfoException;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Integrations\Mailchimp\Mailchimp;
use GuzzleHttp\Exception\ClientException;

/**
 * Class MailchimpRoute
 */
class MailchimpRoute extends BaseRoute implements Filters
{

  /**
   * Route slug
   *
   * @var string
   */
	public const ENDPOINT_SLUG = '/mailchimp';

  /**
   * Parameter for email.
   *
   * @var string
   */
	public const EMAIL_PARAM = 'email';

  /**
   * Parameter for list ID.
   *
   * @var string
   */
	public const LIST_ID_PARAM = 'list-id';

  /**
   * Parameter for member tag.
   *
   * @var string
   */
	public const TAGS_PARAM = 'tags';

  /**
   * Parameter for toggle if we modify Mailchimp user data if they already exist.
   *
   * @var string
   */
	public const ADD_EXISTING_MEMBERS_PARAM = 'add-existing-members';

  /**
   * Error if user exists
   *
   * @var string
   */
	public const ERROR_USER_EXISTS = 'Member Exists';

  /**
   * Mailchimp object.
   *
   * @var Mailchimp
   */
	protected $mailchimp;

  /**
   * Basic Captcha object.
   *
   * @var BasicCaptcha
   */
	protected $basicCaptcha;

  /**
   * Construct object
   *
   * @param Mailchimp    $mailchimp     Mailchimp object.
   * @param BasicCaptcha $basicCaptcha BasicCaptcha object.
   */
	public function __construct(Mailchimp $mailchimp, BasicCaptcha $basicCaptcha)
	{
		$this->mailchimp = $mailchimp;
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

		$listId                     = $params[self::LIST_ID_PARAM] ?? '';
		$email                       = ! empty($params[self::EMAIL_PARAM]) ? strtolower($params[self::EMAIL_PARAM]) : '';
		$tags                        = $params[self::TAGS_PARAM] ?? [];
		$shouldAddExistingMembers = isset($params[self::ADD_EXISTING_MEMBERS_PARAM]) ? filter_var($params[self::ADD_EXISTING_MEMBERS_PARAM], FILTER_VALIDATE_BOOL) : false;
		$mergeFieldParams          = $this->unsetIrrelevantParams($params);
		$response                    = [];

	  // Make sure we have the list ID.
		if (empty($listId)) {
			return $this->restResponseHandler('mailchimp-missing-list-id');
		}

	  // Make sure we have an email.
		if (empty($email)) {
			return $this->restResponseHandler('mailchimp-missing-email');
		}

	  // Retrieve all entities from the "leads" Entity Set.
		try {
			if ($shouldAddExistingMembers) {
				$response['add'] = $this->mailchimp->add_or_update_member($listId, $email, $mergeFieldParams);
			} else {
				$response['add'] = $this->mailchimp->add_member($listId, $email, $mergeFieldParams);
			}

			if (! empty($tags)) {
				$response['tags'] = $this->mailchimp->add_member_tags($listId, $email, $tags);
			}
		} catch (ClientException $e) {
			$decodedException = ! empty($e->getResponse()) ? json_decode($e->getResponse()->getBody()->getContents(), true) : [];

			if (! $shouldAddExistingMembers && isset($decodedException['title']) && $decodedException['title'] === self::ERROR_USER_EXISTS) {
				$msgUserExists = \esc_html__('User already exists', 'eightshift-forms');
				$response['add'] = $msgUserExists;
				$message         = $msgUserExists;

			  // We need to do the "adding tags" call as well (if needed) as the exception in the "add_member" method
			  // has stopped execution.
				if (! empty($tags)) {
					$response['tags'] = $this->mailchimp->add_member_tags($listId, $email, $tags);
				}
			} else {
				return $this->restResponseHandlerUnknownError(['error' => $e->getMessage()]);
			}
		} catch (MissingFilterInfoException $e) {
			return $this->restResponseHandler('mailchimp-missing-keys', ['message' => $e->getMessage()]);
		} catch (\Exception $e) {
			return $this->restResponseHandlerUnknownError(['error' => $e->getMessage()]);
		}

		return \rest_ensure_response([
			'code' => 200,
			'data' => $response,
			'message' => ! empty($message) ? $message : \esc_html__('Successfully added ', 'eightshift-forms'),
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
			self::LIST_ID_PARAM,
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
			self::TAGS_PARAM,
			BasicCaptcha::FIRST_NUMBER_KEY,
			BasicCaptcha::SECOND_NUMBER_KEY,
			BasicCaptcha::RESULT_KEY,
			'privacy',
			'privacy-policy',
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
