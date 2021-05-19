<?php
namespace EightshiftFormsTests\Routes;

use Brain\Monkey\Filters as BrainFilters;
use EightshiftForms\Hooks\Actions;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Authorization\Hmac;
use EightshiftForms\Rest\BuckarooResponseHandlerRoute;
use EightshiftFormsTests\Integrations\Buckaroo\DataProvider;

class BuckarooResponseHandlerRouteTest extends BaseRouteTest implements Filters, Actions
{
	public const METHOD = 'GET';

	protected function getRouteName(): string
	{
		return BuckarooResponseHandlerRoute::class;
	}

	/**
	 * Mocking that a certain filter exists. See documentation of Brain Monkey:
	 * https://brain-wp.github.io/BrainMonkey/docs/wordpress-hooks-added.html
	 *
	 * We can't return any actual value, we can just "mock register" this filter.
	 *
	 * @return void
	 */
	protected function addHooks()
	{
		add_filter(
			self::BUCKAROO,
			function ($key) {
				return $key;
			},
			1,
			1
		);

		add_action(
			self::BUCKAROO_RESPONSE_HANDLER,
			function ($key) {
				return $key;
			},
			1,
			1
		);
	}

	/**
	 * Correct request should result in 200 response
	 *
	 * @return void
	 */
	public function testRestCallSuccessful()
	{
		$this->addHooks();

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());
		$request->params[self::METHOD] = [
			$this->routeEndpoint::REDIRECT_URL_PARAM => 'http://someurl.com',
			$this->routeEndpoint::REDIRECT_URL_CANCEL_PARAM => 'http://someurl.com',
			$this->routeEndpoint::REDIRECT_URL_ERROR_PARAM => 'http://someurl.com',
			$this->routeEndpoint::REDIRECT_URL_REJECT_PARAM => 'http://someurl.com',
			$this->routeEndpoint::STATUS_PARAM => 'success',
		];
		$request->params['POST'] = DataProvider::idealSuccessResponseMock();

		// We need to URL encode params before calculating the hash (because that is done in the route before
		// verifying the hash. However we can't send the URLs encoded because that won't work. In the app these are sent
		// (encoded) to Buckaroo which will decode them when redirecting back to the response handler.
		$request->params[self::METHOD][Hmac::AUTHORIZATION_KEY] = $this->hmac->generateHash(
			$this->urlencodeParams($request->get_query_params()),
			\apply_filters(self::BUCKAROO, 'secretKey')
		);

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(200, $response->data['code'], $response->data['message'] ?? 'message not defined');
	}

	/**
	 * We expect an error when we're missing Buckaroo required keys
	 *
	 * @return void
	 */
	public function testRestCallFailsWhenMissingBuckarooKeys()
	{
		$this->addHooks();

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());
		$request->params[self::METHOD] = [
			$this->routeEndpoint::REDIRECT_URL_PARAM => 'http://someurl.com',
			$this->routeEndpoint::REDIRECT_URL_CANCEL_PARAM => 'http://someurl.com',
			$this->routeEndpoint::REDIRECT_URL_ERROR_PARAM => 'http://someurl.com',
			$this->routeEndpoint::REDIRECT_URL_REJECT_PARAM => 'http://someurl.com',
			$this->routeEndpoint::STATUS_PARAM => 'success',
		];
		$request->params[self::METHOD][Hmac::AUTHORIZATION_KEY] = $this->hmac->generateHash(
			$request->get_query_params(),
			\apply_filters(self::BUCKAROO, 'secretKey')
		);
		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedError($response);
		$this->assertNotEquals(200, $response->data['code'], $response->data['message'] ?? 'message not defined');
	}

	/**
	 * We expect an error when we're missing Buckaroo required keys
	 *
	 * @return void
	 */
	public function testCustomActionRanWhenDefined()
	{
		$this->addHooks();

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());
		$request->params[self::METHOD] = [
			$this->routeEndpoint::REDIRECT_URL_PARAM => 'http://someurl.com',
			$this->routeEndpoint::REDIRECT_URL_CANCEL_PARAM => 'http://someurl.com',
			$this->routeEndpoint::REDIRECT_URL_ERROR_PARAM => 'http://someurl.com',
			$this->routeEndpoint::REDIRECT_URL_REJECT_PARAM => 'http://someurl.com',
			$this->routeEndpoint::STATUS_PARAM => 'success',
		];
		$request->params['POST'] = [
			$this->routeEndpoint::BUCKAROO_RESPONSE_CODE_PARAM => 190,
		];
		$request->params[self::METHOD][Hmac::AUTHORIZATION_KEY] = $this->hmac->generateHash(
			$this->urlencodeParams($request->get_query_params()),
			\apply_filters(self::BUCKAROO, 'secretKey')
		);
		$response = $this->routeEndpoint->routeCallback($request);

		$this->assertSame(
			1,
			did_action(self::BUCKAROO_RESPONSE_HANDLER),
			$response->data['message'] ?? 'message not defined'
		);
	}

	/**
	 * We expect an error when we're missing Buckaroo required keys
	 *
	 * @return void
	 */
	public function testUserWasRedirectedIfRequestWasOk()
	{
		$this->addHooks();

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());
		$request->params[self::METHOD] = [
			$this->routeEndpoint::REDIRECT_URL_PARAM => 'http://someurl.com',
			$this->routeEndpoint::REDIRECT_URL_CANCEL_PARAM => 'http://someurl.com',
			$this->routeEndpoint::REDIRECT_URL_ERROR_PARAM => 'http://someurl.com',
			$this->routeEndpoint::REDIRECT_URL_REJECT_PARAM => 'http://someurl.com',
			$this->routeEndpoint::STATUS_PARAM => rawurlencode('success'),
		];
		$request->params['POST'] = DataProvider::idealSuccessResponseMock();
		$request->params[self::METHOD][Hmac::AUTHORIZATION_KEY] = $this->hmac->generateHash(
			$this->urlencodeParams($request->get_query_params()),
			\apply_filters(self::BUCKAROO, 'secretKey')
		);
		$response = $this->routeEndpoint->routeCallback($request);
		$this->assertSame(1, did_action(self::WP_REDIRECT_ACTION), $response->data['message'] ?? 'message not defined');
	}

	/**
	 * We expect an error when we're missing Buckaroo required keys
	 *
	 * @return void
	 */
	public function testUserWasRedirectedIfRedirectUrlsMissing()
	{
		$this->addHooks();

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());
		$request->params[self::METHOD] = [
			$this->routeEndpoint::REDIRECT_URL_PARAM => '',
			$this->routeEndpoint::REDIRECT_URL_CANCEL_PARAM => '',
			$this->routeEndpoint::REDIRECT_URL_ERROR_PARAM => '',
			$this->routeEndpoint::REDIRECT_URL_REJECT_PARAM => '',
			$this->routeEndpoint::STATUS_PARAM => 'success',
		];
		$request->params['POST'] = [
			$this->routeEndpoint::BUCKAROO_RESPONSE_CODE_PARAM => 190,
		];
		$request->params[self::METHOD][Hmac::AUTHORIZATION_KEY] = $this->hmac->generateHash(
			$this->urlencodeParams($request->get_query_params()),
			\apply_filters(self::BUCKAROO, 'secretKey')
		);
		$response = $this->routeEndpoint->routeCallback($request);

		$this->assertSame(1, did_action(self::WP_REDIRECT_ACTION), $response->data['message'] ?? 'message not defined');
	}

	/**
	 * Test redirection works on iDEAL success
	 *
	 * @return void
	 */
	public function testRedirectUrlBuildingWorksOnIdealSuccess()
	{
		$correctUrl = 'http://success.com';
		$params = [
			$this->routeEndpoint::STATUS_PARAM => $this->routeEndpoint::STATUS_SUCCESS,
			$this->routeEndpoint::REDIRECT_URL_PARAM => $correctUrl,
		];

		$redirectUrl = $this->routeEndpoint->buildRedirectUrl($params, DataProvider::idealSuccessResponseMock());
		$this->assertEquals($redirectUrl, $correctUrl);
	}

	/**
	 * Test redirection works on iDEAL error
	 *
	 * @return void
	 */
	public function testRedirectUrlBuildingWorksOnIdeaelError()
	{
		$correctUrl = 'http://error.com';
		$params = [
			$this->routeEndpoint::STATUS_PARAM => $this->routeEndpoint::STATUS_ERROR,
			$this->routeEndpoint::REDIRECT_URL_ERROR_PARAM => $correctUrl,
		];

		$redirectUrl = $this->routeEndpoint->buildRedirectUrl($params, DataProvider::idealErrorResponseMock());
		$this->assertEquals($redirectUrl, $correctUrl);
	}

	/**
	 * Test redirection works on iDEAL reject
	 *
	 * @return void
	 */
	public function testRedirectUrlBuildingWorksOnIdealReject()
	{
		$correctUrl = 'http://reject.com';
		$params = [
			$this->routeEndpoint::STATUS_PARAM => $this->routeEndpoint::STATUS_REJECT,
			$this->routeEndpoint::REDIRECT_URL_REJECT_PARAM => $correctUrl,
		];

		$redirectUrl = $this->routeEndpoint->buildRedirectUrl($params, DataProvider::idealRejectResponseMock());
		$this->assertEquals($redirectUrl, $correctUrl);
	}

	/**
	 * Test redirection works on iDEAL cancel
	 *
	 * @return void
	 */
	public function testRedirectUrlBuildingWorksOnIdealCancel()
	{
		$correctUrl = 'http://cancel.com';
		$params = [
			$this->routeEndpoint::STATUS_PARAM => $this->routeEndpoint::STATUS_CANCELED,
			$this->routeEndpoint::REDIRECT_URL_CANCEL_PARAM => $correctUrl,
		];

		$redirectUrl = $this->routeEndpoint->buildRedirectUrl($params, DataProvider::idealCancelledResponseMock());
		$this->assertEquals($redirectUrl, $correctUrl);
	}

	/**
	 * Test redirection redirects back to homepage if url isn't provided.
	 *
	 * @return void
	 */
	public function testRedirectUrlBuildingWorksWhenUrlNotProvided()
	{
		$params = [
			$this->routeEndpoint::STATUS_PARAM => $this->routeEndpoint::STATUS_SUCCESS,
		];

		$redirectUrl = $this->routeEndpoint->buildRedirectUrl($params, DataProvider::idealSuccessResponseMock());
		$this->assertEquals($redirectUrl, self::HOME_URL);
	}


	/**
	 * Test redirection works on Emandate success
	 *
	 * @return void
	 */
	public function testRedirectUrlBuildingWorksOnEmandateSuccess()
	{
		$correctUrl = 'http://success.com';
		$params = [
			$this->routeEndpoint::STATUS_PARAM => $this->routeEndpoint::STATUS_SUCCESS,
			$this->routeEndpoint::REDIRECT_URL_PARAM => $correctUrl,
		];

		$redirectUrl = $this->routeEndpoint->buildRedirectUrl($params, DataProvider::emandateSuccessResponseMock());
		$this->assertEquals($redirectUrl, $correctUrl);
	}

	/**
	 * Test redirection works on Emandate error
	 *
	 * @return void
	 */
	public function testRedirectUrlBuildingWorksOnEmandatelError()
	{
		$correctUrl = 'http://error.com';
		$params = [
			$this->routeEndpoint::STATUS_PARAM => $this->routeEndpoint::STATUS_ERROR,
			$this->routeEndpoint::REDIRECT_URL_ERROR_PARAM => $correctUrl,
		];

		$redirectUrl = $this->routeEndpoint->buildRedirectUrl($params, DataProvider::emandateFailedResponseMock());
		$this->assertEquals($redirectUrl, $correctUrl);
	}

	/**
	 * Test redirection works on Emandate cancel
	 *
	 * @return void
	 */
	public function testRedirectUrlBuildingWorksOnEmandateCancel()
	{
		$correctUrl = 'http://cancel.com';
		$params = [
			$this->routeEndpoint::STATUS_PARAM => $this->routeEndpoint::STATUS_CANCELED,
			$this->routeEndpoint::REDIRECT_URL_CANCEL_PARAM => $correctUrl,
		];

		$redirectUrl = $this->routeEndpoint->buildRedirectUrl($params, DataProvider::emandateCancelledResponseMock());
		$this->assertEquals($redirectUrl, $correctUrl);
	}

	/**
	 * Test url was filtered if filter is set in project
	 *
	 * @return void
	 */
	public function testRedirectUrlWasFilteredIfProvided()
	{
		apply_filters(Filters::BUCKAROO_REDIRECT_URL, 'Filter applied', $this);
		$this->routeEndpoint->buildRedirectUrl([], DataProvider::emandateCancelledResponseMock());
		$this->assertTrue(BrainFilters\applied(Filters::BUCKAROO_REDIRECT_URL) > 0);
	}

	private function urlencodeParams(array $params): array
	{
		return array_map(
			function ($param) {
				return is_string($param) ? rawurlencode($param) : $param;
			},
			$params
		);
	}

}
