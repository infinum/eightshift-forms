<?php

namespace EightshiftFormsTests\Integrations\Mailchimp;

use stdClass;

class DataProvider
{
  const INVALID_LIST_ID = 'invalid-list-id';
  const MOCK_EMAIL = 'someemail@infinum.com';
  const MOCK_TAG_1 = 'aaa';
  const MOCK_TAG_2 = 'bbb';

  public static function defaultMergeFields() {
    return [
      'FNAME' => '',
      'LNAME' => '',
      'ADDRESS' => '',
      'PHONE' => '',
      'BIRTHDAY' => '',
      'LDONDATE' => '',
      'DON' => '',
    ];
  }
  /**
   * Example of successful transaction Buckaroo response.
   *
   * @return stdClass
   */
  public static function getMockAddOrUpdateMemberResponse( array $params ): stdClass {
    $response = new \stdClass();
    $response->id = '5ae1cf23294c7b08b5ddd696a454635a';
    $response->email_address = $params['email'];
    $response->unique_email_id = '3cdee7fb82';
    $response->web_id = '205197020';
    $response->email_type = 'html';
    $response->status = $params['status'] ?? 'pending';
    $response->merge_fields = new \stdClass();

    $allMergeFields = array_merge( self::defaultMergeFields(), $params['mergeFields'] );
    foreach( $allMergeFields as $key => $value ) {
      $response->merge_fields->$key = $value;
    }

    $response->stats = new \stdClass();
    $response->stats->avg_open_rate = 0;
    $response->stats->avg_click_rate = 0;
    
    $response->ip_signup = '213.186.17.146';
    $response->timestamp_signup = '2020-10-28T13:46:46+00:00';
    $response->ip_opt = '';
    $response->timestamp_opt = '';
    $response->member_rating = 2;
    $response->last_changed = '2020-10-28T13:46:46+00:00';
    $response->language = '';
    $response->vip = '';
    $response->email_client = '';
    $response->location = new \stdClass();
    $response->location->latitude = new \stdClass();
    $response->location->latitude = 0;
    $response->location->longitude = 0;
    $response->location->gmtoff = 0;
    $response->location->dstoff = 0;
    $response->location->country_code = '';
    $response->location->timezone = '';
    $response->source = 'API - Generic';
    $response->tags_count = 2;
    $tag_1 = new stdClass();
    $tag_1->id = 280164;
    $tag_1->name = self::MOCK_TAG_1;
    $tag_2 = new stdClass();
    $tag_2->id = 280216;
    $tag_2->name = self::MOCK_TAG_2;
    $response->tags = [
      $tag_1,
      $tag_2
    ];

    $response->list_id = $params['listId'] ?? 'invalid-list-id';
    $response->_links = [
      (object) [
        'rel' => 'self',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a',
        'method' => 'GET',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Response.json',
      ],
      (object) [
        'rel' => 'self',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a',
        'method' => 'GET',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Response.json',
      ],
      (object) [
        'rel' => 'parent',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members',
        'method' => 'GET',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/CollectionResponse.json',
        'schema' => 'https://us5.api.mailchimp.com/schema/3.0/CollectionLinks/Lists/Members.json',
      ],
      (object) [
        'rel' => 'update',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a',
        'method' => 'PATCH',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Response.json',
        'schema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/PATCH.json',
      ],
      (object) [
        'rel' => 'upsert',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a',
        'method' => 'PUT',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Response.json',
        'schema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/PUT.json',
      ],
      (object) [
        'rel' => 'delete',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a',
        'method' => 'DELETE',
      ],
      (object) [
        'rel' => 'activity',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a/activity',
        'method' => 'GET',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Activity/Response.json',
      ],
      (object) [
        'rel' => 'goals',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a/goals',
        'method' => 'GET',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Goals/Response.json',
      ],
      (object) [
        'rel' => 'notes',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a/notes',
        'method' => 'GET',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Notes/CollectionResponse.json',
      ],
      (object) [
        'rel' => 'events',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a/events',
        'method' => 'POST',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Events/POST.json',
      ],
      (object) [
        'rel' => 'delete_permanent',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a/actions/delete-permanent',
        'method' => 'POST',
      ],
    ];

    return $response;
  }
}