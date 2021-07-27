<?php
namespace EightshiftFormsTests\Routes;

use Brain\Monkey\Functions;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\GreenhouseRoute;
use EightshiftFormsTests\Integrations\Greenhouse\DataProvider;

class GreenhouseRouteTest extends BaseRouteTest
{
	public const METHOD = 'POST';

	protected function getRouteName(): string
	{
		return GreenhouseRoute::class;
	}

	protected function _before()
	{
		parent::_before();
	}

	/**
	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallMissingRequiredFieldsWhenPostingApplications()
	{
		$this->addHooks();

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(400, $response->data['code']);
		$this->assertEquals($response->data['message'], 'Missing one or more required POST parameters to process the request.');
		$this->assertEquals($response->data['data']['missing-keys'][0], 'email');
		$this->assertEquals($response->data['data']['missing-keys'][1], 'first_name');
		$this->assertEquals($response->data['data']['missing-keys'][2], 'last_name');
		$this->assertEquals($response->data['data']['missing-keys'][3], 'job_id');
	}

	/**
	 * Success request should result in 200 response
	 *
	 * @return void
	 */
	public function testRestCallSuccessWhenPostingApplications()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::successGreenhouseApplicationResponse();
				},
				'get_transient' => function () {
					return DataProvider::getJobsFullMock();
				},
		]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$request->params['POST'] = DataProvider::greenhouseApplicationParams();
		$request->params['FILES'] = DataProvider::greenhouseApplicationFiles();

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(200, $response->data['code']);
		$this->assertEquals($response->data['message'], 'Application successfully saved to Greenhouse.');
	}

	/**
 	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallErrorIfFirstNameFieldIsEmptyWhenPostingApplications()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::successGreenhouseApplicationResponse();
				},
			]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$request->params['POST'] = DataProvider::greenhouseApplicationParams();
		$request->params['FILES'] = DataProvider::greenhouseApplicationFiles();
		$request->params['POST']['first_name'] = '';

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(400, $response->data['code']);
		$this->assertEquals($response->data['message'], 'Please enter your first name.');
	}

	/**
 	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallErrorIfLastNameFieldIsEmptyWhenPostingApplications()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::successGreenhouseApplicationResponse();
				},
			]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$request->params['POST'] = DataProvider::greenhouseApplicationParams();
		$request->params['FILES'] = DataProvider::greenhouseApplicationFiles();
		$request->params['POST']['last_name'] = '';

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(400, $response->data['code']);
		$this->assertEquals($response->data['message'], 'Please enter your last name.');
	}

	/**
 	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallErrorIfEmailFieldIsEmptyWhenPostingApplications()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::successGreenhouseApplicationResponse();
				},
			]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$request->params['POST'] = DataProvider::greenhouseApplicationParams();
		$request->params['FILES'] = DataProvider::greenhouseApplicationFiles();
		$request->params['POST']['email'] = '';

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(400, $response->data['code']);
		$this->assertEquals($response->data['message'], 'Please enter your email.');
	}

	/**
 	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallErrorIfJobIdFieldIsEmptyWhenPostingApplications()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::successGreenhouseApplicationResponse();
				},
			]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$request->params['POST'] = DataProvider::greenhouseApplicationParams();
		$request->params['FILES'] = DataProvider::greenhouseApplicationFiles();
		$request->params['POST']['job_id'] = '';

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(400, $response->data['code']);
		$this->assertEquals($response->data['message'], 'Missing JobId.');
	}

	/**
 	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallErrorIfSpecificFieldsContainsUrlWhenPostingApplications()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::successGreenhouseApplicationResponse();
				},
			]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$request->params['POST'] = DataProvider::greenhouseApplicationParams();
		$request->params['FILES'] = DataProvider::greenhouseApplicationFiles();
		$request->params['POST']['first_name'] = 'https://eightshift.com/';

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(400, $response->data['code']);
		$this->assertEquals($response->data['message'], 'First Name, Last Name and Phone fields must not contain a URL.');
	}

	/**
 	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallErrorIfEmailFieldsIsValidEmailWhenPostingApplications()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::successGreenhouseApplicationResponse();
				},
			]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$request->params['POST'] = DataProvider::greenhouseApplicationParams();
		$request->params['FILES'] = DataProvider::greenhouseApplicationFiles();
		$request->params['POST']['email'] = 'invalid';

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(400, $response->data['code']);
		$this->assertEquals($response->data['message'], 'Email is not an valid format.');
	}

	/**
 	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallErrorIfResumeIsNotValidFormatWhenPostingApplications()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::successGreenhouseApplicationResponse();
				},
			]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$request->params['POST'] = DataProvider::greenhouseApplicationParams();
		$request->params['FILES'] = DataProvider::greenhouseApplicationFiles();
		$request->params['FILES']['resume']['name'] = 'wrong.png';
		$request->params['FILES']['resume']['type'] = 'image/png';

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(400, $response->data['code']);
		$this->assertEquals($response->data['message'], 'Provided resume is not an valid format.');
	}

	/**
 	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallErrorIfResumeIsZeroMinSizeWhenPostingApplications()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::successGreenhouseApplicationResponse();
				},
			]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$request->params['POST'] = DataProvider::greenhouseApplicationParams();
		$request->params['FILES'] = DataProvider::greenhouseApplicationFiles();
		$request->params['FILES']['resume']['size'] = 0;

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(400, $response->data['code']);
		$this->assertEquals($response->data['message'], 'Provided resume is not an valid size.');
	}

	/**
 	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallErrorIfCoverLetterIsNotValidFormatWhenPostingApplications()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::successGreenhouseApplicationResponse();
				},
			]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$request->params['POST'] = DataProvider::greenhouseApplicationParams();
		$request->params['FILES'] = DataProvider::greenhouseApplicationFiles();
		$request->params['FILES']['cover_letter']['name'] = 'wrong.png';
		$request->params['FILES']['cover_letter']['type'] = 'image/png';

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(400, $response->data['code']);
		$this->assertEquals($response->data['message'], 'Provided cover letter is not an valid format.');
	}

	/**
 	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallErrorIfCoverLetterIsZeroMinSizeWhenPostingApplications()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::successGreenhouseApplicationResponse();
				},
			]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$request->params['POST'] = DataProvider::greenhouseApplicationParams();
		$request->params['FILES'] = DataProvider::greenhouseApplicationFiles();
		$request->params['FILES']['cover_letter']['size'] = 0;

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(400, $response->data['code']);
		$this->assertEquals($response->data['message'], 'Provided cover letter is not an valid size.');
	}

	/**
 	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallErrorIfFilterInfoIsMissingWhenPostingApplications()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'get_transient' => function () {
					return '';
				},
				'wp_remote_get' => function() {
					return '';
				},
				'set_transient' => function () {
					return true;
				},
				'has_filter' => function () {
					return false;
				}
			]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$request->params['POST'] = DataProvider::greenhouseApplicationParams();

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(400, $response->data['code']);
		$this->assertEquals($response->data['message'], 'Not all Greenhouse API info is set');
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
			Filters::GREENHOUSE,
			function ($key) {
				return $key;
			},
			1,
			1
		);
		add_filter(
			Filters::GREENHOUSE_CONFIRMATION,
			function ($key) {
				return $key;
			},
			1,
			1
		);
		add_filter(
			Filters::GREENHOUSE_FALLBACK,
			function ($key) {
				return $key;
			},
			1,
			1
		);
	}
}
