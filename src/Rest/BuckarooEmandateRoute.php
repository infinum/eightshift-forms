<?php

/**
 * Endpoint for handling Buckaroo integration on form submit.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/buckaroo-emandate
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
 * Class BuckarooEmandateRoute
 */
class BuckarooEmandateRoute extends AbstractBuckarooRoute
{

  /**
   * Route slug
   *
   * @var string
   */
	public const ENDPOINT_SLUG = '/buckaroo-emandate';

  /**
   * Description of the emandate param.
   *
   * @var string
   */
	public const EMANDATE_DESCRIPTION_PARAM = 'emandate-description';

  /**
   * Sequencetype param for emandates. 0 = recurring, 1 = one off.
   *
   * @var string
   */
	public const SEQUENCE_TYPE_IS_RECURRING_PARAM = 'is-recurring';

	public const SEQUENCE_TYPE_RECURRING_VALUE = '0';
	public const SEQUENCE_TYPE_ONE_TIME_VALUE  = '1';

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
			return rest_ensure_response($e->get_data());
		}

		try {
			$params = $this->setRedirectUrls($params);

			$this->setTestIfNeeded($params);
			$this->buckaroo->set_data_request();

			$this->buckaroo->set_pay_type('emandate');
			$response = $this->buckaroo->create_emandate(
				$this->buckaroo->generate_debtor_reference($params),
				! empty($params[self::SEQUENCE_TYPE_IS_RECURRING_PARAM]) ? self::SEQUENCE_TYPE_RECURRING_VALUE : self::SEQUENCE_TYPE_ONE_TIME_VALUE,
				$this->buckaroo->generate_purchase_id($params),
				'nl',
				$params[self::ISSUER_PARAM] ?? '',
				$params[self::EMANDATE_DESCRIPTION_PARAM] ?? ''
			);
		} catch (MissingFilterInfoException $e) {
			return $this->restResponseHandler('buckaroo-missing-keys', [ 'message' => $e->getMessage() ]);
		} catch (BuckarooRequestException $e) {
			return $this->restResponseHandler('buckaroo-missing-keys', $e->get_exception_for_rest_response());
		} catch (\Exception $e) {
			return $this->restResponseHandlerUnknownError([ 'error' => $e->getMessage() ]);
		}

		return \rest_ensure_response(
			[
			'code' => 200,
			'message' => esc_html__('Successfully started emandate creation process', 'eightshift-forms'),
			'data' => $response,
			]
		);
	}

  /**
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
	protected function getRequiredParams(): array
	{
		return [
		self::EMANDATE_DESCRIPTION_PARAM,
		];
	}

  /**
   * Define name of the filter used for filtering required GET params.
   *
   * @return string
   */
	protected function getRequiredParamsFilter(): string
	{
		return Filters::REQUIRED_PARAMS_BUCKAROO_EMANDATE;
	}
}
