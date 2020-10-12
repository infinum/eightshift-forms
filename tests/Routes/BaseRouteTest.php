<?php namespace EightshiftFormsTests;

use Eightshift_Forms\Core\Main;
use EightshiftFormsTests\BaseTest;

use Eightshift_Forms\Rest\Test_Route;

class BaseRouteTest extends BaseTest
{

  protected function _inject(Main $main)
  {
    $main->set_test(true);
    $di_container = $main->build_di_container();
    $this->base_route_endpoint = $di_container->get(Test_Route::class);
  }

  protected function _before()
  {
    parent::_before();
  }

  public function testSomething()
  {
    $this->assertTrue(true);
  }
}