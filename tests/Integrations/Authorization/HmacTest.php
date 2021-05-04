<?php
namespace EightshiftFormsTests\Integrations\Authorization;

use EightshiftForms\Integrations\Authorization\Hmac;
use EightshiftFormsTests\BaseTest;

class HmacTest extends BaseTest
{

	protected function _inject(Hmac $hmac)
	{
		$this->hmac = $hmac;
	}

	protected function _before()
	{
		parent::_before();
		$this->validSalt = '1234';
		$this->invalidSalt = 'invalid salt';
		$this->testCase = [
			'params' => [
				'aaa' => 1,
				'bbb' => 'some value'
			],
			'salt' => $this->validSalt
		];
	}

	public function testVerificationSuccess()
	{
		$hash = $this->hmac->generateHash($this->testCase['params'], $this->testCase['salt']);
		$this->assertTrue($this->hmac->verifyHash($hash, $this->testCase['params'], $this->testCase['salt']));
	}

	public function testVerificationFailsBecauseSaltIsNotTheSame()
	{
		$hash = $this->hmac->generateHash($this->testCase['params'], $this->testCase['salt']);
		$this->assertFalse($this->hmac->verifyHash($hash, $this->testCase['params'], $this->invalidSalt));
	}
}
