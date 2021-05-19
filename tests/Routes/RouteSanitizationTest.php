<?php
namespace EightshiftFormsTests\Routes;

use EightshiftFormsTests\Mocks\TestRouteSanitization;

class RouteSanitizationTest extends BaseRouteTest
{
	protected function getRouteName(): string
	{
		return TestRouteSanitization::class;
	}

	/**
	 * If we provide $this->getIrrelevantParams(), those params will be unset from the request.
	 *
	 * @return void
	 */
	public function testSanitizeFields()
	{
		$request = new \WP_REST_Request('GET', $this->routeEndpoint->getRouteUri());
		$request->params['GET'] = [
			'paramInt' => 123,
			'paramString' => 'some-string',
			'paramArray' => [
				'valid1' => 'string1',
				'valid2' => 'string2',
				'deepArray' => [
					'deep' => 'deep value',
				],
			],
			'paramUnsafe1' => '<script>do something evil</script>',
			'paramUnsafe2' => [
				'deepUnsafe' => '<script>do something evil</script>',
				'deepArray' => [
					'deeperUnsafe' => '<script>do something evil</script>',
				],
			],
		];
		$response = $this->routeEndpoint->routeCallback($request);

		$this->verifyProperlyFormattedResponse($response);
		$this->assertEquals(200, $response->data['code'], $response->data['data']['message'] ?? 'Unknown error');
		$this->assertArrayHasKey('received-params', $response->data['data']);

		$params = $response->data['data']['received-params'];
		$this->assertArrayHasKey('paramInt', $params);
		$this->assertEquals(123, $params['paramInt']);
		$this->assertArrayHasKey('paramString', $params);
		$this->assertEquals('some-string', $params['paramString']);
		$this->assertArrayHasKey('paramArray', $params);
		$this->assertIsArray($params['paramArray']);
		$this->assertArrayHasKey('valid1', $params['paramArray']);
		$this->assertEquals('string1', $params['paramArray']['valid1']);
		$this->assertIsArray($params['paramArray']['deepArray']);
		$this->assertArrayHasKey('deep', $params['paramArray']['deepArray']);
		$this->assertEquals('deep value', $params['paramArray']['deepArray']['deep']);

		// Proper sanitization is achieved
		$this->assertArrayHasKey('paramUnsafe1', $params);
		$this->assertStringNotContainsString('<script>', $params['paramUnsafe1']);
		$this->assertArrayHasKey('paramUnsafe2', $params);
		$this->assertArrayHasKey('deepUnsafe', $params['paramUnsafe2']);
		$this->assertArrayHasKey('deeperUnsafe', $params['paramUnsafe2']['deepArray']);
		$this->assertStringNotContainsString('<script>', $params['paramUnsafe2']['deepUnsafe']);
		$this->assertStringNotContainsString('<script>', $params['paramUnsafe2']['deepArray']['deeperUnsafe']);
	}
}
