<?php namespace EightshiftFormsTests\Routes;

use EightshiftFormsTests\Mocks\TestRouteSanitization;
use EightshiftForms\Captcha\BasicCaptcha;
use EightshiftForms\Integrations\Authorization\Hmac;

class RouteSanitizationTest extends BaseRouteTest
{
  protected function getRouteName(): string {
    return TestRouteSanitization::class;
  }

  /**
   * If we provide $this->getIrrelevantParams(), those params will be unset from the request.
   *
   * @return void
   */
  public function testSanitizeFields()
  {
    $request = new \WP_REST_Request('GET', $this->route_endpoint->getRouteUri());
    $request->params['GET'] = [
      'param_int' => 123,
      'param_string' => 'some-string',
      'param_array' => [
        'valid1' => 'string1',
        'valid2' => 'string2',
        'deep_array' => [
          'deep' => 'deep value',
        ],
      ],
      'param_unsafe1' => '<script>do something evil</script>',
      'param_unsafe2' => [
        'deep_unsafe' => '<script>do something evil</script>',
        'deep_array' => [
          'deeper_unsafe' => '<script>do something evil</script>',
        ],
      ],
    ];
    $response = $this->route_endpoint->routeCallback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(200, $response->data['code'], $response->data['data']['message'] ?? 'Unknown error');
    $this->assertArrayHasKey('received-params', $response->data['data'] );

    $params = $response->data['data']['received-params'];
    $this->assertArrayHasKey('param_int', $params );
    $this->assertEquals(123, $params['param_int'] );
    $this->assertArrayHasKey('param_string', $params );
    $this->assertEquals('some-string', $params['param_string'] );
    $this->assertArrayHasKey('param_array', $params );
    $this->assertIsArray( $params['param_array'] );
    $this->assertArrayHasKey( 'valid1', $params['param_array'] );
    $this->assertEquals('string1', $params['param_array']['valid1'] );
    $this->assertIsArray( $params['param_array']['deep_array'] );
    $this->assertArrayHasKey( 'deep', $params['param_array']['deep_array'] );
    $this->assertEquals('deep value', $params['param_array']['deep_array']['deep'] );

    // Proper sanitization is achieved
    $this->assertArrayHasKey('param_unsafe1', $params );
    $this->assertStringNotContainsString('<script>', $params['param_unsafe1'] );
    $this->assertArrayHasKey('param_unsafe2', $params );
    $this->assertArrayHasKey('deep_unsafe', $params['param_unsafe2'] );
    $this->assertArrayHasKey('deeper_unsafe', $params['param_unsafe2']['deep_array'] );
    $this->assertStringNotContainsString('<script>', $params['param_unsafe2']['deep_unsafe'] );
    $this->assertStringNotContainsString('<script>', $params['param_unsafe2']['deep_array']['deeper_unsafe'] );
  }
}