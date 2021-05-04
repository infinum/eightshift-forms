<?php

namespace EightshiftFormsTests\Integrations\Mailerlite;

class DataProvider
{
	public const INVALID_GROUP_ID = 'invalid-group-id';
	public const MOCK_EMAIL = 'someemail@infinum.com';
	public const MOCK_TAG_1 = 'aaa';
	public const MOCK_TAG_2 = 'bbb';

	public static function getMockAddSubscriberResponse($params) {
    return [
      'email' => $params['email'] ?? '',
    ];
  }
}
