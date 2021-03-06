<?php

namespace EightshiftFormsTests\Integrations\Mailerlite;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Mailerlite\Mailerlite;
use EightshiftFormsTests\BaseTest;

class MailerliteTest extends BaseTest
{

	protected function _inject(DataProvider $dataProvider)
	{
		$this->dataProvider = $dataProvider;
	}

	protected function _before()
	{
		parent::_before();
		$this->mailerlite = $this->diContainer->get(Mailerlite::class);
	}

	public function testAddOrUpdateSubscriber()
	{
		$this->addHooks();
		$params = [
			'email' => DataProvider::MOCK_EMAIL,
		];
		$groupId = 12345;
		$subscriberData = [
			'name' => 'some name',
		];

		$response = $this->mailerlite->addSubscriber(
			$groupId,
			DataProvider::MOCK_EMAIL,
			$subscriberData,
			$params
		);

		$this->assertEquals($response, $this->dataProvider->getMockAddSubscriberResponse($params));
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
			Filters::MAILERLITE,
			function ($key) {
				return $key;
			},
			1,
			1
		);
	}
}
