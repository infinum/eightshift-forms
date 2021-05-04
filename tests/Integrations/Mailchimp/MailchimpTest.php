<?php

namespace EightshiftFormsTests\Integrations\Mailchimp;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Mailchimp\Mailchimp;
use EightshiftFormsTests\BaseTest;
use GuzzleHttp\Exception\ClientException;

class MailchimpTest extends BaseTest
{

	protected function _inject(DataProvider $dataProvider)
	{
		$this->dataProvider = $dataProvider;
	}

	protected function _before()
	{
		parent::_before();
		$this->mailchimp = $this->diContainer->get(Mailchimp::class);
	}

	public function testAddOrUpdateMember()
	{
		$this->addHooks();
		$params = [
			'list_id' => 'list-id',
			'email' => DataProvider::MOCK_EMAIL,
			'merge_fields' => [
				'FNAME' => 'some name',
			],
		];

		$response = $this->mailchimp->addOrUpdateMember(
			$params['list_id'],
			$params['email'],
			$params['merge_fields'],
			[]
		);

		$this->assertEquals($response, $this->dataProvider->getMockAddOrUpdateMemberResponse($params));
	}

	public function testAddOrUpdateMemberIfMissingListId()
	{
		$this->addHooks();
		$params = [
			'list_id' => DataProvider::INVALID_LIST_ID,
			'email' => DataProvider::MOCK_EMAIL,
			'merge_fields' => [
				'FNAME' => 'some name',
			],
		];

		try {
			$this->mailchimp->addOrUpdateMember(
				$params['list_id'],
				$params['email'],
				$params['merge_fields'],
				[]
			);

			$this->assertEquals(1, 0);
		} catch (ClientException $e) {
			$this->assertIsObject($e);
		}
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
			Filters::MAILCHIMP,
			function ($key) {
				return $key;
			},
			1,
			1
		);
	}
}
