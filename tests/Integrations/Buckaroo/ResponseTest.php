<?php

namespace EightshiftFormsTests\Integrations\Buckaroo;

use EightshiftFormsTests\BaseTest;
use Eightshift_Forms\Buckaroo\Response_Factory;
use Eightshift_Forms\Buckaroo\Response;

class BuckarooResponse extends BaseTest
{

  protected function _inject(DataProvider $dataProvider)
  {
    $this->dataProvider = $dataProvider;
  }

  public function testIdealSuccessfulResponse()
  {
    $response = Response_Factory::build( $this->dataProvider->idealSuccessResponseMock());
    $this->assertTrue( $response->is_ideal());
    $this->assertEquals( $response->get_status(), Response::STATUS_CODE_SUCCESS );
  }

  public function testIdealErrorResponse()
  {
    $response = Response_Factory::build( $this->dataProvider->idealErrorResponseMock());
    $this->assertTrue( $response->is_ideal());
    $this->assertEquals( $response->get_status(), Response::STATUS_CODE_ERROR );
  }

  public function testIdealRejectResponse()
  {
    $response = Response_Factory::build( $this->dataProvider->idealRejectResponseMock());
    $this->assertTrue( $response->is_ideal());
    $this->assertEquals( $response->get_status(), Response::STATUS_CODE_REJECT );
  }

  public function testIdealCancelledResponse()
  {
    $response = Response_Factory::build( $this->dataProvider->idealCancelledResponseMock());
    $this->assertTrue( $response->is_ideal());
    $this->assertEquals( $response->get_status(), Response::STATUS_CODE_CANCELLED );
  }

  public function testIdealCancelledByBackButtonResponse()
  {
    $response = Response_Factory::build( $this->dataProvider->idealCancelledResponseWhenUserClicksBackMock());
    $this->assertEquals( $response->get_status(), Response::STATUS_CODE_CANCELLED );
  }

  public function testEmandateFailResponse()
  {
    $response = Response_Factory::build( $this->dataProvider->emandateFailedResponseMock());
    $this->assertTrue( $response->is_emandate());
    $this->assertEquals( $response->get_status(), Response::STATUS_CODE_ERROR );
  }

  public function testEmandateCancelledResponse()
  {
    $response = Response_Factory::build( $this->dataProvider->emandateCancelledResponseMock());
    $this->assertTrue( $response->is_emandate());
    $this->assertEquals( $response->get_status(), Response::STATUS_CODE_CANCELLED );
  }
}