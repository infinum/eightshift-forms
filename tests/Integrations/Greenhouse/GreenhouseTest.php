<?php

/**
 * Tests for Greenhouse integration file.
 */

namespace EightshiftFormsTests\Integrations\Greenhouse;

use Brain\Monkey\Functions;
use EightshiftForms\Hooks\Filters;
use EightshiftFormsTests\BaseTest;
use EightshiftFormsTests\Mocks\MockGreenhouse;
class GreenhouseTest extends BaseTest
{
	protected function _inject(DataProvider $dataProvider)
	{
		$this->dataProvider = $dataProvider;
	}

	protected function _before()
	{
		parent::_before();
		$this->greenhouse = $this->diContainer->get(MockGreenhouse::class);
	}

	/**
	 * Post application - Success
	 *
	 * @return void
	 */
	public function testSuccessPostingGreenhouseApplication()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::successGreenhouseApplicationResponse();
				},
			]
		);

		$params = DataProvider::greenhouseApplicationParams();

		$response = $this->greenhouse->postGreenhouseApplication(
			DataProvider::MOCKED_GH_APPLICATION_SUCCESS_URL,
			$params
		);

		$this->assertEquals($response['success'], 'Candidate saved successfully');
	}

	/**
	 * Post application - Error Missing First Name
	 *
	 * @return void
	 */
	public function testErrorMissingFirstNamePostingGreenhouseApplication()
	{

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::errorMissingFirstNameGreenhouseApplicationResponse();
				},
			]
		);

		$this->addHooks();

		$params = DataProvider::greenhouseApplicationParams();

		unset($params['first_name']);

		$response = $this->greenhouse->postGreenhouseApplication(
			DataProvider::MOCKED_GH_APPLICATION_ERROR_MISSING_FIRST_NAME_URL,
			$params
		);

		$this->assertEquals($response['error'], 'Invalid attributes: first_name');
	}

	/**
	 * Post application - Error Missing Last Name
	 *
	 * @return void
	 */
	public function testErrorMissingLastNamePostingGreenhouseApplication()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::errorMissingLastNameGreenhouseApplicationResponse();
				},
			]
		);

		$params = DataProvider::greenhouseApplicationParams();

		unset($params['last_name']);

		$response = $this->greenhouse->postGreenhouseApplication(
			DataProvider::MOCKED_GH_APPLICATION_ERROR_MISSING_LAST_NAME_URL,
			$params
		);

		$this->assertEquals($response['error'], 'Invalid attributes: last_name');
	}

	/**
	 * Post application - Error Missing Email
	 *
	 * @return void
	 */
	public function testErrorMissingEmailPostingGreenhouseApplication()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::errorMissingEmailGreenhouseApplicationResponse();
				},
			]
		);

		$params = DataProvider::greenhouseApplicationParams();

		unset($params['email']);

		$response = $this->greenhouse->postGreenhouseApplication(
			DataProvider::MOCKED_GH_APPLICATION_ERROR_MISSING_EMAIL_URL,
			$params
		);

		$this->assertEquals($response['error'], 'Invalid attributes: email');
	}

	/**
	 * Post application - Error Missing Multiple Props.
	 *
	 * @return void
	 */
	public function testErrorMissingMultipleRequiredPropsPostingGreenhouseApplication()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::errorMissingMultipleRequiredPropsGreenhouseApplicationResponse();
				},
			]
		);

		$params = DataProvider::greenhouseApplicationParams();

		unset($params['first_name']);
		unset($params['last_name']);
		unset($params['email']);

		$response = $this->greenhouse->postGreenhouseApplication(
			DataProvider::MOCKED_GH_APPLICATION_ERROR_MISSING_MULTIPLE_URL,
			$params
		);

		$this->assertEquals($response['error'], 'Invalid attributes: first_name,last_name,email');
	}

	/**
	 * Post application - Error First Name empty
	 *
	 * @return void
	 */
	public function testErrorFirstNameEmptyPostingGreenhouseApplication()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::errorMissingFirstNameGreenhouseApplicationResponse();
				},
			]
		);

		$params = DataProvider::greenhouseApplicationParams();

		$params['first_name'] = '';

		$response = $this->greenhouse->postGreenhouseApplication(
			DataProvider::MOCKED_GH_APPLICATION_ERROR_MISSING_FIRST_NAME_URL,
			$params
		);

		$this->assertEquals($response['error'], 'Invalid attributes: first_name');
	}

	/**
	 * Post application - Error Wrong Job ID
	 *
	 * @return void
	 */
	public function testErrorWrongJobIdPostingGreenhouseApplication()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::errorGreenhouseApplicationResponse();
				},
			]
		);

		$params = DataProvider::greenhouseApplicationParams();

		$params['job_id'] = '11313';

		$response = $this->greenhouse->postGreenhouseApplication(
			DataProvider::MOCKED_GH_APPLICATION_ERROR_URL,
			$params
		);

		$this->assertEquals($response['error'], 'Failed to save person');
	}

	/**
	 * Post application - Error Missing Job ID.
	 *
	 * @return void
	 */
	public function testErrorMissingJobIdPostingGreenhouseApplication()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'wp_remote_post' => function() {
					return DataProvider::errorMissingJobIdGreenhouseApplicationResponse();
				},
			]
		);

		$params = DataProvider::greenhouseApplicationParams();

		$params['job_id'] = '';

		$response = $this->greenhouse->postGreenhouseApplication(
			DataProvider::MOCKED_GH_APPLICATION_ERROR_MISSING_JOB_ID_URL,
			$params
		);

		$this->assertEquals($response, []);
	}

	/**
	 * Retrieve all jobs - Success from Cache
	 *
	 * @return void
	 */
	public function testSuccessGettingJobsListFromCache()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'get_transient' => function () {
					return DataProvider::getJobsFullMock();
				},
			]
		);

		$response = $this->greenhouse->getJobs();

		$this->assertIsArray($response);
		$this->assertArrayHasKey('title', $response[0]);
		$this->assertArrayHasKey('id', $response[0]);
		$this->assertEquals($response[0]['title'], 'Android Engineer');
		$this->assertArrayHasKey('questions', $response[0]);
		$this->assertIsArray($response[0]['questions']);
		$this->assertArrayHasKey('label', $response[0]['questions'][0]);
		$this->assertEquals($response[0]['questions'][0]['label'], 'First Name');
		$this->assertArrayHasKey('options', $response[0]['questions'][0]);
	}

	/**
	 * Retrieve all jobs - Success from API
	 *
	 * @return void
	 */
	public function testSuccessGettingJobsListFromApi()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'get_transient' => function () {
					return '';
				},
				'set_transient' => function () {
					return true;
				},
				'wp_remote_get' => function(string $url) {
					if ($url === DataProvider::greenhouseJobsUrl()) {
						return DataProvider::getJobsResponseMock();
					}

					if ($url === DataProvider::greenhouseJobUrl(DataProvider::MOCKED_GH_JOB_SUCCESS_ID)) {
						return DataProvider::getJobResponseMock();
					}
				},
			]
		);

		$response = $this->greenhouse->getJobs();

		$this->assertIsArray($response);
		$this->assertArrayHasKey('title', $response[0]);
		$this->assertArrayHasKey('id', $response[0]);
		$this->assertEquals($response[0]['title'], 'Android Engineer');
		$this->assertArrayHasKey('questions', $response[0]);
		$this->assertIsArray($response[0]['questions']);
		$this->assertArrayHasKey('label', $response[0]['questions'][0]);
		$this->assertEquals($response[0]['questions'][0]['label'], 'First Name');
		$this->assertArrayHasKey('options', $response[0]['questions'][0]);
	}

	/**
	 * Retrieve all jobs - Error from API
	 *
	 * @return void
	 */
	public function testErrorGettingJobsListFromApi()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'get_transient' => function () {
					return '';
				},
				'set_transient' => function () {
					return true;
				},
				'wp_remote_get' => function() {
					return [];
				},
			]
		);

		$response = $this->greenhouse->getJobs();

		$this->assertEmpty($response);
	}

	/**
	 * Retrieve one job - Success from API
	 *
	 * @return void
	 */
	public function testSuccessGettingJobFromApi()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'get_transient' => function () {
					return '';
				},
				'set_transient' => function () {
					return true;
				},
				'wp_remote_get' => function(string $url) {
					if ($url === DataProvider::greenhouseJobsUrl()) {
						return DataProvider::getJobsResponseMock();
					}

					if ($url === DataProvider::greenhouseJobUrl(DataProvider::MOCKED_GH_JOB_SUCCESS_ID)) {
						return DataProvider::getJobResponseMock();
					}
				},
			]
		);

		$response = $this->greenhouse->getJob(DataProvider::MOCKED_GH_JOB_SUCCESS_ID);

		$this->assertIsArray($response);
		$this->assertArrayHasKey('title', $response);
		$this->assertArrayHasKey('id', $response);
		$this->assertEquals($response['title'], 'Android Engineer');
		$this->assertArrayHasKey('questions', $response);
		$this->assertIsArray($response['questions']);
		$this->assertArrayHasKey('label', $response['questions'][0]);
		$this->assertEquals($response['questions'][0]['label'], 'First Name');
		$this->assertArrayHasKey('options', $response['questions'][0]);
	}

	/**
	 * Retrieve one job - Wronk Keys from API
	 *
	 * @return void
	 */
	public function testWrongKeysGettingJobFromApi()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'get_transient' => function () {
					return '';
				},
				'set_transient' => function () {
					return true;
				},
				'wp_remote_get' => function(string $url) {
					if ($url === DataProvider::greenhouseJobsUrl()) {
						return DataProvider::getJobsResponseMock();
					}

					if ($url === DataProvider::greenhouseJobUrl(DataProvider::MOCKED_GH_JOB_SUCCESS_ID)) {
						return DataProvider::getJobResponseMock();
					}
				},
			]
		);

		$response = $this->greenhouse->getJob(DataProvider::MOCKED_GH_JOB_SUCCESS_ID);

		$this->assertIsArray($response);
		$this->assertArrayNotHasKey('absolute_url', $response);
		$this->assertArrayNotHasKey('internal_job_id', $response);
		$this->assertArrayNotHasKey('metadata', $response);
	}


	/**
	 * Retrieve all jobs - Error from Cache
	 *
	 * @return void
	 */
	public function testWrongJobIdGettingJobsListFromCache()
	{
		$this->addHooks();

		Functions\stubs(
			[
				'get_transient' => function () {
					
				},
				'get_transient' => function () {
					return '';
				},
				'set_transient' => function () {
					return true;
				},
				'wp_remote_get' => function(string $url) {
					if ($url === DataProvider::greenhouseJobsUrl()) {
						$data = DataProvider::getJobsMock();
						$data['jobs'][0]['id'] = '112132';

						return [
							'body' => json_encode($data),
						];
					}

					if ($url === DataProvider::greenhouseJobUrl(DataProvider::MOCKED_GH_JOB_SUCCESS_ID)) {
						return DataProvider::getJobResponseMock();
					}
				},
			]
		);

		$response = $this->greenhouse->getJobs();

		$this->assertIsArray($response);
		$this->assertEmpty($response);
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
