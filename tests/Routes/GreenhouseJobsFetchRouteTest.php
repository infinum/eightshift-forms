<?php
namespace EightshiftFormsTests\Routes;

use Brain\Monkey\Functions;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\GreenhouseJobsFetchRoute;
use EightshiftFormsTests\Integrations\Greenhouse\DataProvider;

class GreenhouseJobsFetchRouteTest extends BaseRouteTest
{
	public const METHOD = 'GET';

	protected function getRouteName(): string
	{
		return GreenhouseJobsFetchRoute::class;
	}

	/**
	 * Correct request should result in 200 response
	 *
	 * @return void
	 */
	public function testRestCallSuccessfulWhenGettingAllJobs()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'get_transient' => function () {
					return DataProvider::getJobsFullMock();
				},
			]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertSame(200, $response->data['code']);
		$this->assertSame($response->data['data'], json_decode(DataProvider::getJobsFullMock(), true));
	}

	/**
	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallErrorWhenGettingAllJobs()
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
			]
		);

		$request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertSame(200, $response->data['code']);
		$this->assertSame($response->data['data'], []);
	}

	/**
	 * Error request should result in 400 response
	 *
	 * @return void
	 */
	public function testRestCallErrorWhenMissingApiKeys()
	{
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

		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertSame(400, $response->data['code']);
		$this->assertSame($response->data['message'], 'Not all Greenhouse API info is set');
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
	}
}
