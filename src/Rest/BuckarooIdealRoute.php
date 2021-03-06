<?php

/**
 * Endpoint for handling Buckaroo integration on form submit.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/buckaroo
 *
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Exception\MissingFilterInfoException;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Buckaroo\Exceptions\BuckarooRequestException;

/**
 * Class BuckarooIdealRoute
 */
class BuckarooIdealRoute extends AbstractBuckarooRoute
{

	/**
	 * Route slug
	 *
	 * @var string
	 */
	public const ENDPOINT_SLUG = '/buckaroo-ideal';

	/**
	 * Name of the required parameter for donation amount.
	 *
	 * @var string
	 */
	public const DONATION_AMOUNT_PARAM = 'donation-amount';

	/**
	 * Field to make the iDEAL payment recurring
	 *
	 * @var string
	 */
	public const IS_RECURRING_PARAM = 'is-recurring';

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
			$params = $this->verifyRequest($request, self::BUCKAROO);
		} catch (UnverifiedRequestException $e) {
			return rest_ensure_response($e->getData());
		}

		try {
			$this->setTestIfNeeded($params);
			$params = $this->setRedirectUrls($params);

			$response = $this->buckaroo->sendPayment(
				$params[self::DONATION_AMOUNT_PARAM],
				$this->buckaroo->generatePurchaseId($params),
				$params[self::ISSUER_PARAM] ?? '',
				! empty($params[self::IS_RECURRING_PARAM]),
				$params[self::PAYMENT_DESCRIPTION_PARAM] ?? ''
			);
		} catch (MissingFilterInfoException $e) {
			return $this->restResponseHandler('buckaroo-missing-keys', ['message' => $e->getMessage()]);
		} catch (BuckarooRequestException $e) {
			return $this->restResponseHandler('buckaroo-request-exception', $e->getExceptionForRestResponse());
		} catch (\Exception $e) {
			return $this->restResponseHandlerUnknownError(['error' => $e->getMessage()]);
		}

		return \rest_ensure_response([
			'code' => 200,
			'message' => esc_html__('Successfully started ideal payment process', 'eightshift-forms'),
			'data' => $response,
		]);
	}

	/**
	 * Defines a list of required parameters which must be present in the request or it will error out.
	 *
	 * @return array
	 */
	protected function getRequiredParams(): array
	{
		return [
			self::DONATION_AMOUNT_PARAM,
		];
	}

	/**
	 * Define name of the filter used for filtering required GET params.
	 *
	 * @return string
	 */
	protected function getRequiredParamsFilter(): string
	{
		return Filters::REQUIRED_PARAMS_BUCKAROO_IDEAL;
	}
}
