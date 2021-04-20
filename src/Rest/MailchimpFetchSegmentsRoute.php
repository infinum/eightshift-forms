<?php

/**
 * Endpoint for fetching segments for a list from Mailchimp.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/mailchimp-fetch-segments
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

/**
 * Class MailchimpFetchSegmentsRoute
 */
class MailchimpFetchSegmentsRoute extends BaseRoute implements Filters
{

  /**
   * Route slug
   *
   * @var string
   */
	public const ENDPOINT_SLUG = '/mailchimp-fetch-segments';

  /**
   * Parameter for list ID.
   *
   * @var string
   */
	public const LIST_ID_PARAM = 'list-id';


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

		$listId = $params[self::LIST_ID_PARAM] ?? '';

	  // Retrieve all entities from the "leads" Entity Set.
		try {
			$response = $this->extractTagsAndSegments($this->mailchimp->get_all_segments($listId));
		} catch (MissingFilterInfoException $e) {
			return $this->restResponseHandler('mailchimp-missing-keys', ['message' => $e->getMessage()]);
		} catch (\Exception $e) {
			return $this->restResponseHandlerUnknownError([
				'error' => $e->getMessage(),
				'list-id' => $listId,
			]);
		}

		return \rest_ensure_response([
			'code' => 200,
			'data' => $response,
			'message' => esc_html__('success', 'eightshift-forms'),
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
			self::LIST_ID_PARAM,
		];
	}

  /**
   * Extracts segments and tags from the segments response depending on their type
   *
   * @param  Object $response Mailchimp API call response.
   * @return array
   */
	private function extractTagsAndSegments($response): array
	{
		$tagsSegments = [
			'tags' => [],
			'segments' => [],
		];

		if (! isset($response->segments)) {
			return $tagsSegments;
		}

		foreach ($response->segments as $segment) {
			switch ($segment->type) {
				case 'static':
					$tagsSegments['tags'][] = $segment;
					break;
				case 'saved':
					$tagsSegments['segments'][] = $segment;
					break;
			}
		}
		return $tagsSegments;
	}
}
