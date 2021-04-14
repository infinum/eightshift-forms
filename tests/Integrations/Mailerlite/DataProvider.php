<?php

namespace EightshiftFormsTests\Integrations\Mailerlite;

class DataProvider
{
  const INVALID_GROUP_ID = 'invalid-group-id';
  const MOCK_EMAIL = 'someemail@infinum.com';
  const MOCK_TAG_1 = 'aaa';
  const MOCK_TAG_2 = 'bbb';

  public static function getMockAddSubscriberResponse($params) {
    return [
      'email' => $params['email'] ?? '',
    ];
  }
}
