<?php namespace EightshiftFormsTests;

use Eightshift_Forms\Integrations\Authorization\HMAC;
use EightshiftFormsTests\BaseTest;

class HmacTest extends BaseTest
{

  protected function _inject(HMAC $hmac)
  {
    $this->hmac = $hmac;
  }

  protected function _before()
  {
    parent::_before();
    $this->valid_salt = '1234';
    $this->invalid_salt = 'invalid salt';
    $this->test_case = [
      'params' => [
        'aaa' => 1,
        'bbb' => 'some value'
      ],
      'salt' => $this->valid_salt
    ];
  }

  public function testVerificationSuccess()
  {
    $hash = $this->hmac->generate_hash( $this->test_case['params'], $this->test_case['salt'] );
    $this->assertTrue($this->hmac->verify_hash($hash, $this->test_case['params'], $this->test_case['salt']));
  }

  public function testVerificationFailsBecauseSaltIsNotTheSame()
  {
    $hash = $this->hmac->generate_hash( $this->test_case['params'], $this->test_case['salt'] );
    $this->assertFalse($this->hmac->verify_hash($hash, $this->test_case['params'], $this->invalid_salt));
  }
}