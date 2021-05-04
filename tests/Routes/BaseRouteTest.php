<?php namespace EightshiftFormsTests\Routes;

use EightshiftFormsTests\BaseTest;

use EightshiftForms\Main\Main;
use EightshiftForms\Integrations\Authorization\Hmac;

abstract class BaseRouteTest extends BaseTest
{

  protected function _inject(Hmac $hmac)
  {
    $this->hmac = $hmac;
  }

  protected function _before()
  {
    parent::_before();
    $this->routeEndpoint = $this->diContainer->get($this->getRouteName());
    $this->addHooks();
  }

  protected function verifyProperlyFormattedResponse($response) {
    $this->assertInstanceOf('WP_REST_Response', $response);
    $this->assertObjectHasAttribute('data', $response);
    $this->assertArrayHasKey('code', $response->data);
    $this->assertArrayHasKey('data', $response->data);
  }

  protected function verifyProperlyFormattedError($response) {
    $this->assertInstanceOf('WP_REST_Response', $response);
    $this->assertObjectHasAttribute('data', $response);
    $this->assertArrayHasKey('code', $response->data);
    $this->assertArrayHasKey('data', $response->data);
    $this->assertArrayHasKey('message', $response->data);
    $this->assertNotEquals($response->data['code'], 200);
  }

	/**
	 * Mocking that a certain filter exists. See documentation of Brain Monkey:
	 * https://brain-wp.github.io/BrainMonkey/docs/wordpress-hooks-added.html
	 *
	 * We can't return any actual value, we can just "mock register" this filter.
	 *
	 * @return void
	 */
  protected function addHooks() {
  }

	/**
	 * Define route class you're testing.
	 *
	 * @return string
	 */
  abstract protected function getRouteName(): string;
}